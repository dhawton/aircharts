<?php

namespace App\Console\Commands;

use App\Chart;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

class SpiderMX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:mx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spider for Mexico Charts (VATSIM)';

    protected $vaturl = "https://vatmex.net/pilotos/charts/";

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

        $data = file($this->vaturl, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $url = "";
        $blobClient = BlobRestProxy::createBlobService(config('filesystems.disks.azure.endpoint'));

        foreach ($data as $line) {

            $airport = Airport::where('id', $icao)->first();
            if (preg_match("!class=\"download-link\".+href=\"(https:\/\/vatmex\.net\/descargas\/\d+\/)\"", $line, $matches)) {
                $url = $matches[1];
            } elseif (preg_match("!(MM[A-Z]{2})\w*-\w*([^<]+)", $line, $matches)) {
                $icao = $matches[1];
                $name = $matches[2];

                $chart = Chart::where('icao',$icao)->first();
                if (!$chart) { $chart = new Chart(); }

                $chart->icao = $icao;
                $chart->country = "MX";
                $chart->charttype = "General";
                $chart->chartname = "VATMEX Package";
                $chart->airportname = $airport->name;
                $chart->id = sha1("mx.$icao,." . $chart->chartname);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_VERBOSE, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36");
                curl_setopt($ch, CURLOPT_REFERER, $this->vaturl);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,sdch');
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $result = curl_exec($ch);

                $options = new CreateBlockBlobOptions();
                $options->setContentType("application/pdf");

                $blobClient->createBlockBlob(
                    "charts",
                    "mx/" . $chart->id . '.pdf',
                    $result,
                    $options
                );

                $chart->url = config('filesystems.disks.azure.blob_service_url') . "charts/mx/" . $chart->id . ".pdf";
                $chart->save();
            }
        }

        // Clear out non-updated MX charts
        foreach (Chart::where('country', 'MX')->where('updated_at', '<', Carbon::yesterday()->toDateString())->get() as $chart) {
            $blobClient->deleteBlob("charts", "mx/" . $chart->id . ".pdf");
            $chart->delete();
        }
    }
}
