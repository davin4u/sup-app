<?php

namespace App\Http\Controllers\API;

use App\Gps\Gpx\GpxParser;
use App\Http\Controllers\Controller;
use App\Models\Activity;
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
     * @param Request $request
     * @param GpxStorage $gpxStorage
     */
    public function __construct(Request $request, GpxStorage $gpxStorage)
    {
        $this->request = $request;
        $this->gpxStorage = $gpxStorage;
    }

    /**
     * @param Activity $activity
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityData(Activity $activity)
    {
        $start = $this->request->query('start', null);
        $end = $this->request->query('end', null);

        $data = (new GpxParser($this->gpxStorage->getFullPath($activity->gpx_file)))
            ->slice($start, $end)
            ->transform();

        return response()->json([
            'data' => $data,
        ]);
    }
}
