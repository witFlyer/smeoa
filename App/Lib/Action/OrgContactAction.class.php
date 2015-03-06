<?php
class OrgContactAction extends CommonAction {

	//protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','sign'=>'read','mark' => 'admin', 'upload' => 'write'));
	protected $config = array('app_type' => 'personal');
	
	//过滤查询字段
	function _search_filter(&$map)
	{
		if ( ! empty($_REQUEST['keyword']) AND empty($map['org_name']))
		{
			$map['org_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		// 机构名称
		if (isset($_REQUEST['org_name']) AND ! empty($_REQUEST['org_name']) AND empty($map['org_name']))
		{
			$map['org_name'] = array('like', "%" . $_POST['org_name'] . "%");
		}
		// 机构类别
		if (isset($_REQUEST['category_id_2']) AND intval($_REQUEST['category_id_2']) > 0 AND empty($map['category_id_2']))
		{
			$map['category_id_2'] = intval($_REQUEST['category_id_2']);
		}
		elseif (isset($_REQUEST['category_id_1']) AND intval($_REQUEST['category_id_1']) > 0 AND empty($map['category_id_1']))
		{
			$map['category_id_1'] = intval($_REQUEST['category_id_1']);
		}
		elseif (isset($_REQUEST['category_id_0']) AND intval($_REQUEST['category_id_0']) > 0 AND empty($map['category_id_0']))
		{
			$map['category_id_0'] = intval($_REQUEST['category_id_0']);
		}
		// 机构能级
		if (isset($_REQUEST['level_id']) AND intval($_REQUEST['level_id']) > 0 AND empty($map['level_id']))
		{
			$map['level_id'] = intval($_REQUEST['level_id']);
		}
		// 企业所在地
		if (isset($_REQUEST['location_id']) AND intval($_REQUEST['location_id']) > 0 AND empty($map['location_id']))
		{
			$map['location_id'] = intval($_REQUEST['location_id']);
		}
		// 状态
		if (isset($_REQUEST['status']) AND $_REQUEST['status'] != '' AND empty($map['status']))
		{
			$map['status'] = intval($_REQUEST['status']);
		}
		// 享受政策
		if (isset($_REQUEST['is_policy']) AND $_REQUEST['is_policy'] != '' AND empty($map['is_policy']))
		{
			$map['is_policy'] = intval($_REQUEST['is_policy']);
		}
		// 职务
		if (isset($_REQUEST['post']) AND $_REQUEST['post'] != '' AND empty($map['post']))
		{
			$map['post'] = intval($_REQUEST['post']);
		}
		// 姓名
		if (isset($_REQUEST['realname']) AND $_REQUEST['realname'] != '' AND empty($map['realname']))
		{
			$map['realname'] = array('like', "%" . $_POST['realname'] . "%");
		}
	}
	
	function index()
	{
		$map = $this->_search('OrgContactView');
		
		if (method_exists($this, '_search_filter'))
		{
			$this->_search_filter($map);
		}
		
		$category_list_0 = D("SystemFolder")->get_folder_list_2('OrgCategory', 0);
		$this->assign("category_list_0", $category_list_0);
		
		$category_tree = D("SystemFolder")->getTree('json', 'OrgCategory');
		$this->assign("category_tree", $category_tree);
		
		$level_list = D("SystemFolder")->get_folder_list('OrgLevel');
		$this->assign("level_list", $level_list);
		
		$location_list = D("SystemFolder")->get_folder_list('OrgLocation');
		$this->assign("location_list", $location_list);
		
		$categoryId	= $_REQUEST['category_id_2'];
		if ($categoryId <= 0) $categoryId = $_REQUEST['category_id_1'];
		if ($categoryId <= 0) $categoryId = $_REQUEST['category_id_0'];
		
		$category_linkage = D("SystemFolder")->getLinkage($categoryId, TRUE, FALSE, 'OrgCategory');
		$this->assign("category_linkage", $category_linkage);
		
		// $this->assign('post_list', C('ORG_POST'));
		$this->assign('tags_list', C('ORG_CONTACT_TAGS'));
		
		$model = D('OrgContactView');
		if ( ! empty($model))
		{
			$this->_list($model, $map,'update_time');
		}
		$this->display();
		return;
	}
	
	function edit()
	{
//		$this->_edit();

		$widget['jquery-ui'] = TRUE;
		$widget['date']      = TRUE;
		$this->assign("widget", $widget);
		
		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		
		if ($id > 0)
		{
			$detail = D("OrgContact")->find(array("where" => "id='$id'"));
			
			if ($detail)
			{
				foreach ($detail AS $k => $v)
				{
					$this->assign($k, $v);
				}
			}
		}

		$this->assign('tags_list', C('ORG_CONTACT_TAGS'));
		
		$this->display();
	}
	
	function add()
	{
		$widget['jquery-ui'] = TRUE;
		$this->assign("widget", $widget);

		$orgId = isset($_REQUEST['org_id']) ? intval($_REQUEST['org_id']) : 0;
		$this->assign('org_id', $orgId);
		
		$this->assign('tags_list', C('ORG_CONTACT_TAGS'));
		
		$this->display();
	}
	
	function ajaxList()
	{
		$orgId = isset($_REQUEST['org_id']) ? intval($_REQUEST['org_id']) : 0;
		$list  = D("OrgContact")->where(array("org_id"=>$orgId))->order("update_time DESC")->findAll();
		echo json_encode($list);
	}
	
	function ajaxDelete()
	{
		$id     = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$result = D("OrgContact")->delete(array('where' => "id=$id"));
		
		echo json_encode(array('flag' => $result));
	}
}