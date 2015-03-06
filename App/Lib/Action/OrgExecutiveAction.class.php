<?php
class OrgExecutiveAction extends CommonAction {

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
			$detail = D("OrgExecutive")->find(array("where" => "id='$id'"));
			
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
		$list  = D("OrgExecutive")->findAll(array("where" => "org_id='$orgId'"));
		echo json_encode($list);
	}
	
	function ajaxDelete()
	{
		$id     = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$result = D("OrgExecutive")->delete(array('where' => "id=$id"));
		
		echo json_encode(array('flag' => $result));
	}
}