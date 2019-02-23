# Fetchr delivery SDK
Wet... dirt... code


## Установка

> Минимальные требования — PHP 5.3+.

> curl enabled

```bash
composer require seal/fetchr_sdk
```

## Test

В директории test создан интерфейс для тестирования 
для этого вставте в файл user_token.php свой api token

> Токен можно получить обратившись в службу поддержки fetchr ( support@fetchr.us )



## Использование

```php

require_once 'vendor/autoload.php';

$fetchr = new Seal\fetchr_sdk( USER_TOKEN );


/**
 * @param $method_name - метод к которому обращаетесь
 * 
 * @param $data - данные которые передаете 
 * ( структура полностю соответствует данным из документации https://xapidoc.docs.apiary.io/ )
 *
 * @return array
 */

$response = $fetchr->init( $method_name, $data );

	
```

### Ответ

```json
{

	"body": {"SOME DATA FETCHR"},
	"header": "response header",
	"method": "the method that was used",

}
```

В $response['body'] находятся данные соответствующие ответу из документации https://xapidoc.docs.apiary.io/ 

!!! ИЗМЕНЕН ОТВЕТ ДЛЯ МЕТОДОВ ТРЕКИНГА !!!
(для страндартизации всех ответов)

Пример ответа для методов трекинга:

```json

{

	"body": {
		"status":"OK",
		"data": [

			{
			  "tracking_information": {
			    "status_name": "uploaded",
			    "status_code": "UPL",
			    "status_date": "2018-06-27T10:20:37.317385",
			    "source": "fetchr",
			    "status_date_local": "2018-06-27 14:20:46",
			    "status_description": "Order Created"
			  },
			  "order_information": {
			    "tracking_no": "34146607575779",
			    "client_ref": "199619721aabb12345"
			  },

			  "status":"OK"
			}

		]
	},
	"header": "response header",
	"method": "the method that was used",

}

```


## Методы

- Order Creation:
	- [x] Create Dropship Orders - Fetchr забирает продукты ИЗ МЕСТОПОЛОЖЕНИЯ ОТПРАВИТЕЛЯ и доставляет их получателю.
		
		```php
			$response = $fetchr->init( 'createDropshipOrders', $data );
		```


	- [x] Create Reverse Orders - Обратный заказ - это заказ на получение товаров (которые необходимо вернуть) у получателя и отправка обратно отправителю 

		```php
			$response = $fetchr->init( 'createReverseOrders', $data );
		```


	- [x] Create Fulfillment Orders - Fulfillment Orders - это заказ по сбору товара на складе Fetchr и доставке получателю.
		
		```php
			$response = $fetchr->init( 'createFulfillmentOrders', $data );
		```

	- [x] Get Fulfillment SKU Stock Info - Используется для получения информации о складе для выполнения заказов по sku
		
		```php
			$response = $fetchr->init( 'getFulfillmentSKUInfo', $data );
		```

- Order Tracking:
	- [x] Get Order Status - Получаем последний статус для заказа
		
		```php
			$response = $fetchr->init( 'getOrderStatus', $data );
		```


	- [x] Get Order History - Получаем историю статусов для заказа
		
		```php
			$response = $fetchr->init( 'getOrderHistory', $data );
		```


	- [x] Get Bulk Order Status - Получаем последний статус заказа для нескольких ттн
		
		```php
			$response = $fetchr->init( 'getBulkOrderStatus', $data );
		```


	- [x] Get Bulk Order History - Получаем историю статусов нескольких ттн
		
		```php
			$response = $fetchr->init( 'getBulkOrderHistory', $data );
		```


- Order Service:
	- [x] Get AWB Link - Получаем ссылку пдф
		
		```php
			$response = $fetchr->init( 'getAWBLink', $data );
		```

- Cancel Order:
	- [x] Cancel Order - отмена созданого заказа
		
		```php
			$response = $fetchr->init( 'cancelOrder', $data );
		```

- Schedule:
	- [ ] Get Timeslots
	- [ ] Schedule Order



## Лицензия

Данный SDK распространяется под лицензией [MIT](http://opensource.org/licenses/MIT).

