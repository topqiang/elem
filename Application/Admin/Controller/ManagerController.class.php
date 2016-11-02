<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-3-15
 * Time: 下午5:13
 * To change this template use File | Settings | File Templates.
 */
namespace Admin\Controller;
use Think\Controller;

/**
 * Class LoginController
 * @package Admin\Controller
 * post数据控件name
 * 验证码:verify
 * 新密码:newpassword
 * 重复密码:repassword
 */
class ManagerController extends Controller{
	public $uid,$uname,$tname,$password;
	public function _initialize(){
		//数据库字段名
		$this->uid='aid';
		$this->uname='account';
		$this->password='password';
		//管理员表名
		$this->tname='Admin';
	}
	public function login(){
		if(empty($_POST)){
			if(!empty($_SESSION[$this->uid]))redirect(U('Index/index'));
			$this->display('login');
		}else{
			//验证数据
			$check_arr=array(
				$this->uname	=>'用户名,no,,',
				$this->password	=>'密码,no,,',
				'verify'		=>'验证码,no,,',
			);
			$check_res=checkData($check_arr,$_POST);
			if($check_res['flag']=='error')$this->error($check_res['message']);
			//检查验证码
			if(!$this->check_verify($_POST['verify']))$this->error('验证码错误');
			//验证用户是否存在
			$obj=D('Basic');
			$info=$obj->basicInfo($this->tname,array($this->uname=>$_POST[$this->uname]));
			if(empty($info))$this->error('用户不存在');
			//验证密码
			if($info[$this->password]!=md5($_POST[$this->password])){
				$this->error('密码错误');
			}else{
				$_SESSION[$this->uid]=$info[$this->uid];
				$_SESSION[$this->uname]=$info[$this->uname];
                $_SESSION['g_id']=$info['g_id'];
				$this->success('登录成功',U('Index/index'),1);
			}
		}
	}
	public function logout(){
		unset($_SESSION[$this->uid]);
		unset($_SESSION[$this->uname]);
		redirect(U('Manager/login'));
	}
	public function editPwd(){
		if(empty($_SESSION[$this->uid]))$this->error('请登录',U('Manager/login'));
		if(empty($_POST)){
			$this->display('editPwd');
		}else{
			if(empty($_POST[$this->password])||empty($_POST['newpassword'])||empty($_POST['repassword']))$this->error('密码为空');
			$obj=D($this->tname);
			if(md5($_POST[$this->password])!=$obj->where(array($this->uid=>$_SESSION[$this->uid]))->getField($this->password))$this->error('密码错误');
			if($_POST['newpassword']==$_POST[$this->password])$this->error('新密码与原密码相同');
			if($_POST['newpassword']!=$_POST['repassword'])$this->error('两次输入密码不一致');
			if($obj->where(array($this->uid=>$_SESSION[$this->uid]))->save(array($this->password=>md5($_POST['newpassword'])))){
				$this->success('密码修改成功');
			}else{
				$this->error('密码修改失败');
			}
		}
	}

	/**
	 * 生成验证码
	 */
	public function verify(){
		$Verify=new \Think\Verify();
		$Verify->entry();
	}

	/**验证码检验
	 * @param $code
	 * @param string $id
	 *
	 * @return bool
	 */
	public function check_verify($code,$id=''){
		$verify=new \Think\Verify();
		return $verify->check($code,$id);
	}
}
