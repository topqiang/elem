<?php
namespace Wap\Controller;
use Think\Controller;
/**
* 
*/
class UserController extends Controller{
	
	function _initialize(){
		
	}

	public function login(){
 		$username = $_POST['username'];
 		$password = $_POST['password'];
 		$shopdb = M("Shop");
 		$ajax['code'] = 0;
 		if (!empty($username) && !empty($password)) {
 			$res = $shopdb -> where( array("username" => $username) ) -> select();
 			if (isset($res)) {
 				if ($res[0]['status'] == "9"){
 					$ajax['msg'] = "您已被下线，请联系客服！";
 				}else if( md5($password) == $res[0]['password'] ) {
 					session("userid",$res[0]['id']);
 					$ajax['code'] = 1;
 					$ajax['msg'] = "登录成功！";
 				}else{
 					$ajax['msg'] = "密码错误！";
 				}
 			}else{
 				$ajax['msg'] = "用户不存在！";
 			}
 		}else{
 			$ajax['msg'] = "用户名密码不能为空！";
 		}
 		$this -> ajaxReturn(json_encode($ajax));
 	}
}