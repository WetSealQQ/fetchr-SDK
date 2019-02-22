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
	- [x] [Create Dropship Orders](#сreateDropshipOrders)
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

### Ответ fetchr

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
	
### сreateDropshipOrders
- Fetchr забирает продукты ИЗ МЕСТОПОЛОЖЕНИЯ ОТПРАВИТЕЛЯ и доставляет их получателю.


### сreateReverseOrders
- Обратный заказ - это заказ на получение товаров (которые необходимо вернуть) у получателя и отправка обратно отправителю


### сreateFulfillmentOrders
- Fulfillment Orders - это заказ по сбору товара на складе Fetchr и доставке получателю.


### getFulfillmentSKUInfo
- Используется для получения информации о складе для выполнения заказов по sku


### getOrderStatus
- Получаем последний статус для заказа


### getOrderHistory
- Получаем историю статусов для заказа


### getBulkOrderStatus
- Получаем последний статус заказа для нескольких ттн


### getBulkOrderHistory
- Получаем историю статусов нескольких ттн


### getAWBLink
- Получаем ссылку пдф


### cancelOrder
- отмена созданого заказа






## Лицензия

Данный SDK распространяется под лицензией [MIT](http://opensource.org/licenses/MIT).

