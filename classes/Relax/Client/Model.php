<?php

/**
 * A model of a REST-based system.
 *
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_Model
{
    private $_root;
    private $_node;

    /**
     * Constructor
     * @param $connection object an instance of a {@link Relax_Client_Connection}
     */
    public function __construct($connection)
    {
        $this->_node = new Relax_Client_Node('Root',$connection);
        $this->_root = new Relax_Client_Resource($this->_node,'', false);
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
        $this->_node->hasMany($node, $alias);

        return $this;
    }

    /**
     * Adds a has-one relationship to the top-level of the model
     *
     * @param $node mixed either a string or a {@link Relax_Client_Node}
     * @param $alias string the string to use to describe the relationship
     * @return object return the model object, chainable
     */
    public function hasOne($node, $alias=false)
    {
        $this->_node->hasOne($node, $alias);

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
        return $this->_node->define($name,$class);
    }

    /**
     * Returns the relationships that exist in the model
     *
     * @return array an array of the relationships
     */
    public function relationships()
    {
        return $this->_node->relationships();
    }

    /**
     * Returns a specific relationship, or throws a
     * {@link BadMethodCallException} exception
     *
     * @param $rel string the relationship to lookup
     * @return array an array of the relationships
     */
    public function relationship($rel)
    {
        return $this->_node->relationship($rel);
    }

    /**
     * Generic call method for handling relationship methods
     */
    public function __call($method,$params)
    {
        return $this->_root->__call($method,$params);
    }
}
