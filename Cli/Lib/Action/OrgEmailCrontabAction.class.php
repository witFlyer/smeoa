<?php
class OrgEmailCrontabAction extends Action {
	
	protected $config = array('app_type' => 'public');
	
	function _initialize()
	{
		if ( ! (php_sapi_name() === 'cli' OR defined('STDIN')))
		{
			exit("Must the command line operation.");
		}
	}
	
	function index()
	{
		$config = C('ORG_SMS');
		$maxTry = isset($config['MAX_TRY']) ? intval($config['MAX_TRY']) : 1;
		
		$time = time();
		$map  = "is_send = 0 AND send_times < $maxTry "
//			  . " AND (email != '' AND email IS NOT NULL)"
			  . " AND (send_title != '' AND send_title IS NOT NULL)"
			  . " AND (send_message != '' AND send_message IS NOT NULL)"
			  . " AND (send_type = 0 OR (send_type = 1 AND send_timer <= $time))";
		$list = D('OrgEmail')->where($map)->limit(10)->select();
		
		if ($list)
		{
			foreach ($list AS $v)
			{
				if (is_email($v['email']))
				{
					$time 		 = time();
					$sendTitle 	 = str_replace('{$realname}', $v['realname'], $v['send_title']);
					$sendMessage = str_replace('{$realname}', $v['realname'], $v['send_message']);
					$sendTimes	 = intval($v['send_times']);
					
					if ($this->send($v['email'], $v['realname'], $sendTitle, $sendMessage))
					{
						D('OrgEmail')->save(array(
							'send_time' => $time,
							'is_send'	=> 1,
							'send_times'=> $sendTimes + 1,
						), array(
							'where' => array('id' => $v['id']),
						));
					}
					else
					{
						D('OrgEmail')->save(array(
							'send_time' => $time,
							'send_times'=> $sendTimes + 1,
						), array(
							'where' => array('id' => $v['id']),
						));
					}
				}
				else
				{
					echo $v['mobile'] ." not email account.". PHP_EOL;
					
					D('OrgEmail')->save(array(
						'is_send' => -1,
					), array(
						'where' => array('id' => $v['id']),
					));
				}
			}
		}
	}
	
	/**
	 * 系统邮件发送函数
	 * @param string $to    接收邮件者邮箱
	 * @param string $name  接收邮件者名称
	 * @param string $subject 邮件主题
	 * @param string $body    邮件内容
	 * @param string $attachment 附件列表
	 * @return boolean
	 */
	private function send($to, $name, $subject = '', $body = '', $attachment = NULL)
	{
		$config = C('ORG_EMAIL');
		vendor('Mail.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
		$mail             = new PHPMailer(); //PHPMailer对象
		$mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
		$mail->IsSMTP();  // 设定使用SMTP服务
		$mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
		// 1 = errors and messages
		// 2 = messages only
		$mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
		
		if ($config['SMTP_SECURE'])
		{
			$mail->SMTPSecure = 'ssl';                 // 使用安全协议
		}
		$mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
		$mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
		$mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
		$mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
		$mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
		$replyEmail       = $config['REPLY_EMAIL'] ? $config['REPLY_EMAIL'] : $config['FROM_EMAIL'];
		$replyName        = $config['REPLY_NAME']  ? $config['REPLY_NAME']  : $config['FROM_NAME'];
		$mail->AddReplyTo($replyEmail, $replyName);
		$mail->Subject    = $subject;
		$mail->MsgHTML($body);
		$mail->AddAddress($to, $name);
		if (is_array($attachment))
		{
			// 添加附件
			foreach ($attachment AS $file)
			{
				is_file($file) && $mail->AddAttachment($file);
			}
		}
//		return $mail->Send() ? true : $mail->ErrorInfo;
		
		if ($mail->Send())
		{
			return TRUE;
		}
		else
		{
			echo $mail->ErrorInfo . PHP_EOL;
			
			return FALSE;
		}
	}
}
