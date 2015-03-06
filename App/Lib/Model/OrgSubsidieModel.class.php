<?php
class OrgSubsidieModel extends CommonModel {
	// 自动验证设置
	protected $_validate = array(
		array('org_id', 'require', '机构编码必须', 1),
	);
	
	protected $pk = 'id';

	function getTypeName($value)
	{
		$list 	= C('ORG_SUBSIDIE_TYPE');
		$result = '';
	
		if ($list AND isset($list[$value]))
		{
			$result = $list[$value];
		}
	
		return $result;
	}
	function _before_insert(&$data, $options) {
		$data['update_time'] = time();
	}
}