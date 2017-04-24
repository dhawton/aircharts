<?php

namespace App\Console\Commands;

use App\Chart;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SpiderUS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:us';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for US Charts';

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
        $nextdate = Storage::disk('local')->get('spider.us.date');
        if ($nextdate != date("m/d/y")) return;

        $airac = Storage::disk('local')->get('spider.us.airac');
        if (substr($airac, -2) == "13") {
            $airac = date("y") . "01";
        } else {
            $airac += 1;
        }

        $xml = file_get_contents("http://155.178.201.160/d-tpp/$airac/xml_data/d-TPP_Metafile.xml");
        $xml = simplexml_load_string($xml);

        // Current Cycle
        $cycle = $xml->attributes()->cycle;
        $todate = $xml->attributes()->to_edate;
        preg_match("!\d+Z\s+(\d+/\d+/\d+)$!", $todate, $matches);
        $todate = $matches[1];
        Storage::disk('local')->put('spider.us.date', $todate);
        Storage::disk('local')->put('spider.us.airac', $cycle);

        // Start processing
        foreach($xml->state_code as $state) {
            // We don't really need to do anything with state except process the children
            foreach($state->city_name as $city) {
                // We don't need to process cities, so process the children
                foreach($city->airport_name as $airport) {
                    // Here we go.
                    $icao = $airport['icao_ident'];
                    $iata = $airport['apt_ident'];
                    $airport_name = $airport['ID'];
                    foreach($airport->record as $record) {
                        $chart = Chart::where('chartname', $record->chart_name)->where(function ($query, $icao, $iata) {
                            $query->where('icao', $icao)->orWhere('iata', $iata);
                        });
                        if (!$chart) {
                            $chart = new Chart();
                        }
                        $chart->icao = $icao;
                        $chart->iata = $iata;
                        $chart->country = "US";
                        $chart->airportname = $airport_name;
                        $chart->chartname = $record->chart_name;
                        if ($record->chart_code == "IAP") { $chart->charttype = "Approach"; }
                        elseif ($record->chart_code == "DP") { $chart->charttype = "SID"; }
                        elseif ($record->chart_code == "STAR") { $chart->charttype = "STAR"; }
                        else { $chart->charttype = "General"; }
                        $chart->url = "http://155.178.201.160/d-tpp/$cycle/" . $record->pdf_name;
                        $chart->flag = ($record->cn_flg != "N") ? $record->cn_flg : "";
                        $chart->id = sha1("us.$icao,$iata." . $record->pdf_name);
                        $chart->save();
                    }
                }
            }
        }

        // Clear out non-updated US charts
        foreach (Chart::where('country', 'US')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $chart->delete();
        }
    }
}
