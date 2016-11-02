<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * Class AdminController
 * @package Admin\Controller
 */
class AdminController extends AdminBasicController{
	public $basic;
	public function _initialize(){
		parent::checkAuth('Admin');
		$this->basic=D('Basic');
	}

	public function adminList(){
		//没有搜索
		//超级管理员不显示
		$res=$this->basic->basicList('admin',array('g_id'=>array('neq','99')),15);
		foreach($res['list'] as $k=>$v){
			$res['list'][$k]['group_name']=$this->groupName($v['g_id']);
		}
		$this->assign('list',$res['list']);
		$this->assign('page',$res['page']);
		$this->display('adminList');
	}
	public function adminAdd(){
		if(empty($_POST)){
			$group_list=$this->groupList();
			$this->assign('group_list',$group_list);
			$this->display('adminAdd');
		}else{
			if(empty($_POST['account']))$this->error('没有管理员名称');
			if(empty($_POST['password']))$this->error('没有密码');
			if(empty($_POST['g_id']))$_POST['g_id']=0;
			$data=array(
				'account'	=>$_POST['account'],
				'password'	=>md5($_POST['password']),
				'g_id'		=>$_POST['g_id'],
				'ctime'		=>time(),
				'status'	=>0,
			);
			$res=$this->basic->basicAdd('admin',$data);
			if($res){
				$this->success('添加管理员成功',U('Admin/adminList'));
			}else{
				$this->error('添加管理员失败');
			}
		}
	}
	public function adminEdit(){
		if(empty($_GET['aid']))$this->error('没有管理员id');
		if(empty($_POST)){
			$this->assign('aid',$_GET['aid']);
			$info=$this->basic->basicInfo('admin',array('aid'=>$_GET['aid']));
			$this->assign('info',$info);
			$group_list=$this->groupList();
			$this->assign('group_list',$group_list);
			$this->display('adminEdit');
		}else{
			if(empty($_POST['account']))$this->error('没有管理员名称');
			if(empty($_POST['g_id']))$_POST['g_id']=0;
			//判断昵称为重复
			$old_info=$this->basic->basicInfo('admin',array('aid'=>$_GET['aid']));
			if($old_info['account']!=$_POST['account']){
				$check_info=$this->basic->basicInfo('admin',array('account'=>$_POST['account']));
				if(!empty($check_info))$this->error('用户名已存在');
			}
			$data=array(
				'account'=>$_POST['account'],
				'g_id'=>$_POST['g_id'],
			);
			if(!empty($_POST['password']))$data['password']=md5($_POST['password']);
			$res=$this->basic->basicSave('admin',array('aid'=>$_GET['aid']),$data);
			if($res){
				$this->success('修改成功',U('Admin/adminList'));
			}else{
				$this->error('修改失败');
			}
		}
	}
	public function adminDel(){
		if(empty($_GET['aid']))$this->error('没有管理员id');
		$res=$this->basic->basicDel('admin',array('aid'=>$_GET['aid']));
		if($res){
			$this->success('操作成功',U('Admin/adminList'));
		}else{
			$this->error('分组删除失败');
		}
	}
	
	/**---------本类公共方法---------**/

	/**
	 * 检查表单数据
	 */
	public function checkData(){
		
	}

	/**
	 * @param $g_id
	 *
	 * @return mixed
	 */
	public function groupName($g_id){
		if($g_id==99){
			return '超级管理员';
		}elseif($g_id==0){
			return '初级管理员';
		}else{
			$obj=D('Admin_group');
			$name=$obj->where(array('g_id'=>$g_id))->getField('name');
			if($name){
				return $name;
			}else{
				return '分组错误';
			}
		}
	}
	public function groupList(){
		$obj=D('Admin_group');
		$list=$obj->select();
		foreach($list as $k=>$v){
			$arr[$k]['g_id']=$v['g_id'];
			$arr[$k]['name']=$v['name'];
		}
		return $arr;
	}
}
