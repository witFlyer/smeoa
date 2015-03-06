<?php

class ImportAction extends CommonAction{
  protected $config = array('app_type' => 'personal');
  function base(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/base.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col<=$highestColumn;$col++){
        $address =$col.$row;
        if($col=='D'){
          $cell = excelTime($objWorksheet->getCell($address)->getValue());
        }else{
          $cell = $objWorksheet->getCell($address)->getValue();
        }
        $excelData[$row][] = $cell;
    }
    $data=array(
      'org_no'            => $excelData[$row][1],
      'org_name'          => $excelData[$row][0],
      'category_id_0'     => orgCate2id($excelData[$row][2]),
      'create_time'       => time(),
      'update_time'       => time(),
      'status'            => status2id($excelData[$row][18]),
      'establish_date'    => excelTime(($excelData[$row][3])),
      'business_reg_no'   => $excelData[$row][4],
      'tax_reg_no'        => $excelData[$row][5],
      'reg_capital_unit'  => unit2id($excelData[$row][6]),
      'reg_capital_value' => $excelData[$row][7],
      'legal_person'      => $excelData[$row][8],
      'reg_address'       => $excelData[$row][9],
      'office_address'    => $excelData[$row][10],
      'postcode'          => $excelData[$row][11],
      'street'            => $excelData[$row][12],
      'tax_tube_land'     => $excelData[$row][13],
      'tax_tube_station'  => $excelData[$row][14],
      'office_tel'        => $excelData[$row][15],
      'office_fax'        => $excelData[$row][16],
      'company_intro'     => $excelData[$row][17],
      'is_member'         => is2id($excelData[$row][21])
      );
    $where1 = array('org_no'=>array("eq",$excelData[$row][1]));
    $where2 = array('org_name'=>array("eq",$excelData[$row][0]));
    $result1 = M("Org")->where($where1)->find();
    $result2 = M("Org")->where($where2)->find();
    if($result1){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][1]."<br/>",FILE_APPEND);
    }
    if($result2){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."<br/>",FILE_APPEND);
    }
    M("Org")->add($data);
  }
}
//入会情况
function payfee(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/member.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col<=$highestColumn;$col++){
        $address =$col.$row;
        if($col=='W'){
         $excelData[$row][] = gmdate("Y-m-d H:i", PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell($address)->getValue()));
        }else{
          $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
        }      
    }

    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");

    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-入会情况(不存在)\r\n".date("Y-m-d",time()),FILE_APPEND);
    }
    $data=array(
      'org_id'            => $org_id,
      'year'              => $excelData[$row][23],
      'amount'            => $excelData[$row][24]
      );
    $membership_date = array('membership_date'=>$excelData[$row][22]);
    M("Org")->where("org_id=$org_id")->save($membership_date);
    M("OrgPayfee")->add($data);
  }
}
//股东情况
function shareHolder(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/shareholder.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();
  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col!="AP";$col++){
        $address =$col.$row;
        if($col=='Z'){
           $excelData[$row][] = $objWorksheet->getCell($address)->getValue()*100;
        }else{
          $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
        }  
    }
    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");

    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-股东情况(不存在)\r\n".date("Y-m-d",time()),FILE_APPEND);
    }
    $data=array(
      'org_id'            => $org_id,
      'name'              => $excelData[$row][23],
      'contribution'      => $excelData[$row][24],
      'proportion'        => $excelData[$row][25]
      );
    M("OrgShareholder")->add($data);
    $membership_date = array('membership_date'=>excelTime($excelData[$row][22]));
    M("Org")->where("org_id=$org_id")->save($membership_date);
    // echo M("OrgShareholder")->getLastSql();die;
  }
}
//联系人
function contact(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/contact.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col!="AP";$col++){
        $address =$col.$row;
        $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
    }

    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");

    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-联系人(不存在)\r\n".date("Y-m-d",time()),FILE_APPEND);
    }
    $data=array(
      'org_id'            => $org_id,
      'realname'          => $excelData[$row][23],
      'post'              => $excelData[$row][24],
      'mobile'            => $excelData[$row][26],
      'tel'               => $excelData[$row][25],
      'email'             => $excelData[$row][27]
      );
    M("OrgContact")->add($data);
    $membership_date = array('membership_date'=>excelTime($excelData[$row][22]));
    M("Org")->where("org_id=$org_id")->save($membership_date);
  }
}

//高管信息
function executive(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/executive.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col!="AP";$col++){
        $address =$col.$row;
        $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
    }

    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");

    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-高管信息(不存在)\r\n".date("Y-m-d",time()),FILE_APPEND);
    }
    $data=array(
      'org_id'            => $org_id,
      'realname'          => $excelData[$row][23],
      'post'              => $excelData[$row][24],
      'idcard'            => $excelData[$row][25],
      'mobile'            => $excelData[$row][27],
      'tel'               => $excelData[$row][26],
      'email'             => $excelData[$row][28]
      );

    M("OrgExecutive")->add($data);
    $membership_date = array('membership_date'=>excelTime($excelData[$row][22]));
    M("Org")->where("org_id=$org_id")->save($membership_date);
  }
}


//纳税人信息
function tax(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/tax.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col!="AP";$col++){
        $address =$col.$row;
        $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
    }

    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");

    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-纳税信息(不存在)\r\n".date("Y-m-d",time()),FILE_APPEND);
    }
    $total = $excelData[$row][24]+$excelData[$row][25]+$excelData[$row][26]+$excelData[$row][27];
    $data=array(
      'org_id'            => $org_id,
      'year'              => $excelData[$row][23],
      'sales'             => $excelData[$row][24],
      'value_added'       => $excelData[$row][25],
      'income'            => $excelData[$row][26],
      'individual_income' => $excelData[$row][27],
      'retained'          => $excelData[$row][28],
      'num'               => $excelData[$row][29],
      'total'             => $total
      );
    M("OrgTax")->add($data);
    $membership_date = array('membership_date'=>excelTime($excelData[$row][22]));
    M("Org")->where("org_id=$org_id")->save($membership_date);
  }
}

//补贴信息
function subsidie(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/subsidie.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col!="AP";$col++){
        $address =$col.$row;
        $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
    }

    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");

    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-补贴信息(不存在)\r\n".date("Y-m-d",time()),FILE_APPEND);
    }
    $data=array(
      'org_id'            => $org_id,
      'type'              => subsidie($excelData[$row][23]),
      'year'              => $excelData[$row][24],
      'realname'          => $excelData[$row][25],
      'amount'            => $excelData[$row][26],
      'implement'         => excelTime($excelData[$row][27]),
      'remark'            => $excelData[$row][28]
      );
    M("OrgSubsidie")->add($data);
    $membership_date = array('membership_date'=>excelTime($excelData[$row][22]));
    M("Org")->where("org_id=$org_id")->save($membership_date);
  }
}

//工作动态
function schedules(){
  //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/schedules.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  for($row = 2; $row <= $highestRow; $row++){
     for($col = 'A';$col!="AP";$col++){
        $address =$col.$row;
        if($col=='X'|| $col=='Y'){
         $excelData[$row][] = gmdate("Y-m-d H:i", PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell($address)->getValue())); 
        }else{
          $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
        }
    }
    $where = array("org_name"=>array("eq",$excelData[$row][0]));
    $org_id = M("Org")->where($where)->getField("org_id");  
    //记录错误
    if(!$org_id){
      file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."-工作动态(不存在)".date("Y-m-d",time()),FILE_APPEND);
    }
    $data=array(
      'org_id'            => $org_id,
      'org_name'          => $excelData[$row][0],
      'name'              => $excelData[$row][27],
      'content'           => $excelData[$row][27],
      'location'          => $excelData[$row][25],
      'actor'             => $excelData[$row][28],
      'start_date'        => $excelData[$row][23],
      'end_date'          => $excelData[$row][24],
      'user_id'           => 1,
      'modify_id'         => 1,
      'type'              => schetype2id($excelData[$row][26])
      );
    M("Schedules")->add($data);
  }
}

// 查出重复机构名称
 function repeatOrg(){
   //导入thinkphp第三方类库
  Vendor('Excel.PHPExcel');

  $filePath = APP_PATH.'Tpl/Excel/org2.xlsx';
  // $fileType = PHPExcel_IOFactory::identify($filePath); //文件名自动判断文件类型
  // $objReader = PHPExcel_IOFactory::createReader($fileType);

  $objReader = new PHPExcel_Reader_Excel2007();

  if(!$objReader->canRead($filePath)){
    $objReader = new PHPExcel_Reader_Excel5();
  }
  
  $objPHPExcel = $objReader->load($filePath);
  
  $objWorksheet = $objPHPExcel->getSheet(0);

  $highestColumn = $objWorksheet->getHighestColumn();
  $highestRow = $objWorksheet->getHighestRow();

  $excelData = array();
  $orgNames = array();
  $orgNames2 = array();
  for($row = 2; $row <= $highestRow; $row++){
      $address = "A$row";
      $excelData[$row][] = $objWorksheet->getCell($address)->getValue();
      if(!empty($excelData[$row][0])){
        $where = array("org_name"=>array("eq",$excelData[$row][0]));
        $org_id = M("Org")->where($where)->getField("org_id"); 
      
    //记录不存在的
    if(!$org_id){
      M("Sameos")->add(array('org_name'=>$excelData[$row][0]));
    }
      //记录存在的
    if($org_id){
      M("Sameos")->add(array('org_name'=>$excelData[$row][0]));
    }
    }
   }
   }
   // dump($orgNames2);
 
// file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$excelData[$row][0]."\n",FILE_APPEND);

function logOrg(){
   $os = M('Sameos')->group("org_name")->select();
  foreach ($os as $k => $v) {
    file_put_contents(APP_PATH.'Tpl/Excel/log.txt',$v['org_name']."\n",FILE_APPEND);
  }
 }
}
?>