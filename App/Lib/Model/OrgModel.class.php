<?php
class OrgModel extends CommonModel {
	// 自动验证设置
	protected $_validate = array(
		array('org_no', 'require', '机构编码必须', 1),
		array('org_name', 'require', '机构名称必须', 1),
	);
	
	protected $pk = 'org_id';
	
	public $selectFields = array(
		"*",
		"(SELECT `name` FROM smeoa_system_folder WHERE folder='OrgCategory' AND id=smeoa_org.category_id_0) AS category_name_0",
		"(SELECT `name` FROM smeoa_system_folder WHERE folder='OrgCategory' AND id=smeoa_org.category_id_1) AS category_name_1",
		"(SELECT `name` FROM smeoa_system_folder WHERE folder='OrgCategory' AND id=smeoa_org.category_id_2) AS category_name_2",
		"(SELECT `name` FROM smeoa_system_folder WHERE folder='OrgLevel' AND id=smeoa_org.level_id) AS level_name",
	);
	
	function del_data_by_row($row_id, $module = MODULE_NAME)
	{
		if (isset($row_id))
		{
			if (is_array($row_id))
			{
				$where['org_id'] = array("in", array_filter($row_id));
			}
			else
			{
				$where['org_id'] = array('in', array_filter(explode(',', $row_id)));
			}
			$model = M("Org");
			$where['module'] = $module;
			$result = $model->where($where)->delete();
			
			if ($result)
			{
				
			}
		}
		return $result;
	}
	function _before_insert(&$data, $options) {
		$data['update_time'] = time();
	}
		//添加新增 日志
	function _after_insert($data, $options) {

		$result = array(
			'org_id'	=>$data['org_id'],
			'refer_id'	=>$data['org_id'],
			'info_type' =>1,
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
			'refer_id'	=>$data['org_id'],
			'info_type'=>1,
			'do_type'	=>2,
			'time'		=>time(),
			'user_id'	=>get_user_id()
			);
		M('OrgLog')->add($result);
	}
}