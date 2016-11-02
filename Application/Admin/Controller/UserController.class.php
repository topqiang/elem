<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends Controller{
	public function _initialize(){
		
	}
	public function userlist(){
		$name = $_POST['name'];
		if (isset($name)) {
			$where['name'] = array("like","%$name%");
		}
		$count = D('Useradd') -> where($where)->count();
		$page = new \Think\Page($count,15);
		$userlist = D('Useradd') -> where($where) -> limit($page->firstRow,$page->listRows) -> select();
		$this -> assign('userlist',$userlist);
		$this -> assign('page',$page->show());
		$this -> display();
	}
}