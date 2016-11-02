<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * Class ArticleController
 * @package Admin\Controller
 */
class ArticleController extends AdminBasicController{

    public $article = '';
    public function _initialize(){
        $this->article = D('Article');
        header("Content-type: text/html; charset=utf-8");
    }
    /**
     * 菜单列表
     */
    public function articleList(){
        $art_result = $this->article->searchArticle('','ctime desc',13);
        $this->assign('page',$art_result['page_info']);
        $this->assign('art_list',$art_result['list']);
        $this->display('articleList');
    }

    /**
     * 添加文章
     */
    public function addArticle(){
        if(empty($_POST['art_title']) || empty($_POST['art_content'])){
            $this->error('您未填写文章标题或内容');
        }
        $data['art_title'] = $_POST['art_title'];
        $data['art_content'] = $_POST['art_content'];
        $data['art_author'] = $_SESSION['account'];
        $data['ctime'] = time();
        $result = $this->article->addArticle($data);
        if($result){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
    /**
     * 编辑菜单
     */
    public function editArticle(){
        if(empty($_POST)){
            $art_info = $this->article->findArticle(array('art_id'=>$_GET['art_id']));
            $this->assign('art_info',$art_info);
            $this->display('editArticle');
        }else{
            if(empty($_POST['art_title']) || empty($_POST['art_content'])){
                $this->error('您未填写文章标题或内容');
            }
            $where['art_id'] = $_POST['art_id'];
            $data['art_title'] = $_POST['art_title'];
            $data['art_content'] = $_POST['art_content'];
            $result = $this->article->editArticle($where,$data);
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }
    /**
     * 删除菜单
     */
    public function delArticle(){
        $where['art_id'] = $_GET['art_id'];
        $result = $this->article->deleteArticle($where);
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}