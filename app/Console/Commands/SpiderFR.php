<?php

namespace App\Console\Commands;

use App\Chart;
use App\Models\Airport;
use Illuminate\Console\Command;

class SpiderFR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:fr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for France Charts (VATSIM)';

    protected $vatfrurl = "http://www.vatfrance.org/pilotbrief/?lang=en";

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
        $data = file($this->vatfrurl, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $in_charts = 0;
        foreach ($data as $line) {
            if (preg_match("!id=\"charts\"!", $line)) { $in_charts = 1; }
            if (preg_match("!href=\"([^\"]+\/(LF..)\/AD 2 LF.. ([^\"]+)\.pdf)\"!", $line, $matches) && $in_charts) {
                $url = $matches[1];
                $icao = $matches[2];
                $chartname = $matches[3];

                if (preg_match("!SID!", $chartname)) {
                    $charttype = "SID";
                    $chartname = str_replace("SID ", "", $chartname);
                }
                elseif (preg_match("!STAR!", $chartname)) {
                    $charttype = "STAR";
                    $chartname = str_replace("STAR ", "", $chartname);
                }
                elseif (preg_match("!IAC!", $chartname)) {
                    $charttype = "Approach";
                    $chartname = str_replace("IAC ", "", $chartname);
                }
                else {
                    $charttype = "General";
                }

                $chart = Chart::where('icao',$icao)->where('chartname', $chartname)->first();
                if (!$chart) { $chart = new Chart(); }

                $chart->icao = $icao;
                $chart->country = "FR";
                $chart->url = $url;
                $chart->charttype = $charttype;
                $chart->chartname = $chartname;

                $airport = Airport::where('id', $icao)->first();
                if ($airport) {
                    $chart->airportname = $airport->name;
                } else {
                    $chart->airportname = "Unknown";
                }

                $chart->id = sha1("fr.$icao,." . $chartname);
                $chart->save();
            }
        }
    }
}
