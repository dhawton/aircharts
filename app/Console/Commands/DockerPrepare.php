<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DockerPrepare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        if (file_exists("/run/secrets/aircharts.env")) {
            $data = file_get_contents("/run/secrets/aircharts.env");
            file_put_contents("/app/.env", $data);
            file_put_contents("/app/.env", "APP_KEY=" . file_get_contents("/run/secrets/aircharts.key"), FILE_APPEND);
        }

        $cache = new MakeCache();
        $cache->handle();
    }
}
