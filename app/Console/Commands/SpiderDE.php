<?php

namespace App\Console\Commands;

use App\Chart;
use Illuminate\Console\Command;

define("ICAO", 0);
define("AP_NAME", 1);
define("CHART_TYPE", 2);
define("CHART_NAME", 3);
define("CHART_URL", 10);

class SpiderDE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:de';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Germany charts (VATSIM)';

    protected $index = "http://www.vacc-sag.org/charts_vateud_local_index.txt";

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
        foreach($data as $line) {
            $d = explode("|", $line);

            if (strtoupper($d[CHART_TYPE]) == "PACKAGE") continue;

            $chart = Chart::where('icao', $d[ICAO])->where('chartname', $d[CHART_NAME])->first();
            if (!$chart) {
                $chart = new Chart();
            }

            $chart->icao = $d[ICAO];
            $chart->airportname = $d[AP_NAME];
            $chart->country = "DE";
            $chart->chartname = $d[CHART_NAME];
            if ($d[CHART_TYPE] == "Arrival") {
                $chart->charttype = "STAR";
            } elseif ($d[CHART_TYPE] == "Approach") {
                $chart->charttype = "Approach";
            } elseif ($d[CHART_TYPE] == "Departure") {
                $chart->charttype = "SID";
            } else {
                $chart->charttype = "General";
            }
            $chart->url = $d[URL];
            $chart->id = sha1("de." . $chart->icao . ",." . $d[CHART_NAME]);
            $chart->save();
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'DE')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
