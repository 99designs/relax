<?php

/**
 * A connection object that uses JSON over HTTP
 */
class Relax_Client_HttpConnection implements Relax_Client_Connection
{
	private $_client;

	public function __construct($client)
	{
		$this->_client = clone $client;
		$this->_client->addHeader('Content-Type: application/json');
	}

	private function _decodeResponse($response)
	{
		$response = json_decode($response->getBody());

		// a string response that is not empty must be a php fatal error
		if(is_string($response) && strlen($response)>0)
		{
			throw new Relax_Client_Error($response);
		}

		return $response;
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function put($path, $body)
	{
		if(!is_object($body))
		{
			throw new InvalidArgumentException("Data must be in object form");
		}

		$response = $this->_client->put("/$path", json_encode($body));
		return $this->_decodeResponse($response);
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function post($path, $body)
	{
		if(!is_object($body))
		{
			throw new InvalidArgumentException("Data must be in object form");
		}

		$response = $this->_client->post("/$path", json_encode($body));
		return $this->_decodeResponse($response);
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function get($path)
	{
		$response = $this->_client->get("/$path");
		return $this->_decodeResponse($response);
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function delete($path)
	{
		$response = $this->_client->delete("/$path");
		return $this->_decodeResponse($response);
	}
}
