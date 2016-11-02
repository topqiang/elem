<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller{
	public function _initialize(){
		$this->user=D('User');
	}
	public function index(){
		$code = $_GET['code'];
		if (isset( $code )) {
			session('code','123');
			$base = A("Base");
			$appid = $base->appid;
			$scret = $base->scret;
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$scret&code=$code&grant_type=authorization_code";
			$res = $this -> curl("",$url);
			$access = json_decode($res,true);
			$openid = $access['openid'];
			$where['wx_id'] = array('eq',$openid);
			$userinfo = $this -> user ->where($where) -> select();
			if (isset($userinfo[0]['id'])) {
				session('userid',$userinfo[0]['id']);
				session('wx_id',$openid);
				session('code',null);
				redirect('Index/index');
			}else{
				$getuserurl = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access['access_token']."&openid=".$access['openid']."&lang=zh_CN";
				$userinfo = $this -> curl("",$getuserurl);
				if (isset($userinfo)) {
					$array = json_decode($userinfo,true);
					$userdata = array(
					'name' => $array['nickname'],
					'wx_id' => $array['openid'],
					'sex' => ($array['sex'] == '1') ? "男" : "女",
					'pic' => $array['headimgurl'],
					'create_time' => time()
					);
					$userid = $this -> user ->add($userdata);
					session('userid',$userid);
					session('wx_id',$openid);
					session('code',null);
					redirect('Index/index');
				}
			}
		}
		$this->display("login");
	}

	public function register(){
		$tel = $_POST['tel'];
		$pwd = $_POST['pwd'];
		$repwd = $_POST['repwd'];
		if (!isset($tel)) {
			$this -> display();
			exit();
		}
		if ($tel != ""  && $pwd == $repwd) {
			$where['tel'] = array('eq',$tel);
			$res = M('User') -> field('tel') -> where( $where ) -> select();
			if (count($res) == 1) {
				$this -> ajaxReturn("用户已存在");
			}else{
				$data = array('tel' => $tel ,'pwd' => md5($pwd),'create_time' => time());
				$result = M('User') -> add($data);
				if (isset($result)) {
					session("userid",$result);
					$this -> ajaxReturn("success");
				}
			}
		}else{
			$this -> ajaxReturn("信息填写有误");
		}
	}

	public function checkhas(){
		$tel = $_POST['tel'];
		$where['tel'] = array('eq',$tel);
		$res = M('User') -> field('tel') -> where( $where ) -> select();
		if (count($res) == 1) {
			$this -> ajaxReturn("success");
		}else{
			$this -> ajaxReturn("next");
		}
	}

	public function recharge(){
		A('Base');
		$this -> display();
	}
	public function about(){
		$about = M('About')->select();
		$this -> assign('about',$about[0]);
		$this -> display();
	}
	public function msg(){
		A('Base');
		$where['status'] = array('neq','9');
		$msgs = M('Msg') -> where($where) -> select();
		$this -> assign('msgs' ,$msgs);
		$this -> display();
	}
	public function balance(){
		A('Base');
		$this -> display();
	}

	public function login(){
		$phone = $_POST['mobile'];
		$pwd = $_POST['pwd'];
		$where['tel'] = array('eq', $phone);
		$res = D('User')->where($where)->select();
		if (isset($res)) {
			if ($res[0]['pwd'] == md5( $pwd )) {
				session("userid",$res[0]['id']);
				$msg = "success";
			}else{
				$msg = '密码不正确';
			}
		}else{
			$msg = '用户不存在';	
		}
		$this->ajaxReturn($msg);
	}


	public function self(){
		A('Base');
		$userid = session("userid");
		$where['id'] = $userid;
		$user = $this->user->where( $where )->select();
		$this -> assign('user' ,$user[0]);
		$this -> display();
	}

	public function setself(){
		A('Base');
		$userid = session("userid");
		$where['id'] = $userid;
		$user = $this->user->where( $where )->select();
		$this -> assign('user' ,$user[0]);
		$this -> display();
	}

	public function updaddress(){
		$oid = session("oid");
		if (isset($oid)) {
			$addid = $_POST['id'];
			$addinfo = D('Address') -> where("id = $addid") -> select();
			$detailadd = $addinfo[0]['detailadd'].$addinfo[0]['numhouse'];
			$delivertype = $_POST['delivertype'];
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
		}else{
			$addid = $_POST['id'];
			$userid = session("userid");
			$delivertype = $_POST['delivertype'];
			$data = array('id' => $userid ,'address' => $addid , 'delivertype' => $delivertype);
			$res = $this -> user -> save ( $data );
			if (!empty($res)) {
				$this -> ajaxReturn("success");
			}else{
				$this -> ajaxReturn("error");
			}
		}
	}

	public function loginout(){
		session(null);
		redirect(U('User/index'));
	}

	public function coupon(){
		A('Base');
		$uid = session('userid');
		$ucoupon = M('Ucoupon');
		$where['status'] = array('neq',9);
		$where['uid'] = array('eq',$uid);
		$res = $ucoupon -> where($where) ->order('utype asc , endtime asc') -> select();
		$this -> assign('coupons',$res);
		$this -> display();
	}

	public function curl($data,$url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT,  'Mozilla/5.0 (compatible;MSIE 5.01;Windows NT5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo=curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_errno($ch);
        }
        curl_close($ch);
        return $tmpInfo;
    }

}