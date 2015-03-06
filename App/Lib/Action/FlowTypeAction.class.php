<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class FlowTypeAction extends CommonAction {
    protected $config=array('app_type'=>'master');

	//过滤查询字段
	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])){
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	function add(){
				
		$widget['editor']=true;
		$this->assign("widget",$widget);
		
		$this -> assign("user_id",get_user_id());
		$this ->_assign_tag_list();
		$this ->_assign_duty_list();				
		$this->display();
	}
	
	function index(){
		$model = D("FlowTypeView");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$list = $model -> where($map) ->order('tag,sort')-> select();
		$this -> assign('list', $list);
		$this ->_assign_tag_list();
		$this -> display();
		return;
	}

	function mark() {
   		$action = $_REQUEST['action'];
		$id = $_REQUEST["id"];
		$val = $_REQUEST["val"];
		if (!empty($id)) {
			switch ($action){
				case 'del' :					
						$result=$this->_destory($id);
						if ($result) {
							$this -> ajaxReturn('', "删除成功", 1);
						} else {
							$this -> ajaxReturn('', "删除失败", 0);
						}
					break;
				case 'move_folder' :
					if (!empty($id)){
						$model = D("SystemTag");
						$model -> del_data_by_row($id);
						if (!empty($val)){
							$result = $model -> set_tag($id,$val);
							$field = 'tag';
							$result=$this -> _set_field($id, $field, $val);
						}
					};
				if ($result !== false) {
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
			}
		}
	}
	
	protected function _assign_tag_list() {
		$model = D("SystemTag");
		$tag_list = $model -> get_tag_list('id,name');
		$this -> assign("tag_list", $tag_list);
	}
	
	protected function _assign_duty_list() {
		$model = D("Duty");
		$where['is_del']=array('eq',0);
		$duty_list = $model ->where($where)->order('sort')->getField("id,name");
		$this -> assign("duty_list",$duty_list);
	}
	
	function tag_manage() {
		$this -> _tag_manage("分组管理",false);
	}

	function edit() {
		$widget['editor']=true;
		$this->assign("widget",$widget);			
		$this -> assign("user_id",get_user_id());
		$model = D("FlowTypeView");
		$id = $_REQUEST['id'];
		$vo = $model -> getById($id);
		$this -> assign('vo', $vo);
		$this->_assign_tag_list();
		$this->_assign_duty_list();
		$person=array();
		// 分页

		// import('@.ORG.Util.Page');
		// $count = M('User')->where('is_del=0')->count();
		// $p = new Page($count,7);
		// $page  = $p->show();

		$person_list=M('User')->where('is_del=0')->field('id,emp_no,name')->order('dept_id ASC')->select();
		foreach ($person_list as $k=>$v){
			$list=M('UserFlow')->where(array('flow_type_id'=>$id,'user_id'=>$v['id']))->find();
			if($list){
				$person[$k]=$v;
				$person[$k]['confirm_name']=$list['confirm_name'];
				$person[$k]['status']=1;
			}else{
				$person[$k]=$v;
				$person[$k]['confirm_name']=$vo['confirm_name'];
				$person[$k]['status']=0;
			}
		}

		// var_dump($confirm_person);die;
		$actionName=$this->getActionName();
		$this->assign('actionName',$actionName);
		$this->assign('person',$person);
		$this->assign('page',$page);
		$this -> display();
	}
	
//删除草稿数据
	public function del_draft(){
		if($this->isAjax()){
			$id=I('post.id','','int');
			$where=array('id'=>$id);
			$k = M('Flow')->where($where)->delete();
			if($k){
				M('FlowFieldData')->where(array('flow_id'=>$id))->delete();
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	function field(){
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
				
		if($_POST){
			$opmode = $_POST["opmode"];
			$model = D("FlowField");
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			if ($opmode == "add"){
				$list = $model -> add();
				if ($list !== false) {//保存成功
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('新增成功!');
				} else {
					$this -> error('新增失败!');
					//失败提示
				}
			}
			if ($opmode == "edit") {
				$list = $model -> save();
				if ($list !== false) {//保存成功
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('保存成功!');
				} else {
					$this -> error('保存失败!');
					//失败提示
				}
			}
			if ($opmode == "del") {
				$id = $_REQUEST['id'];
				$list=$model ->where("id=$id")->delete();
				if ($list !== false) {//保存成功
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('删除成功!');
				} else {
					$this -> error('删除失败!');
					//失败提示
				}
			}
		}

		$widget['date'] = true;					

		$this -> assign("widget", $widget);		

		$model = D("FlowField");
		$type_id=$_REQUEST['type_id'];
		$this->assign('type_id',$type_id);

		$where['type_id']=array('eq',$type_id);
		$where['is_del']=0;

		$field_list = $model ->where($where)->order('sort asc')->select();
		
		$tree = list_to_tree($field_list);
		$this -> assign('menu',sub_tree_menu($tree));

		$this -> assign("field_list", $field_list);
		$this -> display();
	}

	function get_field(){
		$id=$_REQUEST['id'];
		$model=M("FlowField");
		$vo = $model -> getById($id);
		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "", 0);
			} else {
				die ;
			}
		}
	}
}
?>