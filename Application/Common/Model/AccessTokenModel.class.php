<?php
namespace Common\Model;
use Think\Model;
/**
 * Class BasicModel
 * @package Admin\Controller
 */
class AccessTokenModel extends Model{
    protected $tableName = 'access_token';
    /**
     * 添加
     */
    public function addAccessToken($data){
        $result = $this->data($data)->add();
        return $result;
    }
    /**
     * 查询一条数据
     */
    public function findAccessToken($where){
        $result = $this->where($where)->find();
        return $result;
    }
    /**
     * 编辑
     */
    public function editAccessToken($where,$data){
        $result = $this->where($where)->data($data)->save();
        return $result;
    }
    /**
     * 删除
     */
    public function deleteAccessToken($where){
        $result = $this->where($where)->delete();
        return $result;
    }
}