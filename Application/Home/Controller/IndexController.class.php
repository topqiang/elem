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
    }

    /**
     * 系统首页
     */
    public function goodlist(){
        $list = D("Toppic") -> select();
        $this -> assign("piclist",$list);

        $sid = 1;
        session("sid",$sid);
        $shop = M("Shop") -> where(array("id" => $sid)) -> find();
        $where['sid'] = $sid;
        $where['status'] = array('neq',9);
        $res = M("Goods") -> where($where) -> select();
        if ( isset($res) ) {
            $this -> assign('shop',$shop);
            $this -> assign('goods',$res);
        }
        $this -> display();
    }

    public function result(){
        $this -> display();
    }

    public function _empty(){
        $this -> redirect("goodlist");
    }
}
