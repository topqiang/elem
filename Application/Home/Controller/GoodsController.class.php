<?php
namespace Home\Controller;
use Think\Controller;
class GoodsController extends BaseController{
	public function _initialize(){
		parent::_initialize();
	}
	public function goodsAdd(){;
		$name = $_POST['name'];
		$foods = $_POST['foods'];
		$cut = $_POST['cut'];
		$pic = 'Uploads/goods/201609/4c086e017b084739bfec686dfd5fe5c0.jpg';
		$price = 48;
		$cate_id = 5;//配置数据库里大份小份自选id
		if (count($foods) == 0) {
			$this->ajaxReturn("empty");
			exit();
		}
		switch ($name) {
			case 'big':
				$name = "自选沙拉大份";
				break;
			case 'small':
				$name = "自选沙拉";
				$price = 38;
				$cate_id = 6;
				break;
			case 'free':
				$name = "自选卷";
				$pic = "Uploads/goods/201609/efc3527e79264b90aa657037f19780e1.jpg";
				$price = 25;
				$cate_id = 7;
				break;
			default:
				$name = "自选沙拉大份";
				break;
		}
		$constituent = '';
		foreach ($foods as $key => $obj) {
			$constituent .= $obj['foodsname'].'X'.$obj['num'].'份    ';
		}
		$data=array(
			'name'			=>	$name,
			'cate_id'		=>	$cate_id,
			'constituent'	=>	$constituent,
			'pic'			=>	$pic,
			'price'			=>	$price,
			'create_time'	=>	time(),
			'update_time'	=>	time(),
			'remark'		=>	$cut,
			'status'		=>	0,
		);
		$goodid = D('Goods')->add($data);
		$userid = session("userid");
		$data = array('goods' => $goodid, 'fromuser' => $userid);
		$res = D("Gley")->add($data);
		if (isset($res)) {
			$this->ajaxReturn($res);
		}else{
			$this->ajaxReturn("error");
		}
	}


	public function gley(){
		if ($this->count > 0 ) {
			$userid = session('userid');
			$where['glfromuser'] = array('eq',$userid);
			$res = D("Gleygood")->where($where)->select();
			$this->assign('goods',$res);
		}
		$this->display();
	}

	public function updHub(){
		$hub = D("Hub");
		$clum = $_POST['clum'];
		$fid = $_POST['fid'];
		$zhi = $_POST['zhi'];
		$uid = session("userid");
		$data[$clum] = $zhi;
		if ($clum == "well") {
			$data["bad"] = 0;
		}else{
			$data["well"] = 0;
		}
		$where = array('uid'=>$uid,'fid'=>$fid);
		$hid = $hub -> field('id') -> where($where) -> select();
		if (!empty($hid)) {
			$data['id'] = $hid[0]['id'];
			$res = $hub ->save($data);
			$this->ajaxReturn("success");
		}else{
			$data['uid'] = $uid;
			$data['fid'] = $fid;
			$res = $hub->add($data);
			$this->ajaxReturn($res);
		}
	}


	public function updHubGood(){
		$clum = $_POST['clum'];
		$hid = $_POST['hid'];
		$gid = $_POST['gid'];
		$zhi = $_POST['zhi'];
		$data[$clum] = $zhi;
		if ($clum == "well") {
			$data["bad"] = 0;
		}else{
			$data["well"] = 0;
		}
		if (isset($hid) && !empty($hid)) {
			$data['id'] = $hid;
			$res = D("Hubgood")->save($data);
			$this->ajaxReturn("success");
		}else{
			$data['uid'] = session("userid");
			$data['gid'] = $gid;
			$res = D("Hubgood")->add($data);
			$this->ajaxReturn($res);
		}
	}


	public function rangood(){
		$foodcate = D( "Foodcate" );
    	$where['cname'] = array( 'eq' , '酱料' );
    	$list = $foodcate -> field( "fid,fpic,fname" ) -> where( $where ) -> select();
    	$this -> assign( 'list' , $list );
		$this->display();
	}


	public function goodlist(){
		$goodcate = D("Goodcate");
    	$gcate = D("Gcate");
    	$Hubgood = D("Hubgood");
    	$rate = M('Rate');
    	$catew['name'] = array('not in' , '自选沙拉大份,自选沙拉小份,自选卷,帮选沙拉');
    	$catew['status'] = array('neq' , '9');
    	$catelist = $gcate -> field("id,name") -> where($catew) -> select();
    	$this->assign( 'catelist' , $catelist );
    	if (isset($_GET['cname'])) {
    		$where1['cname'] = $_GET['cname'];
    	}else{
    		$where1['gcate_id'] = isset($_POST['cid']) ? $_POST['cid'] : $catelist[0]['id'];
    	}
    	$list = $goodcate -> field("gid,gpic,gprice,gconstituent,gname,gcate_id,cname,zannum,unzannum") -> where($where1) -> select();
    	// dump($list);
    	// exit();
    	$curid = $list[0]['gcate_id'];
    	$this->assign("curid" ,$curid);
    	foreach ($list as $key => $good) {
    		$countarg['gid'] = array('eq' , $good['gid']);
    		$countarg['well'] = array('eq' , 1);
    		$wellnum = $Hubgood->where($countarg)->count();
    		$countarg['well'] = array('eq' , 0);
    		$countarg['bad'] = array('eq' , 1);
    		$badnum = $Hubgood->where($countarg)->count();
    		$good['wellnum'] = $wellnum + $good['zannum'];
    		$good['badnum']	= $badnum + $good['unzannum'];
    		$where['gid'] = array('eq' , $good['gid']);
    		$where['uid'] = array('eq' , session('userid'));
    		$hub = $Hubgood->where($where)->select();
    		if (isset($hub[0]['well']) && $hub[0]['well'] == "1") {
    			$good['well'] = $hub[0]['well'];
    			$good['hid'] = $hub[0]['id']; 
    		}else if (isset($hub[0]['bad']) && $hub[0]['bad'] == "1") {
    			$good['bad'] = $hub[0]['bad'];
    			$good['hid'] = $hub[0]['id']; 
    		}
    		$sum = $rate -> where(array('gid'=>$good['gid'])) -> select();
    		$good['ratenum'] = count( $sum );
    		$list[$key] = $good;
    	}
    	if (isset($_POST['cid'])) {
    		$this -> ajaxReturn(json_encode($list));
    		exit();
    	}
    	$this->assign( 'list' , $list );
    	$this -> display();
	}

	public function delgley(){
		if (isset($_GET['glid'])) {
			$gley = D("Gley");
			$where["id"] = $_GET['glid'];
			$res = $gley -> where($where) -> delete();
			if (isset($res)) {
				$this ->ajaxReturn("success");
			}else{
				$this ->ajaxReturn("error");
			}
		}
	}
	public function togley(){
		$goods = $_POST['goods'];
		$userid = session("userid");
		$gley = D("Gley");
		$flag = true;
		foreach ($goods as $key => $good) {
			$data['goods'] = $good['gid'];
			$data['fromuser'] = $userid;
			$ids = $gley->field('id,goodnum')->where(array('goods' =>$good['gid'],'fromuser' =>$userid))->select();
			if (isset($ids) && count($ids) > 0) {
				$data['id'] = $ids[0]['id'];
				$data['goodnum'] = $good['goodnum'] + $ids[0]['goodnum'];
				$res = D("Gley")->save($data);
			}else{
				$data['goodnum'] = $good['goodnum'];
				$res = D("Gley")->add($data);
			}
			if (!isset($res)) {
				$flag = false;
			}
		}
		if ( $flag ) {
			$this->ajaxReturn($res);
		}else{
			$this->ajaxReturn("error");
		}
	}

	public function findgood(){
		$key = $_GET['key'];
		$goodcate = D("Goodcate");
		if (isset($key)){
			$where1['gname'] = array('like' ,"%$key%");
			$where1['cname'] =  array('not in' , '自选沙拉大份,自选沙拉小份,自选卷');
			$list = $goodcate -> field("gid,gpic,gprice,gconstituent,gname,gcate_id,cname") -> where($where1) -> select();
			$num = count($list);
			if ($num > 0) {
				$this->assign("text","为您找到".$num."个有关“".$key."”的菜品");
				$Hubgood = D("Hubgood");
				foreach ($list as $key => $good) {
	    		$countarg['gid'] = array('eq' , $good['gid']);
	    		$countarg['well'] = array('eq' , 1);
	    		$wellnum = $Hubgood->where($countarg)->count();
	    		$countarg['well'] = array('eq' , 0);
	    		$countarg['bad'] = array('eq' , 1);
	    		$badnum = $Hubgood->where($countarg)->count();
	    		$good['wellnum'] = $wellnum;
	    		$good['badnum']	= $badnum;


	    		$where['gid'] = array('eq' , $good['gid']);
	    		$where['uid'] = array('eq' , session('userid'));
	    		$hub = $Hubgood->where($where)->select();
	    		if (isset($hub[0]['well']) && $hub[0]['well'] == "1") {
	    			$good['well'] = $hub[0]['well'];
	    			$good['hid'] = $hub[0]['id']; 
	    		}else if (isset($hub[0]['bad']) && $hub[0]['bad'] == "1") {
	    			$good['bad'] = $hub[0]['bad'];
	    			$good['hid'] = $hub[0]['id']; 
	    		}
	    		$list[$key] = $good;
	    	}
	    	$this-> assign("list" , $list);
			}
	    	$this-> display("goodlist");
		}else{
			$this-> display();
		}
	}
}