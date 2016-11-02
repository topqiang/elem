<?php
namespace Common\Model;
use Think\Model;
/**
 * Class BasicModel
 * @package Admin\Controller
 */
class MenuModel extends Model{
    protected $tableName = 'menu';
    /**
     * 查询多条数据
     */
    public function searchMenu($where,$order,$page_size,$parameter=array()){
        if($page_size == ''){
            $result = $this->where($where)->order($order)->select();
        }else{
            $count = $this->where($where)->order($order)->count();
            $page = new \Think\Page($count,$page_size);
            $page->parameter = $parameter;
            $page_info =$page->show();
            $list = $this->where($where)
                ->order($order)
                ->limit($page->firstRow,$page_size)
                ->select();
            $result = array('page_info'=>$page_info,'list'=>$list);
        }
        return $result;
    }
    /**
     * 添加商户
     */
    public function addMenu($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 查询一条数据
     */
    public function findMenu($where){
        $result = $this->where($where)->find();
        return $result;
    }
    /**
     * 编辑
     */
    public function editMenu($where,$data){
        $result = $this->where($where)->data($data)->save();
        return $result;
    }
    /**
     * 删除
     */
    public function deleteMenu($where){
        $result = $this->where($where)->delete();
        return $result;
    }
}