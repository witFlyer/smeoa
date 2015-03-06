<?php
    if (!defined('THINK_PATH')) exit();
    $array=array(    	
        'LOAD_EXT_CONFIG'	=>'db,auth,wechat,ldap,params',  //载入扩张配置文件
		'VAR_PAGE'	=>'p',
		'TMPL_EXCEPTION_FILE'=>APP_PATH.'/Tpl/Public/error.html',
		'TMPL_NO_HAVE_AUTH'=>APP_PATH.'/Tpl/Public/no_have_auth.html',	
		'TMPL_CACHE_ON' => false,
		'TOKEN_ON'=>false,
		'URL_CASE_INSENSITIVE' =>   true,
		'TMPL_STRIP_SPACE'=>false,
		'URL_HTML_SUFFIX' => '',
		'DB_FIELDS_CACHE'=>false,
        'SESSION_AUTO_START'=>true,
        'USER_AUTH_KEY'	=>'auth_id',	// 用户认证SESSION标记
        'ADMIN_AUTH_KEY'			=>'administrator',        
        'USER_AUTH_GATEWAY'=>'login/index',// 默认认证网关
        'DB_LIKE_FIELDS'            =>'content|remark',
		'SAVE_PATH'=>"Data/Files/",
        'SHOW_PAGE_TRACE'=>0, //显示调试信息
//    	/* 错误页面模板 */
//		'TMPL_ACTION_ERROR'     =>APP_PATH.'/Tpl/View/error.html', // 默认错误跳转对应的模板文件
//		'TMPL_ACTION_SUCCESS'   =>APP_PATH.'/Tpl/View/success.html', // 默认成功跳转对应的模板文件
//		'TMPL_EXCEPTION_FILE'   =>APP_PATH.'/Tpl/View/exception.html',// 异常页面的模板文件
    );
    return $array;
?>