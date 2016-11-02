<?php
namespace Home\Controller;
use Think\Controller;

/**
 * Class IndexController
 * @package Home\Controller
 */
class IndexController extends BaseController {
    public function _initialize(){
    	parent::_initialize();
    	//完善购物车数量查询
    	$num=D('Gley')->count('id');
    	$this->assign('glexnum',$num);
    }

    /**
     * 系统首页
     */
    public function goodlist(){
        $this -> display();
    }
}
