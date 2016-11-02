<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * Class GroupController
 * @package Admin\Controller
 */
class GroupController extends AdminBasicController{
	public $group;
	public function _initialize(){
		parent::checkAuth('Group');
		$this->group=D('Admin_group');
	}
	
	public function groupList(){
		$list=$this->group->select();
		foreach($list as $k=>$v){
			$list[$k]['act_list']=unserialize($v['act_list']);
		}
		$act_list=$this->actList();
		$this->assign('list',$list);
		$this->assign('act_list',$act_list);
		$this->display('groupList');
	}
	public function groupAdd(){
		if(empty($_POST)){
			$act_list=$this->actList();
			$this->assign('act_list',$act_list);
			$this->display('groupAdd');
		}else{
			$this->checkData();
			$data=$this->group->create();
			$data['act_list']=serialize($data['act_list']);
			$res=$this->group->add($data);
			if($res){
				$this->success('添加分组成功',U('Group/groupList'));
			}else{
				$this->error('添加分组失败');
			}
		}
	}
	public function groupEdit(){
		if(empty($_GET['g_id']))$this->error('没有分组id');
		if(empty($_POST)){
			$this->assign('g_id',$_GET['g_id']);
			$info=$this->group->where(array('g_id'=>$_GET['g_id']))->find();
			$info['act_list']=unserialize($info['act_list']);
			$this->assign('info',$info);
			$act_list=$this->actList();
			$this->assign('act_list',$act_list);
			$this->display('groupEdit');
		}else{
			$this->checkData();
			$data=$this->group->create();
			$data['act_list']=serialize($data['act_list']);
			$res=$this->group->where(array('g_id'=>$_GET['g_id']))->save($data);
			if($res){
				$this->success('修改成功',U('Group/groupList'));
			}else{
				$this->error('修改失败');
			}
		}
	}
	public function groupDel(){
		if(empty($_GET['g_id']))$this->error('没有分组id');
		//删除分组
		$res=$this->group->where(array('g_id'=>$_GET['g_id']))->delete();
		if($res){
			$obj=D('Basic');
			$list=$obj->basicList('admin',array('g_id'=>$_GET['g_id']));
			if(!empty($list)){//判断该分组下是否有管理员
				$change_res=$obj->basicSave('admin',array('g_id'=>$_GET['g_id']),array('g_id'=>0));
				if(!$change_res)$this->error('管理员转移失败');
			}
			$this->success('操作成功',U('Group/groupList'));
		}else{
			$this->error('分组删除失败');
		}
	}
	
	/**---------本类公共方法---------**/

	/**
	 * 检查表单数据
	 */
	public function checkData(){
		if(empty($_POST['name']))$this->error('没有分组名称');
		if(empty($_POST['act_list']))$this->error('该分组不能进行任何操作');
	}
	
	/**获得动作列表
	 * @return mixed
	 */
	public function actList(){
		$obj=D('Admin_action');
		$list=$obj->select();
		foreach($list as $k=>$v){
			$arr[$v['act_id']]=$v['name'];
		}
		return $arr;
	}
}
