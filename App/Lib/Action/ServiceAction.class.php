<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ServiceAction extends CommonAction {
	protected $config = array('app_type' => 'flow', 'action_auth' => array('folder' =>'read','mark' =>'admin','report'=>'admin','json'=>'read','appoint'=>'read','search'=>'read'));

	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if(!empty($_POST["be_create_start"])){
			$map['create_time'] = array('between',array(strtotime($_POST['be_create_start']),strtotime($_POST['en_create_end'])));
		}
		if(!empty($_POST["be_mark_start"])){
			$map['update_time'] = array('between',array(strtotime($_POST['be_mark_start']),strtotime($_POST['en_mark_end'])));
		}
		if (!empty($_REQUEST['keyword'])) {
			$keyword = $_POST['keyword'];
			$map['name'] = array('like', "%" . $keyword . "%");					
		}
		if (isset($_POST['eq_service_type']) AND $_POST['eq_service_type']!='') {
			$eq_service_type = $_POST['eq_service_type'];
			$map['service_type'] = array('eq',$eq_service_type);					
		}
		if (isset($_POST['is_ensure']) AND $_POST['is_ensure']!=''){
			$map['is_ensure'] = $_POST['is_ensure'];
		}
	}

	function index(){
		$model = D('ServiceType');
		$where['is_del'] = 0;
		
		// 申请权限
		$user_id=get_user_id();
		$role_list=D("Role")->get_role_list($user_id);
		$role_list=rotate($role_list);
		$role_list=$role_list['role_id'];
		// dump($role_list);die;
		$where['request_duty']=array('in',$role_list);
		$list = $model->where($where)->order('sort')-> select();
		$this -> assign("list", $list);
		
		$this -> display();
	}
	
	function _flow_auth_filter($folder,&$map){
		$emp_no = get_emp_no();
		$user_id = get_user_id();		
		switch ($folder){
			case 'confirm' :
				$this -> assign("folder_name", '需要我审批的服务需求');
				$ServiceLog = M("ServiceLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is null";
				$log_list = $ServiceLog -> where($where) -> field('flow_id') -> select();
				
				$log_list = rotate($log_list);

				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string']='1=2';
				}
				break;

			case 'darft' :
				$this -> assign("folder_name", '我临时保存的服务需求');
				$map['user_id'] = $user_id;
				$map['step'] = 10;
				break;

			case 'submit' :
				$this -> assign("folder_name", '我发起的服务需求');
				$map['user_id'] = array('eq',$user_id);
				$map['step'] = array(array('gt',10),array('eq',0), 'or');
				
				break;

			case 'finish' :
				$this -> assign("folder_name", '我处理过的服务需求');
				$ServiceLog = M("ServiceLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "comment is not null";
				$log_list = $ServiceLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string']='1=2';
				}
				break;

			case 'receive' :
				$this -> assign("folder_name", '抄送给我的服务需求');
				$ServiceLog = M("ServiceLog");
				$where['emp_no'] = $emp_no;
				$where['step'] = 100;
				$log_list = $ServiceLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in',$log_list['flow_id']);
				} else {
					$map['_string']='1=2';
				}
				break;	
			case 'report' :
				$this -> assign("folder_name", '统计报告');
				$role_list=D("Role")->get_role_list($user_id);
				$role_list=rotate($role_list);
				$role_list=$role_list['role_id'];
				
				$duty_list=D("Role")->get_duty_list($role_list);
				$duty_list=rotate($duty_list);
				$duty_list=$duty_list['duty_id'];	
						
				if (!empty($duty_list)) {
					$map['report_duty'] = array('in',$duty_list);
					$map['step']=array('gt',10);
				} else {
					$this->error("没有权限");
				}
				break;
			case 'assign' :
				$this -> assign("folder_name", '指派给我处理的');
				$Service = M("Service");
				$where['deal_userid'] = get_user_id();
				$where['step'] = 40;
				$where['is_ensure'] = 0;
				$log_list = $Service -> where($where) -> field('id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in',$log_list['id']);
				} else {
					$map['_string']='1=2';
				}
				break;
			case 'deal' :
				$this -> assign("folder_name", '我处理过的');
				$Service = M("Service");
				$where['deal_userid'] = get_user_id();
				$where['step'] = 40;
				$where['is_ensure'] = 1;
				$log_list = $Service -> where($where) -> field('id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in',$log_list['id']);
				} else {
					$map['_string']='1=2';
				}
				break;
			case 'toassign' :
				$this -> assign("folder_name", '我指派的');
				$Service = M("Service");
				$where['appoint_userid'] = get_user_id();
				$where['step'] = 40;
				$log_list = $Service -> where($where) -> field('id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in',$log_list['id']);
				} else {
					$map['_string']='1=2';
				}
				break;
		}
	}
	function folder(){
		 
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$emp_no = get_emp_no();
		$user_id = get_user_id();

		$flow_type_where['is_del']=array('eq',0);
		
		$flow_type_list=M("ServiceType")->where($flow_type_where)->getField("id,name");
		$this->assign("flow_type_list",$flow_type_list);
		
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$folder = $_REQUEST['fid'];
		
		$this -> assign("folder", $folder);
		
		if(empty($folder)){
			$this  ->error("系统错误");
		}
		
		$this->_flow_auth_filter($folder,$map);
		$model = D("ServiceView");

		$this -> _list($model, $map);
		if($folder=='darft'){		
			$this -> display('draft');
		}elseif($folder=='submit'){
			$this -> display('submit');
		}elseif($folder=='finish'){
			$this -> display('finish');
		}elseif($folder=='assign'){
			$this -> display('assign');
		}elseif($folder=='toassign'){
			$this -> display('toassign');
		}else{
			$this -> display();
		}
		
	}

	function add() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		// 属于个人的服务流程
		$user_id=get_user_id();
		$type_id = $_REQUEST['type'];
		$list=M('UserService')->where(array('user_id'=>$user_id,'flow_type_id'=>$type_id))->find();
		$model = M("ServiceType");
		$flow_type = $model -> find($type_id);

		if($list){
			$flow_type['confirm']=$list['confirm'];
			$flow_type['confirm_name']=$list['confirm_name'];
		}
		$this -> assign("flow_type", $flow_type);
		
		$doc_time = date("md",time());
		$this -> assign("doc_time", $doc_time);			
		$this -> display();
	}

	/** 插入新新数据  **/
	protected function _insert(){
		$model = D("Service");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
		$data=$_POST;

		$list = $model -> add();
		if ($list !== false){//保存成功
			if($data['step']==10){
				$this -> assign('jumpUrl',U('service/folder?fid=darft'));
				$this -> success('新增成功!');
			}elseif($data['step']==20){
				$this -> assign('jumpUrl',U('service/folder?fid=submit'));
				$this -> success('新增成功!');
			}else{
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('新增成功!');
			}	
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	function read(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);	
        // var_dump($folder);die;
		if(empty($folder)){
			$this ->error("系统错误");
		}
		$this->_flow_auth_filter($folder,$map);		
			
		$model = D("Service");
		$id = $_REQUEST['id'];
		$where['id']=array('eq',$id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;	
		$vo = $model ->where($map)->find();
		if(empty($vo)){
			$this ->error("系统错误");	
		}

		$flow_type_id=$vo['type'];
		$this -> assign('vo', $vo);
		$this -> assign("emp_no", $vo['emp_no']);
		$this -> assign("user_name", $vo['user_name']);

		$model = M("ServiceType");
		$flow_type= $model -> find($flow_type_id);
		$this -> assign("flow_type", $flow_type);

		$model = M("ServiceLog");
		$where=array();
		$where['flow_id'] = $id;
		$where['step'] = array('lt',100);
		$where['_string'] = "result is not null";
		$flow_log = $model -> where($where) ->order("id")-> select();
		foreach($flow_log as $k=>$v){
			$dept_id=M('User')->where(array('id'=>$v['user_id']))->getField('dept_id');
			$dept_n=M('Dept')->where(array('id'=>$dept_id))->getField('name');
			$flow_log[$k]['user_name']=$v['user_name'].'/'.$dept_n;
		}
		$this -> assign("flow_log", $flow_log);

		$where = array();
		$where['flow_id'] = $id;
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$to_confirm = $model -> where($where) -> find();
		$this -> assign("to_confirm", $to_confirm);
		
		if (!empty($to_confirm)) {
			$is_edit = $flow_type['is_edit'];
			$this -> assign("is_edit", $is_edit);
			if (!empty($is_edit)) {
				$widget['uploader'] = true;
				$widget['editor'] = true;
				$this -> assign("widget", $widget);
			}
		}

		$where = array();
		$where['flow_id'] = $id;
		$where['_string'] = "result is not null";
		$where['emp_no']=array('neq',$vo['emp_no']);
		$confirmed = $model ->Distinct(true)-> where($where) -> field('emp_no,user_name') -> select();
		$this -> assign("confirmed", $confirmed);
		
		$time=time();
		$user_id = $vo['user_id'];//流程所属用户id
		//查看的同时删除流程处理提示效果
		$k=M('UserInform2')->where("flow_id=$id")->select();
		if(!empty($k)){
			M('UserInform2')->where(array('user_id'=>$user_id,'flow_id'=>$id,'to_user_id'=>get_user_id()))->delete();
		}
		if($folder=='receive'){
			M('ServiceLog')->where(array('flow_id'=>$id,'step'=>100,'user_id'=>get_user_id()))->setField('is_read',1);	
			$k=M('UserInform2')->where("flow_id=$id")->select();
			if(!empty($k)){
				M('UserInform2')->where(array('user_id'=>$user_id,'flow_id'=>$id,'to_user_id'=>get_user_id()))->delete();
			}
		}
		//分配流程数据
			$voList=M('Service')->where("id=$id")->find();
				$u_name['emp_no']=explode('|', $voList['confirm']);
				foreach ($u_name['emp_no'] as $key => $value) {
					if(!$value){
						unset($u_name['emp_no'][$key]);
					}
				}
				$u_name['id']=$voList['id'];
				foreach ($u_name['emp_no'] as $key => $value) {
						$nname=M('User')->where(array('emp_no'=>$value))->field('name,dept_id')->find();
						$dept_name=M('Dept')->where(array('id'=>$nname['dept_id']))->field('name')->find();
						$uu_name[$key]['name']=$nname['name'].'/'.$dept_name['name'];
					if(M('ServiceLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>1))->find()){
						$update_time=M('ServiceLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>1))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=1;
					}elseif(M('ServiceLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'_string'=>'result is null'))->find()){
						$update_time=M('ServiceLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'_string'=>'result is null'))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=2;
					}elseif(M('ServiceLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>0))->find()){
						$update_time=M('ServiceLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>0))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=0;
					}else{
						$uu_name[$key]['is_agree']=3;
					}
				}
		$voList['confirm_name']=$uu_name;
		$this->assign('list',$voList);
		$this->assign('folder',$folder);
		$ensure_name=get_user_name().'/'.get_dept_name();
		$this->assign('ensure_name',$ensure_name);

		$userId = get_user_id();
		$refer = explode('|', $voList['refer']);
		array_pop($refer);
		// 判断指派人是否在抄送人里 1.在的话指派处理人 2.不在则直接在抄送执行处理
		$assign_count = 0;
		foreach ($refer as $k => $v) {
			$is_assigner = M('User')->where(array('emp_no'=>$v))->getField("is_assigner");
			if($is_assigner){
				$assign_count++;
			}
		}
		if($assign_count>=1){
			
			//标识可以执行指派人 处理服务
			if($voList['deal_userid']==get_user_id()){
				$this->assign('is_write','b');
			}
			//登陆人是否有指派权限
			$is_assigner = M('User')->where("id=$userId")->getField("is_assigner");
			if($is_assigner){
				$this->assign('is_assigner',1);
			}

			//判断抄送人是否可以处理服务
			$user_list = M("User")->where(array('ensure_service'=>1))->field('id,name,emp_no')->select();	
			$this->assign('user_list',$user_list);
			$this->assign('is_assign',1);  //标识是否可以指派
		}
		$this -> display();
	}

	function edit() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);

		if(empty($folder)){
			$this ->error("系统错误");
		}
		$this->_flow_auth_filter($folder,$map);	
		
		$model = D("Service");
		$id = $_REQUEST['id'];
		$where['id']=array('eq',$id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;
		$vo = $model ->where($map)->find();
		if(empty($vo)){
			$this ->error("系统错误");	
		}
		$this -> assign('vo', $vo);

		$model = M("ServiceType");
		$type = $vo['type'];
		$flow_type = $model -> find($type);
		$this -> assign("flow_type", $flow_type);
		$model = M("ServiceLog");
		$where['flow_id'] = $id;
		$where['_string'] = "result is not null";
		$flow_log = $model -> where($where) -> select();

		$this -> assign("flow_log", $flow_log);
		$where = array();
		$where['flow_id'] = $id;
		$where['emp_no'] = get_emp_no();
		$where['_string'] = "result is null";
		$confirm = $model -> where($where) -> select();
		$this -> assign("confirm", $confirm[0]);

		$this -> display();
	}

	/* 更新数据  */
	protected function _update() {
		$name = $this -> getActionName();
		$model = D($name);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$flow_id=$model->id;
		$list = $model -> save();

		if (false !== $list) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}

	public function mark() {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'approve' :
				$model = D("ServiceLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
			
				$model -> result = 1;

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();

				//保存审核时间
				M('Service')->where(array('id'=>$flow_id))->save(array('mark_time'=>time()));

				$model = D("ServiceLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") ->delete();

				if ($list !== false) {
					//保存成功,添加流程处理通知信息
					$user_id=M("Service") -> where("id=$flow_id") -> getField('user_id');  //申请人
					$this -> _pushReturn($new, "有一个服务需求状态有变化 ",1,$user_id);
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$user_id
					);
					$uinform = M('UserInform2')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
					if($uinform==false){
						M('UserInform2')->add($inform);
					}
					D("Service") -> next_step($flow_id,$step);
					$this -> assign('jumpUrl',U('service/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;				
			case 'reject' :
				$model = D("ServiceLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
				$model -> result = 0;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();

				//保存审核时间
				M('Service')->where(array('id'=>$flow_id))->save(array('mark_time'=>time()));
				//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
				$model = D("ServiceLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					D("Service") -> where("id=$flow_id") -> setField('step', 0);
					
					$user_id=M("Service") -> where("id=$flow_id") -> getField('user_id'); //申请人
					$this -> _pushReturn($new, "您有一个服务需求申请被否决",1,$user_id);
					//添加流程处理通知信息
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$user_id
					);
					$uinform = M('UserInform2')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
					if($uinform==false){
						M('UserInform2')->add($inform);
					}
					$this -> assign('jumpUrl',U('service/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			default :
				break;
		}
	}

	public function approve() {

		$model = D("ServiceLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 1;

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();

		M('Service')->where(array('id'=>$flow_id))->save(array('mark_time'=>time()));
		$model = D("ServiceLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del',1);

		if ($list !== false) {//保存成功
			D("Service") -> next_step($flow_id, $step);
			$user_id=M("Service") -> where("id=$flow_id") -> getField('user_id'); 
			$this -> _pushReturn($new, "有一个服务需求状态有变化 ",1,$user_id);	
			//添加流程处理通知信息
			$inform=array(
				'user_id'	=> $user_id,
				'flow_id'	=>$flow_id,
				'time'		=>time(),
				'trans_id'	=>get_user_id(),
				'to_user_id'=>$user_id
				);
			$uinform = M('UserInform2')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
			if($uinform==false){
				M('UserInform2')->add($inform);
			}
			$this -> assign('jumpUrl',U('Service/confirm'));
			$this -> success('操作成功!');
		}else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("ServiceLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 0;
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name =get_user_name();
		};

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();
		M('Service')->where(array('id'=>$flow_id))->save(array('mark_time'=>time()));
		//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
		$model = D("ServiceLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del',1);

		if ($list !== false) {
			//保存成功,处理信息的推送
			D("Service") -> where("id=$flow_id") -> setField('step',0);
			$user_id=M("Service") -> where("id=$flow_id") -> getField('user_id'); 
			$this -> _pushReturn($new, "您有一个服务需求申请被否决 ",1,$user_id);	
			//添加流程处理通知信息
			$inform=array(
				'user_id'	=> $user_id,
				'flow_id'	=>$flow_id,
				'time'		=>time(),
				'trans_id'	=>get_user_id(),
				'to_user_id'=>$user_id
				);
			$uinform = M('UserInform2')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
			if($uinform==false){
				M('UserInform2')->add($inform);
			}
			$this -> assign('jumpUrl',U('service/confirm'));
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function down() {
		$this -> _down();
	}

	public function upload() {
		$this -> _upload();
	}

	protected function _assign_tag_list() {
		$model = D("SystemTag");
		$tag_list = $model -> get_tag_list('id,name','ServiceType');
		$this -> assign("tag_list", $tag_list);
	}
	// 指派服务处理人
	function appoint(){
		$id = $_POST['flow_id'];
		$deal_userid = $_POST['deal_userid']; //指派处理人
		$appoint_remark = $_POST['appoint_remark'];//指派备注
		$appoint_userid = get_user_id(); //指派人
		$appoint_time = time();
		$data = array(
			'deal_userid' 	=> $deal_userid,
			'appoint_time'	=> $appoint_time,
			'appoint_userid'=> $appoint_userid,
			'appoint_remark'=> $appoint_remark
			);
		$list = M('Service')->where("id=$id")->save($data);
		if($list !== false){
			$this -> _pushReturn($new, "有一个服务需求需要我处理",1,$deal_userid);
			echo 1;
		}else{
			echo 0;
		}
	}
	//服务查询
	public function search(){
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$model = D("Service");
		$map['step'] = 40;
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
		return;
	}
}
