<?php

/**
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_CollectionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = new Relax_Client_ArrayConnection();
        $this->connection
            ->inject('customers',array(
                (object) array('id'=>55,'name'=>'Testy McTesterson'),
                (object) array('id'=>56,'name'=>'Screech'),
                ));
    }

    public function testIteratingACollection()
    {
        $node = new Relax_Client_Node('Customer',$this->connection);
        $collection = new Relax_Client_Collection($node, 'customers');
        $customers = array();

        foreach ($collection as $customer) {
            $customers[] = $customer;
        }

        $this->assertEquals(2, count($customers));
        $this->assertEquals($customers[0]->name,'Testy McTesterson');
        $this->assertEquals($customers[1]->name,'Screech');
    }

    public function testArrayAccess()
    {
        $node = new Relax_Client_Node('Customer',$this->connection);
        $collection = new Relax_Client_Collection($node, 'customers');

        $this->assertEquals(2, count($collection));
        $this->assertEquals($collection[0]->name,'Testy McTesterson');
        $this->assertEquals($collection[1]->name,'Screech');
    }
}
