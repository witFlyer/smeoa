<?php
class OrgSubsidieAction extends CommonAction {

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
			$detail = D("OrgSubsidie")->find(array("where" => "id='$id'"));
			
			if ($detail)
			{
				foreach ($detail AS $k => $v)
				{
					$this->assign($k, $v);
				}
			}
		}
		
		$year		= date('Y');
		$yearList  	= array();
		for ($i = $year - 10; $i <= $year; $i++)
		{
			$yearList[$i] = $i;
		}
		$this->assign('year_list', $yearList);
		
		$this->display();
	}
	
	function add()
	{
		$widget['jquery-ui'] = TRUE;
		$widget['date']      = TRUE;
		$this->assign("widget", $widget);

		$orgId = isset($_REQUEST['org_id']) ? intval($_REQUEST['org_id']) : 0;
		$this->assign('org_id', $orgId);

		$year		= date('Y');
		$yearList  	= array();
		for ($i = $year - 10; $i <= $year; $i++)
		{
		$yearList[$i] = $i;
		}
		$this->assign('year_list', $yearList);
		
		$this->display();
	}
	
	function ajaxList()
	{
		$orgId = isset($_REQUEST['org_id']) ? intval($_REQUEST['org_id']) : 0;
		$list  = D("OrgSubsidie")->findAll(array("where" => "org_id='$orgId'"));
		
		if ($list)
		{
			foreach ($list AS $k => &$v)
			{
				$v['typeName'] = D('OrgSubsidie')->getTypeName($v['type']);
			}
		}
		
		echo json_encode($list);
	}
	
	function ajaxDelete()
	{
		$id     = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$result = D("OrgSubsidie")->delete(array('where' => "id=$id"));
		
		echo json_encode(array('flag' => $result));
	}
}