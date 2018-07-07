<?php

namespace App\Console\Commands;

use App\Chart;
use Carbon\Carbon;
use Illuminate\Console\Command;


class SpiderLV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:lv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Latvia charts (VATSIM)';
//view-source:https://www.lv-vacc.org/index.php/pilots/charts
    protected $base_url = "https://www.lv-vacc.org";
    protected $index = "https://www.lv-vacc.org/index.php/pilots/charts";


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
        $data = file($this->index, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $airports = [];

        $icao = ""; $name = "";

        foreach ($data as $line) {
            if (preg_match("!>([^(]+) \((EV[A-Z]{2})\)!i", $line, $matches)) {
                $icao = $matches[2];
                $name = $matches[1];
            }
            if (preg_match("!>Enroute airspace!i", $line)) {
                break;
            }
            elseif (preg_match("!(\/CHARTS.+\.pdf)\"[^>]+>(.+)<\/a>!", $line, $matches)) {
                if ($icao == "" || $name == "") {
                    print "Got a chart matches with no known icao or airport on $line\n";
                    exit;
                }

                $url = $matches[1];
                $cname = $matches[2];

                $chart = Chart::where('icao', $icao)->where('chartname', $cname)->first();
                if (!$chart) $chart = new Chart();
                $chart->icao = $icao;
                $chart->airportname = strip_tags(utf8_encode($name));
                $chart->country = "LV";
                $chart->chartname = strip_tags(utf8_encode($cname));
                $chart->charttype = "General";
                if (preg_match("!SID!", $cname)) {
                    $chart->charttype = "SID";
                }
                if (preg_match("!STAR!", $cname)) {
                    $chart->charttype = "STAR";
                }
                if (preg_match("!ILS!", $cname) || preg_match("!VOR!", $cname) ||
                    preg_match("!LOC!", $cname) || preg_match("!VISUAL APPROACH!i", $cname) ||
                    preg_match("!GPS!", $cname)) {
                    $chart->charttype = "Approach";
                }

                $chart->id = sha1("lv.$icao,.$cname");
                $chart->url = $this->base_url . $url;
                $chart->save();
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'LV')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
