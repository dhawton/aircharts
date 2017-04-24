<?php

namespace App\Console\Commands;

use App\Chart;
use Illuminate\Console\Command;

class SpiderIR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:ir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Ireland Charts (real)';

    protected $index = "http://iaip.iaa.ie/iaip/aip_directory.htm";
    protected $base_url = "http://iaip.iaa.ie/iaip/";
    protected $airac_url = "http://iaip.iaa.ie/iaip/IAIP_Banner.htm";

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
        $due = \Storage::disk('local')->get('spider.ir.date');

        $airac = file($this->airac_url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($airac as $line) {
            if (preg_match("!(\d+ [A-Z]+ \d+)<\/p!i", $line, $matches)) {
                $airac_date = $matches[1];
            }
        }

        if ($due != $airac_date) { echo "AIRAC Date mismatch, $due v $airac_date, running\n"; }
        else { return; }

        $data = file($this->index, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($data as $line) {
            if (preg_match("!href=\"(aip_(ei..)_charts\.htm)\">(.+) Chart Information<\/!i", $line, $matches)) {
                $airports[] = [
                    'icao' => strtoupper($matches[2]),
                    'url' => $this->base_url . $matches[1],
                    'name' => $matches[3]
                ];
            }
        }

        foreach ($airports as $airport) {
            $data = file($airport['url'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $chart_title = ""; $catch_rest = false;
            foreach($data as $line) {
                if (preg_match("!p class=MsoHeading7>([^<]+)<\/p!i", $line, $matches)) {
                    $chart_title = $matches[1];
                    $catch_rest = false;
                } elseif (preg_match("!p class=MsoHeading7>(.+)!i", $line, $matches)) {
                    $chart_title = $matches[1];
                    $catch_rest = true;
                } elseif (preg_match("!(.+)</p>!i", $line, $matches) && $catch_rest) {
                    $chart_title .= $matches[1];
                    $catch_rest = false;
                }
                if (preg_match("!href=\"(Published.+\.pdf)\">EI..!i", $line, $matches)) {
                    $chart_title = preg_replace("!\s+!", " ", trim($chart_title));
                    $chart = Chart::where("icao", $airport['icao'])->where('chartname', $chart_title)->first();
                    if (!$chart) { $chart = new Chart(); }

                    $chart->icao = $airport['icao'];
                    $chart->airportname = $airport['name'];
                    $chart->chartname = $chart_title;
                    $chart->charttype = "General";
                    $chart->country = "IR";

                    if (preg_match("!Departure!", $chart_title)) {
                        $chart->charttype = "SID";
                    } elseif (preg_match("!Arrival!", $chart_title)) {
                        $chart->charttype = "STAR";
                    } elseif (preg_match("!Approach!", $chart_title)) {
                        $chart->charttype = "Approach";
                    }

                    $chart->url = $this->base_url . $matches[1];
                    $chart->id = sha1("ir." . $airport['icao'] . ",." . $chart_title);
                    $chart->save();

                    $chart_title = "";
                    $chart_rest = false;
                }
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'DE')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
