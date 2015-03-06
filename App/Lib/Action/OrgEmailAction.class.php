<?php
class OrgEmailAction extends CommonAction {
	//protected $config = array('app_type' => 'flow', 'action_auth' => array('folder' =>'read','mark' =>'admin','report'=>'admin','flow_list'=>'read','json'=>'read'));
	protected $config = array('app_type' => 'personal');
	

	function index()
	{
		$widget['uploader'] = TRUE;
		$widget['date']     = TRUE;
		$widget['editor']	= true;
		$this->assign("widget", $widget);
		
		$this->display();
	}
	
	public function upload()
	{
		$result = $this->_upload(1);
		
		if ($result['error'] == 0)
		{
			$root = dirname(dirname(dirname(dirname(__FILE__))));
			$file = $root . $result['url'];
			$data = file_get_contents($file);
			//unlink($file);
				
			if ($data)
			{
				$data = explode(PHP_EOL, $data);
				$html = array();
		
				foreach ($data AS $line)
				{
					$arr = explode(',', $line);
						
					if (count($arr) == 2 AND is_email($arr[1]))
					{
						$html[] = $arr;
					}
				}
		
				if ($html)
				{
					$html = json_encode($html);
					setcookie('org_email_import', $html, NULL, '/');
				}
			}
				
		}
		
		exit(json_encode($result));
	}
	
	function save()
	{
		$error		= array();

		$title		= $_POST['title'];
		$content	= $_POST['content'];
		$sendType	= isset($_POST['sendType'])  ? intval($_POST['sendType']) : 0;
		$sendTimer	= $_POST['sendTimer'];
		
		$orgId		= isset($_POST['orgId'])     ? $_POST['orgId']            : FALSE;
		$contactId	= isset($_POST['contactId']) ? $_POST['contactId']        : FALSE;
		$realname	= isset($_POST['realname'])  ? $_POST['realname']         : FALSE;
		$email		= isset($_POST['email'])     ? $_POST['email']            : FALSE;

		if (empty($title))
		{
			$error[] = '标题不能为空';
		}
		if (empty($content))
		{
			$error[] = '内容不能为空';
		}
		if ($sendType == 1)
		{
			if (empty($sendTimer))
			{
				$error[] = '定时时间不能为空';
			}
		}
		if (empty($email))
		{
			$error[] = '至少添加一条数据';
		}
		
		if ($error)
		{
			$this->error(implode("<br />", $error));
			return;
		}
		
		foreach ($email AS $k => $v)
		{
			D('OrgEmail')->insert(array(
				'email'				=> $v,
				'realname'			=> $realname[$k],
				'org_id'			=> $orgId[$k],
				'org_contact_id'	=> $contactId[$k],
				'send_type'			=> $sendType,
				'send_timer'		=> ($sendType == 0 ? 0 : strtotime($sendTimer)),
				'send_title'		=> $title,
				'send_message'		=> $content,
				'create_time'		=> time(),
			));
		}
		
//		$this->assign('jumpUrl', get_return_url());
		$this->assign('jumpUrl', U('org_email/index'));
		$this->success('新增成功!');
	}
	
	function select()
	{
		$widget['jquery-ui'] = TRUE;
		$this->assign("widget", $widget);
		
		$category_list_0 = D("SystemFolder")->get_folder_list_2('OrgCategory', 0);
		$this->assign("category_list_0", $category_list_0);
		
		$category_tree = D("SystemFolder")->getTree('json', 'OrgCategory');
		$this->assign("category_tree", $category_tree);
		
		$level_list = D("SystemFolder")->get_folder_list('OrgLevel');
		$this->assign("level_list", $level_list);
		
		$categoryId	= $_REQUEST['category_id_2'];
		if ($categoryId <= 0) $categoryId = $_REQUEST['category_id_1'];
		if ($categoryId <= 0) $categoryId = $_REQUEST['category_id_0'];
		
		$category_linkage = D("SystemFolder")->getLinkage($categoryId, TRUE, FALSE, 'OrgCategory');
		$this->assign("category_linkage", $category_linkage);
		
		$this->assign('post_list', C('ORG_POST'));
		
		$this->display();
	}
	
	function selectAjax()
	{
		$categoryId_0 = isset($_REQUEST['category_id_0']) ? intval($_REQUEST['category_id_0']) : 0;
		$categoryId_1 = isset($_REQUEST['category_id_1']) ? intval($_REQUEST['category_id_1']) : 0;
		$categoryId_2 = isset($_REQUEST['category_id_2']) ? intval($_REQUEST['category_id_2']) : 0;
		$levelId 	  = isset($_REQUEST['level_id'])      ? intval($_REQUEST['level_id'])      : 0;
		$orgName 	  = isset($_REQUEST['org_name'])      ? trim($_REQUEST['org_name'])        : '';
		$post 	  	  = isset($_REQUEST['post'])          ? trim($_REQUEST['post'])            : '';
		$type 	      = isset($_REQUEST['type'])          ? intval($_REQUEST['type'])          : 0;
		$tags 	  	  = isset($_REQUEST['tags'])          ? trim($_REQUEST['tags'])            : '';
		
		$map = $this->_search('Org');
		
		if ($categoryId_0 > 0)
		{
			$map['category_id_0'] = $categoryId_0;
				
			if ($categoryId_1 > 0)
			{
				$map['category_id_1'] = $categoryId_1;
		
				if ($categoryId_2 > 0)
				{
					$map['category_id_2'] = $categoryId_2;
				}
			}
		}
		if ($levelId > 0)
		{
			$map['level_id'] = $levelId;
		}
		if ( ! empty($orgName))
		{
			$map['org_name'] = array('like', "%" . $orgName . "%");
		}
		if ( ! empty($post))
		{
			$map['post'] = array('like', "%" . $post . "%");
		}
		
//		$map['email'] = array(array('NEQ', ''), array('EXP', NULL));
		
		if ($type == 1)
		{
			$orgList = D("OrgExecutiveView")->findAll(array('where' => $map));
		}
		else
		{
			if ( ! empty($tags))
			{
				$tags = explode(',', $tags);
				$sql  = array();
		
				foreach ($tags AS $v)
				{
					$sql[] = "FIND_IN_SET('$v', tags)";
				}
		
				$sql  = implode(" OR ", $sql);
				$sql  = "($sql)";
		
				$map['_string'] = $sql;
			}

			$orgList = D("OrgContactView")->findAll(array('where' => $map));
		}
		
		echo json_encode(array('data' => $orgList));
	}
	
	//过滤查询字段
	function _search_filter(&$map)
	{
		if ( ! empty($_REQUEST['keyword']) && empty($map['realname']))
		{
			$map['realname'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		
		if (isset($_REQUEST['email']) AND ! empty($_REQUEST['email']) AND empty($map['email']))
		{
			$map['email'] = array('like', "%" . $_POST['email'] . "%");
		}
		
		if (isset($_REQUEST['realname']) AND ! empty($_REQUEST['realname']) AND empty($map['realname']))
		{
			$map['realname'] = array('like', "%" . $_POST['realname'] . "%");
		}
		
		if (isset($_REQUEST['send_title']) AND ! empty($_REQUEST['send_title']) AND empty($map['send_title']))
		{
			$map['send_title'] = array('like', "%" . $_POST['send_title'] . "%");
		}
		
		if (isset($_REQUEST['is_send']) AND $_REQUEST['is_send'] != '')
		{
			$map['is_send'] = intval($_REQUEST['is_send']);
		}
	}
	
	function history()
	{
		$map = $this->_search();
		
		if (method_exists($this, '_search_filter'))
		{
			$this -> _search_filter($map);
		}
		
		$model = D("OrgEmail");
		if ( ! empty($model))
		{
			$this->_list($model, $map);
		}
		$this->display();
		return;
	}

	function read(){
		$widget['editor']	= true;
		$this->assign("widget", $widget);
		$id = $_REQUEST['id'];
		$list = M('OrgEmail')->where("id=$id")->find();
		$this->assign('list',$list);
		$this->display();
	}
}
