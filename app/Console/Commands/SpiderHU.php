<?php

namespace App\Console\Commands;

use App\Chart;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SpiderHU extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:hu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Hungary Charts (VATSIM)';

    protected $vathuurl = "http://vacchun.hu/tart.php?menu=letoltes&page=terkep";
    protected $base_url = "http://vacchun.hu/";
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
        $data = file($this->vathuurl, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $in_charts = 0;
        $icao = $airportname = null;
        foreach ($data as $line) {
            if (preg_match("!<h3>(.+) (LH..)!", $line, $matches)) {
                $airportname = $matches[1];
                $icao = $matches[2];
            }

            if (preg_match("!href=\"(charts/[^\"]+\.pdf)\"[^>]*>([^<]+)<\/a>!i", $line, $matches) && $icao) {
                $url = $this->base_url . $matches[1];
                $chartname = $matches[2];

                if (preg_match("!SID!", $chartname)) {
                    $charttype = "SID";
                }
                elseif (preg_match("!Transition!", $chartname)) {
                    $charttype = "STAR";
                }
                elseif (preg_match("!APP!", $chartname)) {
                    $charttype = "Approach";
                }
                else {
                    $charttype = "General";
                }

                $chart = Chart::where('icao',$icao)->where('chartname', $chartname)->first();
                if (!$chart) { $chart = new Chart(); }

                $chart->icao = $icao;
                $chart->country = "HU";
                $chart->url = $url;
                $chart->charttype = $charttype;
                $chart->chartname = $chartname;
                $chart->airportname = $airportname;

                $chart->id = sha1("hu.$icao,." . $chartname);
                $chart->save();
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'HU')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
