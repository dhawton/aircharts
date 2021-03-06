<?php

namespace App\Http\Controllers;

use App\Chart;
use App\Models\Airport;
use Illuminate\Http\Request;

class APIv2Controller extends Controller
{
    public function getAirport($data)
    {
        $data = str_replace(" ", "", strtoupper($data));
        if (preg_match("![^A-Z,]+!", $data)) {
            return response()->json(['status' => 'error', 'msg' => 'Malformed Request'], 400);
        }

        if (preg_match('!,!', $data)) {
            $aps = explode(",", $data);
        } else {
            $aps[] = $data;
        }

        $output = [
        ];
        $error = 0;
        foreach ($aps as $ap) {
            $airport = Airport::where('id', $ap)->first();
            if (!$airport) {
                $output[$ap] = "Not Found";
                $error = 1;
                continue;
            }
            $output[$ap]['info'] = [
                'id' => $ap,
                'name' => $airport->name,
                'latitude' => $airport->lat,
                'longitude' => $airport->lon,
                'elevation' => $airport->elevation
            ];
            $groups = ['General', 'SID', 'STAR', 'Intermediate', 'Approach'];
            foreach ($groups as $group) {
                $charts = Chart::where(function ($query) use ($ap) {
                    $query->where('icao', $ap);
//                    $query->orWhere('iata', $ap);
                })->where('charttype', $group)->orderBy('chartname')->get();
                foreach ($charts as $chart) {
                    $output[$ap]["charts"][$group][] = [
                        'id' => $chart->id,
                        'chartname' => $chart->chartname,
                        'url' => $chart->url,
                        'proxy' => "https://www.aircharts.org/view/" . $chart->id
                    ];
                }
            }
        }

        if (count($aps) == 1 && $error) {
            return response()->json($output, 404);
        }

        return response()->json($output);
    }

    public function getCharts($ap)
    {
        $output = [
        ];
        $error = 0;
        $airport = Airport::where('id', $ap)->first();
        if (!$airport) {
            return response()->json(["error" => "Not Found"],404);
            }
        $output['info'] = [
            'id' => $ap,
            'name' => $airport->name,
            'latitude' => $airport->lat,
            'longitude' => $airport->lon,
            'elevation' => $airport->elevation
        ];
        $groups = ['General', 'SID', 'STAR', 'Intermediate', 'Approach'];
        foreach ($groups as $group) {
            $charts = Chart::where(function ($query) use ($ap) {
                $query->where('icao', $ap);
            })->where('charttype', $group)->orderBy('chartname')->get();
            foreach ($charts as $chart) {
                $output["charts"][$group][] = [
                    'id' => $chart->id,
                    'chartname' => $chart->chartname,
                    'url' => $chart->url,
                    'proxy' => "https://www.aircharts.org/view/" . $chart->id
                ];
            }
        }

        return response()->json($output);
    }
}
