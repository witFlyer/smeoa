<?php

class RoomAction extends CommonAction{
	protected $config = array('app_type' => 'personal');
	
	// function read() {
	// 	$widget['jquery-ui'] = true;		
	// 	$this -> assign("widget", $widget);
				
	// 	$model = M('Room');
	// 	$id = $_REQUEST['id'];
	// 	$where['id'] = $id;
	// 	$vo = $model -> where($where) -> find();
	// 	$this -> assign('vo', $vo);
	// 	$this -> display();
	// }

	// 预约申请
	public function apply() {
		$widget['jquery-ui'] = true;
		$widget['date'] = true;	
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		//会议室列表
		$field = 'id,name';
		$room_list = D("SystemTag")->get_tag_list2($field,'RoomList');
		$this->assign("room_list", $room_list);

		$model = M("FlowType");
		$type_id = 99;
		$flow_type = $model -> find($type_id);
		$this->assign('flow_type',$flow_type);
		$this -> display();
	}

	//预约管理
	public function manage(){
		$model = D("FlowType");
		$id = 99;
		$vo = $model -> getById($id);
		$this -> assign('vo', $vo);
		$this -> assign('id',$id);
		$this -> display();
	}
	public function manageSave(){
		$data = $_POST;
		$list = D('FlowType')->save($data);
		if (false !== $list) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			$this -> error('编辑失败!');
		}
	}
	function _flow_auth_filter($folder,&$map){
		$emp_no = get_emp_no();
		$user_id = get_user_id();		
		switch ($folder){
			case 'confirm' :
				$this -> assign("folder_name", '待审批的');
				$RoomLog = M("RoomLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is null";
				$log_list = $RoomLog -> where($where) -> field('flow_id') -> select();
				
				$log_list = rotate($log_list);

				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string']='1=2';
				}
				break;
			case 'submit' :
				$this -> assign("folder_name", '我预约的');
				$map['user_id'] = array('eq',$user_id);
				$map['step'] = array(array('gt',10),array('eq',0), 'or');
				
				break;

			case 'finish' :
				$this -> assign("folder_name", '已审批的');
				$RoomLog = M("RoomLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "comment is not null";
				$log_list = $RoomLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
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
		
		$flow_type_list=M("FlowType")->where($flow_type_where)->getField("id,name");
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
		$model = D("Room");

		if($_REQUEST['mode']=='export'){
			$this->_folder_export($model,$map);
		}else{
			$this -> _list($model, $map);
		}
		if($folder=='darft'){		
			$this -> display('draft');
		}elseif($folder=='submit'){
			$this -> display('submit');
		}elseif($folder=='finish'){
			$this -> display('finish');
		}else{
			$this -> display();
		}
		
	}

	/** 插入新新数据  **/
	protected function _insert(){
		$model = D("Room");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		$data=$_POST;
		$list = $model -> add();
		if ($list !== false){//保存成功
			if($data['step']==20){
				$this -> assign('jumpUrl',U('room/folder?fid=submit'));
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
			
		$model = D("Room");
		$id = $_REQUEST['id'];
		$this->assign('rid',$id);
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

		$model = M("FlowType");
		$flow_type= $model -> find($flow_type_id);
		$this -> assign("flow_type", $flow_type);

		$model = M("RoomLog");
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
		
		//查看抄送修改权限
		$write=M('User')->where(array('id'=>get_user_id()))->getField('ensure_Room');
		if($write) {
			$this->assign('is_write',1);
		}
		
		$time=time();
		$user_id = $vo['user_id'];//流程所属用户id
		//查看的同时删除流程处理提示效果
		if($folder == 'submit'){
			$k=M('UserInform3')->where("flow_id=$id")->select();
			if(!empty($k)){
				M('UserInform3')->where(array('user_id'=>$user_id,'flow_id'=>$id,'to_user_id'=>get_user_id()))->delete();
			}	
		}
		if($folder=='finish'){
			$k=M('UserInform3')->where("flow_id=$id")->select();
			if(!empty($k)){
				M('UserInform3')->where(array('trans_id'=>array('neq',get_user_id()),'flow_id'=>$id,'to_user_id'=>get_user_id()))->delete();
			}		
		}
		if($folder=='receive'){
			M('RoomLog')->where(array('flow_id'=>$id,'step'=>100,'user_id'=>get_user_id()))->setField('is_read',1);	
		}
		//分配流程数据
			$voList=M('Room')->where("id=$id")->find();
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
					if(M('RoomLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>1))->find()){
						$update_time=M('RoomLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>1))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=1;
					}elseif(M('RoomLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'_string'=>'result is null'))->find()){
						$update_time=M('RoomLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'_string'=>'result is null'))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=2;
					}elseif(M('RoomLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>0))->find()){
						$update_time=M('RoomLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>0))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=0;
					}else{
						$uu_name[$key]['is_agree']=3;
					}
				}
		//dump($uu_name);die;
		$voList['confirm_name']=$uu_name;
		$this->assign('list',$voList);
		$this->assign('folder',$folder);
		$ensure_name=get_user_name().'/'.get_dept_name();
		$this->assign('ensure_name',$ensure_name);	
		$field = 'id,name';
		$room_list = D("SystemTag")->get_tag_list2($field,'RoomList');
		$this->assign("room_list", $room_list);

		//是否有删除权限
		$userArr = array();
		$userEmpNo = M('User')->where("id=$user_id")->getField('emp_no'); //预约申请人的编号
		$confirm = M('Room')->where("id=$id")->getField('confirm');
		$userArr = explode('|', $confirm);
		array_pop($userArr);
		array_push($userArr, $userEmpNo);
		$userIdNow =  get_emp_no(); //当前用户的编号
		if(in_array($userIdNow, $userArr)){
			$this->assign('can_del',1);
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
		
		$model = D("Room");
		$id = $_REQUEST['id'];
		$where['id']=array('eq',$id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;
		$vo = $model ->where($map)->find();
		if(empty($vo)){
			$this ->error("系统错误");	
		}
		$this -> assign('vo', $vo);

		$model = M("RoomType");
		$type = $vo['type'];
		$flow_type = $model -> find($type);
		$this -> assign("flow_type", $flow_type);
		$model = M("RoomLog");
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
		$form_data = $_POST;
		$s_time = strtotime($form_data['start_date']);
		$e_time = strtotime($form_data['end_date']);
		$room_list=M('Room')->where(array('room_id'=>$form_data['room_id'],'is_del'=>0))->field('start_date,end_date')->select();
		foreach ($room_list as $k=>$v){
			if($s_time<=strtotime($v['end_date']) && $e_time>=strtotime($v['start_date'])){
				$this -> error('时间冲突!');
			}
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

	public function mark() {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'approve' :
				$model = D("RoomLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
			
				$model -> result = 1;

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				$model = D("RoomLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					$user_id=M("Room") -> where("id=$flow_id") -> getField('user_id');
					$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
					$user = M('RoomLog')->where($where)->field('user_id')->select();
					if(!empty($user)){
						foreach ($user as $k=>$v){
							$this -> _pushReturn($new, "您有一个预约处理有变化",1,$v['user_id']);
							$this -> _pushReturn($new, "您有一个预约处理有变化",1,$user_id);
							$inform=array(
								'user_id'	=> $user_id,
								'flow_id'	=>$flow_id,
								'time'		=>time(),
								'trans_id'	=>get_user_id(),
								'to_user_id'=>$v['user_id']
						);
						$uinform = M('UserInform3')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform3')->add($inform);
						}
						}
					}else{
						$inform=array(
								'user_id'	=> $user_id,
								'flow_id'	=>$flow_id,
								'time'		=>time(),
								'trans_id'	=>get_user_id(),
								'to_user_id'=>$user_id
						);
						$uinform=M('UserInform3')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform3')->add($inform);
						}
						$this -> _pushReturn($new, "您有一个预约处理有变化",1,$user_id);	
					}
						
					
					//			添加流程处理通知信息
				
					
					D("Room") -> next_step($flow_id,$step);
					$this -> assign('jumpUrl',U('Room/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'back' :	
	
				$model = D("RoomLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
				
				$model -> result = 2;
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
				$emp_no=$_REQUEST['emp_no'];
				if ($list!== false) {//保存成功	
					D("Room") -> next_step($flow_id,$step,$emp_no);
					
					$this -> assign('jumpUrl',U('Room/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;				
			case 'reject' :
				$model = D("RoomLog");
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
				//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
				$model = D("RoomLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					D("Room") -> where("id=$flow_id") -> setField('step', 0);
					
					$user_id=M("Room") -> where("id=$flow_id") -> getField('user_id');
					$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
					$user = M('RoomLog')->where($where)->field('user_id')->select();
					if(!empty($user)){
						foreach ($user as $k=>$v){
							$this -> _pushReturn($new, "您有一个预约处理有变化",1,$v['user_id']);	
					//添加流程处理通知信息
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$v['user_id']
					);
					$uinform=M('UserInform3')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform3')->add($inform);
						}
						}
					}else{
						$inform=array(
								'user_id'	=> $user_id,
								'flow_id'	=>$flow_id,
								'time'		=>time(),
								'trans_id'	=>get_user_id(),
								'to_user_id'=>$user_id
						);
						$uinform=M('UserInform3')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform3')->add($inform);
						}
					}
					$this -> _pushReturn($new, "您有一个预约被否决",1,$user_id);				
					
					$this -> assign('jumpUrl',U('flow/folder?fid=confirm'));
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

		$model = D("RoomLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 1;

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();
		$model = D("RoomLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del',1);

		if ($list !== false) {//保存成功
			D("Room") -> next_step($flow_id, $step);
			$user_id=M("Room") -> where("id=$flow_id") -> getField('user_id'); 
			$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
			$user = M('RoomLog')->where($where)->field('user_id')->select();
			if(!empty($user)){
				foreach ($user as $k=>$v){
					$this -> _pushReturn($new, "您有一个预约处理有变化",1,$v['user_id']);	
					//添加流程处理通知信息
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$v['user_id']
					);
				$uinform=M('UserInform3')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
				if($uinform==false){
					M('UserInform3')->add($inform);
					}
				}
			}else{
				$inform=array(
					'user_id'	=> $user_id,
					'flow_id'	=>$flow_id,
					'time'		=>time(),
					'trans_id'	=>get_user_id(),
					'to_user_id'=>$user_id
				);
				$uinform=M('UserInform3')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
				if($uinform==false){
					M('UserInform3')->add($inform);
					}
				}
			$this -> _pushReturn($new, "您有一个预约处理有变化",1,$user_id);
		
			$this -> assign('jumpUrl',U('Room/confirm'));
			$this -> success('操作成功!');
		}else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("RoomLog");
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
		//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
		$model = D("RoomLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del',1);

		if ($list !== false) {
			//保存成功,处理信息的推送
			D("Room") -> where("id=$flow_id") -> setField('step',0);
			$user_id=M("Room") -> where("id=$flow_id") -> getField('user_id');
			$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
			$user = M('RoomLog')->where($where)->field('user_id')->select();
				if(!empty($user)){
//						array_push($user, array('user_id'=>$user_id));
					foreach ($user as $k=>$v){
						$this -> _pushReturn($new, "您有一个预约处理有变化",1,$v['user_id']);	
						//添加流程处理通知信息
						$inform=array(
							'user_id'	=> $user_id,
							'flow_id'	=>$flow_id,
							'time'		=>time(),
							'trans_id'	=>get_user_id(),
							'to_user_id'=>$v['user_id']
						);
					$uinform=M('UserInform3')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
					if($uinform==false){
						M('UserInform3')->add($inform);
					}
					}
				}else{
					$inform=array(
							'user_id'	=> $user_id,
							'flow_id'	=>$flow_id,
							'time'		=>time(),
							'trans_id'	=>get_user_id(),
							'to_user_id'=>$user_id
						);
					$uinform=M('UserInform3')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
					if($uinform==false){
						M('UserInform3')->add($inform);
					}
				}
			 
			$this -> _pushReturn($new, "您有一个预约被否决",1,$user_id);
			
			$this -> assign('jumpUrl',U('flow/confirm'));
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
	// 检查时间
	function check_time(){
		$data = $_POST;
		$count = 0;
		$s_time = strtotime($data['start_date']);
		$e_time = strtotime($data['end_date']);
		$room_list=M('Room')->where(array('room_id'=>$data['room_id'],'is_del'=>0))->field('start_date,end_date')->select();
		foreach ($room_list as $k=>$v){
			if($s_time<=strtotime($v['end_date']) && $e_time>=strtotime($v['start_date'])){
				$count++;
			}
		}
		if($s_time>=$e_time){
			echo 1;
		}elseif($count>0){
			echo 2;
		}else{
			echo 0;
		}
	}
	//删除	
	public function del(){
		if($this->isAjax()){
			$id = $_POST['id'];
			$is_del = M("Room")->where(array('id'=>$id))->delete();
			if($is_del !== false){
				M('RoomLog')->where(array('flow_id'=>$id))->delete();
				M('UserInform3')->where(array('flow_id'=>$id))->delete();
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	function json() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type:text/html; charset=utf-8");
		$start_date = $_REQUEST["start_date"]." 00:00:00";
		$end_date = $_REQUEST["end_date"]." 23:59:59";
		
		$where['is_del']=array('eq',0);
		$where['start_date'] = array( array('egt', $start_date), array('elt', $end_date));
		$list_result = M("Room") -> where($where) -> order('start_date desc') -> select();
		$list=array();
		$list = $list_result;
		foreach ($list_result as $k => $v) {
			$list[$k]['room_name'] = get_sys_tag($v['room_id']);
		}
		exit(json_encode($list));
	}
	
}