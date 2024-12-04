StpApiV3Repository<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\DigitalSigner;

class StpApiRepository
{
    private $baseUrl;
    private $efwsUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.stpmex.url');
        $this->efwsUrl = config('services.stpmex.efws_url');
    }

    /**
     * Make an authenticated API request to STP
     *
     * @param string $method HTTP method (GET, POST, PUT, etc.)
     * @param string $url Full URL for the API endpoint
     * @param array $data Request data (for POST, PUT requests)
     * @param string $token Authentication token
     * @return array|false Response data or false on failure
     */
    private function makeRequest($method, $url, $data = [], $token = null)
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];

            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("STP API error: " . $response->body());
                return false;
            }
        } catch (Exception $e) {
            Log::error("Error making STP API request: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register a crypto account for an individual or business
     *
     * @param string $token Authentication token
     * @param array $accountData Account data to register
     * @param bool $isIndividual True for individual, false for business
     * @return array|false Response data or false on failure
     */
    public function registerCryptoAccount($accountData, $isIndividual = true)
    {
        $endpoint = $isIndividual ? 'fisicaCrypto' : 'moralCrypto';
        $url = "{$this->baseUrl}/speiws/rest/cuentaModule/{$endpoint}";
        return $this->makeRequest('put', $url, $accountData);
    }

    /**
     * Modify an existing crypto account
     *
     * @param string $token Authentication token
     * @param array $accountData Updated account data
     * @return array|false Response data or false on failure
     */
    public function modifyCryptoAccount($token, $accountData)
    {
        $url = "{$this->baseUrl}/speiws/rest/cuentaModule/modificaCuentaCrypto";
        return $this->makeRequest('put', $url, $accountData, $token);
    }

    /**
     * Get transaction conciliation data
     *
     * @param string $token Authentication token
     * @param array $params Query parameters for the request
     * @return array|false Response data or false on failure
     */
    public function getTransactionConciliation($token, $params)
    {
        $url = "{$this->efwsUrl}/efws/API/V2/conciliacion";
        return $this->makeRequest('get', $url, $params, $token);
    }

    /**
     * Get account balance
     *
     * @param string $token Authentication token
     * @param string $accountNumber Account number to check
     * @return array|false Response data or false on failure
     */
    public function getAccountBalance($data)
    {
        //$url = "{$this->efwsUrl}/efws/API/consultaSaldoCuenta";
        $url = 'https://efws-dev.stpmex.com/efws/API/consultaSaldoCuenta';
        return $this->makeRequest('post', $url, $data);
    }

    /**
     * Register a payment order (Dispersión)
     *
     * @param string $token Authentication token
     * @param array $orderData Payment order data
     * @return array|false Response data or false on failure
     */
    public function registerPaymentOrder($token, $orderData)
    {
        $url = "{$this->baseUrl}/speiws/rest/ordenPago/registra";
        return $this->makeRequest('post', $url, $orderData, $token);
    }

    /**
     * Get details of a specific order
     *
     * @param string $token Authentication token
     * @param string $orderId Order ID to query
     * @return array|false Response data or false on failure
     */
    public function getOrderDetails($token, $orderId)
    {
        $url = "{$this->efwsUrl}/efws/API/consultaOrden";
        return $this->makeRequest('get', $url, ['id' => $orderId], $token);
    }

    /**
     * Get details of multiple orders
     *
     * @param string $token Authentication token
     * @param array $params Query parameters (e.g., date range, status)
     * @return array|false Response data or false on failure
     */
    public function getOrdersDetails($token, $params)
    {
        $url = "{$this->efwsUrl}/efws/API/consultaOrdenes";
        return $this->makeRequest('get', $url, $params, $token);
    }

    /**
     * Get list of financial institutions
     *
     * @param string $token Authentication token
     * @return array|false Response data or false on failure
     */
    public function getInstitutions($token)
    {
        $url = "{$this->efwsUrl}/efws/API/consultaInstituciones";
        return $this->makeRequest('get', $url, [], $token);
    }

    /**
     * Get account confirmation letter
     *
     * @param string $token Authentication token
     * @param string $accountNumber Account number to confirm
     * @return array|false Response data or false on failure
     */
    public function getAccountConfirmationLetter($token, $accountNumber)
    {
        $url = "{$this->efwsUrl}/efws/API/confirmacion-cuenta";
        return $this->makeRequest('get', $url, ['cuenta' => $accountNumber], $token);
    }

    public function getSign($originalData, $privatekey_path, $passphrase)
    {
        try {
            $signer = new DigitalSigner();
            
            $signature = $signer->sign(
                $originalData,
                $privatekey_path,
                $passphrase
            );
            
            return trim($signature);
            
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function oldSign($originalData, $privatekey_path, $passphrase)
    {
        try {
            $signer = new DigitalSigner();
            
            $signature = $signer->getSign(
                $originalData,
                $privatekey_path,
                $passphrase
            );
            
            return trim($signature);
            
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
