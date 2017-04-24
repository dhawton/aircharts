<?php

namespace App\Console\Commands;

use App\Chart;
use Carbon\Carbon;
use Illuminate\Console\Command;


class SpiderPL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:pl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Poland charts (VATSIM)';

    protected $index = "http://www.pl-vacc.org.pl/pol3/charts_vateud/index_aircharts.txt";

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
            $chart->airportname = utf8_encode($d[AP_NAME]);
            $chart->country = "PL";
            $chart->chartname = utf8_encode($d[CHART_NAME]);
            if ($d[CHART_TYPE] == "Arrival") {
                $chart->charttype = "STAR";
            } elseif ($d[CHART_TYPE] == "Approach") {
                $chart->charttype = "Approach";
            } elseif ($d[CHART_TYPE] == "Departure") {
                $chart->charttype = "SID";
            } else {
                $chart->charttype = "General";
            }
            $chart->url = $d[CHART_URL];
            $chart->id = sha1("pl." . $chart->icao . ",." . $d[CHART_NAME]);
            $chart->save();
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'PL')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
