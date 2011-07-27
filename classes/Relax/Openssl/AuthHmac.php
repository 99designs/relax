<?php

/**
 * Signs and verifies HTTP requests using HMAC.
 * Based on (and compatible with) the Ruby AuthHMAC library.
 *
 * @see http://auth-hmac.rubyforge.org/
 * @author Paul Annesley
 */
class Relax_Openssl_AuthHmac implements \Ergo\Http\ClientFilter
{
	const HEADER_NAME = 'Authorization';
	const SERVICE_ID = 'AuthHMAC';
	const DATE_FORMAT = 'D, d M Y H:i:s \G\M\T';

	private $_keys;
	private $_access_id;

	/**
	 * @param array $keys { access_id: secret, ... }
	 * @param string $access_id Access ID to use for signing, not used by verify().
	 */
	public function __construct($keys, $access_id = null)
	{
		$this->_keys = $keys;
		$this->_access_id = $access_id;
	}

	// ----------------------------------------
	// \Ergo\Http\ClientFilter interface

	/**
	 * Signs an outbound request.
	 * @see \Ergo\Http\ClientFilter
	 */
	public function request($request)
	{
		$this->_prepare_request($request);

		$secret = $this->_secret($this->_access_id);
		if (!$secret) throw new Relax_Openssl_SigningException();

		$signature = $this->_signature_for_request($request, $secret);

		$request->getHeaders()->add(new \Ergo\Http\HeaderField(
			self::HEADER_NAME,
			sprintf('%s %s:%s', self::SERVICE_ID, $this->_access_id, $signature)
		));

		return $request;
	}

	// @see \Ergo\Http\ClientFilter
	public function response($response)
	{
		return $response;
	}

	// ----------------------------------------

	public function verify($request)
	{
		$authorization = $request->getHeaders()->value(self::HEADER_NAME);

		if (!$authorization)
			throw new Relax_Openssl_SigningException("Unsigned request");

		if (!preg_match('#^(\w+) ([^:]+):(.+)$#', $authorization, $matches))
			throw new Relax_Openssl_SigningException("Invalid signature");

		if ($matches[1] !== self::SERVICE_ID)
			throw new Relax_Openssl_SigningException("Unrecognized authorization type");

		$secret = $this->_secret($matches[2]);

		if ($matches[3] === $this->_signature_for_request($request, $secret))
			return true;

		throw new Relax_Openssl_SigningException("Invalid signature");
	}

	// ----------------------------------------

	/**
	 * Adds missing Date and Content-MD5 headers to request.
	 */
	private function _prepare_request($request)
	{
		$headers = $request->getHeaders();

		// Add Date header if it's missing.
		if (!$headers->value('Date'))
			$headers->add('Date: ' . gmdate(self::DATE_FORMAT, Ergo::time()));

		// Add Content-MD5 if it's missing and there's a body.
		if (!$headers->value('Content-MD5') && $request->getBody())
			$headers->add('Content-MD5: ' . md5($request->getBody()));
	}

	/**
	 * Computes the AuthHMAC signature (not full header) for a request.
	 */
	private function _signature_for_request($request, $secret)
	{
		$headers = $request->getHeaders();

		$parts = array(
			$request->getRequestMethod(),
			$headers->value('Content-Type'),
			$headers->value('Content-MD5'),
			$headers->value('Date'),
			$request->getUrl()->getPath()
		);

		return base64_encode(
			hash_hmac('sha1', implode("\n", $parts), $secret, true)
		);
	}

	/**
	 * Look up the secret for a given access ID.
	 * @param string $access_id
	 * @return string
	 * @throws Relax_Openssl_SigningException
	 */
	private function _secret($access_id)
	{
		if (empty($this->_keys[$access_id]))
			throw new Relax_Openssl_SigningException("Access ID not found");

		return $this->_keys[$access_id];
	}
}
