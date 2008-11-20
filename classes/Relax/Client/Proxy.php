<?php

/**
 * A proxy to a resource/collection, which is automatically generated
 * when a relationship doesn't exist
 */
class Relax_Client_Proxy implements ArrayAccess, Countable, IteratorAggregate
{
	private $_node;
	private $_path;
	private $_relation;

	// delegates
	private $_resource;
	private $_collection;

	/**
	 * Constructor
	 */
	function __construct($node, $path, $relation)
	{
		$this->_node = $node;
		$this->_path = $path;
		$this->_relation = $relation;
	}

	/**
	 * Dispatches relationship methods to a resource or collection delegate
	 */
	public function __call($method,$params)
	{
		$delegate = method_exists('Relax_Client_Collection', $method) ?
			$this->_collection() : $this->_resource();

		return call_user_func_array(array($delegate,$method),$params);
	}

	/**
	 * Creates a resource delegate, or returns the existing one
	 */
	private function _resource()
	{
		if(!isset($this->_resource))
		{
			$this->_resource = $this->_node->resource($this->_path,$this->_relation);
		}

		return $this->_resource;
	}

	/**
	 * Creates a collection delegate, or returns the existing one
	 */
	private function _collection()
	{
		if(!isset($this->_collection))
		{
			$path = 	Relax_Client_Resource::joinPaths($this->_path,$this->_relation);
			$this->_collection = new Relax_Client_Collection($this->_node, $path);
		}

		return $this->_collection;
	}

	// -------------------------------------------------------
	// resource methods

	/**
	 * Magic method, invokes {@link get()} for property getters
	 */
	function __get($prop)
	{
		return $this->_resource()->get($prop);
	}

	/**
	 * Magic method, invokes {@link set()} for property setters
	 */
	function __set($prop, $value)
	{
		return $this->_resource()->set($prop, $value);
	}

	// -------------------------------------------------------
	// collection methods

	/* (non-phpdoc)
	 * @see ArrayAccess::offsetExists
	 */
	function offsetExists($offset)
	{
		return $this->_collection()->offsetExists($offset);
	}

	/* (non-phpdoc)
	 * @see ArrayAccess::offsetGet
	 */
	function offsetGet($offset)
	{
		return $this->_collection()->offsetGet($offset);
	}

	/* (non-phpdoc)
	 * @see ArrayAccess::offsetSet
	 */
	function offsetSet($offset,$value)
	{
		return $this->_collection()->offsetSet($offset,$value);
	}

	/* (non-phpdoc)
	 * @see ArrayAccess::offsetUnset
	 */
	function offsetUnset($offset)
	{
		return $this->_collection()->offsetUnset($offset);
	}

	/* (non-phpdoc)
	 * @see Countable::count
	 */
	function count()
	{
		return $this->_collection()->count();
	}

	/* (non-phpdoc)
	 * @see IteratorAggregate::getIterator
	 */
	function getIterator()
	{
		return $this->_collection()->getIterator();
	}
}

