<?php

namespace App\Console\Commands;

use App\Chart;
use Illuminate\Console\Command;

class SpiderUK extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:uk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for UK Charts';

    protected $base_url = "http://www.nats-uk.ead-it.com/public/";
    protected $index_url = "index.php%3Foption=com_content&task=blogcategory&id=6&Itemid=13.html";

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
        $x = 0; // For testing purposes, only do ~10 charts.
        $index = file($this->base_url . $this->index_url, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        foreach ($index as $index_line) {
            if (preg_match("!href=\"([^\"]+)\">(.+) - (EG[A-Z]{2})<\/a>!", $index_line, $matches)) {
                $url = $this->base_url . $matches[1];
                $name = $matches[2];
                $icao = $matches[3];
                $airportfile = file($url, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
                foreach ($airportfile as $line) {
                    if (preg_match("!class=\"desc\"[^>]*><a target=\"_blank\" href=\"([^\"]+)\">(.+)\s+?-?\s+?(ICAO)?<\/a>!", $line, $matches)) {
                        if ($x == 10) { exit; }
                        $charturl = $matches[1];
                        $chartname = $matches[2];
                        $charttype = "General";
                        if (preg_match("!INSTRUMENT APPROACH (CHART|PROCEDURE|PROCEDURES) (.+)$!", $chartname, $matches)) {
                            $charttype = "Approach";
                            $chartname = $matches[2];
                        }
                        if (preg_match("!STANDARD DEPARTURE CHART\s+-\s+(.+)$!", $chartname, $matches)) {
                            $charttype = "SID";
                            $chartname = $matches[1];
                        }
                        if (preg_match("!STANDARD ARRIVAL CHART\s+-\s+(.+)$!", $chartname, $matches)) {
                            $charttype = "STAR";
                            $chartname = $matches[1];
                        }
                        if (preg_match("!INITIAL APPROACH PROCEDURES\s+-\s+(.+)$!", $chartname, $matches)) {
                            $charttype = "Intermediate";
                            $chartname = $matches[1];
                        }
                        $pdf = file_get_contents($charturl);
                        $chart = Chart::where('icao', $icao)->where('chartname', $chartname)->first();
                        if (!$chart) {
                            $chart = new Chart();
                        }
                        $chart->id = sha1("uk.$icao,." . $chartname);
                        $chart->icao = $icao;
                        $chart->country = "UK";
                        $chart->airportname = $name;
                        $chart->chartname = $chartname;
                        $chart->charttype = $charttype;
                        \Storage::disk('s3')->put("/uk/" . $chart->id . ".pdf", file_get_contents($charturl), "public");
                        $chart->url = "http://awsir.aircharts.org/uk/" . $chart->id . ".pdf";
                        $chart->save();
                        $x++;
                    }
                }
            }
        }
    }
}
