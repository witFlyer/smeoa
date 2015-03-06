<?php
class RoomListAction extends SystemFolderAction {
	//过滤查询字段
	function _search_filter(&$map) {
		$map['name'] = array('like', "%" . $_POST['name'] . "%");
		$map['is_del'] = array('eq', '0');
	}

	function index() {
		$this -> _tag_manage("会议室管理",false);
	}
}
