<?php
namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\Http;

class GetBlockRepository
{
    private $apiKey;
    private $baseURL;
    private $defaultBlockchain;
    private $mode;

    public function __construct()
    {
        $this->apiKey = config('services.getblockio.api_key');
        $this->baseURL = 'https://go.getblock.io/';
        $this->defaultBlockchain = config('services.getblockio.default_blockchain');
        $this->mode = config('services.getblockio.mode') ?? 'testnet';
    }

    public function testConection()
    {
        $url = "{$this->baseURL}{$this->apiKey}";
        
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $data = [
            "jsonrpc" => "2.0",
            "method" => "getmininginfo",
            "method" => "createwallet",
            "params" => [],
            "id" => "getblock.io"
        ];

        $response = Http::post($url, $data, $headers);

        return $response->json();
    }

    public function createWallet()
    {
        $blockchain = $this->defaultBlockchain;
        $mode = $this->mode;

        $url = "{$this->baseURL}{$blockchain}/{$mode}/wallet/address";
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
        ];

        $response = Http::post($url, [], $headers);

        return $response->json();
    }

    public function sendTransaction($senderAddress, $recipientAddress, $amount)
    {
        $blockchain = $this->defaultBlockchain;
        $mode = $this->mode;

        $url = "{$this->baseURL}{$blockchain}/{$mode}/wallet/transaction";
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
        ];

        $transactionData = [
            'from' => $senderAddress,
            'to' => $recipientAddress,
            'value' => $amount,
        ];

        $response = Http::post($url, $transactionData, $headers);

        return $response->json();
    }

    public function checkBalance($walletAddress)
    {
        $blockchain = $this->defaultBlockchain;
        $mode = $this->mode;

        $url = "{$this->baseURL}{$blockchain}/{$mode}/wallet/balance?address={$walletAddress}";
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
        ];

        $response = Http::get($url, $headers);

        return $response->json();
    }
}
