<?php

/**
 * A wrapper for a connection that can populate a cache based on extra
 * data returned in the response
 */
class Relax_Client_PathCache implements Relax_Client_Connection
{
	private $_cache = array();

	public function __construct($connection)
	{
		$this->_connection = $connection;
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function get($path)
	{
		if (isset($this->_cache[$path]))
			return $this->_cache[$path];

		$obj = $this->_connection->get($path);

		$this->_cache($path, $obj);

		return $obj;
	}

	private function _cache($base, $obj)
	{
		$this->_cache[$base] = $obj;
		foreach ($obj as $k => $v)
		{
			if (is_array($v) || is_object($v))
				$this->_cache("$base/$k", $v);
		}
	}

	public function clearPathCache()
	{
		$this->_cache = array();
	}

	// ---- All other operations invalidate the cache...

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function delete($path)
	{
		$this->clearPathCache();
		return $this->_connection->delete($path);
	}
	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function put($path, $body)
	{
		$this->clearPathCache();
		return $this->_connection->put($path, $body);
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put()
	 */
	function post($path, $body)
	{
		$this->clearPathCache();
		return $this->_connection->post($path, $body);
	}
}
