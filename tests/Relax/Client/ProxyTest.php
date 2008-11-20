<?php

Mock::generate('Relax_Client_Connection','Relax_Client_MockConnection');

/**
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_ProxyTest extends UnitTestCase
{
	public function setUp()
	{
		$this->connection = new Relax_Client_MockConnection();
		//$this->model =  new Relax_Client_Model(new Commerce_LoggingConnection($this->connection));
		$this->model = new Relax_Client_Model($this->connection);
	}

	public function testCollectionSelector()
	{
		$this->connection->expectOnce('get');
		$this->connection->setReturnValue('get',(object) array(
			'id'=>1,
			'name'=>'apple'
			), array('fruits/1'));

		$this->assertEqual($this->model->fruits(1)->name,'apple');
	}

	public function testNestedCollections()
	{
		$this->connection->expectOnce('get');
		$this->connection->setReturnValue('get',array(
			(object) array('id'=>1,'street'=>'48 Cambridge St'),
			(object) array('id'=>2,'street'=>'424 Smith St'),
			), array('people/1/addresses'));

		$collection = $this->model->people(1)->addresses();

		$this->assertEqual($collection->count(),2);
		$this->assertEqual($collection[0]->street,'48 Cambridge St');
	}

	public function testIteratingCollections()
	{
		$this->connection->expectOnce('get');
		$this->connection->setReturnValue('get',array(
			(object) array('id'=>1,'street'=>'48 Cambridge St'),
			(object) array('id'=>2,'street'=>'424 Smith St'),
			), array('people/1/addresses'));

		$collection = $this->model->people(1)->addresses();
		$counter = 0;

		foreach($collection as $address)
		{
			$counter++;
		}

		$this->assertEqual(2, $counter);
	}

	public function testTopLevelResource()
	{
		$this->connection->expectOnce('get');
		$this->connection->setReturnValue('get',(object) array(
			'id'=>1,
			'name'=>'apple'
			), array('fruit'));

		$this->assertEqual($this->model->fruit()->name,'apple');
	}

	public function testSavingAResource()
	{
		$this->connection->expectOnce('get');
		$this->connection->expectOnce('put',array('things/1/fruits/5','*'));
		$this->connection->setReturnValue('get',array(
			(object)array(
				'id'=>5,
				'name'=>'apple'
				)
			), array('things/1/fruits'));

		$apple = $this->model->things(1)->fruits()->first();
		$this->assertEqual($apple->name,'apple');

		$apple->name = 'pear';
		$apple->save();
	}

	public function testLongChains()
	{
		$this->connection->expectOnce('get');
		$this->connection->setReturnValue('get',(object) array(
			'id'=>1,
			'name'=>'apple'
			), array('things/with/stuff/1/fruits'));

		$apple = $this->model->things()->with()->stuff(1)->fruits();
		$this->assertEqual($apple->name, 'apple');
	}

	public function testStringCollectionIdentifiers()
	{
		$this->connection->expectOnce('post');
		$this->connection->setReturnValue('post',(object) array(
			'id'=>'1',
			'key'=>'value'
			), array('things/1/fruits/apple/keys','*'));
		$this->connection->setReturnValue('get',(object) array(
			'id'=>'1',
			'color'=>'red',
			), array('things/1/fruits/apple'));

		// things/1/fruits/apple
		$apple = $this->model->things(1)->fruits('apple');

		// things/1/fruits/apple { id: 1, color:red }
		$this->assertEqual($apple->color, 'red');

		// things/1/fruits/apple
		$apple->keys()->create(array(
			'key'=>'value'
			));
	}

	public function testCreateResourceInCollection()
	{
		$this->connection->expectOnce('post');
		$this->connection->expectNever('get');
		$this->connection->setReturnValue('post',(object) array(
			'id'=>'5',
			'key'=>'value'
			), array('things','*'));

		$newThing = $this->model->things()->create(array('key' => 'value'));
		$this->assertEqual($newThing->key, 'value');
	}
}

