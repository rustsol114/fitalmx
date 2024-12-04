<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class StpApiV3Repository
{
    private $baseUrl = 'https://market-fital-api.fitalmx.com';
    private $accessToken;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();
    }

    public function getAccessToken($email = '', $password = '')
    {
        try {

            $requestData = [
                'email' => !empty($email) ? $email : config('services.stpmex.email'),
                'password' => !empty($password) ? $password : config('services.stpmex.password'),
            ];

            $response = Http::post("{$this->baseUrl}/api/auth/generarToken", $requestData);

            //$response->throw(); 
            $responseData = $response->json();

            return $responseData['data']['token'] ?? '';
        } catch (Exception $e) {
            Log::error('Failed to get STP access token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createNewUser(array $userData)
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/user", $userData);
            $response->throw();
            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to create new STP user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateUserInfo(array $userInfo)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->patch("{$this->baseUrl}/api/user", $userInfo);
            $response->throw();
            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to update STP user info: ' . $e->getMessage());
            throw $e;
        }
    }

    public function validateUserDocumentation(string $userId)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/api/user/admin/validate-documentation/{$userId}");
            $response->throw();
            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to validate STP user documentation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function registerNaturalPerson(array $data)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->put("{$this->baseUrl}/api/stp/fisica-crypto", $data);
            //$response->throw();
            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to register STP natural person: ' . $e->getMessage());
            throw $e;
        }
    }

    public function registerLegalEntity(array $data)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->put("{$this->baseUrl}/api/stp/moral-crypto", $data);
            //$response->throw();
            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to register STP legal entity: ' . $e->getMessage());
            throw $e;
        }
    }

    public function withdrawFunds(array $data)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/api/stp/registrarOrden", $data);
            //$response->throw();
            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to withdraw funds from STP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     *
     * @param array|string $accounts 
     * @param string $company ('FITAL_MX')
     * @return array
     * @throws \Exception
     */
    public function consultaCuentaCrypto(array|string $accounts, string $company = 'FITAL_MX'): array
    {
        try {
            $accountsString = is_array($accounts) ? implode(',', $accounts) : $accounts;
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/consultaCuentaCrypto', [
                'cuentas' => $accountsString,
                'empresa' => $company,
            ]);
            
            if (!$response->successful()) {
                Log::error('STP API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'accounts' => $accountsString,
                ]);
                
                throw new \Exception('Failed to fetch crypto account data: ' . $response->status());
            }
            
            return $response->json();
            
        } catch (\Exception $e) {
            Log::error('STP Service Error', [
                'message' => $e->getMessage(),
                'accounts' => $accounts,
                'company' => $company,
            ]);
            
            throw $e;
        }
    }
    
    // implementation from wrong doc

    // $userData = [
    //     "name" => "Donal ",
    //     "last_name" => "Trump ",
    //     "second_last_name" => "Perez",
    //     "email" => "donal@test.com",
    //     "password" => "F1ta!2024",
    //     "passwordConfirm" => "F1ta!2024"
    // ];

    // //$r = $this->stpV3->createNewUser($userData);

    // //$r = $this->stpV3->getAccessToken('donal@test.com', 'F1ta!2024');

    // //$tokenOfNewUser = $r;


    // $updateUserInfo = [
    //     "rfc" => "MAR210922710",
    //     "curp" => "MAR210922710",
    //     "gender" => "H",
    //     "birthdate" => 19940815,
    //     "id_identification" => "",
    //     "telephone" => "0990723"
    // ];

    // $d = $this->stpV3->updateUserInfo($updateUserInfo);

    // $testUserId = '0b494800-b2bb-4e36-ad87-ca8ae6871e61';

    // $testUserId = '4d4df891-45e7-4b8d-afee-3a156939bcaa';


    // $p = $this->stpV3->validateUserDocumentation($testUserId);


    // $ddd = [
    //     "empresa" => "FITAL_MX",
    //     "paisNacimiento" => 187,
    //     "cuentaNoSTP" => "072180004379692200",
    //     "institucionCuentaNoSTP" => 90646,
    //     "entidadFederativa" => 9,
    //     "actividadEconomica" => 31
    // ];

    // $f = $this->stpV3->registerNaturalPerson($ddd);


    // $legalData = [
    //     "empresa" => "FITAL_MX",
    //     "pais" => 187,
    //     "cuentaNoSTP" => "012180015368777619",
    //     "institucionCuentaNoSTP" => 40012,
    //     "entidadFederativa" => 9,
    //     "actividadEconomica" => 31
    // ];

    // $y = $this->stpV3->registerLegalEntity($legalData);


    // $orderData = [
    //     "conceptoPago" => "Test",
    //     "monto" => 0.01,
    //     "latitude" => "19.4872878",
    //     "longitude" => "-99.1549057"
    // ];

    // $h = $this->stpV3->withdrawFunds( $orderData);

    // dd( $h);

    //     "data" => array:17 [▼
    // "id" => "12e60ec4-f6ab-4c64-99bf-6af2eccf6cd2"
    // "name" => "DONAL"
    // "last_name" => "TRUMP"
    // "second_last_name" => "PEREZ"
    // "email" => "donal@test.com"
    // "userId" => "aaf264f2-d421-4e19-a548-f15886ddcf46"
    // "rfc" => null
    // "curp" => null
    // "gender" => null
    // "birthdate" => null
    // "id_identification" => null
    // "telephone" => null
    // "ownerId" => null
    // "doc_validated" => false
    // "createdAt" => "2024-10-14T13:18:23.756Z"
    // "updatedAt" => "2024-10-14T13:18:23.756Z"
    // "rol" => "user"

}
