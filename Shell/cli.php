<?php
//	header("content-type:text/html;charset=utf-8");

	define('ROOT_PATH', 	dirname(dirname(__FILE__)));
	define('THINK_PATH', 	ROOT_PATH .'/ThinkPHP/');
    define('APP_NAME', 		'Cli');
    define('APP_PATH', 		ROOT_PATH .'/Cli/');
    define('APP_DEBUG', 	TRUE);
    define('MODE_NAME', 	'cli');  // 采用CLI运行模式运行
	
	$depr = '/';
	$path = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
	if ( ! empty($path))
	{
		$params = explode($depr, trim($path, $depr));
	}
//	! empty($params) ? $_GET['g'] = array_shift($params) : "";
	! empty($params) ? $_GET['m'] = array_shift($params) : "";
	! empty($params) ? $_GET['a'] = array_shift($params) : "";
	if (count($params) > 1)
	{
		// 解析剩余参数 并采用GET方式获取
		preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',', $params));
	}
	
    // 加载框架入口文件
    require(THINK_PATH ."ThinkPHP.php");
    //实例化一个网站应用实例
?>