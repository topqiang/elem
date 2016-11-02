<?php
namespace Home\Controller;
use Think\Controller;
class RateController extends BaseController{
	public function _initialize(){
		parent::_initialize();
	}
	public function addrate(){
		$rates = $_POST['rate'];
		$ratem = M('Rate');
		if (isset($rates)) {
			$flag = true;
			foreach ($rates as $key => $rate) {
				$data['gid'] = $rate['gid'];
				$data['uid'] = session('userid');
				$data['wellnum'] = $rate['wellnum'];
				$data['create_time'] = time();
				if (!empty( $rate['msgtext'] )) {
					$data['msgtext'] = $rate['msgtext'];
				}
				$res = $ratem -> add($data);
				if (!isset($res)) {
					$flag = false;
				}
			}
			if (!$flag) {
				$this -> ajaxReturn("error");
			}else{
				$this -> ajaxReturn(count($rate));
			}
		}else{
			$this->ajaxReturn("error");
		}
	}

	public function ratelist(){
		$gid = $_GET['gid'];
		if (!empty($gid)) {
			$ratem = M('Urate');
			$rates = $ratem -> where(array('gid' => $gid)) -> select();
			$wellnum = 0;
			foreach ($rates as $key => $rate) {
				$wellnum += $rate['wellnum'];
			}
			$avgnum = ceil($wellnum/count($rates));
		}

		$this -> assign("ratelist",$rates);
		$this -> assign("avgnum" ,$avgnum);
		$this -> display('Goods/ratelist');
	}
}