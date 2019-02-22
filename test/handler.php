<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// класс для работы с fetchr
require_once ('../../../vendor/autoload.php');


//api ключ fetchr
require_once ("user_token.php");


//==================================================

//обрабатывем данные
$post_data = @$_POST["data"];

$post_data = json_decode( $post_data, true );

$fetchr_data = @$post_data["fetchr_data"];

$action = @$post_data["action"];


$fetchr = new Seal\fetchr_sdk( USER_TOKEN );


// запрос 
$resp = $fetchr->init( $action, $fetchr_data );

echo  json_encode( $resp );



?>