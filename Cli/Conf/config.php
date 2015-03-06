<?php if ( ! defined('THINK_PATH')) exit();

$array = array(    	
    'LOAD_EXT_CONFIG'		=> 'db,smtp,sms',  	//载入扩张配置文件
	'URL_MODEL' 			=> 1, 				//PATHINFO模式
	'URL_CASE_INSENSITIVE' 	=> TRUE,
);

return $array;
?>