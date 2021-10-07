<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Strava\API\Client;
use Strava\API\OAuth;
use Strava\API\Service\REST;

class StravaController extends Controller
{
    public function index()
    {
        $options = [
            'clientId'     => config('strava.client_id'),
            'clientSecret' => config('strava.secret'),
            'redirectUri'  => 'http://sup.loc'
        ];
        $oauth = new OAuth($options);

        if (!isset($_GET['code'])) {
            print '<a href="'.$oauth->getAuthorizationUrl([
                    // Uncomment required scopes.
                    'scope' => [
                        'public',
                        // 'write',
                        // 'view_private',
                    ]
                ]).'">Connect</a>';
        } else {
            $token = $oauth->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
            $service = new REST($token->getToken(), $adapter);  // Define your user token here.
            $client = new Client($service);

            $athlete = $client->getAthlete();
            print_r($athlete);

            $activities = $client->getAthleteActivities();
            print_r($activities);
        }
    }

    public function check()
    {
        $accessToken = '84155bf039292cb36474f03c7d33e96b64f39051';
        $refreshToken = '6a5175d340cc989802d74350354938a9c7293a4c';

        $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        $service = new REST($accessToken, $adapter);  // Define your user token here.
        $client = new Client($service);

        echo "<pre>";
        $athlete = $client->getAthlete();
        //print_r($athlete);

        //$activities = $client->getAthleteActivities();
        //print_r($activities);

        //$lastActivity = $activities[0];

        $routes = $client->getAthleteRoutes($athlete['id']);

        var_dump($routes);

        //$res = $client->getRouteAsGPX($lastActivity['id']);

        //var_dump($res);
        die();
    }
}
