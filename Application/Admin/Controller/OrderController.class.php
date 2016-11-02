<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * Class OrderController
 * @package Admin\Controller
 */
class OrderController extends AdminBasicController{
    public $ordadd;
    public $orgood;
	public function _initialize(){
        $this -> ordadd = D("Ordadd");
        $this -> orgood = D("Orgood");
	}

    public function updorder(){
        $order = $_POST['order'];
        $ord = D("Order");
        if (isset($order['getcode'])) {
            $getcode = $order['getcode'];
            $code = $ord -> field('getcode') -> where(array('id' => $order['id'])) -> select();
            if ($getcode != $code[0]['getcode']) {
                $this -> ajaxReturn( "提货码不正确！" );
                exit();
            }
        }
        $order['update_time'] = time();
        $res = $ord -> save( $order );
        if ( isset( $res ) ) {
            $this -> ajaxReturn( $res );
        }else{
            $this -> ajaxReturn( "error" );
        }
    }

    public function orderlist(){
        $type = $_GET['type'];
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : $type;
        $ordname = $_POST['ordname'];
        $addname = $_POST['addname'];
        $tel = $_POST['tel'];
        $where = array();
        if (empty($type)) {
            $where["type"] = array( 'neq' , 9 );
        }else{
            $where["type"] = array( 'eq' , $type);
            $parameter["type"] = $type;
        }
        if (isset($ordname)) {
            $where["ordname"] = array( 'like' , "%$ordname%");
        }
        if (isset($addname)) {
            $where["addname"] = array( 'like' , "%$addname%");
        }
        if (isset($tel)) {
            $where["tel"] = array( 'like' , "%$tel%");
        }
        $count = $this -> ordadd -> where($where)->count();
        $page = new \Think\Page($count,15);
        $page->parameter = $parameter;
        $res = $this -> ordadd -> where($where)->limit($page->firstRow,$page->listRows) -> order('update_time desc , type asc')-> select();
        $this -> assign("orders" , $res);
        $this -> assign("page",$page->show());
        $this -> display();
    }

    public function getinfo(){
        $oid = $_POST['oid'];
        if ( isset($oid) ) {
            $where['oid'] = $oid;
            $res = $this -> orgood -> field('name,gprice,gnum,constituent,remark') -> where($where) -> select();
            if ( isset($res) ) {
                $this -> ajaxReturn(json_encode($res));
            }else{
                $this -> ajaxReturn('error');
            }
        }
    }

    /**
    *计划任务执行方法
    */ 
    public function autoupd(){
        $time = time() - 48*60*60;
        $ord = D("Order");
        $where['type'] = 0;
        $where['update_time'] = array('lt' , $time);
        $ord -> where($where) -> save(array('type' => 9));
        $where['type'] = 2;
        $ord -> where($where) -> save(array('type' => 3));
    }
}
