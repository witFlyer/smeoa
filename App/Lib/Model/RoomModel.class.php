<?php
/*---------------------------------------------------------------------------
 小微OA系统 - 让工作更轻松快乐

 Copyright (c) 2013 http://www.smeoa.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class RoomModel extends CommonModel {
	// 自动填充设置
	function _before_insert(&$data, $options) {
		$type = $data["type"];

		$doc_no_format = M("FlowType") -> where("id=$type") -> getField("doc_no_format");

		$sql = "SELECT count(*) count FROM `" . $this -> tablePrefix . "room` WHERE type=$type ";
		$sql .= " and year(FROM_UNIXTIME(create_time))>=year(now())";

		$rs = $this -> db -> query($sql);
		$count = $rs[0]['count'] + 1;

		if (strpos($doc_no_format, "{#}") !== false) {
			$doc_no_format = str_replace("{#}", str_pad($count, 1, "0", STR_PAD_LEFT), $doc_no_format);
		}
		if (strpos($doc_no_format, "{##}") !== false) {
			$doc_no_format = str_replace("{##}", str_pad($count, 2, "0", STR_PAD_LEFT), $doc_no_format);
		}
		if (strpos($doc_no_format, "{###}") !== false) {
			$doc_no_format = str_replace("{###}", str_pad($count, 3, "0", STR_PAD_LEFT), $doc_no_format);
		}
		if (strpos($doc_no_format, "{####}") !== false) {
			$doc_no_format = str_replace("{####}", str_pad($count, 4, "0", STR_PAD_LEFT), $doc_no_format);
		}
		if (strpos($doc_no_format, "{#####}") !== false) {
			$doc_no_format = str_replace("{#####}", str_pad($count, 5, "0", STR_PAD_LEFT), $doc_no_format);
		}
		if (strpos($doc_no_format, "{######}") !== false) {
			$doc_no_format = str_replace("{######}", str_pad($count, 6, "0", STR_PAD_LEFT), $doc_no_format);
		}
		$data['doc_no'] = $doc_no_format;
	}

	function _after_insert($data, $options) {

		if ($data['step'] == 20) {

			$model = M("Room");
			$id = $data['id'];
			$where['id'] = array('eq', $id);
			$str_confirm = $this -> _conv_auditor($data['confirm']);
			$str_consult = $this -> _conv_auditor($data['consult']);
			$str_refer = $this -> _conv_auditor($data['refer']);

			$model -> where($where) -> setField('confirm', $str_confirm);
			$model -> where($where) -> setField('consult', $str_consult);
			$model -> where($where) -> setField('refer', $str_refer);

			$this -> next_step($data['id'], 20);
		}
	}

	function _after_update($data, $options) {
		if ($data['step'] == 20) {

			$model = M("Room");
			$id = $data['id'];
			$where['id'] = array('eq', $id);

			$str_confirm = $this -> _conv_auditor($data['confirm']);
			$str_consult = $this -> _conv_auditor($data['consult']);
			$str_refer = $this -> _conv_auditor($data['refer']);

			$model -> where($where) -> setField('confirm', $str_confirm);
			$model -> where($where) -> setField('consult', $str_consult);
			$model -> where($where) -> setField('refer', $str_refer);

			$this -> next_step($data['id'], 20);
		}
		if($data['is_ensure']){
			$flow_id = $data['id'];
			$where=array('flow_id'=>$flow_id,'result'=>1);
			$user = M('RoomLog')->where($where)->field('user_id')->select();
			foreach ($user as $k => $v) {
				$this -> _pushReturn($new, "您有一个预约处理完成", 1, $v['user_id']);
			}
		}
	}

	function _get_dept($dept_id, $dept_grade) {
		$model = M("Dept");
		$dept = $model -> find($dept_id);
		if ($dept['dept_grade_id'] == $dept_grade) {
			return $dept_id;
		} else {
			if ($dept['pid'] != 0) {
				return $this -> _get_dept($dept['pid'], $dept_grade);
			}
		}
		return false;
	}

	function _conv_auditor($val) {
		$arr_auditor = array_filter(explode("|", $val));
		$str_auditor;
	
		if (strpos($val, "_") == false) {
			$str_auditor = $val;
 		}
		return $str_auditor;
	}

	public function next_step($flow_id, $step, $emp_no) {

		if (!empty($emp_no)) {
			$data['flow_id'] = $flow_id;
			$data['emp_no'] = $emp_no;
			$model = D("RoomLog");
			$where['flow_id'] = $flow_id;
			$where['emp_no'] = $emp_no;
			$data['step'] = D("RoomLog") -> where($where) -> getField('step');
			if (empty($data['step'])) {
				$data['step'] = 20;
			}

			$user_id = M("User") -> where("emp_no=$emp_no") -> getField("id");
			$this -> _pushReturn($new, "您有一个预约被退回", 1, $user_id);

			$model -> create($data);
			$model -> add();
			return;
		}

		$model = D("Room");
		if (substr($step, 0, 1) == 2) {
			if ($this -> is_last_confirm($flow_id)) {
				$model -> where("id=$flow_id") -> setField('step', 40);
				$step = 40;
			} else {
				$step++;
			}
		}

		if (substr($step, 0, 1) == 3) {
			if ($this -> is_last_consult($flow_id)) {
				$step = 40;
			} else {
				$step++;
			}
		}

		if ($step == 40) {
			$model -> where("id=$flow_id") -> setField('step', 40);

			$user_id = $model -> where("id=$flow_id") -> getField('user_id');
			$this -> _pushReturn($new, "您有一个预约通过审核", 1, $user_id);

			$this -> send_to_refer($flow_id);
			
		} else {
			
			$data['flow_id'] = $flow_id;
			$data['step'] = $step;
			
			if (!empty($emp_no)) {
				$data['emp_no'] = $emp_no;
			} else {
				$data['emp_no'] = $this -> duty_emp_no($flow_id, $step);
			}

			if (strpos($data['emp_no'], ",") !== false) {
				$emp_list = explode(",", $data['emp_no']);
				foreach ($emp_list as $emp) {
					$data['emp_no'] = $emp;
					$model = D("RoomLog");
					$model -> create($data);
					$model -> add();
				}
			} else {
				$model = D("RoomLog");
				$model -> create($data);
				$model -> add();
			}
		}
	}

	function is_last_confirm($flow_id) {
		
		$confirm = M("Room") -> where("id=$flow_id") -> getField("confirm");
		$last_confirm = array_filter(explode("|", $confirm));
		$last_confirm_emp_no = end($last_confirm);

		if (strpos($last_confirm_emp_no, get_emp_no()) !== false) {
			return true;
		}
		return false;
	}

	function is_last_consult($flow_id) {
		$consult = M("Room") -> where("id=$flow_id") -> getField("consult");
		if (empty($consult)) {
			return true;
		}

		$last_consult = array_filter(explode("|", $consult));
		$last_consult_emp_no = end($last_consult);

		if (strpos($last_consult_emp_no, get_emp_no()) !== false) {
			return true;
		}
		return false;
	}

	function duty_emp_no($flow_id, $step) {
		if (substr($step, 0, 1) == 2) {
			$confirm = M("Room") -> where("id=$flow_id") -> getField("confirm");
			$arr_confirm = array_filter(explode("|", $confirm));

			return $arr_confirm[fmod($step, 10) - 1];

		}
		if (substr($step, 0, 1) == 3) {
			$consult = M("Room") -> where("id=$flow_id") -> getField("consult");
			$arr_consult = array_filter(explode("|", $consult));
			return $arr_consult[fmod($step, 10) - 1];
		}
	}

	function send_to_refer($flow_id) {
		$model = M("Room");

		$list = $model -> where("id=$flow_id") -> getField('refer');
		$list = str_replace("|", ",", $list);
		$emp_list = array_filter(explode(",", $list));

		$data['flow_id'] = $flow_id;
		$data['result'] = 1;
		
		foreach ($emp_list as $val) {
			$data['emp_no'] = $val;
			$data['step'] = 100;
			$data['create_time'] = time();			
			D("RoomLog") -> add($data);
			
			$user_id = M("User")->where("emp_no=$val")-> getField("id");
			// $this -> _pushReturn($new,"收到一份抄送给我的预约",1,$user_id);
		}
	}

}
?>