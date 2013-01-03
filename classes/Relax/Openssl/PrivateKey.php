<?php

class Relax_Openssl_PrivateKey
{
    private $_path;
    private $_keyId;
    private $_passphrase;

    public function __construct($path, $keyId=false, $passphrase=NULL)
    {
        $this->_path = $path;
        $this->_keyId = $keyId;
        $this->_passphrase = $passphrase;
    }

    public function getKeyId()
    {
        return $this->_keyId;
    }

    public function unseal($data, $envelopeKey)
    {
        $key = $this->_getKeyResource();
        if (!openssl_open($data, $open, $envelopeKey, $key)) {
            throw new Relax_Openssl_Exception("Error unsealing data: ".openssl_error_string());
        }

        return $open;
    }

    public function sign($data, $algorithm=OPENSSL_ALGO_SHA1)
    {
        $key = $this->_getKeyResource();
        if (!openssl_sign($data, $hash, $key, $algorithm)) {
            throw new Relax_Openssl_Exception("Error signing data: ".openssl_error_string());
        }

        return $hash;
    }

    private function _getKeyResource()
    {
        if (!is_file($this->_path)) {
            throw new Relax_Openssl_Exception("Invalid private key: $this->_path");
        }

        return openssl_pkey_get_private(file_get_contents($this->_path), $this->_passphrase);
    }
}
