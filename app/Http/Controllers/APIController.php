<?php

namespace App\Http\Controllers;

use App\Chart;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public function getAirport(Request $request, $data) {
        $format = "json";
        $data = str_replace(" ", "", $data);
        if (preg_match("!\.(xml|json)$!", $data, $matches)) {
            $format = $matches[1];
        }
        if (preg_match("!^(.*)\.!", $data, $matches)) {
            $list = $matches[1];
        } else {
            $list = $data;
        }

        $list = explode(",", $list);
        if (!is_array($list)) { $list[0] = $list; }
        $output = [];
        for($i = 0 ; isset($list[$i]) ; $i++) {
            $airportdef = false; $id = $list[$i];
            foreach (Chart::where('icao', $list[$i])->orWhere('iata', $list[$i])->get() as $chart) {
                if (!$airportdef) {
                    $output[$id]['info'] = [
                        'icao' => $chart->icao,
                        'iata' => $chart->iata,
                        'name' => $chart->airportname,
                    ];
                    $airportdef = true;
                }
                $output[$id]['charts'][] = [
                    'id' => $chart->id,
                    'type' => $chart->charttype,
                    'name' => $chart->chartname,
                    'url' => $chart->url
                ];
            }
        }

        if ($format == "json") {
            return response()->json($output);
        }
        if ($format == "xml") {
            echo "<?xml version=\"1.0\"?>\n<aircharts>";
            foreach($output as $key) {
                if (is_array($key)) {
                    echo "<airport icao=\"" . $key['info']['icao'] . "\"";
                    echo " iata=\"" . $key['info']['iata'] . "\" name=\"" . htmlspecialchars($key['info']['name']) . "\">";
                    foreach($key['charts'] as $ck) {
                        echo "<chart id=\"" . $ck['id'] . "\" type=\"" . $ck['type'] . "\" name=\"" . htmlspecialchars($ck['name']) . "\">";
                        echo $ck['url'] . "</chart>";
                    }
                    echo "</airport>";
                }
            }
            echo "</aircharts>";
        }
    }
}
