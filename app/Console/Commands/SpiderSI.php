<?php

namespace App\Console\Commands;

use App\Chart;
use Carbon\Carbon;
use Illuminate\Console\Command;


class SpiderSI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:si';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Slovenia charts (RW)';
//view-source:https://www.lv-vacc.org/index.php/pilots/charts
    protected $airacheader = "https://www.sloveniacontrol.si/acrobat/aip/Operations/history-en-GB.html";
    protected $base_url = "https://www.sloveniacontrol.si/acrobat/aip/Operations/";
    private $airports = [
        "LJLJ" => "Ljubljana/Brnik",
        "LJMB" => "Maribor/Orehova VAS",
        "LJPZ" => "Portoroz/Secovlje",
        "LJCE" => "Cerklje Ob Krki"
    ];
    protected $airport_tmpl = "/html/eAIP/LJ-AD-2.%icao%-en-GB.html#AD-2.%icao%";


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
        //$data = file($this->index, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        // Filter for current AIRAC
        $current = false;
        $data = file($this->airacheader, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        foreach ($data as $line) {
            if (preg_match("!Currently Effective Issue!i", $line)) $current = true;
            if (preg_match("!href=\"([0-9\-]+-AIRAC)\/", $line, $matches) && $current) {
                $airac = $matches[0];
                break;
            }
        }

        $tmpl = $this->base_url . $airac . $this->airport_tmpl;

        foreach ($this->airports as $icao => $name) {
            $apt_url = str_replace("%icao%", $icao, $tmpl);
            $aptdata = file($apt_url, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            foreach ($aptdata as $line) {
                if (preg_match("!Charts related to an aerodrome<\/h4>(<table.+<\/table>)!i", $line, $matches)) {
                    $chartdata = $matches[1];
                    $chartdata = explode("</tr>", $chartdata);
                    /*
                     * Will need to parse:
                     * <tr><td rowspan="2" valign="top" colspan="1" class="bleft btop bright bbottom">LJLJ AD 2.24.11-1</td><td colspan="1" class="bleft btop bright bbottom" rowspan="1">Radar Vectoring Chart</td></tr>
                     * <tr><td colspan="1" class="bleft btop bright bbottom" rowspan="1"><div class="graphic-box "><img src="../images/application_pdf.png" class="icon" alt="PDF"/><a id="LJLJ_AD_2.24.11-1" href="../../graphics/eAIP/LJ_AD_2_LJLJ_11-1_en.pdf">../graphics/eAIP/LJ_AD_2_LJLJ_11-1_en.pdf</a></div></td></tr>
                     */
                    $chartname = $url = "";
                    foreach ($chartdata as $l) {
                        if (preg_match("!rowspan=\"1\">([^<]+)<\/td!i", $l, $matches)) {
                            $chartname = $matches[1];
                        }
                        elseif (preg_match("!href=\"(\.\.\/[^\"]+\.pdf)\"!i", $l, $matches)) {
                            $url = $matches[1];

                            $chart = Chart::where('icao', $icao)->where('chartname', $chartname)->first();
                            if (!$chart) $chart = new Chart();
                            $chart->icao = $icao;
                            $chart->airportname = $name;
                            $chart->country = "SI";
                            $chart->chartname = strip_tags(utf8_encode($chartname));
                            $chart->charttype = "General";
                        }
                    }
                }
            }
        }

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
                $chartname = $matches[2];

                $chart = Chart::where('icao', $icao)->where('chartname', $chartname)->first();
                if (!$chart) $chart = new Chart();
                $chart->icao = $icao;
                $chart->airportname = strip_tags(utf8_encode($name));
                $chart->country = "LV";
                $chart->chartname = strip_tags(utf8_encode($chartname));
                $chart->charttype = "General";
                if (preg_match("!SID!", $chartname)) {
                    $chart->charttype = "SID";
                }
                if (preg_match("!STAR!", $chartname)) {
                    $chart->charttype = "STAR";
                }
                if (preg_match("!ILS!", $chartname) || preg_match("!VOR!", $chartname) ||
                    preg_match("!LOC!", $chartname) || preg_match("!VISUAL APPROACH!i", $chartname) ||
                    preg_match("!GPS!", $chartname) || preg_match("!APPROACH!", $chartname)) {
                    $chart->charttype = "Approach";
                }

                $chart->id = sha1("si.$icao,.$chartname");
                $chart->url = $this->base_url . $airac . "/html/eAIP/" . $url;
                $chart->save();
            }
        }

        // Clear out non-updated charts
        foreach (Chart::where('country', 'LV')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
