<?php
namespace Common\Model;
use Think\Model;

/**
 * Class BasicModel
 * @package Admin\Controller
 */
class BasicModel extends Model{
	/**
	 * @param        $table_name
	 * @param array  $where
	 * @param        $num(值为空时不分页)
	 * @param string $order(默认按ctime排序，值为no时不排序)注：如果表中没有ctime字段会出错
	 *
	 * @return mixed
	 */
	public function basicList($table_name,$where=array(),$num='',$order=''){
		if(empty($table_name))return false;
		if(!isset($where['status']))$where['status']=array('neq','9');
		//if($where['status']=='')$where['status']=array('neq','9');
		if(empty($order))$order='ctime desc';
		if($order=='no')$order='';
		//if(empty($num))$num=15;
		$obj=M($table_name);
		if(empty($num)){
			$list=$obj->where($where)->order($order)->select();
		}else{
			$count=$obj->where($where)->count();
			$page=new \Think\Page($count,$num);
			$list['list']=$obj->where($where)->order($order)->limit($page->firstRow,$page->listRows)->select();
			$list['page']=$page->show();
		}
		return $list;
	}

	/**
	 * @param       $table_name
	 * @param array $where
	 *
	 * @return bool
	 */
	public function basicInfo($table_name,$where=array()){
		if(empty($table_name))return false;
		$obj=M($table_name);
		if(!isset($where['status']))$where['status']=array('neq','9');
		//if($where['status']=='')$where['status']=array('neq','9');
		return $obj->where($where)->find();
	}

	/**
	 * @param       $table_name
	 * @param array $where
	 * @param array $data
	 *
	 * @return bool
	 */
	public function basicSave($table_name,$where=array(),$data=array()){
		if(empty($table_name))return false;
		if(!isset($where['status']))$where['status']=array('neq','9');
		//if($where['status']=='')$where['status'] = array('neq','9');
		$obj=M($table_name);
		return $obj->where($where)->save($data);
	}

	/**
	 * @param $table_name
	 * @param $data
	 *
	 * @return bool
	 */
	public function basicAdd($table_name,$data){
		if(empty($table_name))return false;
		if(empty($data))return false;
		$obj=M($table_name);
		return $obj->add($data);
	}

	/**删除方法
	 * @param        $table_name
	 * @param array  $where
	 * @param string $type删除类型，默认修改status为9,如果为‘real’则彻底删除
	 *
	 * @return bool
	 */
	public function basicDel($table_name,$where=array(),$type=''){
		if(empty($table_name))return false;
		$obj=M($table_name);
		if($type=='real'){
			return $obj->where($where)->delete();
		}else{
			return $obj->where($where)->save(array('status'=>9));
		}
	}
}
