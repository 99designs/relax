<?php

/**
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_NodeTest extends UnitTestCase
{
	public function setUp()
	{
		$this->connection = new Relax_Client_ArrayConnection();
	}

	public function testManyRelationships()
	{
		$node = new Relax_Client_Model('Root',$this->connection);
		$node->hasMany('Customer');

		$this->assertEqual($node->relationships(), array('customers'));
		$this->assertEqual($node->relationship('customers')->type, 'many');
	}

	public function testOneRelationships()
	{
		$node = new Relax_Client_Model('Root',$this->connection);
		$node->hasOne('Customer');

		$this->assertEqual($node->relationships(), array('customer'));
		$this->assertEqual($node->relationship('customer')->type, 'one');
	}

	public function testMissingRelationshipsCauseExceptions()
	{
		$node = new Relax_Client_Model('Root',$this->connection);
		$node->hasMany('Customer');

		try
		{
			$node->relationship('blargh');
			$this->fail('missing relationships should throw an exception');
		}
		catch(BadMethodCallException $e)
		{
			$this->assertTrue(true);
		}
	}
}
