<?php 

namespace Seal;



class fetchr_sdk 
{

	private $token_key;
	private $debug;

	private $current_method_init; // название метода который выполняется

	const request_url = 'https://api.order.fetchr.us/';

    const MAX_WEIGHT_IN_BAG = 300; // макс вес в 1й упаковке



    // если вызвали не существующий метод
    public function __call( $methodName, $args ) {
	    return $this->errLog( self::METHOD_CALL_ERR, $methodName );
    }


	public function __construct( $token_key, $debug = null ){
		if( !$token_key ) return $this->errLog( self::AUTH_FAIL_ERR );

		$this->token_key = $token_key;
		$this->debug = $debug;
	
	}





	//------------------------------------------
	//       ERRORS
	// -----------------------------------------
    const AUTH_FAIL_ERR = 1;
    const METHOD_CALL_ERR = 2;
    const PARSE_RESPONSE_ERR = 3;
    const EMPTY_DATA_ERR = 4;
    const VALUE_EXIST_ERR = 5;
    const METHOD_SETTINGS_ERR = 6;

    private function get_errors_message( $id, $err_name = null ){

		$available_error = array(
	    	self::AUTH_FAIL_ERR => "Не указаны данные авторизации",
	    	self::METHOD_CALL_ERR => "Метод {$err_name} не существует",
	    	self::PARSE_RESPONSE_ERR => "Невозможно преобразовать ответ FETCHR",
	    	self::EMPTY_DATA_ERR => "Неверная структура данных",
	    	self::VALUE_EXIST_ERR => "Не передано обязательное значение - {$err_name}",
	    	self::METHOD_SETTINGS_ERR => "Не установлены настройки {$err_name} для даного метода ",
	    );

    	return $available_error[$id];
    }


    // вывод ошибок
    private function errLog( $id, $value = '' ) {

		$mess = $this->get_errors_message($id, $value);

		$err['body'] = array(
			"status" => "error",
			"error_code" => $id,
			"message" => $mess,
		);
		
		if( !empty($this->current_method_init) ) $err['method'] = $this->current_method_init;

		return $err;
   		/*echo 
   	
        die();*/
    }
	//------------------------------------------
	// 		END	ERRORS
	// -----------------------------------------







	//=================================================
	//       	     ОСНОВНОЙ ОБРАБОТЧИК 
	//           Все запросы идут черз него
	//=================================================
	public function init( $action, $data ){

/*		var_dump($data);
		die();*/

		$available_methods = array_keys( $this->methods );

		// ВЫЗОВ ТОЛЬКО ДОСТУПНЫХ МЕТОДОВ
		if ( !in_array($action, $available_methods) ) return $this->errLog(self::METHOD_CALL_ERR, $methodName);

		//устанваливаем название текущего метода
		$this->current_method_init = $action;


		//вызываем нужный метод
		$response = $this->$action( $data );
		$response['method'] = $action;

		return $response;

	}

	//=================================================
	//=================================================

	// проверяет наявность значения в месиве или выдает ошибку
	private function checkRequiredValue( $val, $err_id, array $arr = array() ){

		if(!!$arr){
			return (!empty($arr[$val]) ? $arr[$val] : $this->errLog( $err_id, $val) );
		}else{
			return (!empty($val) ? $val : $this->errLog( $err_id, $val) );
		}
	}


	// возвращает масив с настройками текущего метода
	private function getResponseSettings( $method_name ){

		$arr = array();
		$settings_method =  $this->methods[$method_name];

		$path = $settings_method['path'];
		$method = $settings_method['method'];

		$arr['header'] = $this->generateHeader();
		$arr['url'] = self::request_url.$path;
		$arr['method'] = $method;

		return $arr;
	}


	// генерируем заголовки
	private function generateHeader(){
		$key = $this->token_key;
		return array(
			"Content-Type: application/json",
			"Authorization: Bearer {$key}",
			
		);
	}


	// пишем лог в файл
    private function addLogResponse($data, $header = null){
    	
    	file_put_contents("{$this->debug}/response_log__".date("d-m-Y").".txt", 
    		">>> {$this->current_method_init} ".date('(h:i)')."Header - {$header} \r\n".print_r($data, true)."\r\n\r\n", FILE_APPEND);
    	
    }





	// ======================================
	//         отправка данных 
	// ======================================
   	private function sendRequest( $method_name, $url, array $header = array(), $data = null ){

    	$curl = curl_init();

	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	    // устанавливаем заголовки
	    if( !!$header ){
		    curl_setopt($curl, CURLOPT_HTTPHEADER,  $header);
		    curl_setopt($curl, CURLOPT_HEADER, false);
	    }

/*	    // устанавливаем метод если это не пост и не гет
	    if( mb_strtolower($method_name) !== "post" && mb_strtolower($method_name) !== "get" ){
	    	
	    }*/
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method_name);

	    // утсанавливаем данные если это не гет
	    if( mb_strtolower($method_name) !== "get" ){
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    }


	    // если передаем данные постом
	    if( mb_strtolower($method_name) === "post" ){
		    curl_setopt($curl, CURLOPT_POST, true);
	    }

    
	    $out = curl_exec($curl);
	    $content_type = curl_getinfo( $curl, CURLINFO_CONTENT_TYPE );	
	    
	    curl_close($curl);
	   

	    $response = @json_decode($out, true);

        //пишем ответ в лог
        if( !!$this->debug ){
        	 $this->addLogResponse( $out, $content_type );
        }

	    // если прислали не json - выводим ошибку
	    if( !$response ){
	    	return $this->errLog( self::PARSE_RESPONSE_ERR );
	    }

        $response_arr = array('method'=> $this->current_method_init, "body" => $response, "header" =>$content_type);

        /*$this->checkResponse($response_arr);*/

	    return $response_arr;
    } 






    // если в данных прислали location разбиваем ее на country и city
/*    private function locationParse(&$curr_data){
		if( !empty($curr_data['location']) ){
			$location = explode(',', $curr_data['location']);

			$country = $location[0];
			$city = !empty( $location[1] ) ? $location[1] : '' ;
			
			$curr_data['receiver_country'] = $country;
			$curr_data['receiver_city'] = $city;
		}
    }*/



    // проверяет наличие обязательных значений
    // возвращает строку для ABW(бланк пдф) description 
    private function itemsParse(&$curr_data){
		$tmp_str = '';
		if( !empty($curr_data['items']) && is_array($curr_data['items']) ){
			$count_items = count($curr_data['items']);

			for ($i=0; $i < $count_items; $i++) { 
				$curr_item = &$curr_data['items'][$i];


				if(!empty($curr_item['description'])){
					$desc = $curr_item['description'];
				}else{
					return $this->errLog(self::VALUE_EXIST_ERR, "description");
				}
				
				if( !empty($curr_item['quantity']) ){
					$desc = $curr_item['quantity'];
				}else{
					return $this->errLog(self::VALUE_EXIST_ERR, "quantity");
				}					

				$desc = str_replace(':', ' ', $desc);

				$tmp_str .= "{$desc} : {$quantity},";

			}

			return rtrim($tmp_str, ',');
		}else{
			return $this->errLog(self::VALUE_EXIST_ERR, "description | quantity");	
		}

		return $tmp_str;
		
    }




    // парсим строку разбиваем ее не части 
    private function splitValue($data){
		// если значения пришли строкой 
		if( is_string( $data ) ){
			$arr_data = array();
			$data = explode(",", $data);
			$count_order = count($data);

			for ( $i=0; $i < $count_order; $i++ ) { 
				$curr_data = trim( $data[$i] );

				// если значение не пустое то добавляем в рузультирующий масив
				if(!empty($curr_data)) $arr_data[] = $curr_data;
				
			}
			return $arr_data;

		// если значения пришли масивом 
		}elseif(is_array($data)){
			return $data;

		}else{
			return $this->errLog( self::EMPTY_DATA_ERR, "tracking_number" );	
		}

    }	




/*    // вычисляем количество пакетов (в 1м пакете max 300кг)
    private function checkCountBag ( $weight ){
    	return ceil( $weight / self::MAX_WEIGHT_IN_BAG );
    }*/



	// настройки для каждого метода
	private $methods = array(
		
		'сreateDropshipOrders' => array(
			'method' => 'POST',
			'path' => 'order/',
		),

		'сreateReverseOrders' => array(
			'method' => 'POST',
			'path' => 'reverse/',
		),

		'сreateFulfillmentOrders' => array(
			'method' => 'POST',
			'path' => 'fulfillment/',
		),


		'getFulfillmentSKUInfo' => array(
			'method' => 'GET',
			'path' => 'stock/',
		),
		






	
		'getOrderStatus' => array(
			'method' => 'GET',
			'path' => 'order/status/',
		),

		'getOrderHistory' => array(
			'method' => 'GET',
			'path' => 'order/history/',
		),

		'getBulkOrderStatus' => array(
			'method' => 'POST',
			'path' => 'order/status/',
		),

		'getBulkOrderHistory' => array(
			'method' => 'POST',
			'path' => 'order/history/',
		),





		'getAWBLink' => array(
			'method' => 'GET',
			'path' => 'awb/',
		),




		'cancelOrder' => array(
			'method' => 'PUT',
			'path' => 'order/status',
		),

	);



    //======================================
    //            MAIN METHODS
    //======================================

	// -------------------------------------
	// Create Dropship Orders
	// используеться когда:
	// Fetchr забирает продукты ИЗ МЕСТОПОЛОЖЕНИЯ ОТПРАВИТЕЛЯ и доставляет их получателю.
	// -------------------------------------
    private function сreateDropshipOrders( $data ){

    	$method_name = __FUNCTION__;

    	$count_data = count( $data['data'] );

    	//обрабатываем каждый заказ
    	for ($i=0; $i < $count_data; $i++) { 
    		$curr_data = &$data['data'][$i];

/*    		// проверяем количество пакетов
    		if( !empty($curr_data['weight']) && empty($curr_data['bag_count']) ){
    			$count_bag = $this->checkCountBag ( $curr_data['weight'] );
    			$curr_data['bag_count'] = $count_bag;
    		}   */ 	
	    	
    		//проверка на location (устанавливаем город и страну)
    		//$this->locationParse($curr_data);

    		//добавляем description
    		if( empty($curr_data['description']) ){
    			$descABW = $this->itemsParse($curr_data);

		    	//возвращаем если ошибка
		    	if( !empty($descABW['body']['status']) && $descABW['body']['status'] == 'error' ){
		    		return $descABW;
		    	}
		    	
    			$curr_data['description'] = $descABW;
    		}
   		
    	}


    	$resp_settings = $this->getResponseSettings( $method_name );

    	$json_data = json_encode($data);
    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'], $resp_settings['header'], $json_data );

    	return $response;
    }



	// -------------------------------------
	// Create Reverse Orders
	// Обратный заказ - это заказ на получение товаров (которые необходимо вернуть) у получателя и отправка 
	// обратно отправителю
	// -------------------------------------
    private function сreateReverseOrders( $data ){

    	$method_name = __FUNCTION__;

    	$count_data = count( $data['data'] );

    	//обрабатываем каждый заказ
    	for ($i=0; $i < $count_data; $i++) { 
    		$curr_data = &$data['data'][$i];


    		// проверяем количество пакетов
    		if( !empty($curr_data['package_data']['weight']) && empty($curr_data['package_data']['package_count']) ){
    			$count_bag = $this->checkCountBag ( $curr_data['weight'] );
    			$curr_data['package_count'] = $count_bag;
    		}    	
   		
    	}


    	$resp_settings = $this->getResponseSettings( $method_name );

    	$json_data = json_encode($data);
    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'], $resp_settings['header'], $json_data );

    	return $response;
    }




	// -------------------------------------
	// Create Fulfillment Orders 
	// Fulfillment Orders - это заказ по сбору товара на складе Fetchr 
	// и доставке получателю.
	// -------------------------------------
    private function сreateFulfillmentOrders( $data ){

    	$method_name = __FUNCTION__;

    	$count_data = count( $data['data'] );

    	//обрабатываем каждый заказ
    	for ($i=0; $i < $count_data; $i++) { 
    		$curr_data = &$data['data'][$i];

    		// проверяем наличие скидки
    		if( !isset($curr_data['details']['discount']) ){
    			$curr_data['details']['discount'] = 0;
    		}
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );

    	$json_data = json_encode($data);
    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'], $resp_settings['header'], $json_data );

    	return $response;
    }




    // -------------------------------------
	// Get Fulfillment SKU Stock Info
	// используется для получения информации о складе для 
	// выполнения заказов по sku
	// -------------------------------------

    private function getFulfillmentSKUInfo( $data ){

    	$method_name = __FUNCTION__;

    	// обрабатываем обязательные параметры
    	$sku = $this->checkRequiredValue( 'sku', self::VALUE_EXIST_ERR, $data );
    	
    	//возвращаем если ошибка
    	if( !empty($sku['body']['status']) && $sku['body']['status'] == 'error' ){
    		return $sku;
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );

    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'].$sku.'/' , $resp_settings['header'] );

    	return $response;	

    }











    //==========================================================

    // -------------------------------------
	// Get Order Status
	// Получаем последний статус заказа
	// -------------------------------------

    private function getOrderStatus( $data ){

    	$method_name = __FUNCTION__;

    	// обрабатываем обязательные параметры
    	$track_num = $this->checkRequiredValue( 'tracking_number', self::VALUE_EXIST_ERR, $data );

    	//возвращаем если ошибка
    	if( !empty($track_num['body']['status']) && $track_num['body']['status'] == 'error' ){
    		return $track_num;
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );

    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'].$track_num.'/' , $resp_settings['header'] );


		$arr_body = $response['body'];
		$response['body'] = array(
			'status' => 'OK',
			'data' => array($arr_body)
		);

    	return $response;	

    }



    // -------------------------------------
	// Get Order History
	// Получаем историю статусов для заказа
	// -------------------------------------

    private function getOrderHistory( $data ){

    	$method_name = __FUNCTION__;

    	// обрабатываем обязательные параметры
    	$track_num = $this->checkRequiredValue( 'tracking_number', self::VALUE_EXIST_ERR, $data );

    	//возвращаем если ошибка
    	if( !empty($track_num['body']['status']) && $track_num['body']['status'] == 'error' ){
    		return $track_num;
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );

    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'].$track_num.'/' , $resp_settings['header'] );

    	
		$arr_body = $response['body'];
		$response['body'] = array(
			'status' => 'OK',
			'data' => array($arr_body)
		);

    	return $response;	

    }





 	// -------------------------------------
	// Get Bulk Order Status
	// Получаем последний статус заказа для нескольких ттн
	// -------------------------------------

    private function getBulkOrderStatus( $data ){

    	$method_name = __FUNCTION__;

    	// обрабатываем обязательные параметры
    	$track_num = $this->splitValue($data['tracking_numbers']);

    	//возвращаем если ошибка
    	if( !empty($track_num['body']['status']) && $track_num['body']['status'] == 'error' ){
    		return $track_num;
    	}
    	
    	$resp_settings = $this->getResponseSettings( $method_name );

    	$json_data['tracking_numbers'] = $track_num;
    	$json_data = json_encode($json_data);

    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'], $resp_settings['header'], $json_data );


		$arr_body = $response['body'];
		$response['body'] = array(
			'status' => 'OK',
			'data' => $arr_body
		);
    	


    	return $response;	

    }


	// -------------------------------------
	// Get Bulk Order History
	// Получаем историю статусов нескольких ттн
	// -------------------------------------

    private function getBulkOrderHistory( $data ){

    	$method_name = __FUNCTION__;

    	// обрабатываем обязательные параметры
    	$track_num = $this->splitValue($data['tracking_numbers']);

    	//возвращаем если ошибка
    	if( !empty($track_num['body']['status']) && $track_num['body']['status'] == 'error' ){
    		return $track_num;
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );

    	$json_data['tracking_numbers'] = $track_num;
    	$json_data = json_encode($json_data);

    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'], $resp_settings['header'], $json_data );

		$arr_body = $response['body'];
		$response['body'] = array(
			'status' => 'OK',
			'data' => $arr_body
		);

    	return $response;	

    }
















    // ===========================================================
    
	// -------------------------------------
	// Get AWB Link
	// получаем ссылку пдф
	// -------------------------------------

    private function getAWBLink( $data ){

    	$method_name = __FUNCTION__;

		// обрабатываем обязательные параметры
    	$track_num = $this->checkRequiredValue( 'tracking_number', self::VALUE_EXIST_ERR, $data );

    	//возвращаем если ошибка
    	if( !empty($track_num['body']['status']) && $track_num['body']['status'] == 'error' ){
    		return $track_num;
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );
	
		$query_type = !empty($data['type']) ? '/?type='.$data['type'] : '';


		$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'].$track_num.$query_type, $resp_settings['header'] );

		//$response = array('header'=> '', 'method'=> $method_name, 'body'=> array("status"=> "success", "data"=>'https://cms-awbs.fetchr.us/label8x4_34170409312003.pdf') );


    	return $response;	

    }
    










 	// ===========================================================
    
	// -------------------------------------
	// Cancel Order
	// отмена созданого заказа
	// -------------------------------------

    private function cancelOrder( $data ){

    	$method_name = __FUNCTION__;

    	// обрабатываем обязательные параметры
    	$track_num = $this->splitValue($data['tracking_no']);

    	//возвращаем если ошибка
    	if( !empty($track_num['body']['status']) && $track_num['body']['status'] == 'error' ){
    		return $track_num;
    	}

    	$resp_settings = $this->getResponseSettings( $method_name );

    	$json_data['tracking_no'] = $track_num;

    	// устанавливаем значение по умалчанию
    	$json_data['status'] = empty($data['status']) ? 'cancel' : $data['status'] ;

    	$json_data = json_encode($json_data);//http_build_query($json_data);//

    	$response = $this->sendRequest( $resp_settings['method'], $resp_settings['url'], $resp_settings['header'], $json_data );

    	return $response;	

    }
    




}












?>