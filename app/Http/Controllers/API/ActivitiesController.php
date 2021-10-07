<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Utilities\GpxAnalyzer;
use App\Utilities\GpxStorage;
use Illuminate\Http\Request;

class ActivitiesController extends Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var GpxStorage
     */
    protected $gpxStorage;

    /**
     * @var GpxAnalyzer
     */
    protected $gpxAnalyzer;

    /**
     * @param Request $request
     * @param GpxStorage $gpxStorage
     * @param GpxAnalyzer $gpxAnalyzer
     */
    public function __construct(Request $request, GpxStorage $gpxStorage, GpxAnalyzer $gpxAnalyzer)
    {
        $this->request = $request;
        $this->gpxStorage = $gpxStorage;
        $this->gpxAnalyzer = $gpxAnalyzer;
    }

    /**
     * @param Activity $activity
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityData(Activity $activity)
    {
        $start = $this->request->query('start', null);
        $end = $this->request->query('end', null);

        $data = $this->gpxAnalyzer->analyze(
            $this->gpxStorage->getFullPath($activity->gpx_file),
            $start,
            $end
        );

        return response()->json([
            'data' => $data,
        ]);
    }
}
