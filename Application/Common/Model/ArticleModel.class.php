<?php
namespace Common\Model;
use Think\Model;
/**
 * @package Admin\Controller
 */
class ArticleModel extends Model{
    protected $tableName = 'article';
    /**
     * 查询多条数据
     */
    public function searchArticle($where,$order,$page_size,$parameter=array()){
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
    public function addArticle($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 查询一条数据
     */
    public function findArticle($where){
        $result = $this->where($where)->find();
        return $result;
    }
    /**
     * 编辑
     */
    public function editArticle($where,$data){
        $result = $this->where($where)->data($data)->save();
        return $result;
    }
    /**
     * 删除
     */
    public function deleteArticle($where){
        $result = $this->where($where)->delete();
        return $result;
    }
}