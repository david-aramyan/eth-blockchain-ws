<?php

namespace App\Console\Commands;

use App\Events\BlockEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use WSSC\Components\ClientConfig;
use WSSC\WebSocketClient;
use Illuminate\Support\Facades\Artisan;

class collectBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collect:blocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects ethereum blocks';

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
        $config = new ClientConfig();
        $config->setTimeout(60);

        try {
            $client = new WebSocketClient('wss://mainnet.infura.io/ws/v3/'.env('INFURA_API_KEY'), $config);
            $client->send('{"jsonrpc":"2.0", "id": 1, "method": "eth_subscribe", "params": ["newHeads"]}');

            while ($client->isConnected()) {
                $response = $client->receive();
                if (!empty($response)) {
                    $responseDecoded = json_decode($response, true);
                    if (isset($responseDecoded['params']) && isset($responseDecoded['params']['result']) && isset($responseDecoded['params']['result']['hash'])) {
                        broadcast(new BlockEvent($responseDecoded['params']['result']['hash']))->toOthers();
                        Artisan::call('collect:transactions', [
                            'hash' => $responseDecoded['params']['result']['hash']
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }
}
