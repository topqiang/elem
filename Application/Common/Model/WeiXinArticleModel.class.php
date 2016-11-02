<?php
namespace Common\Model;
use Think\Model;
/**
 * Class BasicModel
 * @package Admin\Controller
 */
class WeiXinArticleModel extends Model{
    protected $tableName = 'weixin_article';
    /**
     * 查询多条数据
     */
    public function searchWeiXinArticle($where,$order,$page_size,$parameter=array()){
        if($where['status']==''|| empty($where['status'])){
            $where['status'] = array('neq',9);
        }if($page_size == ''){
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
    public function addWeiXinArticle($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 查询一条数据
     */
    public function findWeiXinArticle($where){
        if($where['status'] == '' || empty($where['status'])){
            $where['status'] = array('neq','9');
        }
        $result = $this->where($where)->find();
        return $result;
    }
    /**
     * 编辑
     */
    public function editWeiXinArticle($where,$data){
        $result = $this->where($where)->data($data)->save();
        return $result;
    }
    /**
     * 删除
     */
    public function deleteWeiXinArticle($where){
        $result = $this->where($where)->delete();
        return $result;
    }
}