<?php

namespace App\Console\Commands;

use App\Events\TransactionEvent;
use Illuminate\Console\Command;
use WSSC\Components\ClientConfig;
use WSSC\WebSocketClient;

class collectTransactions extends Command
{
    const TIMEOUT = 60;
    const GET_BLOCK_METHOD = "eth_getBlockByHash";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collect:transactions {hash}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects transactions by block\'s hash';

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
        $block_hash = $this->argument('hash');
        $config = new ClientConfig();
        $config->setTimeout(self::TIMEOUT);

        try {
            $client = new WebSocketClient(env('INFURA_API_ENDPOINT').env('INFURA_API_KEY'), $config);
            $client->send('{"jsonrpc":"2.0","method":"'.self::GET_BLOCK_METHOD.'","params": ["'.$block_hash.'",false],"id":1}');

            if ($client->isConnected()) {
                $response = $client->receive();
                if (!empty($response)) {
                    $responseDecoded = json_decode($response, true);
                    if (isset($responseDecoded['result']) && $responseDecoded['result'] != null && $responseDecoded['result']['transactions']) {
                        $transaction['blockId'] = $block_hash;
                        foreach ($responseDecoded['result']['transactions'] as $txId) {
                            $transaction['txId'] = $txId;
                            $client->send('{"jsonrpc":"2.0","method":"eth_getTransactionByHash","params": ["'.$txId.'"],"id":1}');
                            $txResponse = $client->receive();
                            if (!empty($txResponse)) {
                                $txResponseDecoded = json_decode($txResponse, true);
                                if (isset($txResponseDecoded['result']) && $txResponseDecoded['result'] != null && isset($txResponseDecoded['result']['to'])) {
                                    $transaction['toAddress'] = $txResponseDecoded['result']['to'] ?? '';
                                }
                            }
                            broadcast(new TransactionEvent($transaction))->toOthers();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }
}
