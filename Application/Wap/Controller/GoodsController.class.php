<?php
namespace Wap\Controller;
use Think\Controller;
/**
* 
*/
class GoodsController extends BaseController{
	
	function _initialize(){
		parent::_initialize();
		$this -> id = session("shopid");
	}

	public function goodlist(){
		$where['sid'] = $this -> id;
		$where['status'] = array('neq',9);
		$res = M('Goods') -> where($where) -> select();
		$this -> assign("goods",$res);
		$this -> display("Goods/goodlist");
	}

	public function addgood(){
		$goodname = $_POST['goodname'];
		if (isset($goodname)) {
			$gooddesc = $_POST['gooddesc'];
			$goodprice = $_POST['goodprice'];
			$upload = $this -> upload("goodpic","goodpic");
			$data = array(
				"goodname" 		=> 	$goodname,
				"gooddesc" 		=> 	$gooddesc,
				"price" 		=> 	$goodprice,
				"createtime" 	=> 	time(),
				"updatetime" 	=> 	time(),
				"status" 		=> 	"0",
				"sid" 			=> 	$this->id
				);
			if ( $upload['flag'] == "success") {
				$data['goodpic'] = 'Uploads/goodpic/'.$upload['result'];
			}
			$res = M("Goods") -> add( $data );
			if ( isset($res) ) {
				$this -> redirect("Goods/goodlist");
			}else{
				$this -> assign("msg","添加失败！");
				$this -> display();
			}

		}else{
			$this -> display();
		}
	}


	public function edigood(){
		$id = $_REQUEST['id'];
		$goodname = $_POST['goodname'];
		if (isset($goodname)) {
			$gooddesc = $_POST['gooddesc'];
			$goodprice = $_POST['goodprice'];
			$id = $_POST['id'];
			$upload = $this -> upload("goodpic","goodpic");
			$data = array(
				"id"			=> 	$id,
				"goodname" 		=> 	$goodname,
				"gooddesc" 		=> 	$gooddesc,
				"price" 		=> 	$goodprice,
				"updatetime" 	=> 	time()
				);
			if ( $upload['flag'] == "success") {
				$data['goodpic'] = 'Uploads/goodpic/'.$upload['result'];
			}
			$res = M("Goods") -> save( $data );
			if ( isset($res) ) {
				$this -> redirect("Goods/goodlist");
			}else{
				$this -> assign("msg","修改失败！");
			}
		}else{
			$where['id'] = $id;
			$where['status'] = array('neq',9);
			$res = M("Goods") -> where( $where ) -> find();
			$this -> assign("good",$res);
			$this -> display();
		}
	}



	public function delgood(){
		$id = $_GET['id'];
		$data = array(
			'id' => $id,
			'status' => 9
			);
		$res = M("Goods") -> save($data);
		if (isset($res)) {
			$this -> redirect("Goods/goodlist");
		}
	}

	/**
	 * 处理商品图片上传
	 */
	public function upload($goodname){
		if(empty($_FILES[$goodname]['name'])){
			$is_upload=false;
		}else{
			$is_upload=true;
		}
		/*foreach($_FILES['pic']['name'] as $k=>$v){
			if(!empty($v))$is_upload=true;
		}*/
		if($is_upload){
            //load("@.function.php");
			$upload_res=$this->uploadThemeImg($goodname);
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