<?php
class CardAction extends Action {

	protected $config = array('app_type' => 'public');
	
	public function index()
	{
		$this->display();
		return;
	}
	
	function save()
	{
		$model = D($this->getActionName());
		
		if (false === $model->create())
		{
			$this -> error($model->getError());
		}
		/*保存当前数据对象 */
		$list = $model->add();
		if ($list !== false)
		{
			$this->assign('jumpUrl', get_return_url());
			$this->success('新增成功!');
		}
		else
		{
			$this->error('新增失败!');
			//失败提示
		}
	}
	
	function xhr()
	{
		$limit 	= intval($_GET['iDisplayLength']);
		$offset	= intval($_GET['iDisplayStart']);
		
		$list = M("Card")->order(array('id' => 'DESC'))->limit($offset, $limit)->findAll();
		
		if (empty($list)) $list = array();
		
		$response['sEcho'] 					= $_GET['sEcho'];
		$response['iTotalRecords'] 			= count($list);
		$response['iTotalDisplayRecords']	= count($list);
		$response['aaData']					= $list;
		
		echo json_encode($response);
	}
	
	function save_check()
	{
		if($this->isAjax())
		{
			$no  = trim($_POST['no']);
			$mac = trim($_POST['mac']);
			
			$where[0] = array('no' => array('eq', $no));
			$where[1] = array('mac'=> array('eq', $mac));
			
			$result[0] = M("Card")->where($where[0])->find();
			$result[1] = M("Card")->where($where[1])->find();
			
			if ($result[0])
			{
				echo "1";
			}
			elseif($result[1])
			{
				echo "2";
			}
			else
			{
				echo "0";
			}
		}
	}
}