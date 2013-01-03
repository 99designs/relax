<?php

/**
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = new Relax_Client_ArrayConnection();
        $this->connection
            ->inject('test/55',(object) array(
                'id'=>55,
                'name'=>'Testy McTesterson',
                'optional'=>null,
            ));

        $this->resource = new Relax_Client_Resource(
            new Relax_Client_Node('Test',$this->connection),'test',55
            );
    }

    public function testUrlBuilding()
    {
        $this->assertEquals($this->resource->url(), 'test/55');
    }

    public function testPropertyAccess()
    {
        $this->assertEquals($this->resource->name,'Testy McTesterson');
        $this->resource->blargh = 'blargh';
        $this->assertEquals($this->resource->blargh,'blargh');
    }

    public function testNullPropertyAccess()
    {
        $this->assertFalse(isset($this->resource->optional));
        $this->assertEquals($this->resource->optional, null);
    }

    public function testMissingPropertyAccess()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->resource->doesnotexist;
    }

    public function testPropertyIsset()
    {
        $this->assertTrue(isset($this->resource->name),"isset should return true");
        $this->assertFalse(empty($this->resource->name),"empty should return false");
    }
}
