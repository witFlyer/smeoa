<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class FlowAction extends CommonAction {

	protected $config = array('app_type' => 'flow', 'action_auth' => array('folder' =>'read','mark' =>'admin','report'=>'admin','flow_list'=>'read','json'=>'read'));

	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword'])) {
			$keyword = $_POST['keyword'];
			$map['name'] = array('like', "%" . $keyword . "%");			
		}
	}

	function index(){
		$model=D("Flow");
		$model = D('FlowTypeView');
		$where['is_del'] = 0;
		
		$user_id=get_user_id();
		$role_list=D("Role")->get_role_list($user_id);
		$role_list=rotate($role_list);
		$role_list=$role_list['role_id'];
		
		$duty_list=D("Role")->get_duty_list($role_list);
		$duty_list=rotate($duty_list);
		$duty_list=$duty_list['duty_id'];

		$where['request_duty']=array('in',$duty_list);	

		$list = $model -> where($where)->order('sort')-> select();
		$this -> assign("list", $list);
		// var_dump($list);die;
		$this -> _assign_tag_list();
		
		$this -> display();
	}
	
	function _flow_auth_filter($folder,&$map){
		$emp_no = get_emp_no();
		$user_id = get_user_id();		
		switch ($folder){
			case 'confirm' :
				$this -> assign("folder_name", '需要我办理的公文');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "result is null";
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				
				$log_list = rotate($log_list);

				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string']='1=2';
				}
				break;

			case 'darft' :
				$this -> assign("folder_name", '我临时保存的公文');
				$map['user_id'] = $user_id;
				$map['step'] = 10;
				break;

			case 'submit' :
				$this -> assign("folder_name", '我发起的公文');
				$map['user_id'] = array('eq',$user_id);
				$map['step'] = array(array('gt',10),array('eq',0), 'or');
				
				break;

			case 'finish' :
				$this -> assign("folder_name", '我处理过的公文');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['_string'] = "comment is not null";
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
				$log_list = rotate($log_list);
				if (!empty($log_list)) {
					$map['id'] = array('in', $log_list['flow_id']);
				} else {
					$map['_string']='1=2';
				}
				break;

			case 'receive' :
				$this -> assign("folder_name", '抄送给我的公文');
				$FlowLog = M("FlowLog");
				$where['emp_no'] = $emp_no;
				$where['step'] = 100;
				$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
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
		$model = D("FlowView");

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
	
	private function _folder_export($model,$map){
		$list = $model -> where($map) -> select();

		//导入thinkphp第三方类库
		Vendor('Excel.PHPExcel');
		
		//$inputFileName = "Public/templete/contact.xlsx";
		//$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		$objPHPExcel=new PHPExcel();
 
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);

			//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i","编号") -> setCellValue("B$i","类型") -> setCellValue("C$i","标题") -> setCellValue("D$i","登录时间") -> setCellValue("E$i","部门") -> setCellValue("F$i","登录人") -> setCellValue("G$i","状态") -> setCellValue("H$i","审批") -> setCellValue("I$i","协商") -> setCellValue("J$i","抄送") -> setCellValue("J$i","审批情况");
		foreach ($list as $val){
			$i++;
			//dump($val);
			$id=$val['id'];
			$doc_no=$val["doc_no"]; //编号
			$name=$val["name"]; //标题
			$confirm_name=strip_tags($val["confirm_name"]); //审批
			$consult_name=strip_tags($val["consult_name"]); //协商
			$refer_name=strip_tags($val["refer_name"]); //协商
			$type_name=$val["type_name"]; //公文类型
			$user_name=$val["user_name"]; //登记人
			$dept_name=$val["dept_name"]; //部门
			$create_time=$val["create_time"];
			$create_time=toDate($val["create_time"],'Y-m-d H:i:s');//创建时间
			$step=show_step_type($val["step"]); //

			
			//编号，类型，标题，登录时间，部门，登录人，状态，审批，协商，抄送，审批情况，自定义字段
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i",$doc_no) -> setCellValue("B$i", $type_name) -> setCellValue("C$i",$name) -> setCellValue("D$i",$create_time) -> setCellValue("E$i",$dept_name) -> setCellValue("F$i",$user_name) -> setCellValue("G$i",$step) -> setCellValue("H$i",$confirm_name) -> setCellValue("I$i",$consult_name);

			$model_flow_field=D("FlowField");
			$field_list = $model_flow_field ->get_data_list($id);
		//	dump($field_list);
			$k=0;
			if(!empty($field_list)){
				foreach($field_list as $field){
					$k++;
					$field_data=$field['name'].":".$field['val'];
					$location=get_cell_location("J",$i,$k);
					$objPHPExcel -> setActiveSheetIndex(0) ->setCellValue($location,$field_data);
				}
			}
		}
		// Rename worksheet
		$objPHPExcel -> getActiveSheet() -> setTitle('公文统计');

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$file_name="公文统计.xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//readfile($filename);
		$objWriter -> save('php://output');
		exit ;
	}

	function add() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		
		$user_id=get_user_id();
		$type_id = $_REQUEST['type'];
		$list=M('UserFlow')->where(array('user_id'=>$user_id,'flow_type_id'=>$type_id))->find();
		
		$model = M("FlowType");
		$flow_type = $model -> find($type_id);
		//判断是否存在个人所属公文
		if($list){
			$flow_type['confirm']=$list['confirm'];
			$flow_type['confirm_name']=$list['confirm_name'];
		}
		$this -> assign("flow_type", $flow_type);
		
		$model_flow_field=D("FlowField");
		$field_list = $model_flow_field ->get_field_list($type_id);
		
		$doc_time = date("md",time());
		//个人休假情况
		$per_jia=M('user')->where(array('id'=>$user_id))->field('shijia_count,bingjia_count,nianjia_count,nianjia_total')->find();
		$this->assign('per_jia',$per_jia);
		$nianjia_remain=$per_jia['nianjia_total']-$per_jia['nianjia_count'];
		$this->assign('nianjia_remain',$nianjia_remain);
		$this -> assign("field_list", $field_list);
		$this -> assign("doc_time", $doc_time);					
		$this -> display();
	}

	/** 插入新新数据  **/
	protected function _insert(){
		$model = D("Flow");
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
//		请假时间的判断
		
		$list = $model -> add();
		$model_flow_filed=D("FlowField")->set_field($list);

		if ($list !== false){//保存成功
			if($data['step']==10){
				$this -> assign('jumpUrl',U('flow/folder?fid=darft'));
				$this -> success('新增成功!');
			}elseif($data['step']==20){
				$this -> assign('jumpUrl',U('flow/folder?fid=submit'));
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
		$this -> assign("widget", $widget);
		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);	
        // var_dump($folder);die;
		if(empty($folder)){
			$this ->error("系统错误");
		}
		$this->_flow_auth_filter($folder,$map);		
			
		$model = D("Flow");
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

		$model_flow_field=D("FlowField");
		$field_list = $model_flow_field ->get_data_list($id);
		$this -> assign("field_list", $field_list);

		$model = M("FlowType");
		$flow_type= $model -> find($flow_type_id);
		$this -> assign("flow_type", $flow_type);

		$model = M("FlowLog");
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
		
		//个人休假情况
		$user_id = $vo['user_id'];//流程所属用户id
		$per_jia=M('User')->where(array('id'=>$user_id))->field('shijia_count,bingjia_count,nianjia_count,nianjia_total')->find();
		$this->assign('per_jia',$per_jia);
		
		$nianjia_remain=$per_jia['nianjia_total']-$per_jia['nianjia_count'];
		$this->assign('nianjia_remain',$nianjia_remain);
		
		//查看抄送修改权限
		$write=M('User')->where(array('id'=>get_user_id()))->getField('able_ensure');
		if($write) {
			$this->assign('is_write',1);
		}

		// 本次休假天数
		$num=M('FlowFieldData')->where("flow_id=$id and field_id=29")->getField('val');
		$this->assign('this_count',$num);
		
		$time=time();
		//查看的同时删除流程处理提示效果
		if($folder == 'submit'){
			$k=M('UserInform')->where("flow_id=$id")->find();
			if(!empty($k)){
				M('UserInform')->where(array('user_id'=>$user_id,'flow_id'=>$id,'to_user_id'=>get_user_id()))->delete();
			}
			
		}
		if($folder=='finish'){
			$k=M('UserInform')->where("flow_id=$id")->find();
			if(!empty($k)){
				M('UserInform')->where(array('trans_id'=>array('neq',get_user_id()),'flow_id'=>$id,'to_user_id'=>get_user_id()))->delete();
			}		
		}
		if($folder=='receive'){
			M('FlowLog')->where(array('flow_id'=>$id,'step'=>100,'user_id'=>get_user_id()))->setField('is_read',1);	
		}
		//分配流程数据
			$voList=M('Flow')->where("id=$id")->find();
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
					if(M('FlowLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>1))->find()){
						$update_time=M('FlowLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>1))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=1;
					}elseif(M('FlowLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'_string'=>'result is null'))->find()){
						$update_time=M('FlowLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'_string'=>'result is null'))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=2;
					}elseif(M('FlowLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>0))->find()){
						$update_time=M('FlowLog')->where(array('flow_id'=>$u_name['id'],'emp_no'=>$value,'result'=>0))->getField('update_time');
						$uu_name[$key]['update_time']=$update_time;
						$uu_name[$key]['is_agree']=0;
					}else{
						$uu_name[$key]['is_agree']=3;
					}
				}
//				dump($uu_name);die;
		$voList['confirm_name']=$uu_name;
		$this->assign('list',$voList);
		$this->assign('folder',$folder);
		$ensure_name=get_user_name().'/'.get_dept_name();
		$this->assign('ensure_name',$ensure_name);		
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
		
		$model = D("Flow");
		$id = $_REQUEST['id'];
		$where['id']=array('eq',$id);
		$where['_logic'] = 'and';
		$map['_complex'] = $where;
		$vo = $model ->where($map)->find();
		if(empty($vo)){
			$this ->error("系统错误");	
		}
		$this -> assign('vo', $vo);
		$model_flow_field=D("FlowField");
		$field_list = $model_flow_field ->get_data_list($id);
		$this -> assign("field_list", $field_list);

		$model = M("FlowType");
		$type = $vo['type'];
		$flow_type = $model -> find($type);
		$this -> assign("flow_type", $flow_type);
		$model = M("FlowLog");
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
		
		$user_id = get_user_id();
		$per_jia=M('User')->where(array('id'=>$user_id))->field('shijia_count,bingjia_count,nianjia_count,nianjia_total')->find();
		$this->assign('per_jia',$per_jia);
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
		$date=$_POST;
		$num = $date['flow_field_29'];
		$again_tag = $date['again_tag'];
		//抄送的可以修改，并最终确认
		$folder=$date['folder2'];
//		dump($folder);die;
		if($again_tag!=2){
		if($folder=='receive'){
			$user_id=M('Flow')->where(array('id'=>$date['id']))->getField('user_id');
			if($date['flow_field_12']=="事假"){
				M('User')->where(array('id'=>$user_id))->setInc('shijia_count',$num);
			}elseif ($date['flow_field_12']=="病假"){
				M('User')->where(array('id'=>$user_id))->setInc('bingjia_count',$num);
			}elseif($date['flow_field_12']=="年休假"){
					M('User')->where(array('id'=>$user_id))->setInc('nianjia_count',$num);
			}
		}
	}
		unset($date['folder2']);
		$list = $model -> save();
		
		$model_flow_filed=D("FlowField")->set_field($flow_id);
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
				$model = D("FlowLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
			
				$model -> result = 1;

				$flow_id = $model -> flow_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				$model = D("FlowLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					$user_id=M("Flow") -> where("id=$flow_id") -> getField('user_id');
					$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
					$user = M('FlowLog')->where($where)->field('user_id')->select();
					if(!empty($user)){
						foreach ($user as $k=>$v){
							$this -> _pushReturn($new, "您处理过的一个公文有变化",1,$v['user_id']);
							$this -> _pushReturn($new, "您有一个公文被处理",1,$user_id);
							$inform=array(
								'user_id'	=> $user_id,
								'flow_id'	=>$flow_id,
								'time'		=>time(),
								'trans_id'	=>get_user_id(),
								'to_user_id'=>$v['user_id']
						);
						$uinform = M('UserInform')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform')->add($inform);
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
						$uinform=M('UserInform')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform')->add($inform);
						}
						$this -> _pushReturn($new, "您有一个公文被处理",1,$user_id);	
					}	
					//添加流程处理通知信息
					D("Flow") -> next_step($flow_id,$step);
					$this -> assign('jumpUrl',U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'back' :	
	
				$model = D("FlowLog");
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

				if ($list!== false) {//保存成功	
					D("Flow") -> next_step($flow_id,$step,$emp_no);
					
					$this -> assign('jumpUrl',U('flow/folder?fid=confirm'));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;				
			case 'reject' :
				$model = D("FlowLog");
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
				$model = D("FlowLog");
				$model -> where("step=$step and flow_id=$flow_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					D("Flow") -> where("id=$flow_id") -> setField('step', 0);
					
					$user_id=M("Flow") -> where("id=$flow_id") -> getField('user_id');
					$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
					$user = M('FlowLog')->where($where)->field('user_id')->select();
					if(!empty($user)){
						foreach ($user as $k=>$v){
							$this -> _pushReturn($new, "您处理过的一个公文有变化",1,$v['user_id']);	
					//添加流程处理通知信息
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$v['user_id']
					);
					$uinform=M('UserInform')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform')->add($inform);
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
						$uinform=M('UserInform')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform')->add($inform);
						}
					}
					$this -> _pushReturn($new, "您有一个公文被否决",1,$user_id);				
					
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

		$model = D("FlowLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 1;

		$flow_id = $model -> flow_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();
		$model = D("FlowLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del',1);

		if ($list !== false) {//保存成功
			D("Flow") -> next_step($flow_id, $step);
			$user_id=M("Flow") -> where("id=$flow_id") -> getField('user_id'); 
			$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
			$user = M('FlowLog')->where($where)->field('user_id')->select();
			if(!empty($user)){
				foreach ($user as $k=>$v){
					$this -> _pushReturn($new, "您处理过的一个公文有变化",1,$v['user_id']);	
					//添加流程处理通知信息
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$v['user_id']
					);
				$uinform=M('UserInform')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
				if($uinform==false){
					M('UserInform')->add($inform);
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
				$uinform=M('UserInform')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
				if($uinform==false){
					M('UserInform')->add($inform);
					}
				}
			$this -> _pushReturn($new, "您有一个公文被处理",1,$user_id);
		
			$this -> assign('jumpUrl',U('flow/confirm'));
			$this -> success('操作成功!');
		}else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("FlowLog");
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
		$model = D("FlowLog");
		$model -> where("step=$step and flow_id=$flow_id and result is null") -> setField('is_del',1);

		if ($list !== false) {//保存成功
			D("Flow") -> where("id=$flow_id") -> setField('step',0);
			
			$user_id=M("Flow") -> where("id=$flow_id") -> getField('user_id');
			$where=array('flow_id'=>$flow_id,'user_id'=>array('neq',get_user_id()),'result'=>1);
					$user = M('FlowLog')->where($where)->field('user_id')->select();
					if(!empty($user)){
//						array_push($user, array('user_id'=>$user_id));
						foreach ($user as $k=>$v){
							$this -> _pushReturn($new, "您处理过的一个公文有变化",1,$v['user_id']);	
							//添加流程处理通知信息
					$inform=array(
						'user_id'	=> $user_id,
						'flow_id'	=>$flow_id,
						'time'		=>time(),
						'trans_id'	=>get_user_id(),
						'to_user_id'=>$v['user_id']
					);
					$uinform=M('UserInform')->where(array('to_user_id'=>$v['user_id'],'flow_id'=>$flow_id))->find();
						if($uinform==false){
							M('UserInform')->add($inform);
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
						$uinform=M('UserInform')->where(array('to_user_id'=>$user_id,'flow_id'=>$flow_id))->find();
					if($uinform==false){
							M('UserInform')->add($inform);
						}
					}
			 
			$this -> _pushReturn($new, "您有一个公文被否决",1,$user_id);
			
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

	protected function _assign_tag_list() {
		$model = D("SystemTag");
		$tag_list = $model -> get_tag_list('id,name','FlowType');
		$this -> assign("tag_list", $tag_list);
	}
	function flow_list(){
		$key_level=I('get.key');
		if($key_level=='part'){
			$this->assign('key_level',$key_level);
		}
		$this->display();
	}
	function json() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type:text/html; charset=utf-8");
		$start_date = strtotime($_REQUEST["start_date"]);
		$end_date = strtotime($_REQUEST["end_date"]);
		$key=$_REQUEST["key"];

		if($key=='part'){
			$user_id=get_user_id();
			$dept_id=M('User')->where(array('id'=>$user_id))->getField('dept_id');
			$user_ids=M('User')->where(array('dept_id'=>$dept_id))->field('id')->select();
			foreach ($user_ids as $k => $v) {
				$user_ids[$k]=$v['id'];
			}
			$where['user_id']=array('in',$user_ids);
		}
		//获取符合条件的flow
		$where['is_del']=0;
		$where['step']=40;
		$list=array();
		
		$flow = M("Flow") -> where($where)-> field('id,name,user_name,type')->select();
		foreach ($flow as $k=>$v){
			if($v['type']==39){
				$time1=M('FlowFieldData')->where(array('flow_id'=>$v['id'],'field_id'=>14))->getField('val');
				$time2=M('FlowFieldData')->where(array('flow_id'=>$v['id'],'field_id'=>13))->getField('val');
			}elseif($v['type']==43){
				$time1=M('FlowFieldData')->where(array('flow_id'=>$v['id'],'field_id'=>36))->getField('val');
				$time2=M('FlowFieldData')->where(array('flow_id'=>$v['id'],'field_id'=>37))->getField('val');
			}	
			$flow[$k]['start_time']=strtotime($time1);
			$flow[$k]['end_time']=strtotime($time2);
		}
		foreach ($flow as $k=>$v){
			if($v['start_time']<=$end_date && $v['end_time']>=$start_date){
				$list[]=$flow[$k];
			}
		}
		exit(json_encode($list));
	}
}
