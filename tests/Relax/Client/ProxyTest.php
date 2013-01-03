<?php

class Relax_Client_ProxyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = Mockery::mock();
        //$this->model =  new Relax_Client_Model(new Commerce_LoggingConnection($this->connection));
        $this->model = new Relax_Client_Model($this->connection);
    }

    public function testCollectionSelector()
    {
        $this->connection
            ->shouldReceive('get')
            ->andReturn((object) array(
                'id'=>1,
                'name'=>'apple'
            ), array('fruits/1'))
            ->once();

        $this->assertEquals($this->model->fruits(1)->name,'apple');
    }

    public function testNestedCollections()
    {
        $this->connection
            ->shouldReceive('get')
            ->andReturn(array(
                (object) array('id'=>1,'street'=>'48 Cambridge St'),
                (object) array('id'=>2,'street'=>'424 Smith St'),
            ), array('people/1/addresses'))
            ->once()
            ;

        $collection = $this->model->people(1)->addresses();

        $this->assertEquals($collection->count(),2);
        $this->assertEquals($collection[0]->street,'48 Cambridge St');
    }

    public function testIteratingCollections()
    {
        $this->connection
            ->shouldReceive('get')
            ->andReturn(array(
                (object) array('id'=>1,'street'=>'48 Cambridge St'),
                (object) array('id'=>2,'street'=>'424 Smith St'),
            ), array('people/1/addresses'))
            ->once()
            ;

        $collection = $this->model->people(1)->addresses();
        $counter = 0;

        foreach ($collection as $address) {
            $counter++;
        }

        $this->assertEquals(2, $counter);
    }

    public function testTopLevelResource()
    {
        $this->connection
            ->shouldReceive('get')
            ->andReturn((object) array(
                'id'=>1,
                'name'=>'apple'
            ), array('fruit'))
            ->once();

        $this->assertEquals($this->model->fruit()->name,'apple');
    }

    public function testSavingAResource()
    {
        $this->connection
            ->shouldReceive('put')->with('things/1/fruits/5',\Mockery::any())->once();

        $this->connection
            ->shouldReceive('get')
            ->with('things/1/fruits')
            ->andReturn(array(
                (object) array(
                    'id'=>5,
                    'name'=>'apple'
                )))
            ->once();

        $apple = $this->model->things(1)->fruits()->first();
        $this->assertEquals($apple->name,'apple');

        $apple->name = 'pear';
        $apple->save();
    }

    public function testLongChains()
    {
        $this->connection
            ->shouldReceive('get')
            ->with('things/with/stuff/1/fruits')
            ->andReturn((object) array(
                'id'=>1,
                'name'=>'apple'
            ))
            ->once();

        $apple = $this->model->things()->with()->stuff(1)->fruits();
        $this->assertEquals($apple->name, 'apple');
    }

    public function testStringCollectionIdentifiers()
    {
        $this->connection
            ->shouldReceive('post')
            ->with('things/1/fruits/apple/keys',\Mockery::any())
            ->andReturn((object) array(
                'id'=>'1',
                'key'=>'value'
            ), array())
            ->once()
            ;

        $this->connection
            ->shouldReceive('get')
            ->with('things/1/fruits/apple')
            ->andReturn((object) array(
                'id'=>'1',
                'color'=>'red',
            ))
            ->once();

        // things/1/fruits/apple
        $apple = $this->model->things(1)->fruits('apple');

        // things/1/fruits/apple { id: 1, color:red }
        $this->assertEquals($apple->color, 'red');

        // things/1/fruits/apple
        $apple->keys()->create(array(
            'key'=>'value'
            ));
    }

    public function testCreateResourceInCollection()
    {
        $this->connection->shouldReceive('get')->never();
        $this->connection->shouldReceive('post')
            ->with('things', \Mockery::any())
            ->andReturn((object) array(
                'id'=>'5',
                'key'=>'value'
            ))
            ->once()
            ;

        $newThing = $this->model->things()->create(array('key' => 'value'));
        $this->assertEquals($newThing->key, 'value');
    }
}
