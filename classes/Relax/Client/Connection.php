<?php

/**
 * A connection to a rest system, either remote or local
 *
 * @author Lachlan Donald <lachlan@99designs.com>
 */
interface Relax_Client_Connection
{
	/**
	 * Puts an object to a particular path. The object must have an id property.
	 *
	 * @param $path string the path to the resource
	 * @param $body mixed the body content
	 */
	function put($path, $body);

	/**
	 * Posts a new object to a collection path. An id property will be generated.
	 *
	 * @param $path string the path to the resource
	 * @param $body mixed the body content
	 */
	function post($path, $body);

	/**
	 * Gets an object from a collection path.
	 *
	 * @param $path string the path to the resource
	 */
	function get($path);

	/**
	 * Deletes an object or collection from a particular path.
	 */
	function delete($path);
}

