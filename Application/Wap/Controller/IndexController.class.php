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
 	public function index(){
 		$userid = session("userid");
 		$this -> display("self");
 	}
 	
 	public function _empty(){
 		$this -> index();
 	}
 } 