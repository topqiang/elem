<?php
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