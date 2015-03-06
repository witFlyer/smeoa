<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class SchedulesAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	//过滤查询字段
	function _search_filter(&$map) {
		if (!empty($_POST["name"])) {
			$map['name'] = array('like', "%" . $_POST['name'] . "%");
		}
		// $map['user_id'] = array('eq', get_user_id());
		$map['is_del'] = array('eq', '0');		
	}

	public function upload() {
		$this -> _upload();
	}

	function read() {
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
		if($count>0){
			$this -> assign('is_write',1);
		}
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M('Schedules');
		$id = $_REQUEST['id'];
		$list = $_REQUEST['list'];
		$this -> assign("list", $list);
		$list = array_filter(explode("|", $list));
		$current = array_search($id, $list);

		if ($current !== false) {
			$next = $list[$current + 1];
			$prev = $list[$current - 1];
		}
		$this -> assign('next', $next);
		$this -> assign('prev', $prev);

		$where['id'] = $id;
		$vo = $model -> where($where) -> find();
		$this -> assign('vo', $vo);
		$this -> display();
	}

	function search() {
		
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		if (empty($_POST["be_start_date"])&&empty($_POST["en_end_date"])) {
					
		} else {
			$start_date = $_POST["be_start_date"];
			$end_date = $_POST["en_end_date"];
			$map['start_date'] = array(array("egt", $start_date),array("elt",$end_date));
		}
		
		$this -> assign('start_date', $start_date);
		$this -> assign('end_date', $end_date);

		$model = D("Schedules");
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> assign('type_data', $this -> type_data);
		$this -> display();
		return;
	}

	public function down() {
		$this -> _down();
	}

	public function add() {
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
		$widget['jquery-ui'] = true;
		$widget['date'] = true;	
		$widget['uploader'] = true;
		$widget['editor'] = true;
		
		$this -> assign("widget", $widget);

		$this -> display();
	}else{
		$this -> error("您没有权限");
	}
}

	public function edit() {
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
		if($count>0){
			$widget['jquery-ui'] = true;
			$widget['date'] = true;		
			$widget['uploader'] = true;
			$widget['editor'] = true;
			$this -> assign("widget", $widget);

			$id = $_REQUEST['id'];
			$model = M('Schedules');
			$where['id'] = $id;
			$vo = $model -> where($where) -> find();

			$this -> assign('vo', $vo);
			$this -> display();
		}else{
			$this -> error("您没有权限");
		}
		
	}

	public function read2(){
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
		if($count>0){
			$this -> assign('is_write',1);
		}
		$this -> read();
	}
	
	public function del(){
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
		if($count>0){
			$this->_del();
		}else{
			$this -> error("您没有权限");
		}
	}
	
	public function month_view() {
		$this -> index();
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
		$this->display();
	}
	function selectAjax()
	{
		$categoryId_0 = isset($_REQUEST['category_id_0']) ? intval($_REQUEST['category_id_0']) : 0;
		$levelId 	  = isset($_REQUEST['level_id'])      ? intval($_REQUEST['level_id'])      : 0;
		$categoryId_1 = isset($_REQUEST['category_id_1']) ? intval($_REQUEST['category_id_1']) : 0;
		$categoryId_2 = isset($_REQUEST['category_id_2']) ? intval($_REQUEST['category_id_2']) : 0;
		$orgName 	  = isset($_REQUEST['org_name'])      ? trim($_REQUEST['org_name'])        : '';
		
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
		$orgList = D("Org")->findAll(array('where' => $map));
		echo json_encode(array('data' => $orgList));
	}

	function json() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type:text/html; charset=utf-8");
		$user_id = get_user_id();
		$start_date = $_REQUEST["start_date"];
		$end_date = $_REQUEST["end_date"];

		$where['is_del']=array('eq',0);
		$where['start_date'] = array( array('egt', $start_date), array('elt', $end_date));
		$list = M("Schedules") -> where($where) -> order('start_date,priority desc') -> select();
		exit(json_encode($list));
	}

}
?>