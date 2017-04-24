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

    protected $base_url = "http://www.pl-vacc.org.pl/pol3/";
    protected $index = "http://www.pl-vacc.org.pl/pol3/airports.php";
    protected $chart_base = "http://www.pl-vacc.org.pl/files/maps/";


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
        foreach($data as $line) {
            if (preg_match("!href=\"(airports\.php?d=EP..)\">(EP..) ([^<]+)<\/a>!i", $line, $matches)) {
                $airports[] = [
                    'url' => $matches[1],
                    'icao' => $matches[2],
                    'name' => $matches[3]
                    ];
            }
        }

        foreach($airports as $airport) {
            $chart_url = "";
            $chart_name = "";
            $data = file($this->index, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            foreach ($data as $line) {
                if (preg_match("!href=\'download\.php?ts.+&file=(ead/EP_AD_.+\.pdf)\' class=\'aip\'!i", $line, $matches)) {
                    $chart_url = $matches[1];
                }
                if (preg_match("!\s+>(.+)<\/a>!", $line, $matches) && $chart_url) {
                    $chart_name = $matches[1];
                    $chart = Chart::where('icao', $airport['icao'])->where('chartname', $chart_name)->first();
                    if (!$chart) { $chart = new Chart(); }
                    $chart->icao = $airport['icao'];
                    $chart->airportname = utf8_encode($airport['name']);
                    $chart->country = "PL";
                    $chart->chartname = $chart_name;
                    if (preg_match("!SID!", $chart_name)) {
                        $chart->charttype = "SID";
                    } elseif (preg_match("!STAR!", $chart_name)) {
                        $chart->charttype = "STAR";
                    } elseif (preg_match("!(GNSS|NDB|ILS|VOR|Visual)!", $chart_name)) {
                        $chart->charttype = "Approach";
                    } else {
                        $chart->charttype = "General";
                    }
                    $chart->id = sha1("pl." . $airport['icao'] . ",." . $chart_name);
                    $chart->url = $this->chart_base . $chart_url;
                    $chart->save();
                }
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'PL')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
