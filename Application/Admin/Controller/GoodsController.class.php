<?php
namespace Admin\Controller;
use Think\Controller;
class GoodsController extends Controller{
	public function _initialize(){
		$this->goods=D("Goods");
	}

	public function goodsList(){
		
		if(!empty($_POST['name']))$where['gname']=array('like','%'.$_POST['name'].'%');
		if($_POST['cate_id']!=99 and !empty($_POST['cate_id'])){
			$where['gcate_id'] = $_POST['cate_id'];
			// dump($where);
			// exit();
		}
		$where['cstatus'] = array('neq' , '9');
		$count = $this -> goods -> where($where) -> count();
		$page = new \Think\Page($count,15);
		$res=$this -> goods -> where($where) -> limit($page->firstRow,$page->listRows) -> select();
		$this->assign('list',$res);
		$this->assign('page',$page->show());
		$this->display('goodsList');
	}

	public function goodsAdd(){
		if(empty($_POST)){
			//获得分类列表
			$cate_obj=D('Gcate');
			$cate_list=$cate_obj-> where(array('id' => array('not in','5,6,7'),'status' => array('neq','9')))->select();
			$this->assign('cate_list',$cate_list);
			$this->display('goodsAdd');
		}else{
			$upload_res=$this->upload();
			if($upload_res['flag']=='no')$this->error('没有商品图片');
			//存储数据
			$data=array(
				'name'			=>$_POST['name'],
				'cate_id'		=>$_POST['cate_id'],
				'constituent'	=>$_POST['constituent'],
				'pic'			=>"Uploads/goods/".$upload_res['result'],
				'price'			=>$_POST['price'],
				'create_time'	=>time(),
				'update_time'	=>time(),
				'remark'		=>$_POST['remark'],
				'zannum'		=>$_POST['zannum'],
				'unzannum'		=>$_POST['unzannum'],
				'status'		=>0,
			);
			$res=$this->goods->add($data);
			if($res){
				$this->success('添加成功',U('Goods/goodsList'));
			}else{
				$this->error('添加失败');
			}
		}
	}

	public function goodsEdit(){
		if(empty($_GET['id']))$this->error('没有商品id');
		if(empty($_POST)){
			$this->assign('id',$_GET['id']);
			$info=$this->goodcate->where(array('gid'=>$_GET['id']))->select();
			$this->assign('info',$info[0]);
			//获得分类列表
			$cate_obj=D('Gcate');
			$cate_list=$cate_obj->select();
			$this->assign('cate_list',$cate_list);
			
			$this->display('goodsEdit');
		}else{
			$upload_res=$this->upload();
			//删除历史缩略图。。。。
			//存储数据
			$data=array(
				'id'			=>$_GET['id'],
				'name'			=>$_POST['name'],
				'constituent'	=>$_POST['constituent'],
				'cate_id'		=>$_POST['cate_id'],
				'price'			=>$_POST['price'],
				'update_time'	=>time(),
				'zannum'		=>$_POST['zannum'],
				'unzannum'		=>$_POST['unzannum'],
				'remark'		=>$_POST['remark'],
			);
			if($upload_res['flag']=='success')$data['pic']="Uploads/goods/".$upload_res['result'];
			$res=$this->goods->save($data);
			if($res){
				$this->success('修改成功',U('Goods/goodsList'));
			}else{
				$this->error('修改失败');
			}
		}
	}
	public function goodsDel(){
		if(empty($_GET['id']))$this->error('没有商品id');
		
		$res=$this->goods->save(array('id'=>$_GET['id'],'status'=>"9"));
		if($res){
			$this->success('删除成功',U('Goods/goodsList'));
		}else{
			$this->error('删除失败');
		}
	}




	/**
	 * 处理商品图片上传
	 */
	public function upload(){
		if(empty($_FILES['pic']['name'])){
			$is_upload=false;
		}else{
			$is_upload=true;
		}
		/*foreach($_FILES['pic']['name'] as $k=>$v){
			if(!empty($v))$is_upload=true;
		}*/
		if($is_upload){
            //load("@.function.php");
			$upload_res=$this->uploadThemeImg('goods');
			if(empty($upload_res['error'])){
				return array('flag'=>'success','result'=>$upload_res[0]);
			}else{
                return array('flag'=>'error','result'=>$upload_res['error']);//$this->error($upload_res['error']);
			}
		}else{
			return array('flag'=>'no');
		}
	}

    /**
     * 上传图片公共函数
     */
    function uploadThemeImg($file){

        //load("@.uploadfile");
        //include_once 'uploadfile.php';
        $save_path = "./Uploads/".$file."/".date('Ym')."/";
        //$save_path = "./Uploads/".$file."/201404/";
        $upload_info = $this->getUpLoadFiles('',$save_path,'','','200','200','');
        if(count($upload_info[0])<=1){
            return array('error'=>$upload_info);
        }else{
            foreach($upload_info as $k=>$v){
                $url_arr[]=date('Ym')."/".$v['savename'];
            }
        }
        return $url_arr;
    }



    /*
 * by king 2013年5月10日15:08:49
 * 自定义 简单上传类
 * 参数：$name-定义文件上传命名规则
 *      $url-原图保存地址
 *      $maxsize-文件最大 大小
 *      $type-上传文件类型
 *      $width-缩略图宽
 *      $height-缩略图高
 *      $thumb_pre-缩略图前坠名
 * 成功返回 上传后的信息
 * 失败返回异常名称
 * */
    function getUpLoadFiles($name,$url,$maxsize,$type,$width,$height,$thumb_pre,$is_thumb=false)
    {
        $upload = new \Think\UploadFile();
        $upload->maxSize        = !empty($maxsize)?$maxsize:20480000;
        $upload->allowExts      = is_array($type)?$type:array('jpg','png','jpeg','bmp','gif');
        $upload->savePath       = isset($url)?$url:'./Uploads'.date("Ym").'/';
        $upload->saveRule       = !empty($name)?$name:'uniqid';       //保存文件命名规则 如果不是规则的关键字 默认设为上传的文件名称

        if($is_thumb)
        {
            //生成缩略图
            $upload->thumb          = true;
            $upload->thumbPath      = isset($url)?$url:'./Uploads'.date("Ym").'/';
            $upload->thumbPrefix    = !empty($thumb_pre)?$thumb_pre:'thumb_';
            $upload->thumbMaxWidth  = $width;
            $upload->thumbMaxHeight = $height;
            $upload->uploadReplace = true;
        }
        if($upload->Upload())
        {
            $info = $upload->getUploadFileInfo();
            return $info;
        }
        else
        {
            return $upload->getErrorMsg();
        }
    }
}