<?php

/**
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_ModelTest extends UnitTestCase
{
	public function setUp()
	{
		$this->connection = new Relax_Client_ArrayConnection();
	}

	public function testNestedRelationships()
	{
		$model = new Relax_Client_Model($this->connection);
		$model
			->hasMany(
				$model->define('Transaction')
					->hasMany('PaymentDevice')
					->hasMany('PaymentIntention')
					->hasMany('TransactionItem','items','item')
			)
			->hasMany(
				$model->define('Customer')
					->hasOne('Address')
			)
			;

		$items = $model->transactions(1)->items();
		$address = $model->customers(1)->address();

		$this->assertIsA($address,'Relax_Client_Resource');
		$this->assertIsA($items,'Relax_Client_Collection');

		$this->assertEqual($address->url(), 'customers/1/address');
		$this->assertEqual($items->url(), 'transactions/1/items');
	}

	public function testCreatingResource()
	{
		$model = new Relax_Client_Model($this->connection);

		$model
			->hasMany(
				$model->define('Customer')
					->hasOne('Address')
			)
			;

		$customer1 = $model->customers()->create(array('i'=>'x'));
		$customer2 = $model->customers()->create(array('i'=>'y'));

		$this->assertEqual($customer1->id, 1);
		$this->assertEqual($customer2->id, 2);

		$this->assertEqual($model->customers(1)->id, 1);
		$this->assertEqual($model->customers(1)->i,'x');
		$this->assertEqual($model->customers(2)->id, 2);
		$this->assertEqual($model->customers(2)->i,'y');
	}

	public function testRoundTrip()
	{
		$model = new Relax_Client_Model($this->connection);
		$model->hasMany(
			$model->define('Customer')
				->hasMany('Address','addresses')
				->hasMany('Transaction')
			)
			;

		$model->customers()->create(array(
			'name'=>'Lachlan'
			))
			->addresses()->create(array(
				'street'=>'Oriel Rd'
				));

		$this->assertEqual($model->customers(1)->name,'Lachlan');
		$this->assertEqual($model->customers(1)->addresses(1)->street,'Oriel Rd');

		$model->customers(1)->set('name','Fred')->save();
		$model->customers(1)->addresses(1)->set('street','Some St')->save();

		$this->assertEqual($model->customers(1)->name,'Fred');
		$this->assertEqual($model->customers(1)->addresses(1)->street,'Some St');
	}
}

