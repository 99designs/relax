<?php

/**
 * A collection of {@link Relax_Client_Openssl_PublicKey} objects based on a
 * directory of pem encoded public key files. The base filename is used
 * as the keyid. A single filename can also be passed.
 */
class Relax_Openssl_KeyStore
{
	private $_keys=array();

	/**
	 * Constructs
	 */
	function __construct($dir)
	{
		if(is_file($dir))
		{
			$this->_keys[substr(basename($dir),0,-4)] = $dir;
		}
		else if(is_dir($dir))
		{
			foreach(glob("{$dir}*.pem") as $file)
			{
				$this->_keys[substr(basename($file),0,-4)] = $file;
			}
		}
		else
		{
			throw new Relax_Openssl_Exception("$dir doesn't exist");
		}
	}

	/**
	 * Get a public key by keyid
	 */
	function get($keyid)
	{
		if(!isset($this->_keys[$keyid]))
		{
			throw new Relax_Openssl_Exception("No key with the id $keyid");
		}

		return new Relax_Client_Openssl_PublicKey($this->_keys[$keyid], $keyid);
	}
}
