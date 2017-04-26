<?php

namespace App\Console\Commands;

use App\Chart;
use App\Models\Airport;
use Illuminate\Console\Command;

class SpiderPT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:pt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Portugal Charts (VATSIM)';

    protected $url = "http://charts.portugal-vacc.org";

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
        $data = file($this->url, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $airports = [];
        foreach($data as $line) {
            if (preg_match("!href=\"(files\/LP_AD_2.+\.pdf)\">(LP..).+<\/a><td>(.+)$!", $line, $matches)) {
                $chart_url = $matches[1];
                $icao = $matches[2];
                $chart_name = $matches[3];

                $chart = Chart::where('icao', $icao)->where("chartname", $chart_name)->first();
                if (!$chart) { $chart = new Chart(); }

                if (!isset($airports[$icao])) {
                    $airport = Airport::where('icao', $icao)->first();
                    $airports[$icao] = $airport->name;
                }
                $chart->id = sha1("pt.$icao,.$chart_name");
                $chart->charttype = "General";
                if (preg_match("!SID!", $chart_name)) { $chart->charttype = "SID"; }
                if (preg_match("!STAR!", $chart_name)) { $chart->charttype = "STAR"; }
                if (preg_match("!IAC!", $chart_name)) { $chart->charttype = "Approach"; }
                if (preg_match("!Approach Chart!", $chart_name)) { $chart->charttype = "Approach"; }
                $chart->url = $this->url . "/" . $chart_url;
                $chart->icao = $icao;
                $chart->airportname = $airports[$icao];
                $chart->country = "PT";
                $chart->chartname = $chart_name;
                $chart->save();
            }
        }
    }
}
