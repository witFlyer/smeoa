<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class ProfileAction extends CommonAction {
	protected $config=array('app_type'=>'personal');
	
	function index(){	
		$user=D("UserView")->find(get_user_id());
		$this->assign("vo",$user);
		$this->display();
	}

	//重置密码
	public function reset_pwd(){
		$id = get_user_id();
		$password = $_POST['password'];
		if ('' == trim($password)) {
			$this -> error('密码不能为空！');
		}
		$User = M('User');
		$User -> password = md5($password);
		$User -> id = $id;
		$result = $User -> save();
		if (false !== $result) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success("密码修改成功");
		} else {
			$this -> error('重置密码失败！');
		}
	}

	public function password(){	
		$this -> display();
	}

	function save(){
		$model = D("User");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		session('user_pic', $model->pic);
		// 更新数据
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
	}
	//时间的判断
	function checkTime(){
		if($this->isAjax()){
		$data=$_POST;
			$use_time=$data['flow_field_29'];  //请假天数
			$start_time=$data['flow_field_14'];
			$end_time=$data['flow_field_13'];
			$user_id=$data['user_id'];

			// 判断时间冲突
			$count=0;
			if($data['opmode']=='add'){
			$flow = M('Flow')->where(array('user_id'=>get_user_id(),'step'=>array('neq',0)))->field('id')->select();
			foreach ($flow as $k=>$v){
				$time1=M('FlowFieldData')->where(array('flow_id'=>$v['id'],'field_id'=>14))->getField('val');
				$time2=M('FlowFieldData')->where(array('flow_id'=>$v['id'],'field_id'=>13))->getField('val');
				if(strtotime($start_time)<=strtotime($time2) && strtotime($end_time)>=strtotime($time1)){
					$count++;
				}
			}
			}
			if($count>0){
				echo "1";
			}else if(!array_key_exists('flow_field_12', $data)){
				echo "3";
			}else{
				if($data['flow_field_12']=="年休假"){
				if($data['opmode']=='add'){
					$per_nian=M('User')->where(array('id'=>get_user_id()))->field('nianjia_count,nianjia_total')->find();
				}else{
					$per_nian=M('User')->where(array('id'=>$user_id))->field('nianjia_count,nianjia_total')->find();
				}
			if(($per_nian['nianjia_total']-$per_nian['nianjia_count'])<$use_time){
				echo "2";
			}else{
				echo "0";
			}
		}else{
			echo "0";
		}
	}		
	}
}
}
?>