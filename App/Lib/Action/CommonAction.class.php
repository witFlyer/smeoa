<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class CommonAction extends Action {

	function _initialize() {
		$is_weixin = is_weixin();
		if ($is_weixin) {
			$code = $_REQUEST["code"];
			if (!empty($code)) {
				$this -> _welogin($code);
			}
		}
		$auth_id = session(C('USER_AUTH_KEY'));
		if (!isset($auth_id)) {
			//跳转到认证网关
			redirect(U(C('USER_AUTH_GATEWAY')));
		}

		$this -> assign('js_file', 'js/' . ACTION_NAME);
		$this -> _assign_menu();
		$this -> _assign_new_count();
	}

	protected function _welogin($code) {
		import("@.ORG.Util.ThinkWechat");
		$weixin = new ThinkWechat();
		$openid = $weixin -> openid($code);

		$model = M("User");
		$auth_info = $model -> where("openid = '{$openid}' AND westatus = 1") -> find();
		// 查到userid

		if ($auth_info) {
			session(C('USER_AUTH_KEY'), $auth_info['id']);
			session('emp_no', $auth_info['emp_no']);
			session('email', $auth_info['email']);
			session('user_name', $auth_info['name']);
			session('user_pic', $auth_info['pic']);
			session('dept_id', $auth_info['dept_id']);

			if ($auth_info['emp_no'] == 'admin') {
				session(C('ADMIN_AUTH_KEY'), true);
			}
		} else {
			redirect(U('wechat/oauth', array('openid' => $openid)));
		}
	}

	/**显示top menu及 left menu **/
	protected function _assign_menu() {
		$user_id = get_user_id();

		$model = D("Node");
		$top_menu = cookie('top_menu');

		$top_menu_list = $model -> get_top_menu($user_id);
		if (empty($top_menu_list)) {
			$this -> assign('jumpUrl', U("Login/logout"));
			$this -> error("没有权限");
		}

		$this -> assign('top_menu', $top_menu_list);

		//读取数据库模块列表生成菜单项
		$menu = D("Node") -> access_list();
		$system_folder_menu = D("SystemFolder") -> get_folder_menu();
		$user_folder_menu = D("UserFolder") -> get_folder_menu();
		$menu = array_merge($menu, $system_folder_menu, $user_folder_menu);

		//缓存菜单访问
		$tree = list_to_tree($menu);
		if (!empty($top_menu)) {
			$top_menu_name = $model -> where("id=$top_menu") -> getField('name');
			$this -> assign("top_menu_name", $top_menu_name);
			$this -> assign("title", get_system_config("SYSTEM_NAME") . "-" . $top_menu_name);
			$left_menu = list_to_tree($menu, $top_menu);
			$this -> assign('left_menu', $left_menu);
		} else {
			$this -> assign("title", get_system_config("SYSTEM_NAME"));
		}
	}

	protected function _assign_new_count() {
		$this -> assign("new_count", get_new_count());
	}

	/**列表页面 **/
	function index() {
		$this -> _index();
	}

	/**查看页面 **/
	function read() {
		$this -> _edit();
	}

	/**编辑页面 **/
	function edit() {
		$this -> _edit();
	}

	/** 保存操作  **/
	function save() {
		$this -> _save();
	}

	/**列表页面 **/
	protected function _index($name = null) {
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		if($name=="Schedules"){
		// 权限问题
		$user_id=get_user_id();
		$role_list=D("Role")->get_role_list($user_id);
		$role_list=rotate($role_list);
		$role_list=$role_list['role_id'];
		$node=M('Node')->where(array('url'=>'schedules/index'))->field('id')->select();
		$node=rotate($node);
		$node=$node['id'];

		$auth_write=M('roleNode')->where(array('role_id'=>array('in',$role_list),'node_id'=>array('in',$node)))->field('write')->select();
		$count=0;
		foreach ($auth_write as $k => $v) {
			if($v['write']){
				$count++;
			}
		}
			if ($count>0) {
				$this->assign('is_write',1);
			}
		}
		$model = D($name);
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
	}

	/**编辑页面 **/
	protected function _edit($name = null, $id = null) {
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = M($name);
		$id = $_REQUEST['id'];
		$vo = $model -> find($id);
		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "读取成功", 1);
			} else {
				$this -> ajaxReturn(0, "读取失败", 0);
				die ;
			}
		}
		$this -> assign('vo', $vo);
		$this -> display();
	}

	protected function _save($name = null) {
		$opmode = $_POST["opmode"];
		switch($opmode) {
			case "add" :
				$this -> _insert($name);
				break;
			case "edit" :
				$this -> _update($name);
				break;
			case "del" :
				$this -> _del($name);
				break;
			default :
				$this -> error("非法操作");
		}
	}

	/** 插入新数据  **/
	protected function _insert($name = null) {
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = D($name);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		/*保存当前数据对象 */
		$list = $model -> add();
			if($list !== false){
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('新增成功!');
			} else {
				$this -> error('新增失败!');
					//失败提示
			}
	}

	/* 更新数据  */
	protected function _update($name = null) {
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = D($name);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}

		$list = $model -> save();
		if (false !== $list) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}

	/** 删除标记 逻辑删除 **/
	protected function _del($id = null, $name = null, $return_flag = false) {
		if (empty($id)) {
			$id = $_REQUEST['id'];
			if (empty($id)) {
				$this -> error('没有可删除的数据!');
			}
		}
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = M($name);

		if (!empty($model)) {
			if (isset($id)) {
				
				$pk = $model->getPk();
				
				if (is_array($id)) {
//					$where['id'] = array("in", array_filter($id));
					$where[$pk] = array("in", array_filter($id));
				} else {
//					$where['id'] = array('in', array_filter(explode(',', $id)));
					$where[$pk] = array('in', array_filter(explode(',', $id)));
				}
				
				$result = $model -> where($where) -> setField("is_del", 1);
				if ($return_flag) {
					return $result;
				}
				if ($result !== false) {
					$this -> assign('jumpUrl', get_return_url());
					$this -> success("成功删除{$result}条!");
				} else {
					$this -> error('删除失败!');
				}
			} else {
				$this -> error('没有可删除的数据!');
			}
		}
	}

	/** 永久删除数据  **/
	protected function _destory($id = null, $name = null, $return_flag = false) {
		if (empty($id)) {
			$id = $_REQUEST['id'];
			if (empty($id)) {
				$this -> error('没有可删除的数据!');
			}
		}
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = M($name);
		if (!empty($model)) {
			if (isset($id)) {
				if (is_array($id)) {
					$where['id'] = array("in", array_filter($id));
				} else {
					$where['id'] = array('in', array_filter(explode(',', $id)));
				}
				$app_type = $this -> config['app_type'];

				if ($app_type == "personal") {
					$where['user_id'] = get_user_id();
				}

				$file_list = $model -> where($where) -> getField("id,add_file");
				$file_list = array_filter(explode(";", implode($file_list)));
				$this -> _destory_file($file_list);

				$result = $model -> where($where) -> delete();
				if ($return_flag) {
					return $result;
				}
				if ($result !== false) {
					$this -> assign('jumpUrl', get_return_url());
					$this -> success("彻底删除{$result}条!");
				} else {
					$this -> error('删除失败!');
				}
			} else {
				$this -> error('没有可删除的数据!');
			}
		}
	}

	public function del_file() {
		$file_list = $_REQUEST['sid'];
		$this -> _destory_file($file_list);
	}

	protected function _destory_file($file_list) {
		if (isset($file_list)) {
			if (is_array($file_list)) {
				$where["sid"] = array("in", $file_list);
			} else {
				$where["sid"] = array('in', array_filter(explode(',', $file_list)));
			}
		} else {
			exit();
		}

		$model = M("File");
		$where['module'] = MODULE_NAME;
		$admin = $this -> config['auth']['admin'];

		if ($admin) {
			$where['user_id'] = array('eq', get_user_id());
		};

		$list = $model -> where($where) -> select();
		$save_path = get_save_path();

		foreach ($list as $file) {
			if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $save_path . $file['savename'])) {
				unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $save_path . $file['savename']);
			}
		}

		$result = $model -> where($where) -> delete();
		if ($result !== false) {
			return true;
		} else {
			return false;
		}
	}

	protected function _upload($isReturn = FALSE) {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		if (!empty($_FILES)) {
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> subFolder = strtolower(MODULE_NAME);
			$upload -> savePath = get_save_path();
			$upload -> saveRule = "uniqid";
			$upload -> autoSub = true;
			$upload -> subType = "date";
			$upload -> allowExts = array_filter(explode(",", get_system_config('UPLOAD_FILE_TYPE')), 'upload_filter');
			if (!$upload -> upload()) {
				$data['error'] = 1;
				$data['message'] = $upload -> getErrorMsg();
				$data['status'] = 0;
				
				if ($isReturn)
				{
					return $data;
				}
				else
				{
					exit(json_encode($data));
				}
			} else {
				//取得成功上传的文件信息
				$upload_list = $upload -> getUploadFileInfo();
				$sid = get_sid();
				$file_info = $upload_list[0];
				$model = M("File");
				$model -> create($upload_list[0]);
				$model -> create_time = time();
				$model -> user_id = get_user_id();
				$model -> sid = $sid;
				$model -> module = MODULE_NAME;
				$file_id = $model -> add();
				$file_info['sid'] = $sid;
				$file_info['error'] = 0;
				$file_info['url'] = "/" . $file_info['savepath'] . $file_info['savename'];
				$file_info['status'] = 1;
				
				if ($isReturn)
				{
					return $file_info;
				}
				else
				{
					exit(json_encode($file_info));
				}
			}
		}
	}

	protected function _down() {
		$attach_id = $_REQUEST["attach_id"];
		$file_id = f_decode($attach_id);
		$File = M("File") -> find($file_id);
		$filepath = get_save_path() . $File['savename'];
		$filePath = realpath($filepath);
		$fp = fopen($filePath, 'rb');

		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (!preg_match("/MSIE/", $ua)) {
			header("Content-Length: " . filesize($filePath));
			Header("Content-type: application/octet-stream");
			header("Content-Length: " . filesize($filePath));
			header("Accept-Ranges: bytes");
			header("Accept-Length: " . filesize($filePath));
		}

		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($File['name'])));
		header('Cache-Control:must-revalidate, post-check=0,pre-check=0');
		header('Expires:     0');
		header('Pragma:     public');
		//echo $query;
		fpassthru($fp);
		exit ;
	}

	//生成查询条件
	protected function _search($name = null) {
		$map = array();
		//过滤非查询条件
		$request = array_filter(array_keys(array_filter($_REQUEST)), "filter_search_field");
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = D($name);
		$fields = get_model_fields($model);
		foreach ($request as $val) {
			$field = substr($val, 3);
			$prefix=substr($val, 0, 3);
			if(in_array($field,$fields)){				
				if ($prefix == "be_"){
					if (isset($_REQUEST["en_" .$field])) {
						if (strpos($field,"time")) {
							$map[$field] = array(array('egt',date_to_int(trim($_REQUEST[$val]))),array('elt', date_to_int(trim($_REQUEST["en_" .$field])) + 86400));
						}
						if (strpos($field,"date")) {
							$map[$field] = array( array('egt', trim($_REQUEST[$val])), array('elt', trim($_REQUEST["en_" . substr($val, 3)])));
						}
					}
				}

				if ($prefix == "li_") {
					$map[$field] = array('like', '%' . trim($_REQUEST[$val]) . '%');
				}
				if ($prefix == "eq_") {
					$map[$field] = array('eq', trim($_REQUEST[$val]));
				}
				if ($prefix == "gt_") {
					$map[$field] = array('egt', trim($_REQUEST[$val]));
				}
				if ($prefix == "lt_") {
					$map[$field] = array('elt', trim($_REQUEST[$val]));
				}
			}
		}

		return $map;
	}

	protected function _list($model, $map, $sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset($_REQUEST['_order'])) {
			$order = $_REQUEST['_order'];
		} else if (!empty($sortBy)) {
			$order = $sortBy;
		} else if (in_array('sort', get_model_fields($model))) {
			$order = 'sort';
			$asc = true;
		} else {
			$order = $model -> getPk();
		}
		//排序方式默认按照倒序排列
		//接受 sort参数 0 表示倒序 非0都 表示正序
		if (isset($_REQUEST['_sort'])) {
			$sort = $_REQUEST['_sort'] ? 'asc' : 'desc';
		} else if (strpos($sortBy, ',')) {
			$sort = '';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}

		//取得满足条件的记录数

		$count_model = clone $model;
		//取得满足条件的记录数
		if (!empty($count_model -> pk)) {
			$count = $count_model -> where($map) -> count($model -> pk);
		} else {
			$count = $count_model -> where($map) -> count();
		}
		if ($count > 0) {
			import("@.ORG.Util.Page");
			//创建分页对象
			if (!empty($_REQUEST['list_rows'])) {
				$listRows = $_REQUEST['list_rows'];
			} else {
				$listRows = get_user_config('list_rows');
			}
			$p = new Page($count,$listRows);
			
			// Modified by CeeFee at 2014-12-19
			if (isset($model->selectFields))
			{
				$selectFields = $model->selectFields;
			}
			else
			{
				$selectFields = $model->getDbFields();
			}
			$selectFields = implode(',', $selectFields);
			
			//分页查询数据
			if ($sort) {
//				$voList = $model -> where($map) -> order("`" . $order . "` " . $sort) -> limit($p -> firstRow . ',' . $p -> listRows) -> select();
				$voList = $model -> where($map) -> field($selectFields) -> order("`" . $order . "` " . $sort) -> limit($p -> firstRow . ',' . $p -> listRows) -> select();
			} else {
//				$voList = $model -> where($map) -> order($order) -> limit($p -> firstRow . ',' . $p -> listRows) -> select();
				$voList = $model -> where($map) -> field($selectFields) -> order($order) -> limit($p -> firstRow . ',' . $p -> listRows) -> select();
			}
			$actionName = $this->getActionName();
			if($actionName=="Flow" || $actionName=="FlowLog"){

			foreach ($voList as $k => $v) {
				$u_name[$k]['emp_no']=explode('|', $v['confirm']);
				foreach ($u_name[$k]['emp_no'] as $key => $value) {
					if(!$value){
						unset($u_name[$k]['emp_no'][$key]);
					}
				}
				$u_name[$k]['id']=$v['id'];
			}
			foreach ($u_name as $a=>$b) {
				foreach ($b['emp_no'] as $key => $value) {
						$nname=M('user')->where(array('emp_no'=>$value))->field('name')->find();
						$uu_name[$a][$key]=$nname;
					if(M('FlowLog')->where(array('flow_id'=>$b['id'],'emp_no'=>$value,'result'=>1))->find()){
						$uu_name[$a][$key]['is_agree']=1;
					}elseif(M('FlowLog')->where(array('flow_id'=>$b['id'],'emp_no'=>$value,'_string'=>'result is null'))->find()){
						$uu_name[$a][$key]['is_agree']=2;
					}else{
						$uu_name[$a][$key]['is_agree']=0;
					}
				}
			}
			foreach ($voList as $k => $v) {
				$voList[$k]['confirm_name']=$uu_name[$k];
			}
		}
			// var_dump($u_name);die;
			//echo $model->getlastSql();
			$p -> parameter = $this -> _search();
			//分页显示
			$p->setConfig('all','');
			$page = $p -> show();

			//列表排序显示
			$sortImg = $sort;

			//排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列';

			//排序提示
			$sort = $sort == 'desc' ? 1 : 0;

			//排序方式
			
			//模板赋值显示
			// $name = $this -> getActionName();
			$this -> assign('list', $voList);
			$this -> assign('order', $order);
			$this -> assign('sortImg', $sortImg);
			$this -> assign('sortType', $sortAlt);
			$this -> assign("page", $page);
		}
		return;
	}

	protected function _assign_folder_list() {
		if ($this -> config['app_type'] == 'personal') {
			$model = D("UserFolder");
		} else {
			$model = D("SystemFolder");
		}
		$list = $model -> get_folder_list();
		$tree = list_to_tree($list);
		$this -> assign('folder_list', dropdown_menu($tree));
	}

	protected function _set_field($id, $field, $val, $name = '') {
		if (empty($name)) {
			$name = $this -> getActionName();
		}
		$model = M($name);
		if (!empty($model)) {
			if (isset($id)) {
				if (is_array($id)) {
					$where['id'] = array("in", array_filter($id));
				} else {
					$where['id'] = array('in', array_filter(explode(',', $id)));
				}
				$admin = $this -> config['auth']['admin'];
				if (in_array('user_id', $model -> getDbFields()) && !$admin) {
					$where['user_id'] = array('eq', get_user_id());
				};
				$list = $model -> where($where) -> setField($field, $val);
				if ($list !== false) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	protected function _tag_manage($tag_name, $has_pid = true) {

		$this -> assign("tag_name", $tag_name);
		$this -> assign("has_pid", $has_pid);
		if ($this -> config['app_type'] == 'personal') {
			R('UserTag/index');
			$this -> assign('js_file', "UserTag:js/index");
		} else {
			R('SystemTag/index');
			$this -> assign('js_file', "SystemTag:js/index");
		}
	}

	protected function _pushReturn($data, $info, $status,$user_id, $time = null) {
		$model = M("Push");

		$model -> data = $data;
		$model -> info = $info;
		$model -> status = $status;

		if (empty($user_id)) {
			$model -> user_id = get_user_id();
		} else {
			$model -> user_id = $user_id;
		}

		if (empty($time)) {
			$model -> time = time();
		} else {
			$model -> time = $time;
		}
		$model -> add();
	}

}
?>