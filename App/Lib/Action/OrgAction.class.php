<?php
class OrgAction extends CommonAction {

	//protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','sign'=>'read','mark' => 'admin', 'upload' => 'write'));
	protected $config = array('app_type' => 'personal');
	
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['org_name'])) {
			$map['org_name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		
		// 机构名称
		if (isset($_REQUEST['org_name']) AND ! empty($_REQUEST['org_name']) AND empty($map['org_name']))
		{
			$map['org_name'] = array('like', "%" . $_POST['org_name'] . "%");
		}
		// 机构类别
		if (isset($_REQUEST['category_id_2']) AND intval($_REQUEST['category_id_2']) > 0 AND empty($map['category_id_2']))
		{
			$map['category_id_2'] = intval($_REQUEST['category_id_2']);
		}
		elseif (isset($_REQUEST['category_id_1']) AND intval($_REQUEST['category_id_1']) > 0 AND empty($map['category_id_1']))
		{
			$map['category_id_1'] = intval($_REQUEST['category_id_1']);
		}
		elseif (isset($_REQUEST['category_id_0']) AND intval($_REQUEST['category_id_0']) > 0 AND empty($map['category_id_0']))
		{
			$map['category_id_0'] = intval($_REQUEST['category_id_0']);
		}
		// 机构能级
		if (isset($_REQUEST['level_id']) AND intval($_REQUEST['level_id']) > 0 AND empty($map['level_id']))
		{
			$map['level_id'] = intval($_REQUEST['level_id']);
		}
		// 企业所在地
		if (isset($_REQUEST['location_id']) AND intval($_REQUEST['location_id']) > 0 AND empty($map['location_id']))
		{
			$map['location_id'] = intval($_REQUEST['location_id']);
		}
		// 状态
		if (isset($_REQUEST['status']) AND $_REQUEST['status'] != '' AND empty($map['status']))
		{
			$map['status'] = intval($_REQUEST['status']);
		}
		// 享受政策
		if (isset($_REQUEST['is_policy']) AND $_REQUEST['is_policy'] != '' AND empty($map['is_policy']))
		{
			$map['is_policy'] = intval($_REQUEST['is_policy']);
		}
		// 成立日期
		if(isset($_REQUEST['establish_date1']) AND isset($_REQUEST['establish_date2'])){
			$map['establish_date'] = array('between',array($_REQUEST['establish_date1'],$_REQUEST['establish_date2']));
		}
		// 所属街道镇园区
		if(isset($_REQUEST['street'])){
			$map['street'] = array('like', "%" . $_REQUEST['street'] . "%");
		}
		//是否会员
		if(isset($_REQUEST['is_member'])){
			$map['is_member'] = $_REQUEST['is_member'];
		}
		
	}

	public function index()
	{
		$widget['date'] = true;
		$this->assign("widget", $widget);
		$actionName = $this->getActionName();
		$map = $this->_search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$category_list=D("SystemFolder")->get_folder_list('OrgCategory');
		$map['category_id']=array("in", $category_list);
		
		$category_list_0 = D("SystemFolder")->get_folder_list_2('OrgCategory', 0);
		$this->assign("category_list_0", $category_list_0);

		$category_tree = D("SystemFolder")->getTree('json', 'OrgCategory');
		$this->assign("category_tree", $category_tree);
		
		$level_list = D("SystemFolder")->get_folder_list('OrgLevel');
		$this->assign("level_list", $level_list);
		
		$location_list = D("SystemFolder")->get_folder_list('OrgLocation');
		$this->assign("location_list", $location_list);

		$categoryId	= $_REQUEST['category_id_2'];
		if ($categoryId <= 0) $categoryId = $_REQUEST['category_id_1'];
		if ($categoryId <= 0) $categoryId = $_REQUEST['category_id_0'];
		
		$category_linkage = D("SystemFolder")->getLinkage($categoryId, TRUE, FALSE, 'OrgCategory');
		$this->assign("category_linkage", $category_linkage);
		

		$model = D("Org");
		if (!empty($model)) {
			$this->_list($model, $map,"update_time");
		}
		$this->display();
		return;
	}

	function add()
	{
//		$widget['uploader'] = true;
//		$widget['editor'] 	= true;
		$widget['date'] 	= true;
		$this->assign("widget", $widget);
		
		$category_list_0 = D("SystemFolder")->get_folder_list_2('OrgCategory', 0);
		$this->assign("category_list_0", $category_list_0);

		$category_tree = D("SystemFolder")->getTree('json', 'OrgCategory');
		$this->assign("category_tree", $category_tree);
		
		$level_list = D("SystemFolder")->get_folder_list('OrgLevel');
		$this->assign("level_list", $level_list);
		
		$location_list = D("SystemFolder")->get_folder_list('OrgLocation');
		$this->assign("location_list", $location_list);

		$category_linkage = D("SystemFolder")->getLinkage(0, TRUE, FALSE, 'OrgCategory');
		$this->assign("category_linkage", $category_linkage);

		$this->display();
	}

	public function edit()
	{
//		$widget['uploader'] = true;
//		$widget['editor'] 	= true;
		$widget['date'] 	= true;
		$this->assign("widget", $widget);
		
		$category_list_0 = D("SystemFolder")->get_folder_list_2('OrgCategory', 0);
		$this->assign("category_list_0", $category_list_0);

		$category_tree = D("SystemFolder")->getTree('json', 'OrgCategory');
		$this->assign("category_tree", $category_tree);
		
		$level_list = D("SystemFolder")->get_folder_list('OrgLevel');
		$this->assign("level_list", $level_list);
		
		$location_list = D("SystemFolder")->get_folder_list('OrgLocation');
		$this->assign("location_list", $location_list);
		
		$name	= $this->getActionName();
		$model 	= M($name);
		$id 	= $_REQUEST['id'];
		$vo 	= $model->find($id);
		$categoryId	= $vo['category_id_2'];
		if ($categoryId <= 0) $categoryId = $vo['category_id_1'];
		if ($categoryId <= 0) $categoryId = $vo['category_id_0'];
		
		$category_linkage = D("SystemFolder")->getLinkage($categoryId, TRUE, FALSE, 'OrgCategory');
		$this->assign("category_linkage", $category_linkage);
		
		if (isset($_POST['category_id_0']) AND $_POST['category_id_0'] <= 0)
		{
			$_POST['category_id_1'] = 0;
			$_POST['category_id_2'] = 0;
		}
		elseif (isset($_POST['category_id_1']) AND $_POST['category_id_1'] <= 0)
		{
			$_POST['category_id_2'] = 0;
		}
		
		$orgId = $vo['org_id'];
		
		$shareholder_list = D("OrgShareholder")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		$this->assign("shareholder_list", $shareholder_list);
		
		$contact_list = D("OrgContact")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		$this->assign("contact_list", $contact_list);
		
		$executive_list = D("OrgExecutive")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		$this->assign("executive_list", $executive_list);
		
		$tax_list = D("OrgTax")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		$this->assign("tax_list", $tax_list);
		
		$work_list = D("OrgWork")->findAll(array("where" => "org_id='$orgId'"));
		
		if ($work_list)
		{
			foreach ($work_list AS $k => &$v)
			{
				if ($k == 'begin_time')
				{
					$v['begin_time'] = date('Y-m-d', strtotime($v['begin_time']));
				}
			}
		}
		
		$this->assign("work_list", $work_list);
		
		$subsidie_list = D("OrgSubsidie")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		
		if ($subsidie_list)
		{
			foreach ($subsidie_list AS $k => &$v)
			{
				$v['typeName'] = D('OrgSubsidie')->getTypeName($v['type']);
			}
		}
		
		$this->assign("subsidie_list", $subsidie_list);
		
		$comprehensive_list = D("OrgComprehensive")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		
		if ($comprehensive_list)
		{
			foreach ($comprehensive_list AS $k => &$v)
			{
				$v['typeName'] = D('OrgComprehensive')->getTypeName($v['type']);
			}
		}
		
		$this->assign("comprehensive_list", $comprehensive_list);
		
		$payfee_list = D("OrgPayfee")->order("update_time DESC")->findAll(array("where" => "org_id='$orgId'"));
		$this->assign("payfee_list", $payfee_list);

		$schedules_list=M('Schedules')->order("start_date DESC")->where("org_id=$id")->select();
		$this->assign('schedules_list',$schedules_list);
		$is_org = M("User")->where(array('id'=>get_user_id()))->getField("is_org");
		$this->assign('is_org',$is_org);

		//是否某特殊机构处理人,不是则无法编辑
		$is_special = M("User")->where(array('id'=>get_user_id()))->getField("is_special");
		$specialOrg_id = M('Org')->where(array('org_id'=>$id))->getField('category_id_0');
		if($specialOrg_id==99 && !$is_special){
			$this->assign('is_special',1);
		}
		$this->_edit();
	}
	
	function del(){
		$id = $_POST['org_id'];
		$count = $this->_del($id,null,true);
	
		if ($count)
		{
			$model = D("Org");
			$result = $model->del_data_by_row($id);
		}
	
		if ($count !== FALSE)
		{
			$idArr =  implode(',', $id);
			
			D('OrgComprehensive')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgContact')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgExecutive')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgPayfee')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgShareholder')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgSubsidie')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgTax')->delete(array('where' => "org_id IN ($idArr)"));
			D('OrgWork')->delete(array('where' => "org_id IN ($idArr)"));
			
			//保存成功
			$this->assign('jumpUrl', get_return_url());
			$this->success("成功删除{$count}条!");
		}
		else
		{
			//失败提示
			$this->error('删除失败!');
		}
	}

	function export(){
		$user_id = get_user_id();
		$is_org = M("User")->where("id=$user_id")->getField("is_org");
		$is_special = M("User")->where("id=$user_id")->getField("is_special");

		$model = M("Org");
		$type = $_REQUEST['type'];
	if($type=="part"){
			$ids = $_REQUEST['ids'];
			$arr_ids = array();
			$arr_ids=explode(',', $ids);
			$last_id = array_pop($arr_ids);
			$map['org_id'] = array("in",$ids);
			$map['is_del'] = 0;
		}
	if($type=="all"){
		$ids = $_REQUEST['ids'];
		$map_list = explode(',', $ids);
		// dump($map_list);die;
		// 机构名称
		if (isset($map_list[6])) {
			$map['org_name'] = array('like', "%" . $map_list[6] . "%");
		}
		
		// 机构类别
		if (isset($map_list[2]) AND intval($map_list[2])>0)
		{
			$map['category_id_2'] = intval($map_list[2]);
		}
		elseif (isset($map_list[1]) AND intval($map_list[1])>0)
		{
			$map['category_id_1'] = intval($map_list[1]);
		}
		elseif (isset($map_list[0]) AND intval($map_list[0])>0)
		{
			$map['category_id_0'] = intval($map_list[0]);
		}
		// 机构能级
		if (isset($map_list[3]) AND intval($map_list[3])>0)
		{
			$map['level_id'] = intval($map_list[3]);
		}
		// 企业所在地
		if (isset($map_list[4]) AND intval($map_list[4])>0)
		{
			$map['location_id'] = intval($map_list[4]);
		}
		// 状态
		if (isset($map_list[5]) AND intval($map_list[5])>0)
		{
			$map['status'] = intval($map_list[5]);
		}
		// 享受政策
		if (isset($map_list[7]) AND intval($map_list[7])>0)
		{
			$map['is_policy'] = intval($map_list[7]);
		}
		// 成立日期
		if(isset($map_list[8]) AND isset($map_list[9])){
			$map['establish_date'] = array('between',array($map_list[8],$map_list[9]));
		}
		// 所属街道镇园区
		if(isset($map_list[10])){
			$map['street'] = array('like', "%" . $map_list[10] . "%");
		}
		//是否会员
		if(isset($map_list[11])){
			$map['is_member'] = $map_list[11];
		}
	}
		$list = $model -> where($map) -> select();

		Vendor('Excel.PHPExcel');
		//$inputFileName = "Public/templete/contact.xlsx";
		//$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		$objPHPExcel=new PHPExcel();
		$objPHPExcel -> getProperties() -> setCreator("小微OA") -> setLastModifiedBy("小微OA") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// 表格样式
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);  
		
		$i = 1;$a=1;$b=1;$c=1;$d=1;$e=1;$f=1;$g=1;$h=1;
			//机构编码，机构名称，机构类别根ID，机构类别子ID，机构类别孙id，机构能级ID，企业所在地，创建时间，更新时间，状态，是否享受政策，成立时间，工商注册号，税务登记号，注册资金单位，注册资金，法定代表人，注册地址，办公地址，邮政编码，所属街道镇园区，税管地，税管所，公司电话，传真，公司简介
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i","机构编码") -> setCellValue("B$i","机构名称") -> setCellValue("C$i","机构大类别") -> setCellValue("D$i","机构中类别") -> setCellValue("E$i","机构小类别") -> setCellValue("F$i","机构能级") -> setCellValue("G$i","企业所在地") -> setCellValue("H$i","创建时间") -> setCellValue("I$i","更新时间") -> setCellValue("J$i","状态") -> setCellValue("K$i","是否享受政策") -> setCellValue("L$i","成立时间") -> setCellValue("M$i","工商注册号") -> setCellValue("N$i","税务登记号") -> setCellValue("O$i","注册资金单位") -> setCellValue("P$i","注册资金") -> setCellValue("Q$i","法定代表人") -> setCellValue("R$i","注册地址") -> setCellValue("S$i","办公地址") -> setCellValue("T$i","邮政编码") -> setCellValue("U$i","所属街道镇园区") -> setCellValue("V$i","税管地") -> setCellValue("W$i","税管所") -> setCellValue("X$i","公司电话") -> setCellValue("Y$i","传真") -> setCellValue("Z$i","公司简介");
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:Z1')->getFont()->setBold(true);

			//创建第二个工作表
       		 $WorkSheet2 = new PHPExcel_Worksheet($objPHPExcel, "股东信息"); //创建一个工作表
       		 $objPHPExcel->addSheet($WorkSheet2); //插入工作表
      		 $objPHPExcel->setActiveSheetIndex(1)-> setCellValue("A$i","机构名称") -> setCellValue("B$i","股东名称") -> setCellValue("C$i","出资额(万元)")-> setCellValue("D$i","股权比例(%)");
      		 $objPHPExcel->getActiveSheet(1)->getStyle('A1:D1')->getFont()->setBold(true);

       		 $WorkSheet3 = new PHPExcel_Worksheet($objPHPExcel, "联系人"); //创建一个工作表
       		 $objPHPExcel->addSheet($WorkSheet3); //插入工作表
      		 $objPHPExcel->setActiveSheetIndex(2)-> setCellValue("A$i","机构名称") -> setCellValue("B$i","姓名") -> setCellValue("C$i","职务")-> setCellValue("D$i","固定电话")-> setCellValue("E$i","移动电话")-> setCellValue("F$i","电子邮箱");
      		 $objPHPExcel->getActiveSheet(2)->getStyle('A1:F1')->getFont()->setBold(true);
    
       		 $WorkSheet4 = new PHPExcel_Worksheet($objPHPExcel, "高管信息"); //创建一个工作表
       		 $objPHPExcel->addSheet($WorkSheet4); //插入工作表
      		 $objPHPExcel->setActiveSheetIndex(3)-> setCellValue("A$i","机构名称") -> setCellValue("B$i","姓名") -> setCellValue("C$i","职务")-> setCellValue("D$i","身份证") ->setCellValue("E$i","固定电话")-> setCellValue("F$i","移动电话")-> setCellValue("G$i","电子邮箱")-> setCellValue("H$i","备注");
      		 $objPHPExcel->getActiveSheet(3)->getStyle('A1:H1')->getFont()->setBold(true);

      		 $WorkSheet5 = new PHPExcel_Worksheet($objPHPExcel, "工作动态"); //创建一个工作表
       		 $objPHPExcel->addSheet($WorkSheet5); //插入工作表
      		 $objPHPExcel->setActiveSheetIndex(4)-> setCellValue("A$i","机构名称") -> setCellValue("B$i","标题") -> setCellValue("C$i","类别")-> setCellValue("D$i","地点")-> setCellValue("E$i","参与人员")-> setCellValue("F$i","开始时间")-> setCellValue("G$i","开始时间");
      		 $objPHPExcel->getActiveSheet(4)->getStyle('A1:G1')->getFont()->setBold(true);

      		 $WorkSheet6 = new PHPExcel_Worksheet($objPHPExcel, "综合服务"); //创建一个工作表
       		 $objPHPExcel->addSheet($WorkSheet6); //插入工作表
      		 $objPHPExcel->setActiveSheetIndex(5)-> setCellValue("A$i","机构名称")-> setCellValue("B$i","姓名") -> setCellValue("C$i","服务类型") -> setCellValue("D$i","职务")-> setCellValue("E$i","办理时间")-> setCellValue("F$i","事项")-> setCellValue("G$i","备注");
      		 $objPHPExcel->getActiveSheet(5)->getStyle('A1:G1')->getFont()->setBold(true);

      		 $WorkSheet7 = new PHPExcel_Worksheet($objPHPExcel, "金融促进会缴费情况");
       		 $objPHPExcel->addSheet($WorkSheet7); //插入工作表
      		 $objPHPExcel->setActiveSheetIndex(6)-> setCellValue("A$i","机构名称")-> setCellValue("B$i","所属年度") -> setCellValue("C$i","缴纳金额(万元)");
      		 $objPHPExcel->getActiveSheet(6)->getStyle('A1:C1')->getFont()->setBold(true);

      if($is_org){
 				$WorkSheet8 = new PHPExcel_Worksheet($objPHPExcel, "纳税和人员情况"); //创建一个工作表
       		 	$objPHPExcel->addSheet($WorkSheet8); //插入工作表
      		 	$objPHPExcel->setActiveSheetIndex(7)-> setCellValue("A$i","机构名称") -> setCellValue("B$i","年度") -> setCellValue("C$i","营业税（万元）")-> setCellValue("D$i","增值税（万元）")-> setCellValue("E$i","所得税（万元）")-> setCellValue("F$i","个人所得税（万元）")-> setCellValue("G$i","合计（万元）")-> setCellValue("H$i","新区留存")-> setCellValue("I$i","人数");
      			 $objPHPExcel->getActiveSheet(7)->getStyle('A1:I1')->getFont()->setBold(true);

      			 $WorkSheet9 = new PHPExcel_Worksheet($objPHPExcel, "各类补贴"); //创建一个工作表
       		 	 $objPHPExcel->addSheet($WorkSheet9); //插入工作表
      		 	 $objPHPExcel->setActiveSheetIndex(8)-> setCellValue("A$i","机构名称")-> setCellValue("B$i","补贴类型") -> setCellValue("C$i","所属年度") -> setCellValue("D$i","人次")-> setCellValue("E$i","姓名")-> setCellValue("F$i","金额(万元)")-> setCellValue("G$i","落实时间")-> setCellValue("H$i","备注");
      			 $objPHPExcel->getActiveSheet(8)->getStyle('A1:H1')->getFont()->setBold(true);
 			}

		foreach ($list as $val){
			$i++;
			$org_id = $val['org_id'];
			$org_no=$val['org_no'];
			$org_name=$val["org_name"]; 
			$category_id_0=get_org_folder($val["category_id_0"]); 
			$category_id_1=get_org_folder($val["category_id_1"]); 
			$category_id_2=get_org_folder($val["category_id_2"]);
			$level_id=get_org_folder($val["level_id"]); 
			$location_id=get_org_folder($val["location_id"]); 
			$create_time=toDate($val["create_time"],'Y-m-d H:i:s');//创建时间
			$update_time=toDate($val["update_time"],'Y-m-d H:i:s');
			$status=get_org_status($val["status"]); 
			$is_policy=get_isPolicy($val["is_policy"]); 
			$establish_date=$val["establish_date"]; 
			$business_reg_no=$val["business_reg_no"]; 
			$tax_reg_no=$val["tax_reg_no"]; 
			$reg_capital_unit=$val["reg_capital_unit"];
			$reg_capital_value=$val["reg_capital_value"]; 
			$legal_person=$val["legal_person"]; 
			$reg_address=$val["reg_address"]; 
			$office_address=$val["office_address"];  
			$postcode=$val["postcode"];
			$street=$val["street"]; 
			$tax_tube_land=$val["tax_tube_land"]; 
			$tax_tube_station=$val["tax_tube_station"]; 
			$office_tel=$val["office_tel"]; 
			$office_fax=$val["office_fax"]; 
			$company_intro=$val["company_intro"]; 

		if($val["category_id_0"]==99 && !$is_special){
				//特殊机构，不导出(如果到处人没有权限)

		}else{
			//机构编码，机构名称，机构类别根ID，机构类别子ID，机构类别孙id，机构能级ID，企业所在地，创建时间，更新时间，状态，是否享受政策，成立时间，工商注册号，税务登记号，注册资金单位，注册资金，法定代表人，注册地址，办公地址，邮政编码，所属街道镇园区，税管地，税管所，公司电话，传真，公司简介
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i",$org_no) -> setCellValue("B$i", $org_name) -> setCellValue("C$i",$category_id_0) -> setCellValue("D$i",$category_id_1) -> setCellValue("E$i",$category_id_2) -> setCellValue("F$i",$level_id) -> setCellValue("G$i",$location_id) -> setCellValue("H$i",$create_time) -> setCellValue("I$i",$update_time)-> setCellValue("J$i",$status)-> setCellValue("K$i",$is_policy)-> setCellValue("L$i",$establish_date)-> setCellValue("M$i",$business_reg_no)-> setCellValue("N$i",$tax_reg_no)-> setCellValue("O$i",$reg_capital_unit)-> setCellValue("P$i",$reg_capital_value)-> setCellValue("Q$i",$legal_person)-> setCellValue("R$i",$reg_address)-> setCellValue("S$i",$office_address)-> setCellValue("T$i",$postcode)-> setCellValue("U$i",$street)-> setCellValue("V$i",$tax_tube_land)-> setCellValue("W$i",$tax_tube_station)-> setCellValue("X$i",$office_tel)-> setCellValue("Y$i",$office_fax)-> setCellValue("Z$i",$company_intro);

			$where['org_id'] = array('eq',$org_id);
			//股东情况
			$shareholder_list = M("OrgShareholder")->where($where)->select();
			if($shareholder_list){
				foreach ($shareholder_list as $k => $v) {
				$a++;
				$objPHPExcel -> setActiveSheetIndex(1) -> setCellValue("A$a",$org_name) -> setCellValue("B$a", $v['name']) -> setCellValue("C$a",$v['contribution']) -> setCellValue("D$a",$v['proportion']);
				}
			}
			
			//联系人
			$contact_list = M("OrgContact")->where($where)->select();
			if($contact_list){

			foreach ($contact_list as $k => $v) {
				$h++;
				$objPHPExcel -> setActiveSheetIndex(2) -> setCellValue("A$h",$org_name) -> setCellValue("B$h", $v['realname']) -> setCellValue("C$h",$v['post']) -> setCellValue("D$h",$v['tel']) -> setCellValue("E$h",$v['mobile']) -> setCellValue("F$h",$v['email']);
			}}
			//高管信息
			$executive_list = M("OrgExecutive")->where($where)->select();
			if($executive_list){

			foreach ($executive_list as $k => $v) {
				$b++;
				$objPHPExcel -> setActiveSheetIndex(3) -> setCellValue("A$b",$org_name) -> setCellValue("B$b", $v['realname']) -> setCellValue("C$b",$v['post'])->setCellValue("D$b",$v['idcard']) -> setCellValue("E$b",$v['tel']) -> setCellValue("F$b",$v['mobile']) -> setCellValue("G$b",$v['email'])-> setCellValue("H$b",$v['remark']);
			}}

			//工作动态
			$schedules_list = M("Schedules")->where($where)->select();
			if($schedules_list){
				foreach ($schedules_list as $k => $v) {
					$d++;
					$objPHPExcel -> setActiveSheetIndex(4) -> setCellValue("A$d",$org_name) -> setCellValue("B$d", $v['name']) -> setCellValue("C$d",get_schedule_type($v['type'])) -> setCellValue("D$d",$v['location'])-> setCellValue("E$d",$v['actor'])-> setCellValue("F$d",$v['start_date'])-> setCellValue("G$d",$v['end_date']);
				}
			}
			//综合服务
			$comprehensive_list = M("OrgComprehensive")->where($where)->select();
			if($comprehensive_list){
				foreach ($comprehensive_list as $k => $v) {
					$f++;
					$objPHPExcel -> setActiveSheetIndex(5) -> setCellValue("A$f",$org_name) -> setCellValue("B$f", $v['realname']) -> setCellValue("C$f",comprehensive2id($v['type'])) -> setCellValue("D$f",$v['post'])-> setCellValue("E$f",$v['implement'])-> setCellValue("F$f",$v['item'])-> setCellValue("G$f",$v['remark']);
				}
			}
			//综合促进会缴费情况
			$payfee_list = M("OrgPayfee")->where($where)->select();
			if($payfee_list){
				foreach ($payfee_list as $k => $v) {
					$g++;
					$objPHPExcel -> setActiveSheetIndex(6) -> setCellValue("A$g",$org_name) -> setCellValue("B$g", $v['year'])-> setCellValue("C$g",$v['amount']);
				}
			}

		if($is_org){
			//纳税和人员情况
			$tax_list = M("OrgTax")->where($where)->select();
			if($tax_list){
			foreach ($tax_list as $k => $v) {
				$c++;
				$objPHPExcel -> setActiveSheetIndex(7) -> setCellValue("A$c",$org_name) -> setCellValue("B$c", $v['year']) -> setCellValue("C$c",$v['sales']) -> setCellValue("D$c",$v['value_added'])-> setCellValue("E$c",$v['income'])-> setCellValue("F$c",$v['individual_income'])-> setCellValue("G$c",$v['total'])-> setCellValue("H$c",$v['retained'])-> setCellValue("I$c",$v['num']);
			}}

			//各类补贴
				$subsidie_list = M("OrgSubsidie")->where($where)->select();
				if($subsidie_list){
					foreach ($subsidie_list as $k => $v) {
						$e++;
						$objPHPExcel -> setActiveSheetIndex(8) -> setCellValue("A$e",$org_name) -> setCellValue("B$e", subsidiebyid($v['type'])) -> setCellValue("C$e",$v['year']) -> setCellValue("D$e",$v['num'])-> setCellValue("E$e",$v['realname'])-> setCellValue("F$e",$v['amount'])-> setCellValue("G$e",$v['implement'])->setCellValue("H$e",$v['remark']);
					}
				}
			}
		}
	}
		// Rename worksheet
		

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
		$objPHPExcel -> getActiveSheet() -> setTitle("机构基本信息");
		$file_name="机构信息".date("Y-m-d",time()).".xls";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =".$file_name);
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter -> save('php://output');
		exit ;
	}

	// 判断机构编码或机构名称是否存在
	function same_org(){
		if($this->isAjax()){
			$org_id = trim($_POST['org_id']);
			$org_no = trim($_POST['org_no']);
			$org_name = trim($_POST['org_name']);
			$where1 = array('org_no'=>array('eq',$org_no));
			$where2 = array('org_name'=>array('eq',$org_name));
			$result_no = M("Org")->where($where1)->find();
			$result_name = M("Org")->where($where2)->find();
			if($result_no){
				echo "1";
			}elseif($result_name){
				echo "2";
			}else{
				echo "0";
			}
		}
	}
	function same_org_edit(){
		if($this->isAjax()){
			$org_id = trim($_POST['org_id']);
			$org_no = trim($_POST['org_no']);
			$org_name = trim($_POST['org_name']);
			$where1 = array('org_no'=>array('eq',$org_no),'org_id'=>array('neq',$org_id));
			$where2 = array('org_name'=>array('eq',$org_name),'org_id'=>array('neq',$org_id));
			$result_no = M("Org")->where($where1)->find();
			$result_name = M("Org")->where($where2)->find();
			if($result_no){
				echo "1";
			}elseif($result_name){
				echo "2";
			}else{
				echo "0";
			}
		}
	}
}