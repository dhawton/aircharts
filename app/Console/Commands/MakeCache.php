<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'airport:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $airports = \DB::select("SELECT DISTINCT charts.icao, airports.lat, airports.lon FROM charts, airports WHERE charts.icao=airports.id OR charts.iata=airports.id");

        $output = "";
        $x = 1;

        foreach($airports as $airport) {
            $output .= "    var myLatLng = new google.maps.LatLng(" . $airport->lat . ", " . $airport->lon . ");\n";
            $output .= "    var markerOpt = { map: map, position: myLatLng, icon: '/images/map/dot.png' };\n";
            $output .= "    marker_$x = new google.maps.Marker(markerOpt);\n";
            $x++;
        }

        \Storage::disk('local')->put('airport.cache', $output);
    }
}
