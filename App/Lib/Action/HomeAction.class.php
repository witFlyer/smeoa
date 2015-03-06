<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class HomeAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	//过滤查询字段

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['type|name|code'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		cookie("current_node", null);
		cookie("top_menu", null);

		$config = D("UserConfig") -> get_config();
		$this -> assign("home_sort", $config['home_sort']);
		$this -> _mail_list();
		$this -> _flow_list();
		$this -> _schedule_list();
		$this -> _notice_list();
		$this -> _doc_list();
		$this -> _forum_list();
		$this -> _news_list();
		$this -> _slide_list();
		$this -> _room_list();
		$this -> display();
	}

	public function set_sort() {
		$val = $_REQUEST["val"];
		$data['home_sort'] = $val;
		$model = D("UserConfig") -> set_config($data);
	}

	protected function _mail_list() {
		$user_id = get_user_id();
		$model = D('Mail');

		//获取最新邮件
		$where['user_id'] = $user_id;
		$where['is_del'] = array('eq', '0');
		$where['folder'] = array( array('eq', 1), array('gt', 6), 'or');

		$new_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('new_mail_list', $new_mail_list);
		
		//判断是否禁用
//		$is_del = M('Node')->where(array('name'=>"邮件"))->getField("is_del");
		
		//获取未读邮件
		$where['read'] = array('eq', '0');
		$unread_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('unread_mail_list', $unread_mail_list);
//		$this -> assign('is_del', $is_del);
	}

	protected function _flow_list(){
		$user_id = get_user_id();
		$emp_no = get_emp_no();
		$model = D('Flow');
		//带审批的列表
		$FlowLog = M("FlowLog");
		$where['emp_no'] = $emp_no;
		$where['_string'] = "result is null";
		$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
		$log_list = rotate($log_list);
		if (!empty($log_list)) {
			$map['id'] = array('in', $log_list['flow_id']);
			$todo_flow_list = $model -> where($map) -> field("id,name,create_time") -> limit(6)-> order("create_time desc")->select();
			$this -> assign("todo_flow_list", $todo_flow_list);		
		}
		//已提交
		$map = array();
		$map['user_id'] = $user_id;
		// $map['step'] = array('gt', 10);
		$submit_process_list = $model -> where($map) -> field("id,name,step,user_name,create_time") -> limit(6)->order("create_time desc")-> select();
		$this -> assign("submit_flow_list", $submit_process_list);
		
		$where2['emp_no'] = $emp_no;
		$where2['step'] = 100;
		$receive_list = $FlowLog -> where($where2) -> field('flow_id') -> select();
		$receive_list = rotate($receive_list);
		if (!empty($receive_list)) {
			$map2['id'] = array('in',$receive_list['flow_id']);
			$receive_lists=$model->where($map2)->field("id,name,create_time")-> limit(6)-> order("create_time desc")->select();
			$this -> assign("receive_lists", $receive_lists);
		}
	}

	protected function _doc_list() {
		$user_id = get_user_id();
		$model = D('Doc');
		//获取最新邮件

		$where['is_del'] = array('eq', '0');
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id(),"DocFolder");
		$where['folder']=array("in",$folder_list);		
		$doc_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("doc_list", $doc_list);
	}
	
	protected function _news_list() {
		$user_id = get_user_id();
		$model = D('News');
		
		$where['is_del'] = array('eq', '0');
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id(),"NewsFolder");
		$where['folder']=array("in",$folder_list);		
		$news_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("news_list",$news_list);
	}	
	
	
	protected function _slide_list() {
		$slide_list = M("Slide") -> where($where) -> order('sort asc') -> select();		
		$this -> assign("slide_list",$slide_list);
	}
	
	protected function _schedule_list() {
		$user_id = get_user_id();
		$model = M('Schedule');
		//获取最新邮件
		$start_date = date("Y-m-d");
		$where['user_id'] = $user_id;
		$where['start_date'] = array('egt', $start_date);
		$schedule_list = M("Schedule") -> where($where) -> order('start_date,priority desc') -> limit(6) -> select();
		$this -> assign("schedule_list", $schedule_list);

		$model = M("Todo");
		$where = array();
		$where['user_id'] = $user_id;
		$where['status'] = array("in", "1,2");
		$todo_list = M("Todo") -> where($where) -> order('priority desc,sort asc') -> limit(6) -> select();
		$this -> assign("todo_list", $todo_list);
	}

	protected function _notice_list() {
		$model = D('Notice');
		//获取最新通知
		$where['is_del'] = array('eq', '0');
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id(),"NoticeFolder");
		$where['folder']=array("in",$folder_list);
		$new_notice_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(10) -> select();
		$this -> assign("new_notice_list", $new_notice_list);
	}

	protected function _forum_list() {
		$model = D('Forum');
		$where['is_del'] = array('eq', '0');
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id(),"ForumFolder");
		$where['folder']=array("in",$folder_list);		
		$new_forum_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("new_forum_list", $new_forum_list);
	}

	protected function _room_list(){
		$model = D('room');	
		$today = date("Y-m-d");

		$s_time = date("00:00:00");
		$m_time = date("12:00:00");
		$e_time = date("23:59:59");

		$room_list_am =array();
		$room_list_pm =array();

		$where['is_del'] = array('eq', '0');
		$where['start_date'] = array('eq', $today);	
		$room_list = $model -> where($where) -> field("id,room_num,name,start_time,end_time,user_name") -> order("start_time desc")-> select();
		foreach ($room_list as $k => $v) {
			if($v['start_time']>=$s_time && $v['start_time']<=$m_time){
				   $room_list_am[$k]=$v;
			}else{
				   $room_list_pm[$k]=$v;
			}
		}
		$weekarray=array("日","一","二","三","四","五","六");  
		$week = "星期".$weekarray[date("w",strtotime($today))];

		$this -> assign("room_list_am",$room_list_am);
		$this -> assign("room_list_pm",$room_list_pm);
		$this -> assign("week",$week);
		$this -> assign("today",$today);
	}

}
?>