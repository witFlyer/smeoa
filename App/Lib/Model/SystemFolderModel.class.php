<?php
/*---------------------------------------------------------------------------
  小微OA系统 - 让工作更轻松快乐 

  Copyright (c) 2013 http://www.smeoa.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class SystemFolderModel extends CommonModel {

	function get_folder_list($folder='',$field='id,name,pid'){
		if(empty($folder)){
			$folder=MODULE_NAME."Folder";
		}
		$where['folder']=$folder;
		$where['is_del']=0;
        $list = $this ->where($where) -> order("sort") -> Field($field) -> select();
		return $list;
	}
	
	// add by CeeFee at 2014-12-28
	function get_folder_list_2($folder = '', $pid = 0, $field = 'id,name,pid')
	{
		if (empty($folder))
		{
			$folder = MODULE_NAME."Folder";
		}
		$where['pid']	 = $pid;
		$where['folder'] = $folder;
		$where['is_del'] = 0;
		$list = $this->where($where)->order("sort")->Field($field)->select();
		return $list;
	}
	
	function get_folder_name($folder_id){
		$where['id'] = array('eq', $folder_id);
		return $this -> where($where) -> getField("name");
	}

	function get_folder_menu(){
		$sql="		select concat('sfid',a.id) as id,a.name,a.folder,a.sort,CONCAT('sfid',a.pid) as pid,concat(replace(a.folder,'Folder','/folder/?fid='),a.id) as url";
		$sql.="		FROM {$this->trueTableName} AS a";
		$sql.="		WHERE  is_del=0 ";
		$sql.="		ORDER BY a.folder,a.sort asc";
		$rs = $this->db->query($sql);
		$list=array();
		foreach($rs as $val){
			if ($val["pid"]=='sfid0'){
				$where['sub_folder']=$val['folder'];
				$pid=M("Node")->where($where)->getField('id');
				$val["pid"]=$pid;
			}
			$list[]=$val;
		}
		return $list;
	}

	function get_authed_folder($user_id,$folder=null){
		if(empty($folder)){
			$folder=MODULE_NAME."Folder";
		}
		$folder_list=array();
		$list=$this->where("folder='$folder'")->getField('id,id');
		foreach ($list as $key => $val) {
			$auth=$this->get_folder_auth($key);
			if($auth['read']){
				$folder_list[]=$key;
			}
		}
		return $folder_list;
	}
	
	function get_folder_auth($folder_id){				 
		$auth_list=M("SystemFolder")->where("id=$folder_id")->Field('admin,write,read')->find();
		$result= array_map(array("SystemFolderModel","_check_auth"),$auth_list);
		if ($result['admin']==true){
			$result['write']=true;				
		}
		if ($result['write']==true){
			$result['read']=true;			
		}
		//dump($result);
		return $result;			
	}

	private function get_emp_list_by_dept_id($id){
        $dept = tree_to_list(list_to_tree(M("Dept")->where('is_del=0') -> select(), $id));
        $dept = rotate($dept);
		$dept=$dept['id'];
		
		if(!empty($dept)){
			$dept = implode(",", $dept) . ",$id";
			$where['dept_id'] = array('in', $dept);
		}else{
			$where['dept_id'] = array('eq',$id);
		}       
		$model = M("User");			
		$data = $model -> where($where) -> select();
        return $data;		
	}
	
	private function _check_auth($auth_list){
			$arrtmp = array_filter(explode(';', $auth_list));
			foreach ($arrtmp as $item) {
				if (stripos($item, "dept_")!==false){
					$arr_dept = explode('|', $item);
					$dept_id=substr($arr_dept[1],5);
					$emp_list =$this->get_emp_list_by_dept_id($dept_id);
					$emp_list=rotate($emp_list);	
					$emp_list=$emp_list['emp_no'];
					
					//dump($emp_list);
					if (in_array(get_emp_no(),$emp_list)){
						return true;
					}
				} else {
					if (stripos($item,get_emp_no())!==false){
						return true;
					}
				}
			}
			return false;		
	}

	// Added by CeeFee at 2014-12-19
	public function getTree($responseType = 'array', $folder = '')
	{
		$items	= array();

		if (empty($folder)) $folder = MODULE_NAME;
		$where['folder'] = $folder;
		
		$data	= M("SystemFolder")->where($where)->field('id,pid,name')->order('sort asc')->select();
	
		if ($data)
		{
			foreach ($data AS $k => $v)
			{
				$items[$v['id']] = $v;
			}
	
			foreach ($items AS $k => $v)
			{
				$items[$v['pid']]['cell'][$v['id']] = &$items[$v['id']];
			}
		}
	
		$result = isset($items[0]['cell']) ? $items[0]['cell'] : array();
	
		if (strtolower($responseType) == 'json')
		{
			$result = json_encode($result);
		}
	
		return $result;
	}
	
	public function getParentIdByListAndId($list, $id)
	{
		foreach ($list AS $k => $v)
		{
			if ($v['id'] == $id)
			{
				return $v['pid'];
			}
		}
	
		return FALSE;
	}
	
	public function getPath($id, $isIncludeSelf = TRUE, $folder = '')
	{
		if (empty($folder)) $folder = MODULE_NAME;
		$where['folder'] = $folder;

		$list		= M("SystemFolder")->where($where)->field('id,pid,name')->order('sort asc')->select();
		$parentId 	= $id;
		$path 		= array();
	
		if ($isIncludeSelf)
			$path[] = $parentId;
	
		while (FALSE !== $parentId)
		{
			$parentId 	= D("SystemFolder")->getParentIdByListAndId($list, $parentId);
			$path[] 	= $parentId;
		}
	
		foreach ($path AS $k => $v)
		{
			if (empty($v)) unset($path[$k]);
		}
	
		return array_reverse($path);
	}
	
	public function getPathArr($id, $isIncludeSelf = TRUE, $folder = '')
	{
		if (empty($folder)) $folder = MODULE_NAME;
		$path = D("SystemFolder")->getPath($id, $isIncludeSelf, $folder);
	
		return implode(',', $path);
	}
	
	public function getLinkage($id, $isIncludeSelf = TRUE, $isWrap = FALSE, $folder = '')
	{
		if (empty($folder)) $folder = MODULE_NAME;
		$defVal = D("SystemFolder")->getPathArr($id, $isIncludeSelf, $folder);
		$root 	= $defVal ? '0,'. $defVal : '0';
	
		if ( ! $isWrap OR ($isWrap != 'defVal' AND $isWrap != 'root'))
		{
			return array(
				'defVal'=> $defVal,
				'root'	=> $root,
			);
		}
		else
		{
			if ($isWrap == 'defVal')
			{
				return ',defVal: ['. $defVal .']';
			}
			else
			{
				return ',root: ['. $root .']';
			}
		}
	}
}
?>