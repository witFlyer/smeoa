<?php
    // +----------------------------------------------------------------------
    // | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
    // +----------------------------------------------------------------------
    // | Copyright (c) 2012 http://thinkphp.cn All rights reserved.
    // +----------------------------------------------------------------------
    // | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
    // +----------------------------------------------------------------------
    // | Author: liu21st <liu21st@gmail.com>
   // +----------------------------------------------------------------------

    // 定义ThinkPHP框架路径
    //定义项目名称和路径
	if(file_exists("install.php")){
		Header("Location: /install.php");
	}
    define('APP_NAME', 'App');
    define('APP_PATH', './App/');
    define('APP_DEBUG', TRUE);

    /*
     *---------------------------------------------------------------
    * 系统环境
    *---------------------------------------------------------------
    *
    * 更改环境将影响到日志输出及错误报告
    *
    * 支持以下参数：
    *
    *     development	开发环境
    *     testing		测试环境
    *     production	生产环境
    *
    */
    /*if (isset($_SERVER['ENVIRONMENT']))
    {
    	define('ENVIRONMENT', $_SERVER['ENVIRONMENT']);
    }
    else
    {
    	define('ENVIRONMENT', 'production');
    }*/
    
    // 加载框架入口文件
    require( "./ThinkPHP/ThinkPHP.php");
    //实例化一个网站应用实例
?>