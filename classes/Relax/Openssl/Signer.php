<?php

/**
 * Signs a request's components using a public key. Throws signing exceptions
 * for requests that aren't valid.
 */
class Relax_Openssl_Signer implements Ergo_Http_ClientFilter
{
	const FUTURE_SANITY_THRESHOLD=5000;

	private $_timestamp;
	private $_privateKey;
	private $_keyStore;

	/**
	 * @param $privateKey Relax_Client_Openssl_PrivateKey
	 * @param $keyStore Relax_Client_Openssl_KeyStore
	 */
	function __construct($privateKey, $keyStore=null, $timestamp=false)
	{
		$this->_privateKey = $privateKey;
		$this->_keyStore = $keyStore;
		$this->_timestamp = $timestamp ? $timestamp : time();
	}

	/**
	 * Signs the components, returns an array of headers that need to be
	 * added to the request
	 */
	public function sign($method,$path,$body=null)
	{
		$headers = array();
		$headers[] = 'X-Signature-Expires: '.($this->_timestamp + 10);
		$headers[] = 'X-Signature-Key: '. $this->_privateKey->getKeyId();

		$data = $this->_canonicalize($headers,$method,$path,$body);

		$headers[] = 'X-Signature-Hash: '.
			base64_encode($this->_privateKey->sign($data));

		return $headers;
	}

	/**
	 * Verify a request against the internal keystore
	 */
	public function verify($headers,$method,$path,$body)
	{
		if(!is_object($this->_keyStore))
		{
			throw new Relax_Client_Openssl_SigningException("No keystore available to verify against",500);
		}

		$publicKey = $this->_keyStore->get($this->_keyValue($headers,'X-Signature-Key'));
		$hash = base64_decode($this->_keyValue($headers,'X-Signature-Hash'));
		$data = $this->_canonicalize($headers,$method,$path,$body);

		// verify it
		if(!$publicKey->verify($data, $hash))
		{
			throw new Relax_Client_Openssl_SigningException("Invalid request signature",401);
		}

		return false;
	}

	/**
	 * Validates that the expires time in the headers is valid
	 */
	public function validate($headers)
	{
		$time = time();
		$expires = $this->_keyValue($headers,'X-Signature-Expires');

		// validate expires parameter isn't in the far future
		if(($expires - $time) >= self::FUTURE_SANITY_THRESHOLD)
		{
			throw new Relax_Client_Openssl_SigningException(
				"Expiry time is too far in the future",400);
		}

		// validate expires parameter isn't past
		if($time > $expires)
		{
			throw new Relax_Client_Openssl_SigningException(
				"Request is expired",400);
		}
	}

	/**
	 * Joins the components of a request into a single consistant string
	 */
	private function _canonicalize($headers,$method,$path,$body)
	{
		// filter only relevant headers
		$headers = preg_grep("/^X-Signature-(Expires|Key)/i", $headers);

		// normalize headers whitespace
		$headers = array_map('trim',array_map('strtolower',$headers));

		// normalize header order
		usort($headers,'strcasecmp');

		// sign the string
		return strtolower($method).$path.http_build_query($headers).trim($body);
	}

	/**
	 * Searches an array of headers for a value
	 */
	private function _keyValue($headers, $key)
	{
		foreach($headers as $header)
		{
			list($headerKey,$headerValue) = explode(':',$header,2);
			if(strcasecmp($key, $headerKey) == 0) return trim($headerValue);
		}

		throw new Relax_Client_Openssl_SigningException("Missing signature header: $key");
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Http_ClientFilter::request()
	 */
	function request($request)
	{
		$headers = $request->getHeaders();
		$path = $request->getUrl()->getPath();
		$newHeaders = $this->sign($request->getRequestMethod(),
			$path,$request->getBody());

		// add new headers
		foreach($newHeaders as $header) $headers->add($header);

		return $request->copy($headers);
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Http_Filter::response()
	 */
	function response($response)
	{
		return $response;
	}
}

