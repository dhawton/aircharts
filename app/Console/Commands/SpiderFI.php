<?php

namespace App\Console\Commands;

use App\Chart;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SpiderFI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:fi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Finland Charts (Realworld)';

    protected $index = "https://ais.fi/ais/eaip/en/tree_items.js";
    protected $base_url = "https://ais.fi/ais/eaip/";
    protected $airport_base = "https://ais.fi/ais/eaip/html/";

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
        $airports = [];
        $data = file($this->index, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        foreach ($data as $line) {
            if (preg_match("!..\/(aip\/ad\/ad2\/rus\/(u...).+\.pdf)\",\"\(\d+\) (.+)\"!", $line, $matches)) {
                $url = $matches[1];
                $icao = strtoupper($matches[2]);
                $chart_name = $matches[3];

                if (!isset($airports[$icao])) {
                    $airport = Airport::where('id', $icao)->first();
                    if (!$airport) { $airports[$icao] = "Unknown"; echo "Unknown Airport Encountered, $icao"; }
                    else { $airports[$icao] = $airport->name; }
                }

                $chart = Chart::where('icao', $icao)->where('chartname', $chart_name)->first();
                if (!$chart) { $chart = new Chart(); }
                $chart->id = sha1("ru.$icao,.$chart_name");
                $chart->icao = $icao;
                $chart->chartname = utf8_encode($chart_name);
                $chart->charttype = "General";
                if (preg_match("!DEPARTURE!i", $chart_name)) { $chart->charttype = "SID"; }
                if (preg_match("!ARRIVAL!i", $chart_name)) { $chart->charttype = "STAR"; }
                if (preg_match("!APPROACH!i", $chart_name)) { $chart->charttype = "Approach"; }
                $chart->url = $this->base_url . $url;
                $chart->airportname = utf8_encode($airports[$icao]);
                $chart->country = "RU";
                $chart->save();
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'PT')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
