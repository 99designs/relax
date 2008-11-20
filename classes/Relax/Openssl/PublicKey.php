<?php

class Relax_Openssl_PublicKey
{
	private $_path;
	private $_keyId;

	function __construct($path, $keyId=false)
	{
		$this->_path = $path;
		$this->_keyId = $keyId;
	}

	function getKeyId()
	{
		return $this->_keyId;
	}

	function seal($data)
	{
		$key = $this->_getKeyResource();
		if(openssl_seal($data, $sealed, $ekeys, array($key)) === false)
		{
			throw new Relax_Client_Openssl_Exception("Error sealing: ".openssl_error_string());
		}

		return array($sealed,$ekeys[0]);
	}

	function verify($data, $signature, $algorithm=OPENSSL_ALGO_SHA1)
	{
		$key = $this->_getKeyResource();
		if(openssl_verify($data, $signature, $key, $algorithm) === 1)
		{
			return true;
		}

		return false;
	}

	private function _getKeyResource()
	{
		if(!is_file($this->_path))
		{
			throw new Relax_Client_Openssl_Exception("Invalid public key: $this->_path");
		}

		return openssl_pkey_get_public(file_get_contents($this->_path));
	}
}
