# Fetchr delivery SDK Coming soon ... or not
Wet... dirt... code


## Установка

> Минимальные требования — PHP 5.3+.

> curl enabled
```bash
composer require seal/fetchr_sdk
```

## Возможности

- Order Creation:
	- [x] [Create Dropship Orders]()
	- [x] [Create Reverse Orders]()
	- [x] [Create Fulfillment Orders]()
	- [x] [Get Fulfillment SKU Stock Info]()

- Order Tracking:
	- [x] [Get Order Status]()
	- [x] [Get Order History]()
	- [x] [Get Bulk Order Status]()
	- [x] [Get Bulk Order History]()

- Order Service:
	- [x] [Get AWB Link]()

- Cancel Order:
	- [x] [Cancel Order]()

- Schedule:
	- [ ] Get Timeslots
	- [ ] Schedule Order


> [ ] - не реализовано в SDK (возможно скоро появится)

## Использование

```php

require_once 'vendor/autoload.php';

$fetchr = new Seal\fetchr_sdk( USER_TOKEN );



/**
 * @param $method_name - метод к которому обращаетесь
 * @param $data - данные которые передаете ( структура полностю соответствует данным из документации https://xapidoc.docs.apiary.io/ )
 */

$response = $fetchr->init( $method_name, $data );


```

## Методы
	
	### 

	



## Лицензия

Данный SDK распространяется под лицензией [MIT](http://opensource.org/licenses/MIT).

