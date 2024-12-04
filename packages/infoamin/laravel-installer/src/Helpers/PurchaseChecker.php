<?php
namespace Infoamin\Installer\Helpers;

use Infoamin\Installer\Interfaces\PurchaseInterface;
use Infoamin\Installer\Interfaces\CurlRequestInterface;
class PurchaseChecker implements PurchaseInterface {

	protected $curlRequest;

    public function __construct(CurlRequestInterface $curlRequest) {
        $this->curlRequest = $curlRequest;
    }

}
