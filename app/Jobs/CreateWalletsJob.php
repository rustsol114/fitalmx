<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CryptoAssetApiLog;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\CryptoAssetSetting;
use Modules\TatumIo\Class\CryptoNetwork;

class CreateWalletsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public $walletId;
    public $userId;
    public $network;

    /**
     * Create a new job instance.
     *
     * @param int $walletId
     * @param int $userId
     * @param string $network
     */
    public function __construct($walletId, $userId, $network)
    {
        $this->walletId = $walletId;
        $this->userId = $userId;
        $this->network = $network;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $walletId = $this->walletId;
        $userId = $this->userId;
        $network = $this->network;

        $this->network = $network;
        $tatumAssetSetting = (new CryptoAssetSetting())->where(
            [
                'network' => $this->network,
                'payment_method_id' => TatumIo,
            ]
        )->first();

        if (empty($tatumAssetSetting)) {
            throw new Exception(__(':x crypto asset is not found', ['x' => $this->network]));
        }

        $networkCredentials = json_decode($tatumAssetSetting->network_credentials);
        $cryptoNetwork = new CryptoNetwork($networkCredentials->api_key, $this->network);

        try {
            $getTatumAssetApiLog = (new CryptoAssetApiLog())->getCryptoAssetapiLog(['payment_method_id' => TatumIo, 'object_id' => $walletId, 'object_type' => 'wallet_address', 'network' => $network], ['id']);
            if (empty($getTatumAssetApiLog)) {

                $moreWallets = [];
                // it take too long this part should be in queue
                //4294 96 7296 max value for id
                for ($i = 0; $i < 20; $i++) {
                    $tatumNetworkArray = [];

                    $paddedI = str_pad(($i + 1), 2, '0', STR_PAD_LEFT);
                    $uniqueIdentifier = (int)($userId . $paddedI);

                    $tatumAddress = $cryptoNetwork->generateAddress($networkCredentials->xpub, $uniqueIdentifier);
                    $tatumKey = $cryptoNetwork->generateAddressPrivateKey($uniqueIdentifier,  $networkCredentials->mnemonic);
                    $tatumBalance =  $cryptoNetwork->getBalanceOfAddress($tatumAddress['address']);
                    $cryptoNetwork->createSubscription($tatumAddress['address']);


                    $tatumNetworkArray['address'] = $tatumAddress['address'];
                    $tatumNetworkArray['key'] = isset($tatumKey['key']) ? $tatumKey['key'] : '';
                    $tatumNetworkArray['balance'] =  $tatumBalance;
                    $tatumNetworkArray['user_id'] =  $userId;
                    $tatumNetworkArray['wallet_id'] =  $walletId;
                    $tatumNetworkArray['network'] =  $network;

                    $moreWallets[] =  $tatumNetworkArray;
                }

                //create new crypt api log if empty
                $blockIoAssetApiLog = new CryptoAssetApiLog();
                $blockIoAssetApiLog->payment_method_id = TatumIo;
                $blockIoAssetApiLog->object_id = $walletId;
                $blockIoAssetApiLog->object_type = 'wallet_address';
                $blockIoAssetApiLog->network = $network;
                $blockIoAssetApiLog->payload = json_encode($moreWallets);
                $blockIoAssetApiLog->save();
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
}
