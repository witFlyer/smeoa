<?php
return array(
	// 邮件配置
 	'ORG_EMAIL' => array(
	    'SMTP_HOST'   => '61.152.91.44', //SMTP服务器
	    'SMTP_PORT'   => '465', //SMTP服务器端口
	    'SMTP_USER'   => 'oa@pif.com', //SMTP服务器用户名
	    'SMTP_PASS'   => 'Hfo[pD&#*(K@', //SMTP服务器密码
	    'FROM_EMAIL'  => 'oa@pif.org.cn', //发件人EMAIL
	    'FROM_NAME'   => '浦东金融服务中心', //发件人名称
	    'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
	    'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
		
		'SMTP_SECURE' => TRUE, // 使用安全协议
 		'MAX_TRY'	  => 5,	// 发送尝试次数
 	),
);