<?php
class OrgContactModel extends CommonModel {
	// 自动验证设置
	protected $_validate = array(
		array('org_id', 'require', '机构编码必须', 1),
	);
	
	protected $pk = 'id';
	
	function edit()
	{
		$this->_edit();
	}
	
	function getPostName($value)
	{
		$list 	= C('ORG_POST');
		$result = '';
		
		if ($list AND isset($list[$value]))
		{
			$result = $list[$value];
		}
		
		return $result;
	}
	
	function getTagName($value)
	{
		$list 	= C('ORG_CONTACT_TAGS');
		$result = '';
		
		if ($list AND isset($list[$value]))
		{
			$result = $list[$value];
		}
		
		return $result;
	}
	
	function getTagsName($values)
	{
		$list 	= C('ORG_CONTACT_TAGS');
		$result = array();
		
		if (empty($values))
		{
			$values = array();
		}
		if ( ! is_array($values))
		{
			$values = explode(',', $values);
		}
		
		foreach ($values AS $v)
		{
			if (isset($list[$v]))
			{
				$result[] = $list[$v];
			}
		}
		
		return implode(',', $result);
	}
	function _before_insert(&$data, $options) {
		$data['update_time'] = time();
	}
	//添加新增 日志
	function _after_insert($data, $options) {

		$result = array(
			'org_id'	=>$data['org_id'],
			'refer_id'	=>$data['id'],
			'info_type'=>3,
			'do_type'	=>1,
			'time'		=>time(),
			'user_id'	=>get_user_id()
			);
		M('OrgLog')->add($result);
		
	}

	//添加编辑 日志
	function _after_update($data, $options) {

		$result = array(
			'org_id'	=>$data['org_id'],
			'refer_id'	=>$data['id'],
			'info_type'=>3,
			'do_type'	=>2,
			'time'		=>time(),
			'user_id'	=>get_user_id()
			);
		M('OrgLog')->add($result);
	}
}