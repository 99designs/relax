Relax - Easy interaction with RESTful APIs
===============================================

Relax is a client designed to interact with APIs that conform to the following:

- Return JSON
- Respond to GET, PUT AND POST
- Urls map to the pattern of /collection/123/subcollection/234

Relax also provides a set of Openssl helper classes.

```php
<?php


$client = new \Ergo\Http\Client("http://mywebservice.io");

$model = new Relax_Client_Model($client);
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


// GET queries
$items = $model->transactions(1)->items(); // returns a collection from /transactions/1/items
$address = $model->customers(1)->address(); // returns a resource from /customers/1/address

print $address->streetname; // returns the streetname property from the json doc
print $items->count(); // returns the number of items in the transaction

// PUT queries
$model->customers()->create(array('i'=>'x'));

// POST queries
$model->customers(1)->set('name','Fred')->save();

```

Copyright
---------

Copyright (c) 2012 99designs See [LICENSE](https://github.com/99designs/relax/blob/master/LICENSE) for details.

