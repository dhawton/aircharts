<?php

namespace App\Console\Commands;

use App\Chart;
use Illuminate\Console\Command;
use Carbon\Carbon;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

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
        $header = [
            "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
            "Cache-Control: max-age=0",
            "Connection: keep-alive",
            "Keep-Alive: 300",
            "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
            "Accept-Language: en-us,en;q=0.5",
            "Pragma: "
        ];

        $x = 0; // For testing purposes, only do ~10 charts.
        $index = file($this->base_url . $this->index_url, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        
        $blobClient = BlobRestProxy::createBlobService(config('filesystems.disks.azure.endpoint'));
        foreach ($index as $index_line) {
            if (preg_match("!href=\"([^\"]+)\">(.+) - (EG[A-Z]{2})<\/a>!", $index_line, $matches)) {
                $url = str_replace("&amp;", "&", $this->base_url . $matches[1]);
                $name = $matches[2];
                $icao = $matches[3];
                $airportfile = file($url, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
                $airporturl = $url;
                foreach ($airportfile as $line) {
                    if (preg_match("!class=\"desc\"[^>]*><a target=\"_blank\" href=\"([^\"]+)\">(.+)<\/a>!", $line, $matches)) {
                        $charturl = $matches[1];
                        $chartname = trim($matches[2]);
                        $charttype = "General";
                        if (preg_match("!INSTRUMENT APPROACH (CHART|PROCEDURE|PROCEDURES) (.+)$!i", $chartname, $matches)) {
                            $charttype = "Approach";
                            $chartname = $matches[2];
                        }
                        if (preg_match("!STANDARD DEPARTURE CHART\s+-\s+(.+)$!i", $chartname, $matches)) {
                            $charttype = "SID";
                            $chartname = $matches[1];
                        }
                        if (preg_match("!STANDARD ARRIVAL CHART\s+-\s+(.+)$!i", $chartname, $matches)) {
                            $charttype = "STAR";
                            $chartname = $matches[1];
                        }
                        if (preg_match("!INITIAL APPROACH PROCEDURES\s+-\s+(.+)$!i", $chartname, $matches)) {
                            $charttype = "Intermediate";
                            $chartname = $matches[1];
                        }
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

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_VERBOSE, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36");
                        curl_setopt($ch, CURLOPT_REFERER, $airporturl);
                        curl_setopt($ch, CURLOPT_URL, $charturl);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,sdch');
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        $result = curl_exec($ch);

                        $options = new CreateBlobOptions();
                        $options->setBlobContentType("application/pdf");

                        $blobClient->createBlockBlob(
                            "charts",
                            "uk/" . $chart->id . '.pdf',
                            $result,
                            $options
                        );

                        $chart->url = config('filesystems.disks.azure.blob_service_url') . "charts/uk/" . $chart->id . ".pdf";
                        $chart->save();
                    }
                }
            }
        }

        // Clear out non-updated UK charts
        foreach (Chart::where('country', 'UK')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            \Storage::disk('s3')->delete("uk/" . $chart->id . ".pdf");
            $chart->delete();
        }
    }
}
