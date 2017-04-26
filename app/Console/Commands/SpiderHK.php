<?php

namespace App\Console\Commands;

use App\Chart;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SpiderHK extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:hk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Hong Kong Charts (Realworld)';

    protected $index = "http://www.hkatc.gov.hk/HK_AIP/ad.htm";
    protected $base_url = "http://www.hkatc.gov.hk/HK_AIP/";
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
        $in_charts = 0;
        $icao = $airportname = null;
        foreach ($data as $line) {
            if (preg_match("!openBrWindow\('(AIP/AD/HK_AD2\-[^\"]+\.pdf)\'\)[^>]*>AD 2-[0-9A-Z]+ ([^<]+)<\/a>!i", $line, $matches)) {
                $icao = "VHHH";
                $airportname = "Hong Kong International";
                $url = $this->base_url . $matches[1];
                $chartname = $matches[2];

                if (preg_match("!SID!", $chartname)) {
                    $charttype = "SID";
                }
                elseif (preg_match("!STAR!", $chartname)) {
                    $charttype = "STAR";
                }
                elseif (preg_match("!Approach Chart!", $chartname)) {
                    $charttype = "Approach";
                }
                else {
                    $charttype = "General";
                }

                $chart = Chart::where('icao',$icao)->where('chartname', $chartname)->first();
                if (!$chart) { $chart = new Chart(); }

                $chart->icao = $icao;
                $chart->country = "HK";
                $chart->url = $url;
                $chart->charttype = $charttype;
                $chart->chartname = $chartname;
                $chart->airportname = $airportname;

                $chart->id = sha1("hk.$icao,." . $chartname);
                $chart->save();
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'HK')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
