<?php

/**
 * Represents a node in the tree of the model, contains relationships
 * to other nodes and provides the connection to the resources.
 *
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_Node
{
	const REL_MANY='many';
	const REL_ONE='one';

	private $_name;
	private $_class;
	private $_relationships=array();
	private $_connection;

	/**
	 * Constructor
	 *
	 * @param $name string the name of the node
	 * @param $connection object the {@link Relax_Client_Connection} to use
	 */
	function __construct($name,$connection,$class=false)
	{
		$this->_name = $name;
		$this->_connection = $connection;
		$this->_class = $class ? $class : 'Relax_Client_Resource';
	}


	/**
	 * Adds a has-many relationship to the top-level of the model
	 *
	 * @param $node mixed either a string or a {@link Relax_Client_Node}
	 * @param $alias string the string to use to describe the relationship
	 * @return object return the model object, chainable
	 */
	public function hasMany($node, $alias=false)
	{
		if(is_string($node)) $node = new Relax_Client_Node($node,$this->_connection);
		$rel = $alias ? $alias : strtolower($node->_name).'s';

		$this->_relationships[$rel] = (object) array(
			'node' => $node,
			'type' => self::REL_MANY,
			);
		return $this;
	}

	/**
	 * Adds a has-one relationship to the top-level of the model
	 *
	 * @param $node mixed either a string or a {@linkg Relax_Client_Node}
	 * @param $alias string the string to use to describe the relationship
	 * @return object return the model object, chainable
	 */
	public function hasOne($node, $alias=false)
	{
		if(is_string($node)) $node = new Relax_Client_Node($node,$this->_connection);
		$rel = $alias ? $alias : strtolower($node->_name);

		$this->_relationships[$rel] = (object) array(
			'node' => $node,
			'type' => self::REL_ONE,
			);
		return $this;
	}

	/**
	 * Creates a new, unlinked {@link Relax_Client_Node} object, which can be passed in
	 * to another node's {@link hasOne()} or {@link hasMany()} methods.
	 *
	 * @param $name string the name of the node to define
	 * @param $class string the class name to use for instantiated resources
	 * @return object a new {@link Relax_Client_Node}
	 */
	public function define($name,$class=false)
	{
		return new Relax_Client_Node($name,$this->_connection,$class);
	}

	/**
	 * Returns the relationships that exist in the model
	 *
	 * @return array an array of the relationships
	 */
	public function relationships()
	{
		return array_keys($this->_relationships);
	}

	/**
	 * Returns a specific relationship, or throws a
	 * {@link BadMethodCallException} exception
	 *
	 * @param $rel string the relationship to lookup
	 * @return array an array of the relationships
	 */
	public function relationship($key)
	{
		if(!isset($this->_relationships[$key]))
		{
			throw new BadMethodCallException("No relationship for $key");
		}

		return $this->_relationships[$key];
	}

	/**
	 * Returns the {@link Relax_Client_Connection} for the node
	 *
	 * @return object the connection
	 */
	public function connection()
	{
		return $this->_connection;
	}

	/**
	 * Returns a new {@link Relax_Client_Resource} for the node
	 *
	 * @param $path string the path to pass to the constructor
	 * @return object the resource
	 */
	public function resource($path, $id)
	{
		$class = $this->_class;
		return new $class($this,$path,$id);
	}
}

