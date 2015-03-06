<?php
class OrgEmailModel extends CommonModel {
	
	function insert($data, $options = array(), $replace = FALSE)
	{
		$options =  $this->_parseOptions($options);
		$result  = $this->db->insert($data, $options, $replace);
		
		if (FALSE !== $result)
		{
			$insertId = $this->getLastInsID();
			
			return $insertId;
		}
		
		return $result;
	}
	
}