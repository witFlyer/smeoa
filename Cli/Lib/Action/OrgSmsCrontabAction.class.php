<?php
class OrgSmsCrontabAction extends Action {
	
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
//			  . " AND (mobile != '' AND mobile IS NOT NULL)"
			  . " AND (send_message != '' AND send_message IS NOT NULL)"
			  . " AND (send_type = 0 OR (send_type = 1 AND send_timer <= $time))";
		$list = D('OrgSms')->where($map)->limit(10)->select();
		
		if ($list)
		{
			foreach ($list AS $v)
			{
				if (is_mobile($v['mobile']))
				{
					$time 		 = time();
					$sendMessage = str_replace('{$realname}', $v['realname'], $v['send_message']);
					$sendResult  = $this->send($v['mobile'], $sendMessage);
					$sendTimes	 = intval($v['send_times']);
					
					if ($sendResult)
					{
						D('OrgSms')->save(array(
							'send_time' 	=> $time,
							'is_send'		=> 1,
							'msg_id'		=> isset($sendResult['mtmsgid']) ? $sendResult['mtmsgid'] : NULL,
							'send_response'	=> json_encode($sendResult),
							'send_times'	=> $sendTimes + 1,
						), array(
							'where' => array('id' => $v['id']),
						));
					}
					else
					{
						D('OrgSms')->save(array(
							'send_time' 	=> $time,
							'send_times'	=> $sendTimes + 1,
						), array(
							'where' => array('id' => $v['id']),
						));
					}
				}
				else
				{
					echo $v['mobile'] ." not mobile phone number.". PHP_EOL;
					
					D('OrgSms')->save(array(
						'is_send' => -1,
					), array(
						'where' => array('id' => $v['id']),
					));
				}
			}
		}
	}
	
	private function send($mobile, $message)
	{
		$config = C('ORG_SMS');
		vendor('Sms.class#esms');
		
		$sms = new Esms();
		$sms->host			= $config['HOST'];
		$sms->port			= $config['PORT'];
		$sms->spid			= $config['SPID'];
		$sms->sppassword	= $config['SPPASSWORD'];
		$sms->spsc			= $config['SPSC'];
		$sms->dc			= $config['DC'];
		$sms->sa			= $config['SA'];
		
		if (substr($mobile, 0, 2) != '86')
		{
			$mobile	= '86'. $mobile;
		}
		
		$message= iconv("UTF-8", "GB2312//IGNORE", $message);
		$send 	= $sms->sendSingle($mobile, $message);
		$result	= FALSE;
		
		if ($send)
		{
			$result = $sms->getResult();
		}
		else
		{
			echo "print error message: " . PHP_EOL;
			echo "\terrno: ". $sms->getErrno() . PHP_EOL;
			echo "\terror: ". $sms->getError() . PHP_EOL;
		}
		
		return $result;
	}
}
