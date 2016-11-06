<?php
namespace Home\Controller;
use Think\Controller;
/**
* 
*/
class AddressController extends BaseController{
	
	public function _initialize(){
		parent::_initialize();
	}
	public function editadd(){
		$oid = $_POST['oid'];
		if (isset($oid)) {
			$name = $_POST['name'];
			$tel = $_POST['tel'];
			$address = $_POST['address'].$_POST['housenum'];
			if (isset($oid)) {
				$order = array(
					'id'	=> $oid,
					'addname' => $name,
					'addtel' => $tel,
					'address' => $address,
					'updatetime' => time()
					);
				M('Order') -> save($order);
			}
			$userid = session("userid");
			$userinfo = array(
				'id'	=> $userid,
				'name'	=> $name,
				'address'	=> $address,
				'tel'		=> $tel
				);
			$res = M('User') -> save($userinfo);
			if (isset($res)) {
				redirect(U("Order/orderinfo",array('id'=>$oid)));
			}
		}else{
			$this -> display();
		}
	}
}