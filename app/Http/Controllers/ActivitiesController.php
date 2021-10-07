<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Training;
use App\Models\User;
use App\Utilities\ChartHelper;
use App\Utilities\GpxAnalyzer;
use App\Utilities\GpxStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ActivitiesController extends Controller
{
    /**
     * @var GpxStorage
     */
    protected $gpxStorage;

    /**
     * @var GpxAnalyzer
     */
    protected $gpxAnalyzer;

    /**
     * @param GpxStorage $gpxStorage
     * @param GpxAnalyzer $gpxAnalyzer
     */
    public function __construct(GpxStorage $gpxStorage, GpxAnalyzer $gpxAnalyzer)
    {
        $this->gpxStorage = $gpxStorage;

        $this->gpxAnalyzer = $gpxAnalyzer;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        return view('activities.index', ['activities' => $user->activities]);
    }

    /**
     * @param Activity $activity
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(Activity $activity)
    {
        $data = $this->gpxAnalyzer->analyze($this->gpxStorage->getFullPath($activity->gpx_file));

        \JavaScript::put([
            'pageData' => [
                'chart_data' => ChartHelper::prepareLineChartData($data['points'], 'total_distance', 'speed'),
                'data' => $data,
                'activity' => $activity->toArray(),
            ],
        ]);

        return view('activities.show', [
            'data' => $data,
            'activity' => $activity,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function uploadGpx()
    {
        return view('activities.upload_gpx', [
            'activityTypes' => Activity::getActivityTypes(),
            'trainingTypes' => Training::getTrainingTypes(),
        ]);
    }

    public function storeGpx(Request $request)
    {
        if ($gpxFileName = $this->gpxStorage->store($request->file('gpx'))) {
            $data = $this->gpxAnalyzer->analyze($this->gpxStorage->getFullPath($gpxFileName));
            $distance = Arr::get($data, 'stats.distance_km', null);
            $avgSpeed = Arr::get($data, 'stats.average_speed_kmh', null);
            $duration = Arr::get($data, 'stats.duration_m', null);

            Activity::create([
                'user_id' => auth()->user()->id,
                'name' => $request->get('name', ''),
                'activity_type' => $request->get('activity_type', null),
                'training_type' => $request->get('training_type', null),
                'activity_date' => Carbon::parse($data['stats']['started_at_timestamp'])->format('Y-m-d H:i:s'),
                'gpx_file' => $gpxFileName,
                'distance' => $distance ? (int) $distance : null,
                'avg_speed' => $avgSpeed ? (float) $avgSpeed : null,
                'duration' => $duration ? (int) $duration : null,
            ]);
        }

        return redirect(route('activities.upload_gpx'));
    }
}
