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

	public function inputarea(){
		$city = empty($_GET['city']) ? "北京":$_GET['city'];
		$line = empty($_GET['line']) ? "":$_GET['line'];
		$this -> assign("city",$city);
		$this -> assign("line",$line);
		$this -> display();
	}

	public function location(){
		$oid = $_GET['oid'];
		if (isset($oid)) {
			session('oid',$oid);
		}
		$line = empty($_GET['line']) ? "" : $_GET['line'];
		$this -> assign("line",$line);
		$this -> display();
	}

	public function index(){
		$oid = $_GET['oid'];
		if (isset($oid)) {
			session('oid',$oid);
		}
		$type = $_GET['type'];
		$address = D("Address");
		$user = session("userid");
		if (empty($type) || ($type != "1")) {
			//送货上门
			$where['fromuser'] = array( 'eq' , $user);
			$where['status'] = array('neq','9');
			$cityarr = $address -> field("id,name,tel,city,detailadd") -> where($where) -> select();
			$this -> assign( 'cityarr' , $cityarr );
		}
		if (empty($type) || ($type != "2")) {

			$shop = D("Shopadd")->select();
			$this -> assign("shop" ,$shop);
		}
		$useradd = D("User") -> field('address') -> where(array('id' => $user)) -> select();
		$this -> assign( "useradd" , $useradd[0]['address'] );
		$this -> display("index");
	}
	//添加地址
	public function addAddress(){
		if(empty($_POST['name'])){
			$this -> ajaxReturn("error");
			exit();
		}
		$userid = session("userid");
		$data = array(
				"name" => $_POST['name'],
				"sex" => $_POST['sex'],
				"tel" => $_POST['tel'],
				"provice" => $_POST['provice'],
				"city" => $_POST['city'],
				"detailadd" => $_POST['detailadd'],
				"numhouse" => $_POST['numhouse'],
				"label" => $_POST['label'],
				"fromuser" => $userid
			);
		$res = D("Address")->add($data);
		if (!empty($res)) {
			$touseradd = array('id'=>$userid);
			$user = D("User");
			$address = $user -> field('address') -> where( $touseradd ) -> select();
			if (empty($address[0]['address'])) {
				$touseradd['address'] = $res;
				$user -> save( $touseradd );
			}
			$oid = session("oid");
			if (isset($oid)) {
				$addid = $res;
				$detailadd = $_POST['detailadd'].$_POST['numhouse'];
				$delivertype = 1;
				$order = array(
					"id" => $oid,
					"address" => $addid,
					"detailadd" => $detailadd,
					"delivertype" => $delivertype
					);
				$res = M('Order') -> save($order);
				if (isset($res)) {
					session("oid",null);
					$this -> ajaxReturn(json_encode(array('oid' => $oid)));
				}else{
					$this -> ajaxReturn("error");
				}
			}
			$this -> ajaxReturn($res);
		}else{
			$this -> ajaxReturn("error");
		}
	}


	public function ediadd(){
		$line = empty($_GET['line']) ? "":$_GET['line'];
		$this -> assign("line",$line);
		if (empty($line)) {
			if (isset($_POST['id'])) {
				$userid = session("userid");
				$data = array(
						"id" => $_POST['id'],
						"name" => $_POST['name'],
						"sex" => $_POST['sex'],
						"tel" => $_POST['tel'],
						"provice" => $_POST['provice'],
						"detailadd" => $_POST['detailadd'],
						"numhouse" => $_POST['numhouse'],
						"label" => $_POST['label'],
						"fromuser" => $userid
					);
				if (!empty($_POST['city'])) {
					$data['city'] = $_POST['city'];
				}
				$res = D("Address") -> save($data);
				$oid = session("oid");
				if (isset($oid)) {
					$addid = $_POST['id'];
					$detailadd = $_POST['detailadd'].$_POST['numhouse'];
					$delivertype = 1;
					$order = array(
						"id" => $oid,
						"address" => $addid,
						"detailadd" => $detailadd,
						"delivertype" => $delivertype
						);
					$res = M('Order') -> save($order);
					if (isset($res)) {
						session("oid",null);
						$this -> ajaxReturn(json_encode(array('oid' => $oid)));
					}else{
						$this -> ajaxReturn("error");
					}
				}
				if (isset($res)) {
					$this -> ajaxReturn($res);
				}
			}else{
				$id = $_GET['id'];
				$where['id'] = array('eq',$id);
				$list = M('Address') -> where($where) -> select();
				$this -> assign('address',$list[0]);	
			}
		}
		$this -> display();
	}

	public function del(){
		$addid = $_GET['id'];
		if (isset($addid)) {
			$where = array(
				'id' => session('userid'),
				'address' => $addid
				);
			$user = M('User') -> where($where) -> select();
			if (isset($user)) {
				$where['address'] = 0;
				M('User') -> save ( $where );
			}
			$data = array(
				'id' => $addid,
				'status' => '9');
			$res = D("Address") -> save( $data );
			if (isset($res)) {
				$this -> index();
			}
		}
	}
}