<?php

namespace App\Services;

use Exception;

class DigitalSigner
{
    /**
     * Signs data using RSA with configurable hash algorithm and key length
     *
     * @param string $data Data to sign
     * @param string $privateKeyPath Path to private key file
     * @param string $passphrase Key passphrase
     * @param int $keyBits Key length in bits (1024, 2048, etc)
     * @return string Base64 encoded signature
     * @throws Exception
     */
    public function sign($data, $privateKeyPath, $passphrase)
    {
        try {
            // Read private key
            if (!is_readable($privateKeyPath)) {
                throw new Exception("Cannot read private key file");
            }

            $privateKey = file_get_contents($privateKeyPath);
            $keyResource = openssl_pkey_get_private($privateKey, $passphrase);

            if ($keyResource === false) {
                throw new Exception("Invalid private key or passphrase");
            }

            // Get key details
            $keyDetails = openssl_pkey_get_details($keyResource);
            if ($keyDetails === false) {
                throw new Exception("Could not get key details");
            }

            // For 2048-bit keys, use SHA-256
            // For 1024-bit keys, use SHA-1
            $algorithm = ($keyDetails['bits'] > 1024) ?
                OPENSSL_ALGO_SHA1 :  // Returns ~256 bytes signature
                OPENSSL_ALGO_SHA1;   // Returns ~128 bytes signature

            // Create signature
            $signature = "";
            if (!openssl_sign($data, $signature, $keyResource, $algorithm)) {
                throw new Exception("Failed to create signature");
            }

            //return $signature;
            return base64_encode($signature);
        } catch (Exception $e) {
            throw new Exception("Signing failed: " . $e->getMessage());
        }
    }

    /**
     * Alternative implementation using hash then sign approach
     * This gives more control over the output size
     */
    public function signWithHash($data, $privateKeyPath, $passphrase)
    {
        try {
            // First hash the data
            $hash = hash('sha256', $data, true); // Get raw binary output

            // Read private key
            if (!is_readable($privateKeyPath)) {
                throw new Exception("Cannot read private key file");
            }

            $privateKey = file_get_contents($privateKeyPath);
            $keyResource = openssl_pkey_get_private($privateKey, $passphrase);

            if ($keyResource === false) {
                throw new Exception("Invalid private key or passphrase");
            }

            // Sign the hash instead of the full data
            $signature = "";
            if (!openssl_sign($hash, $signature, $keyResource, OPENSSL_ALGO_SHA1)) {
                throw new Exception("Failed to create signature");
            }

            return base64_encode($signature);
        } catch (Exception $e) {
            throw new Exception("Signing failed: " . $e->getMessage());
        }
    }

    public function getSign($originalData, $privateKeyPath, $passphrase)
    {
        $privateKey = $this->getCertified($privateKeyPath, $passphrase);
        $binarySign = "";
        openssl_sign($originalData, $binarySign, $privateKey, "RSA-SHA256");
        $sign = base64_encode($binarySign);
        openssl_free_key($privateKey);
        return $sign;
    }
    private function getCertified($privateKeyPath, $passphrase)
    {
        $fp = fopen($privateKeyPath, "r");
        $privateKey = fread($fp, filesize($privateKeyPath));
        fclose($fp);
        return openssl_get_privatekey($privateKey, $passphrase);
    }
}
