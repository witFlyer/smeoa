<?php

class Esms {
	
	public $host		= '';
	public $port		= '';
	public $spid		= '';
	public $sppassword	= '';
	public $spsc		= '';
	public $dc			= '';
	public $sa			= '';
	
	private $_errno = 0;
	private $_error = '';
	private $_result= NULL;
	
	public function getRandom($length = 6)
	{
		$pattern = '1234567890';
		$result  = '';
		
		for ($i = 0; $i < $length; $i++)
		{
			$result .= $pattern{mt_rand(0, 9)};
		}
		
		return $result;
	}
	
	public function sendSingle($num, $msg)
	{
		// 拼接 URI
		$request = '/sms/mt'
				 . '?command=MT_REQUEST&spid='. $this->spid .'&spsc='. $this->spsc .'&sppassword='. $this->sppassword
				 . '&sa='. $this->sa .'&da='. $num .'&dc='. $this->dc .'&sm='. self::encodeHexStr($msg);
		
		return self::getRequest($request);
	}
	
	public function sendMulti($arr, $msg)
	{
		if ( ! is_array($arr)) $arr = explode(',', $arr);
		if (count($arr) > 100)
		{
			$this->_errno = 1;
			$this->_error = '批量发送个数最大不要超过100个';
			
			return FALSE;
		}
		
		// 拼接 URI
		$request = '/sms/mt'
				 . '?command=MULTI_MT_REQUEST&spid='. $this->spid .'&spsc='. $this->spsc .'&sppassword='. $this->sppassword
				 . '&sa='. $this->sa .'&da='. implode(',', $arr) .'&dc='. $this->dc .'&sm='. self::encodeHexStr($msg);
		
		return self::getRequest($request);
	}
	
	public function sendMultiX($arr)
	{
		if ( ! is_array($arr)) $arr = explode(',', $arr);
		if (count($arr) > 100)
		{
			$this->_errno = 1;
			$this->_error = '批量发送个数最大不要超过100个';
			
			return FALSE;
		}
		
		// 拼接 URI
		$request = '/sms/mt'
				 . '?command=MULTIX_MT_REQUEST&spid='. $this->spid .'&spsc='. $this->spsc .'&sppassword='. $this->sppassword
				 . '&sa='. $this->sa .'&dc='. $this->dc .'&dasm=';
		
		foreach ($arr AS $v)
		{
			list($da, $sm) = explode('/', $v, 2);
			
			$sm 	  = self::encodeHexStr($sm);
			$request .= $da .'/'. $sm .',';
		}
		
		return self::getRequest($request);
	}
	
	public function getErrno()
	{
		return $this->_errno;
	}
	
	public function getError()
	{
		return $this->_error;
	}
	
	public function getResult()
	{
		return  $this->_result;
	}
	
	private function getRequest($request)
	{
		return self::httpSend('GET', $request);
	}
	
	private function postRequest($request)
	{
		return self::httpSend('POST', $request);
	}
	
	private function httpSend($method, $request)
	{
		$httpHeader  = $method ." ". $request. " HTTP/1.1\r\n";
		$httpHeader .= "Host: $this->host\r\n";
		$httpHeader .= "Connection: Close\r\n";
//		$httpHeader .= "User-Agent: Mozilla/4.0(compatible;MSIE 7.0;Windows NT 5.1)\r\n";
		$httpHeader .= "Content-type: text/plain\r\n";
		$httpHeader .= "Content-length: " . strlen($request) . "\r\n";
		$httpHeader .= "\r\n";
		$httpHeader .= $request;
		$httpHeader .= "\r\n\r\n";
		
		$fp = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
		$result = "";
		if ($fp)
		{
			fwrite($fp, $httpHeader);
			while ( ! feof($fp))
			{
				// 读取 GET 的结果
				$result .= fread($fp, 1024);
			}
			fclose($fp);
		}
		else
		{
			// 超时标志
			$this->_errno = 1;
			$this->_error = '连接短信网关超时';
			
			return FALSE;
		}
		
		list($header, $content) = explode("\r\n\r\n", $result);

		// 貌似响应格式变了
		if (strstr($content, PHP_EOL))
		{
			$result = explode("\r\n", $content);
			
			if (count($result) != 3)
			{
				$this->_errno = 2;
				$this->_error = str_replace("\r\n", "\t", $content);
				
				return FALSE;
			}
			
			$result = $result[1];
		}
		$result = explode("&", $result);
		$arr 	= array();
		
		foreach ($result AS $k => $v)
		{
			$tmp = explode("=", $v);
			
			$arr[$tmp[0]] = $tmp[1];
		}
		
		$this->_result = $arr;
		unset($arr);
		
		if (is_array($this->_result))
		{
//			if ($this->_result['mtstat'] == 'DELIVRD' AND $this->_result['mterrcode'] == '000')
			if ($this->_result['mterrcode'] == '000' OR ($this->_result['mtstat'] == 'DELIVRD' OR $this->_result['mtstat'] == 'ACCEPTD'))
			{
				return TRUE;
			}
			else
			{
				$this->_errno = intval($result['mterrcode']);
				$this->_error = $this->_result['mterrcode'] ."\t". $this->_result['mtstat'];
			}
		}
		
		return FALSE;
	}
	
	/**
	 * encode Hex String
	 *
	 * @param 	string $realStr
	 * @return 	string hex string
	 */
	public function encodeHexStr($realStr)
	{
		return bin2hex($realStr);
	}
	
	/**
	 *  decode Hex String
	 *
	 * @param 	string $hexStr  convert a hex string to binary string
	 * @return 	string binary 	string
	 */
	public function decodeHexStr($hexStr)
	{
		$hexLenght = strlen($hexStr);
		
		// only hex numbers is allowed
		if ($hexLenght % 2 != 0 || preg_match("/[^\da-fA-F]/", $hexStr)) return FALSE;
		
		$binString = '';
		
		for ($i = 1; $i <= $hexLenght / 2; $i++)
		{
			$binString .= chr(hexdec(substr($hexStr, 2 * $i - 2, 2)));
		}
		
		return $binString;
	}
}