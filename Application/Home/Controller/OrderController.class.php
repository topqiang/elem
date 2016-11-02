<?php
namespace Home\Controller;
use Think\Controller;
/**
 * Class OrderController
 * @package Home\Controller
 */
class OrderController extends BaseController{
	public function _initialize(){
		parent::_initialize();
	}
	public function addorder(){
		$goods = $_POST['goods'];
		$fromuser = session("userid");
		$name = $fromuser.date("YmdHis").rand(10000,99999);
		$Order = D("Order");
		$address = D("User") -> field('address,delivertype') -> where( array('id'=>$fromuser) ) -> select();
		$delivertype = $address[0]['delivertype'];
		$delivertype = isset($delivertype) ? $delivertype : 1;
		$address = $address[0]['address'];
		if (isset($address)) {
			$addinfo = D('Address') -> where("id = $address") -> select();
			$detailadd = $addinfo[0]['detailadd'].$addinfo[0]['numhouse'];
		}else{
			$address = 0;
			$detailadd = "";
			//强制用户生成订单前保存地址
			// $this -> ajaxReturn(json_encode(array('status' => 'noadd')));
		}
		$flag =false;
		$price = 0;
		foreach ($goods as $key => $good) {
			$price += ($good['gprice'] * $good['gnum']);
		}
		//获取运费，以及优惠卷使用
		$luggage = M('Luggage') -> select();
		if (!empty($goods)) {
			//查询当前物流方式以及地址
			$Order->startTrans();
			$order = array(
				"name" => $name,
				"fromuser" => $fromuser,
				"create_time" =>time(),
				"update_time" =>time(),
				"price" => $price,
				"type" => 0,
				"delivertype" => $delivertype,
				"address" => $address,
				"detailadd" => $detailadd
			);
			if ($delivertype == 0) {
				$getcode = rand(10000,99999);
				$order['getcode'] = $getcode;
			}
			if (isset($luggage)) {
				if ($price <= $luggage[0]['man']) {
					$order['luggage'] = $luggage[0]['price'];
					$price = $price + $order['luggage'];
				}
			}
			$order['paymoney'] = $price;
			$res = $Order -> add($order);
			if (!empty($res)) {
				$orobj = D("Orgo");
				$Gley = D("Gley");
				$price = 0;
				foreach ($goods as $key => $good) {
					$orgo[$key] = array(
						"gid" => $good['gid'],
						"gprice" => $good['gprice'],
						"gnum" => $good['gnum'],
						"oid" => $res
						);
					$Gley -> delete($good['glid']);
				}
				$result = $orobj -> addAll($orgo);
				if (!empty($result)) {
					$flag = true;
					$ajax = array('status'=>'success','id'=>$res);
					if ( $flag ) {
						$Order -> commit();
					}else{
						$Order -> rollback();
					}
					$this -> ajaxReturn(json_encode($ajax));
				}else{
					$this -> ajaxReturn("添加".$good['gid']."商品失败！");
				}
			}else{
				$this -> ajaxReturn("订单生成失败！");
			}
		}else{
			$this -> ajaxReturn("商品数量为空！");
		}
	}
	/**
	*
	*
	*/
	public function updorder(){
		$order = $_POST['order'];
		if ( isset($order) ) {
			$couid = $_POST['couid'];
			$ord = D("Order");
			$order['update_time'] = time();
			$res = $ord -> save( $order );
			if ( isset( $res ) ) {
				if (isset($couid)) {
					$couobj = M('Usercou');
					$res = $couobj -> save(array('id' =>$couid ,'utype'=>1));
				}
				if ( !empty($order['paytype']) && $order['paytype']  == '1') {
					$this -> ajaxReturn("gopay");
				}
				$this -> ajaxReturn( $res );
			}else{
				$this -> ajaxReturn( "error" );
			}
		}
	}

	public function gopay(){
		$oid = $_GET['oid'];
		if (isset($oid)) {
			$ord = D("Order");
			$res = $ord -> field('id,name,price,delivertype,paymoney,delidate') -> where( array('id' => $oid)) -> limit(1) -> select();
			if (empty($res[0]['delidate'])) {
				// $this -> redirect("index.php/Order/orderinfo/id/$oid");
				$_GET['id'] = $oid;
				$this -> orderinfo();
			}
			$order = $res[0];
			$openid = session('wx_id');
			$callbackurl = "http://".$_SERVER['SERVER_NAME']."/index.php/Home/Order/comord/id/".$order['id']."/delivertype/".$order['delivertype'];
			$url = "http://www.happydaze.cn/pay/example/jsapi.php?ordname=".$order['name']."&price=".$order['paymoney']."&openid=$openid&callbackurl=$callbackurl";
			Header("Location: $url");
		}
	}

	public function comord(){
		$id = $_GET['id'];
		$delivertype = $_GET['delivertype'];
		$type = 0;
		$arr = array(
			"id" => $id,
			"type" => $type
			);
		$yes = M("Order") -> where($arr) -> limit(1)-> select();
		if (!isset($yes)) {
			exit();
		}
		if (isset($id)) {
			if ($delivertype == 0) {
				$type = 2;
			}else{
				$type = 1;
			}
		}
		$data = array(
			"id" => $id,
			"type" => $type,
			"update_time" => time()
			);
		$res = M("Order") -> save($data);
		$this -> redirect("Order/orderlist");
	}

	public function payerror(){
		$ordname = $_GET['ordname'];
		$where['name'] = array('eq',$ordname);
		$order = M('Order') -> field('id,name') -> where($where) -> select();
		if (isset($order)) {
		 	$this -> assign('order' ,$order[0]);
		 	$this -> display();
		 }else{
		 	$this -> display("Index/index");
		 }
	}

	public function rate(){
		$oid = $_GET['oid'];
		$where['oid'] = array('eq',$oid);
		$orgood = D("Orgood");
		$goods = $orgood -> where($where) -> select();
		$this -> assign("goods" , $goods);
		$this -> display();
	}

	public function orderinfo(){
		$id = $_GET['id'];
		$where['oid'] = array('eq',$id);
		$ordadd = D("Ordadd");
		$orgood = D("Orgood");
		$ordinfo = $ordadd -> where($where) -> select();
		$goods = $orgood -> where($where) -> select();
		//查询优惠方式
		$couarr['status'] = array('neq',9);
		$couarr['num'] = array('gt',0);
		$couarr['startime'] = array( 'elt' , date('Y/m/d'));
		$couarr['endtime'] = array( 'egt' , date('Y/m/d'));
		$couarr['uid'] = array( 'eq' , session('userid'));
		$couarr['utype'] = array( 'neq' , 1);
		$coupon = M('Ucoupon') -> where( $couarr ) ->select();
		$this -> assign( "coupon" , $coupon );
		$this -> assign( "goods" , $goods );
		$this -> assign( "ordinfo" , $ordinfo[0] );
		if ( empty( $ordinfo[0]['delidate'] ) ) {
			if (isset($ordinfo[0]['delidate']) && $ordinfo[0]['delidate'] != "尽快") {
				$datearr = explode("=", $ordinfo[0]['delidate']);
			}
			//处理配送时间
			$date = date("Y-m-d",(time()+48*60*60));
			$tomaro = date("Y-m-d",(time()+24*60*60));
			$set = json_decode(file_get_contents('set.txt'),true);

			$six = isset($set['setsix']) ? $set['setsix'] : 'no';
			$sev = isset($set['setsev']) ? $set['setsev'] : 'no';
			$this -> assign('six', $six);
			$this -> assign('sev', $sev);
			if ($six == 'no' || $sev == 'no') {
				if ( $six == 'no' && $sev == 'no') {
					if (date("w") == 4) {//今天是周四
						$date = date("Y-m-d",(time()+96*60*60));
					}else if (date("w") == 5) {//今天是周五
						$date = date("Y-m-d",(time()+96*60*60));
						$tomaro = date("Y-m-d",(time()+72*60*60));
					}else if (date("w") == 6) {//今天是周六
						$date = date("Y-m-d",(time()+72*60*60));
						$tomaro = date("Y-m-d",(time()+48*60*60));
					}
					if (date("w") != 6 && date("w") != 0) {
						if ( date('H',time()) < 10 ) {
							$this -> assign('today',date("Y-m-d",time()));
						}
						if (date('H',time()) >= 10 && date('H',time()) < 18) {
							$this -> assign('date',"yes");
						}
					}
				}else if ($sev == 'yes' && $six == 'no') {
					if (date("w") == 4) {//今天是周四
						$date = date("Y-m-d",(time()+72*60*60));
					}else if (date("w") == 5) {//今天是周五
						$date = date("Y-m-d",(time()+72*60*60));
						$tomaro = date("Y-m-d",(time()+48*60*60));
					}else if (date("w") == 6) {//今天是周六
						$date = date("Y-m-d",(time()+48*60*60));
						$tomaro = date("Y-m-d",(time()+24*60*60));
					}
					if (date("w") != 6) {
						if ( date('H',time()) < 10 ) {
							$this -> assign('today',date("Y-m-d",time()));
						}
						if (date('H',time()) >= 10 && date('H',time()) < 18) {
							$this -> assign('date',"yes");
						}
					}
				}else if ($sev == 'no' && $six == 'yes') {
					if (date("w") == 5) {//今天是周五
						$date = date("Y-m-d",(time()+72*60*60));
					}else if (date("w") == 6) {//今天是周六
						$date = date("Y-m-d",(time()+72*60*60));
						$tomaro = date("Y-m-d",(time()+48*60*60));
					}
					if ( date("w") != 0) {
						if ( date('H',time()) < 10 ) {
							$this -> assign('today',date("Y-m-d",time()));
						}
						if (date('H',time()) >= 10 && date('H',time()) < 18) {
							$this -> assign('date',"yes");
						}
					}
				}
			}else{
				if ( date('H',time()) < 10 ) {
					$this -> assign('today',date("Y-m-d",time()));
				}
				if (date('H',time()) >= 10 && date('H',time()) < 18) {
					$this -> assign('date',"yes");
				}
			}
			$this -> assign( "datee" , $date );
			$this -> assign( "tomaro" , $tomaro );
			
			$this -> assign('time',$datearr[1]);
			$this -> display("orderinfo");
		}else{
			$this -> display("orderinfo2");
		}
	}

	public function orderlist(){
		$where[ 'fromuser' ] = session("userid");
		$ordadd = D("Ordadd");
		$orgood = D("Orgood");
		$ordinfo = $ordadd -> field("oid,addname,ordname,type,price,delivertype,paymoney") -> where($where) -> order('type asc,update_time desc') -> select();
		foreach ($ordinfo as $key => $order) {
			$good[ 'oid' ] = $order[ 'oid' ];
			$goods = $orgood -> field('gid,name,gnum,gprice') -> where( $good ) -> select();
			 $ordinfo[ $key ][ 'gsnum' ] = $orgood -> where( $good ) -> sum('gnum');
			// $ordinfo[ $key ][ 'gsnum' ] = count($goods); 
			$ordinfo[ $key ][ 'goods' ] = $goods;
		}
		$this -> assign("ordinfo", $ordinfo);
		$this -> display();
	}

	public function fobygood(){
		$key = $_POST['key'];
		$user = session("userid");
		$ordadd = D("Ordadd");
		$orgood = D("Orgood");
		$oid = $orgood -> distinct("oid") -> field("oid") -> where(array("name" => array("like","%$key%"), "uid" => $user )) -> select();
		foreach ($oid as $key => $order) {
			$newoid[$key] = $order['oid'];
		}
		$where['oid'] = array( 'in' , $newoid);
		$ordinfo = $ordadd -> field("oid,addname,ordname,type,price,delivertype") -> order('type asc,update_time desc') -> where($where) -> select();
		foreach ($ordinfo as $key => $order) {
			$good[ 'oid' ] = $order[ 'oid' ];
			$goods = $orgood -> field('gid,name,gnum,gprice') -> where( $good ) -> select();
			 $ordinfo[ $key ][ 'gsnum' ] = $orgood -> where( $good ) -> sum('gnum');
			// $ordinfo[ $key ][ 'gsnum' ] = count($goods); 
			$ordinfo[ $key ][ 'goods' ] = $goods;
		}
		$this -> assign("ordinfo", $ordinfo);
		$this -> display("orderlist");
	}
}
