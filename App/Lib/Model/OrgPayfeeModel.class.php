<?php
class OrgPayfeeModel extends CommonModel {
	// 自动验证设置
	protected $_validate = array(
		array('org_id', 'require', '机构编码必须', 1),
	);
	
	protected $pk = 'id';
	function _before_insert(&$data, $options) {
		$data['update_time'] = time();
	}
}