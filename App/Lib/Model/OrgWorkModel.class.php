<?php
class OrgWorkModel extends CommonModel {
	// 自动验证设置
	protected $_validate = array(
		array('org_id', 'require', '机构编码必须', 1),
	);
	
	protected $pk = 'id';
	
	function getTypeName($type)
	{
		$name = '';
		
		switch (intval($type))
		{
			case 1:
				$name = '接待';
			break;
			case 2:
				$name = '拜访';
			break;
		}
		
		return $name;
	}
}