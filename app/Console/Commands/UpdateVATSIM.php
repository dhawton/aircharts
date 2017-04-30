<?php

namespace App\Console\Commands;

use App\Models\Flight;
use App\Models\Positions;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

define('callsign', 0);
define('cid', 1);
define('realname', 2);
define('clienttype', 3);
define('frequency', 4);
define('latitude', 5);
define('longitude', 6);
define('altitude', 7);
define('groundspeed', 8);
define('planned_aircraft', 9);
define('planned_tascruise', 10);
define('planned_depairport', 11);
define('planned_altitude', 12);
define('planned_destairport', 13);
define('server', 14);
define('protrevision', 15);
define('rating', 16);
define('transponder', 17);
define('facilitytype', 18);
define('visualrange', 19);
define('planned_revision', 20);
define('planned_flighttype', 21);
define('planned_deptime', 22);
define('planned_actdeptime', 23);
define('planned_hrsenroute', 24);
define('planned_minenroute', 25);
define('planned_hrsfuel', 26);
define('planned_minfuel', 27);
define('planned_altairport', 28);
define('planned_remarks', 29);
define('planned_route', 30);
define('planned_depairport_lat', 31);
define('planned_depairport_lon', 32);
define('planned_destairport_lat', 33);
define('planned_destairport_lon', 34);
define('atis_message', 35);
define('time_last_atis_received', 36);
define('time_logon', 37);
define('heading', 38);
define('QNH_iHg', 39);
define('QNH_Mb', 40);

class UpdateVATSIM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateVATSIM';

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
        if (env('VATTRACK_DATAFILE', null) == null) {
            Log::critical("VATTRACK: No datafile defined, cannot continue");
            return;
        }

        if (config('vattrack.debug')) echo "Firing UpdateVATSIM\nChecking timestamp... ";

        // Make sure file is fully uploaded
        $ready = false; $count = 0;
        while (!$ready) {
            exec("sed -n \"/;   END/p\" " . env('VATTRACK_DATAFILE'), $output);
            if (isset($output[0]) && $output[0] == ";   END") {
                $ready = true;
            }
            else {
                $count++;
                if ($count >= 10) { echo "Failed."; exit; }
            }
        }

        unset($output);
        exec("sed -n -r \"s/UPDATE = ([0-9]+)/\\1/p\" " . env('VATTRACK_DATAFILE'), $output);
        $cur = config('vattrack.lasttsfile');

        if ($output[0] == $cur && config('vattrack.usets')) {
            if (config('vattrack.debug')) echo "Done, ts matched, discontinuing.\n";
            return;
        }

        $current_update = $output[0];

        $fp = fopen(config('vattrack.lasttsfile'), "w");
        fwrite($fp, $output[0]);
        fclose($fp);

        if (config('vattrack.debug')) echo "Saved TS cache.\n";

        $stream = file(env('VATTRACK_DATAFILE'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $in_clients = 0;
        foreach ($stream as $line) {
            if (preg_match("/^!CLIENTS:/", $line)) {
                $in_clients = 1;
                continue;
            }
            elseif (preg_match("/^!/", $line) && $in_clients) $in_clients = 0;

            if (!$in_clients || preg_match("/^;/", $line)) continue;


            $data = explode(":", $line);
            if (!$data[cid]) continue;
            if($data[clienttype] == "ATC") continue;
            if(!$data[planned_depairport] || !$data[planned_destairport] || !$data[planned_route]) continue;
            if(!$data[latitude] || !$data[longitude]) continue;

            // Reformat some variables.
            $data[callsign] = str_replace("-", "", $data[callsign]); // Remove -'s from callsigns
            $data[planned_altitude] = preg_replace("/[FAL]+/", "", $data[planned_altitude]); // Change ICAO and FL350 altitude entries into VATSIM-standard
            if (strlen($data[planned_altitude]) < 4) $data[planned_altitude] = $data[planned_altitude] . "00"; // Make altitudes consistent
            $data[planned_route] = str_replace("DCT", "", $data[planned_route]);
            $data[planned_route] = preg_replace("/[\+\.]/", " ", $data[planned_route]); // Ignore VATSIM ATC amendment and misc. markings
            $data[planned_route] = preg_replace("/\s+/", " ", trim($data[planned_route])); // Remove extra spaces

            $new = 0;
            $flight = Flight::where('callsign', $data[callsign])->where('vatsim_id', $data[cid])->orderBy("created_at", "DESC")->first();
            if (!$flight || ($flight->status == "Arrived" && !$flight->checkArrival())) {
                // Ignore the flight unless they are not airborne and are within their departure airport
                if ($flight && !$flight->checkDeparture()) {
                    continue;
                }
                $new = 1;
                $flight = new Flight();
                $flight->callsign = $data[callsign];
                $flight->vatsim_id = $data[cid];
                $flight->status = "Departing Soon";
            }

            // Update Flight Plan details (in case they updated on their end)
            $flight->lat = $data[latitude];
            $flight->lon = $data[longitude];
            $flight->alt = $data[altitude];
            $flight->hdg = $data[heading];
            $flight->spd = $data[groundspeed];
            $flight->route = $data[planned_route];
            $flight->remarks = $data[planned_remarks];

            // Set aircraft, ensure to filter out things like (2H/) and (/L)
            if (preg_match("/^(?:.\/)?([^\/]+)(?:\/.)?/", $data[planned_aircraft], $matches)) {
                $flight->aircraft_type = substr($matches[1], 0, 4);
            } else {
                $flight->aircraft_type = $data[planned_aircraft];
            }

            // Update flight plan details in case flight plan has changed
            $flight->departure = substr($data[planned_depairport], 0, 4);
            $flight->arrival = substr($data[planned_destairport], 0, 4);
            $flight->planned_alt = $data[planned_altitude];

            if ($new == 1) {
                if (!$flight->checkDeparture()) continue; // Skip non-departing flights
            }

            // Check the status now
            if ($flight->status == "En-Route" && $flight->checkArrival()) {
                $flight->status = "Arrived";
            } elseif ($flight->status == "Unknown") {
                // Check if at Departure Airport
                if ($flight->checkDeparture()) {
                    $flight->status = "Departing Soon";
                } elseif ($flight->checkArrival()) {
                    $flight->status = "Arrived";
                } elseif ($flight->airborne()) {
                    $flight->status = "En-Route";
                } else {
                    $flight->status = "Unknown";
                }
            } elseif ($flight->status == "Departing Soon" && $flight->airborne()) {
                $flight->status = "En-Route";
            }
            $flight->last_update = $current_update;
            $flight->missing_count = 0;
            $flight->save();

            $pos = Positions::where('flight_id', $flight->id)->orderBy('created_at','DESC')->first();
            if ($pos->created_at->diffInMins(\Carbon::now()) >= 10) {
                $position = new Positions();
                $position->flight_id = $flight->id;
                $position->lat = $data[latitude];
                $position->lon = $data[longitude];
                $position->alt = $data[altitude];
                $position->spd = $data[groundspeed];
                $position->save();
            }
        }

        foreach (Flight::where('status', 'NOT LIKE', 'Arrived')->where('last_update','<',$current_update)->get() as $flight) {
            $flight->missing_count += 1;
            $flight->save();

            if ($flight->missing_count >= 5) {
                $flight->delete();
            }
        }
    }
}
