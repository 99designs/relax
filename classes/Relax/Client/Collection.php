<?php

/**
 * A collection of resources
 *
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_Collection implements ArrayAccess, Countable, IteratorAggregate
{
    private $_node;
    private $_path;
    private $_data;
    private $_loaded=false;

    const ID_KEY='id';

    /**
     * Constructor
     */
    public function __construct($node, $path)
    {
        $this->_node = $node;
        $this->_path = $path;
    }

    /**
     * Creates a new resource from provided data, which can be either an array
     * or object
     *
     * @param  array  $data
     * @return object the created resource
     */
    public function create($data = array())
    {
        $result = $this->_node->connection()->post($this->_path,(object) $data);
        $idProperty = self::ID_KEY;

        if (!property_exists($result,$idProperty)) {
            throw new Relax_Client_Error(
                "POST to $this->_path didn't return a $idProperty property");
        }

        $resource = $this->_node->resource($this->_path, $result->$idProperty);
        foreach ($result as $key=>$value) {
            $resource->$key = $value;
        }

        return $resource;
    }

    /**
     * Finds a resource with a particular identifier. This doesn't implicitly
     * load the resource until the resource is accessed.
     *
     * @param $id string the identifier of the resource
     * @return object the collection object, chainable
     */
    public function find($id)
    {
        return $this->_node->resource($this->_path, $id);
    }

    /**
     * Gets the full path of the collection
     */
    public function url()
    {
        return $this->_path;
    }

    /**
     * Returns the first resource in the collection
     */
    public function first()
    {
        if (!$this->_arrayObject()->offsetExists(0)) {
            throw new Relax_Client_Error(
                "No resources in collection $this->_path");
        }

        return $this->_arrayObject()->offsetGet(0);
    }

    /**
     * Returns the last resource in the collection
     */
    public function last()
    {
        $offset = $this->count() - 1;

        if (!$this->_arrayObject()->offsetExists($offset)) {
            throw new Relax_Client_Error(
                "No resources in collection $this->_path");
        }

        return $this->_arrayObject()->offsetGet($offset);
    }

    /**
     * Gets an array of the resources
     */
    public function toArray()
    {
        return $this->_arrayObject()->getArrayCopy();
    }

    /**
     * Gets an array object with the resources in it
     */
    public function _arrayObject()
    {
        if (!$this->_loaded) {
            $idProperty = self::ID_KEY;
            $array = array();
            foreach ($this->_node->connection()->get($this->_path) as $object) {
                if (!property_exists($object,$idProperty)) {
                    throw new Relax_Client_Error(
                        "Collection object didn't have an $idProperty property");
                }

                $resource = $this->_node->resource($this->_path, $object->$idProperty);
                $array[]= $resource->import($object, true);
            }
            $this->_loaded = true;
            $this->_data = new ArrayObject($array);
        }

        return $this->_data;
    }

    // -------------------------------------------------------
    // implementations of PHP magic interface methods

    /* (non-phpdoc)
     * @see ArrayAccess::offsetExists
     */
    public function offsetExists($offset)
    {
        return $this->_arrayObject()->offsetExists($offset);
    }

    /* (non-phpdoc)
     * @see ArrayAccess::offsetGet
     */
    public function offsetGet($offset)
    {
        return $this->_arrayObject()->offsetGet($offset);
    }

    /* (non-phpdoc)
     * @see ArrayAccess::offsetSet
     */
    public function offsetSet($offset,$value)
    {
        return $this->_arrayObject()->offsetSet($offset,$value);
    }

    /* (non-phpdoc)
     * @see ArrayAccess::offsetUnset
     */
    public function offsetUnset($offset)
    {
        return $this->_arrayObject()->offsetUnset($offset);
    }

    /* (non-phpdoc)
     * @see Countable::count
     */
    public function count()
    {
        return $this->_arrayObject()->count();
    }

    /* (non-phpdoc)
     * @see IteratorAggregate::getIterator
     */
    public function getIterator()
    {
        return $this->_arrayObject()->getIterator();
    }
}
