<?php
class OrgComprehensiveAction extends CommonAction {

	//protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','sign'=>'read','mark' => 'admin', 'upload' => 'write'));
	protected $config = array('app_type' => 'personal');
	
	function edit()
	{
//		$this->_edit();

		$widget['jquery-ui'] = TRUE;
		$widget['date']      = TRUE;
		$this->assign("widget", $widget);
		
		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		
		if ($id > 0)
		{
			$detail = D("OrgComprehensive")->find(array("where" => "id='$id'"));
			
			if ($detail)
			{
				foreach ($detail AS $k => $v)
				{
					$this->assign($k, $v);
				}
			}
		}
		
		$this->display();
	}
	
	function add()
	{
		$widget['jquery-ui'] = TRUE;
		$widget['date']      = TRUE;
		$this->assign("widget", $widget);

		$orgId = isset($_REQUEST['org_id']) ? intval($_REQUEST['org_id']) : 0;
		$this->assign('org_id', $orgId);
		
		$this->display();
	}
	
	function ajaxList()
	{
		$orgId = isset($_REQUEST['org_id']) ? intval($_REQUEST['org_id']) : 0;
		$list  = D("OrgComprehensive")->findAll(array("where" => "org_id='$orgId'"));
		
		if ($list)
		{
			foreach ($list AS $k => &$v)
			{
				$v['typeName'] = D('OrgComprehensive')->getTypeName($v['type']);
			}
		}
		
		echo json_encode($list);
	}
	
	function ajaxDelete()
	{
		$id     = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$result = D("OrgComprehensive")->delete(array('where' => "id=$id"));
		
		echo json_encode(array('flag' => $result));
	}
}