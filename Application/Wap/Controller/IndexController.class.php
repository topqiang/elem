<?php
namespace Wap\Controller;
use Think\Controller;
/**
 * 
 */
 class IndexController extends BaseController{
 	public function _initialize(){
 		parent::_initialize();
 	}
 	
 	public function self(){
 		$userid = session("userid");
 		$shopinfo = M("Shop") -> where(array("id"=>$userid)) -> find();
		$this -> assign("shopinfo",$shopinfo); 		
 		$where[ "sid" ] = $userid;
		$ordadd = D("Order");
		$orgood = D("Orgood");
		$ordinfo = $ordadd -> where($where) -> order('updatetime desc,type asc') -> select();
		$this -> assign("ordernum",count($ordinfo));
		foreach ($ordinfo as $key => $order) {
			$good['oid'] = $order[ 'id' ];
			$goods = $orgood -> where( $good ) -> select();
			// $ordinfo[ $key ][ 'gsnum' ] = count($goods); 
			$ordinfo[ $key ][ 'goods' ] = $goods;
		}
		$this -> assign("ordinfo", $ordinfo);
 		$this -> display("self");
 	}

 	public function orderinfo(){
		$id = $_GET['id'];
		$where['id'] = $id;
		$order = M("Order") -> where($where) -> find();
		$goods = M("Orgood") -> where(array('oid'=>$id)) -> select();
		$this -> assign('order',$order);
		$this -> assign('goods',$goods);
		$this -> display("orderinfo");
	}


	public function updorder(){
		$id = $_REQUEST['id'];
		$ajax['code'] = 0; 
		if (isset($id)) {
			$type = $_REQUEST['type'];
			$ord = D("Order");
			$order = array(
				'updatetime' => time(),
				'id' => $id,
				'type' => $type,
				);
			$res = $ord -> save( $order );
			if(isset( $res )){
				$ajax['code'] = 1;
				$ajax['msg'] = "修改成功！";
				$this -> ajaxReturn(json_encode($ajax));
			}else{
				$ajax['msg'] = "修改失败！";
				$this -> ajaxReturn(json_encode($ajax));
			}
		}
	}
 	
 	public function _empty(){
 		$this -> redirect("Index/self");
 	}
 } 