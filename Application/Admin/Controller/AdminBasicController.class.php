<?php
// 本类由系统自动生成，仅供测试用途
namespace Admin\Controller;
use Think\Controller;

/**
 * Class AdminBasicController
 * @package Admin\Controller
 * 后台基类
 */
class AdminBasicController extends Controller {
	/**
	 * @param $controller控制器名称
	 *
	 * @return bool
	 */
	function checkAuth($controller){
		if(empty($controller)){
			$this->error('没有权限');
		}else{
			$keyword=$controller;
		}
		if(empty($_SESSION['aid']))redirect(U('Manager/login'));
		$admin_obj=D('Admin');
		$g_id=$admin_obj->where(array('aid'=>$_SESSION['aid']))->getField('g_id');
		//最高权限管理员
		if($g_id==99)return true;
		//最低权限，不能进行任何操作
		if($g_id==0)$this->error('没有权限');
		$act_obj=D('Admin_action');
		//操作id
		$act_id=$act_obj->where(array('keyword'=>$keyword))->getField('act_id');
		$group_obj=D('Admin_group');
		//用户组操作列表
		$act_list=$group_obj->where(array('g_id'=>$g_id))->getField('act_list');
		$act_arr=unserialize($act_list);
		if(in_array($act_id,$act_arr)){
			return true;
		}else{
			$this->error('没有权限');
		}
	}

	/**删除Url为{:U('AdminBasic/delList',array('tname'=>'Member'))}
	 * 如果表字段没有status项或者需要彻底删除记录，需要添加参数'type'为'real'
	 * url为{:U('AdminBasic/delList',array('tname'=>'Member','type'=>'real'))};
	 * 前台控件name必须为数据库id字段名称,如	name="m_id[]"
	 */
	public function delList(){
		if(empty($_GET['tname']))$this->error('没有tname');
		if(empty($_POST))$this->error('没有任何项被选中!');
		//获得键值,即数据库id字段名
		foreach($_POST as $k=>$v){
			if(is_array($v))$id_name=$k;
		}
		if(empty($id_name))$this->error('表单提交错误');
		$obj=D($_GET['tname']);
		foreach($_POST[$id_name] as $k=>$v){
			if($_GET['type']=='real'){
				$res=$obj->where(array($id_name=>$v))->delete();
			}else{
				$res=$obj->where(array($id_name=>$v))->save(array('status'=>9));
			}
			if(!$res)$error_arr[]=$v;
		}
		if(empty($error_arr)){
			$this->success('批量删除成功!');
		}else{
			$error_char='';
			foreach($error_arr as $k=>$v){
				if($error_char==''){
					$error_char=$v;
				}else{
					$error_char=$error_char.','.$v;
				}
			}
			$this->error('第'.$error_char.'项删除失败!','',5);
		}
	}
}
