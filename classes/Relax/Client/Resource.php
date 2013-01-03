<?php

/**
 * A concrete instance of a resource in a REST system
 *
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_Resource
{
    private $_node;
    private $_data;
    private $_path;
    private $_loaded=false;
    private $_saved=false;
    private $_id=false;

    /**
     * Constructor
     *
     * @param $node object the Relax_Client_Node instance for the resource
     * @param $path string the base path to the resource in the rest system
     */
    public function __construct($node, $path, $id)
    {
        $this->_id = $id;
        $this->_node = $node;
        $this->_path = $path;
        $this->_data = new stdClass();
    }

    /**
     * Loads the resource from the rest system, overwrites any existing
     * data.
     *
     * @throws Relax_Client_Error
     * @return object             the resource, chainable
     */
    public function load()
    {
        $this->_loaded = true;
        $this->_data = $this->_node->connection()->get($this->url());

        return $this;
    }

    /**
     * Saves the resource to the rest system, overwrites any existing data
     *
     * @throws Relax_Client_Error
     * @param $data an array of data to import before save
     * @return object the resource, chainable
     */
    public function save($data=null)
    {
        if(is_array($data)) $this->import($data);
        $this->_saved = true;
        $this->_node->connection()->put($this->url(),$this->_data);

        return $this;
    }

    /**
     * Deletes the resource from the rest system
     *
     * @throws Relax_Client_Error
     * @return object             the resource, chainable
     */
    public function delete()
    {
        $this->_node->connection()->delete($this->url());

        return $this;
    }

    /**
     * Returns a generic object version of the data in the resource
     *
     * @param  bool   $load Whether to automatically load data.
     * @return object a stdClass with the data in it
     */
    public function data($load = true)
    {
        if($load && !$this->_loaded) $this->load();

        return (object) $this->_data;
    }

    /**
     * Gets a property from the resource, or throws an exception if it doesn't
     * exist
     *
     * @param $prop string the name of the property
     * @return mixed the contents of the property
     */
    public function get($prop)
    {
        if (!$this->_exists($prop)) {
            throw new BadMethodCallException("Property $prop doesn't exist");
        }

        return $this->_data->$prop;
    }

    /**
     * Sets a property in the resource
     *
     * @param $prop string the name of the property
     * @param $value mixed the value of the property
     */
    public function set($prop,$value)
    {
        $this->_data->$prop = $value;

        return $this;
    }

    /**
     * Imports any iteratable object into the resource
     */
    public function import($mixed, $markLoaded=false)
    {
        foreach ($mixed as $key=>$value) {
            $this->$key = $value;
        }

        if($markLoaded) $this->_loaded = true;

        return $this;
    }

    /**
     * Gets the full path of the resource, including the id
     */
    public function url()
    {
        return self::joinPaths($this->_path,$this->_id);
    }

    /**
     * Helper method for joining multiple path components together
     */
    public static function joinPaths($paths)
    {
        $components = array();
        foreach (func_get_args() as $arg) {
            $components = array_merge($components,explode('/',$arg));
            $components = array_map('urlencode', $components);
        }

        return implode('/',array_filter($components));
    }

    /**
     * Dispatches relationship methods
     */
    public function __call($method,$params)
    {
        // support dynamically creating relationships
        if (!in_array($method,$this->_node->relationships())) {
            $proxy = new Relax_Client_Proxy($this->_node,$this->url(),$method);

            return isset($params[0]) ? $proxy->find($params[0]) : $proxy;
        }

        $rel = $this->_node->relationship($method);

        //Ergo::loggerFor($this)->info("dispatching $method(%s) to static relation %s",
        //	(isset($params[0]) ? $params[0] : 'none'), $rel->type);

        // dispatch static relationships
        if ($rel->type == Relax_Client_Node::REL_ONE) {
            return $rel->node->resource($this->url(),$method);
        } elseif ($rel->type == Relax_Client_Node::REL_MANY) {
            $collection = new Relax_Client_Collection($rel->node,self::joinPaths($this->url(),$method));

            return isset($params[0]) ? $collection->find($params[0]) : $collection;
        }
    }

    /**
     * Magic method, invokes {@link get()} for property getters
     */
    public function __get($prop)
    {
        return $this->get($prop);
    }

    /**
     * Magic method, invokes {@link set()} for property setters
     */
    public function __set($prop, $value)
    {
        $this->set($prop, $value);
    }

    /**
     * Magic method, determines whether the property is set
     */
    public function __isset($prop)
    {
        if (!$this->_loaded && !isset($this->_data->$prop)) {
            $this->load();
        }

        return isset($this->_data->$prop);
    }

    /**
     * Magic method, unsets a property
     */
    public function __unset($prop)
    {
        unset($this->_data->$prop);
    }

    /**
     * Determines whether the property exists
     */
    public function _exists($prop)
    {
        if (!$this->_loaded && !property_exists($this->_data, $prop)) {
            $this->load();
        }

        return property_exists($this->_data, $prop);
    }
}
