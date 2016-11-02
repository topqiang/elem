<?php
namespace Wap\Controller;
use Think\Controller;
/**
* 
*/
class BaseController extends Controller{
	
	function _initialize(){
		$user = session("userid");
		if (!isset($user)) {
			$this -> display("User/login");
			exit();
		}
	}

}