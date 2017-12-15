<?php

namespace App\Http\Controllers;

use App\Chart;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ACController extends Controller
{
    public function getIndex() {
      if (\Storage::disk("local")->has("airport.cache")) {
        $mappoints = \Storage::disk('local')->get('airport.cache');
      } else {
        $mappoints = "";
      }

        return view('index', ['mappoints' => $mappoints]);
    }

    public function postCharts(Request $request) {
        $query = $request->input("query");
        $query = str_replace(" ", "", $query);
        $airports = explode(",", $query);
        $results = [];
        $errors = null;
        foreach($airports as $ap) {
            $airport = Chart::where('icao', $ap)->orWhere('iata', $ap)->first();
            if (!$airport) {
                if (!$errors) $errors = "Airport(s) not found: $ap";
                else $errors .= ", $ap";
            } else {
                $results[] = [
                    'icao' => $airport->icao,
                    'iata' => $airport->iata,
                    'name' => $airport->airportname
                ];
            }
        }

        return view('charts', ['error' => $errors, 'results' => $results]);
    }

    public function getView($id) {
        $chart = Chart::find($id);
        if (!$chart) abort(404);

        $filename = $id . ".pdf";
        return Response::make(file_get_contents($chart->url), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}
