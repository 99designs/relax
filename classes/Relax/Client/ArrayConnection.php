<?php

/**
 * A connection object that uses a simple array backend, doesn't persist
 * beyond requests
 */
class Relax_Client_ArrayConnection implements Relax_Client_Connection
{
	private $_data=array();

	/**
	 * Injects data to a particular path without any constraints
	 */
	function inject($path, $data)
	{
		$this->_data[$path] = $data;
	}

	/**
	 * Calculates the next highest identifier for a path
	 *
	 * @return int the next highest identifier
	 */
	private function _nextId($path)
	{
		$id = 0;

		foreach($this->_data as $resourcePath=>$resource)
		{
			if($path == dirname($resourcePath) && $resource->id > $id)
			{
				$id = $resource->id;
			}
		}

		return $id + 1;
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::post
	 */
	function post($path, $data)
	{
		if(!is_object($data))
		{
			throw new Relax_Client_Error("Data must be in object form");
		}

		$data->id = $id = $this->_nextId($path);
		$this->put(ltrim("$path/$id",'/'),$data);
		return $data;
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::put
	 */
	function put($path, $data)
	{
		if(!is_object($data))
		{
			throw new Relax_Client_Error("Data must be in object form");
		}

		$this->_data[$path] = $data;
		return $data;
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::get
	 */
	function get($path)
	{
		if(!isset($this->_data[$path]))
		{
			throw new Relax_Client_Error("$path doesn't exist");
		}

		return $this->_data[$path];
	}

	/* (non-phpdoc)
	 * @see Relax_Client_Connection::delete
	 */
	function delete($path)
	{
		if(!isset($this->_data[$path]))
		{
			throw new Relax_Client_Error("$path doesn't exist");
		}
		return $this;
	}
}

