<?php
namespace Home\Controller;
use Think\Controller;

/**
 * Class RoomController
 * @package Home\Controller
 */
class RoomController extends BaseController{
	public function _initialize(){
		parent::_initialize();
	}
	
	public function bulidshop(){
		$pic = D("Shoppic")->select();
		$shop = D("Shopadd")->select();
		$this -> assign("pic",$pic);
		$this -> assign("shop" ,$shop);
		$this->display();
	}
}
