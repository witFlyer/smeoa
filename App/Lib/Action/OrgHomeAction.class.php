<?php
/**
 * 机构首页
 */
class OrgHOmeAction extends CommonAction {

	function index() {
		//统计
		$jiedai = M('Schedules')->where("type=1")->count();
		$baifang = M('Schedules')->where("type=2")->count();
		$huiyi = M('Schedules')->where("type=3")->count();
		$qita = M('Schedules')->where("type=4")->count();
		$total = $jiedai+$baifang+$huiyi+$qita;

		//机构动态
		$list = M('OrgLog')->order("time DESC")->limit(8)->select();

		// dump($list);die;
		$this->assign('jiedai',$jiedai);
		$this->assign('baifang',$baifang);
		$this->assign('huiyi',$huiyi);
		$this->assign('qita',$qita);
		$this->assign('total',$total);
		$this->assign('list',$list);

		$this->display();
	}
}
