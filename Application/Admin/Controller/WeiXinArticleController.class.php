<?php
namespace Admin\Controller;
use Think\Controller;

class WeiXinArticleController extends Controller{

    public $weixin = '';
    public $token = '';
    public $menu = '';
    public $access_token = '';
    public function _initialize(){
        $this->weixin = D('WeiXinArticle');
        $this->token = D('Token');
        $this->menu = D('Menu');
        $this->desc = D('desc');//桃花签
        $this->access_token = D('AccessToken');
        header("Content-type: text/html; charset=utf-8");
    }
    /**
     * 页面
     */
    public function backType(){
        if($_GET['back_type'] == 'FollowBack'){
            $follow_back = $this->weixin->findWeiXinArticle(array('wxa_type'=>1));
            $this->assign('follow_back',$follow_back);
            $this->display('followBack');
        }elseif($_GET['back_type'] == 'TextBack'){
            $text_back = $this->weixin->searchWeiXinArticle(array('wxa_type'=>2),'ctime desc',13);
            foreach($text_back['list'] as $key=>$value){
                $text_back['list'][$key]['wxa_description'] = mb_substr(filterHtml($value['wxa_description']),0,30,'utf-8');
            }
            $this->assign('page',$text_back['page_info']);
            $this->assign('text_back_list',$text_back['list']);
            $this->display('textBack');
        }elseif($_GET['back_type'] == 'PicTextBack'){
            $pic_text_back = $this->weixin->searchWeiXinArticle(array('wxa_type'=>3),'wxa_sort desc',13);
            foreach($pic_text_back['list'] as $key=>$value){
                $text_back['list'][$key]['wxa_description'] = mb_substr(filterHtml($value['wxa_description']),0,30,'utf-8');
            }
            $this->assign('page',$pic_text_back['page_info']);
            $this->assign('pictext_back_list',$pic_text_back['list']);
            $this->display('picTextBack');
        }elseif($_GET['back_type'] == 'thQian'){
            $descarr = $this->desc->select();
            $this->assign('descarr',$descarr);
            $this->display('qianBack');
        }else{
            $none_answer_back = $this->weixin->findWeiXinArticle(array('wxa_type'=>0));
            $this->assign('none_answer_back',$none_answer_back);
            $this->display('noneAnswerBack');
        }
    }
     /**
    *获取编辑签名对象的信息
    */
    public function delDesc(){
        $data['id']=$_GET['id'];
        $edit_res=$this->desc->delete($data['id']);
       if ($edit_res) {
            $this->success('删除成功',U('WeiXinArticle/backType', array('back_type' => 'thQian')));
        }else{
            $this->error('删除失败');
        }
    }
     /**
    *获取编辑签名对象的信息
    */
    public function editDesc(){
        $data['id']=$_GET['id'];
        $edit_res=$this->desc->where($data)->select();
        // dump($edit_res);
        // exit;
        if (count($edit_res)>0) {
            $this->assign("desc",$edit_res[0]);
            $this->display();
        }else{
            $this->error('不存在该数据',U('WeiXinArticle/backType', array('back_type' => 'thQian')));
        }
    }
    /**
    *添加抽签信息
    */
    public function addQianBack(){
        $data['qname']=$_POST['qname'];
        $data['keywords']=$_POST['keywords'];
        $data['text']=$_POST['text'];
        $data['weight']=$_POST['weight'];
        $edit_res=$this->desc->add($data);
        if ($edit_res) {
            $this->success('保存成功',U('WeiXinArticle/backType', array('back_type' => 'thQian')));
        }else{
            $this->error('保存失败');
        }
    } 
    /**
    *修改抽签信息
    */
    public function editQianBack(){
        $data['id']=$_POST['id'];
        $data['qname']=$_POST['qname'];
        $data['keywords']=$_POST['keywords'];
        $data['text']=$_POST['text'];
        $data['weight']=$_POST['weight'];
        $edit_res=$this->desc->save($data);
        if ($edit_res) {
            $this->success('修改成功',U('WeiXinArticle/backType', array('back_type' => 'thQian')));
        }else{
            $this->error('修改失败');
        }
    }
    /**
     * 添加关注时回复
     */
    public function addFollowBack(){
        if(empty($_POST['wxa_description'])){
            $this->error('回复内容不能为空');
        }else{
            $follow_back = $this->weixin->findWeiXinArticle(array('wxa_type'=>1));
            if($follow_back){
                $where['wxa_id'] = $follow_back['wxa_id'];
                $data['wxa_description'] = $_POST['wxa_description'];
                $data['utime'] = time();
                $edit_res = $this->weixin->editWeiXinArticle($where,$data);
                if($edit_res){
                    $this->success('保存成功',U('WeiXinArticle/backType',array('back_type'=>'FollowBack')));
                }else{
                    $this->error('保存失败');
                }
            }else{
                $data['wxa_type'] = 1;
                $data['wxa_description'] = $_POST['wxa_description'];
                $data['ctime'] = time();
                $edit_res = $this->weixin->addWeiXinArticle($data);
                if($edit_res){
                    $this->success('保存成功',U('WeiXinArticle/backType',array('back_type'=>'FollowBack')));
                }else{
                    $this->error('保存失败');
                }
            }
        }
    }
    /**
     * 添加文本回复
     */
    public function addTextBack(){
        if(empty($_POST['wxa_keywords'])){
            $this->error('关键词不能为空');
        }if(empty($_POST['wxa_description'])){
            $this->error('内容不能为空');
        }else{
            $data = $this->weixin->create();
            if($data){
                $data['wxa_type'] = 2;
                $data['ctime'] = time();
                $add_res = $this->weixin->addWeiXinArticle($data);
                if($add_res){
                    if($add_res){
                        $this->success('添加成功');
                    }else{
                        $this->error('添加失败');
                    }
                }
            }
        }
    }

    /**
     * 验证图文信息
     */
    public function checkPicTextInfo(){
        if(empty($_POST['wxa_keywords'])){
            $this->error('关键词不能为空');
        }if(empty($_POST['wxa_title'])){
            $this->error('标题不能为空');
        }if(empty($_POST['wxa_description'])){
            $this->error('简介不能为空');
        }if($_FILES['wxa_pic']['name'] == '' && empty($_POST['wxa_picurl'])){
            $this->error('请上传图片或输入图片链接');
        }if(!empty($_POST['wxa_picurl']) && !preg_match(C('NETURL'),$_POST['wxa_picurl'])){
            $this->error('图片链接格式不正确');
        }if(!empty($_POST['wxa_url']) && !preg_match(C('NETURL'),$_POST['wxa_url'])){
            $this->error('图片外链格式不正确');
        }
        if($_FILES['wxa_pic']['name'] != ''){
            $pic = $this->uploadImage();
            $data['wxa_picurl'] = $pic[0];
        }if($_FILES['wxa_pic']['name'] == '' && !empty($_POST['wxa_picurl'])){
            $data['wxa_picurl'] = $_POST['wxa_picurl'];
        }if($_FILES['wxa_pic']['name'] == '' && empty($_POST['wxa_picurl'])){
            $data['wxa_picurl'] = 'default.png';
        }
        $data['wxa_keywords'] = $_POST['wxa_keywords'];
        $data['wxa_keywords_type'] = $_POST['wxa_keywords_type'];
        $data['wxa_title'] = $_POST['wxa_title'];
        $data['wxa_description'] = $_POST['wxa_description'];
        $data['wxa_url'] = $_POST['wxa_url'];
        $data['wxa_sort'] = $_POST['wxa_sort'];
        return $data;
    }
    /**
     * 添加图文回复
     */
    public function addPicTextBack(){
        $data = $this->checkPicTextInfo();
        $data['wxa_type'] = 3;
        $data['ctime'] = time();
        $add_res = $this->weixin->addWeiXinArticle($data);
        if($add_res){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
    /**
     * 上传图片
     */
    public function uploadImage(){
        load("@.uploadfile");
        $save_path = "./Uploads/wxaImg/".date('Ym')."/";
        $upload_info = getUpLoadFiles('',$save_path,'','','','','',$is_thumb=false);
        if(count($upload_info[0])<=1){
            $this->error($upload_info);
        }else{
            foreach($upload_info as $value){
                $pic[] = date('Ym')."/".$value['savename'];
            }
        }
        return $pic;
    }
    /**
     * 编辑文本回复
     */
    public function editTextBack(){
        if(empty($_POST)){
            $where['wxa_id'] = $_GET['wxa_id'];
            $result = $this->weixin->findWeiXinArticle($where);
            $this->assign('text_back',$result);
            $this->display('editTextBack');
        }else{
            if(empty($_POST['wxa_keywords'])){
                $this->error('关键词不能为空');
            }if(empty($_POST['wxa_description'])){
                $this->error('内容不能为空');
            }else{
                $data = $this->weixin->create();
                if($data){
                    unset($data['wxa_id']);
                    $where['wxa_id'] = $_POST['wxa_id'];
                    $data['utime'] = time();
                    $edit_res = $this->weixin->editWeiXinArticle($where,$data);
                    if($edit_res){
                        $this->success('修改成功');
                    }else{
                        $this->error('修改失败');
                    }
                }
            }
        }
    }
    /**
     * 编辑图文回复
     */
    public function editPicTextBack(){
        if(empty($_POST)){
            $where['wxa_id'] = $_GET['wxa_id'];
            $result = $this->weixin->findWeiXinArticle($where);
            if(!strpos($result['wxa_picurl'], '://')){
                $result['wxa_picurl'] = C('API_URL').'/Uploads/wxaImg/'.$result['wxa_picurl'];
            }
            $this->assign('pic_text_back',$result);
            $this->display('editPicTextBack');
        }else{
            $where['wxa_id'] = $_POST['wxa_id'];
            $data = $this->checkPicTextInfo();
            $data['utime'] = time();
            $edit_res = $this->weixin->editWeiXinArticle($where,$data);
            if($edit_res){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }
    /**
     * 空回答时回复
     */
    public function editNoneAnswerBack(){
        if(empty($_POST['wxa_keywords']) && empty($_POST['wxa_description'])){
            $this->error('文字内容或关键词必须填写一项');
        }else{
            $none_answer_back = $this->weixin->findWeiXinArticle(array('wxa_type'=>0));
            if($none_answer_back){
                $where['wxa_id'] = $none_answer_back['wxa_id'];
                $data['wxa_keywords'] = $_POST['wxa_keywords'];
                $data['wxa_description'] = $_POST['wxa_description'];
                $data['utime'] = time();
                $edit_res = $this->weixin->editWeiXinArticle($where,$data);
                if($edit_res){
                    $this->success('保存成功',U('WeiXinArticle/backType',array('back_type'=>'noneBack')));
                }else{
                    $this->error('保存失败');
                }
            }else{
                $data['wxa_type'] = 0;
                $data['wxa_keywords'] = $_POST['wxa_keywords'];
                $data['wxa_description'] = $_POST['wxa_description'];
                $data['ctime'] = time();
                $edit_res = $this->weixin->addWeiXinArticle($data);
                if($edit_res){
                    $this->success('保存成功',U('WeiXinArticle/backType',array('back_type'=>'noneBack')));
                }else{
                    $this->error('保存失败');
                }
            }
        }
    }
    /**
     * 删除
     */
    public function delWeiXinArticle(){
        $where['wxa_id'] = $_GET['wxa_id'];
        $result = $this->weixin->deleteWeiXinArticle($where);
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 添加菜单
     */
    public function createMenu(){
        if(empty($_POST)){
            $menu_list = $this->menu->searchMenu('','sort desc','');
            $sel_menu_list = $this->menu->searchMenu(array('parent_id'=>0),'sort desc','');
            foreach($menu_list as $key=>$value){
                if($value['parent_id'] != 0){
                    $menu_list[$key]['parent_id'] = $this->menu->where(array('id'=>$value['parent_id']))->getField('name');
                }
            }
            $this->assign('menu_list',$menu_list);
            $this->assign('sel_menu_list',$sel_menu_list);
            $acs_token = $this->access_token->find();
            $this->assign('access_token',$acs_token);
            $this->display('createMenu');
        }else{
            if(empty($_POST['name'])){
                $this->error('菜单名称必填');
            }if(empty($_POST['keywords']) && empty($_POST['url'])){
                $this->error('关联关键字和外链URL选填');
            }if(!empty($_POST['url']) && !preg_match(C('NETURL'),$_POST['url'])){
                $this->error('外链URL格式不正确');
            }else{
                $count = $this->menu->where(array('parent_id'=>$_POST['parent_id']))->count();
                if($_POST['parent_id'] == 0){
                    if($count == 3){
                        $this->error('一级菜单最多可以创建三个');
                    }
                }else{
                    if($count == 5){
                        $this->error('二级菜单最多可以创建五个');
                    }
                }
                $data = $this->menu->create();
                if($data){
                    $data['ctime'] = time();
                    $result = $this->menu->addMenu($data);
                    if($result){
                        $this->success('保存成功');
                    }else{
                        $this->error('保存失败');
                    }
                }else{
                    $this->error('创建数据对象失败');
                }
            }
        }
    }
    /**
     * 编辑菜单
     */
    public function editMenu(){
        if(empty($_POST)){
            $sel_menu_list = $this->menu->searchMenu(array('parent_id'=>0),'sort desc','');
            $this->assign('sel_menu_list',$sel_menu_list);
            $menu_res = $this->menu->findMenu(array('id'=>$_GET['id']));
            $this->assign('menu_info',$menu_res);
            $this->display('editMenu');
        }else{
            if(empty($_POST['name'])){
                $this->error('菜单名称必填');
            }if(empty($_POST['keywords']) && empty($_POST['url'])){
                $this->error('关联关键字和外链URL选填');
            }if(!empty($_POST['url']) && !preg_match(C('NETURL'),$_POST['url'])){
                $this->error('外链URL格式不正确');
            }else{
                $data = $this->menu->create();
                if($data){
                    $where['id'] = $_POST['id'];
                    $data['ctime'] = time();
                    $result = $this->menu->editMenu($where,$data);
                    if($result){
                        $this->success('修改成功');
                    }else{
                        $this->error('修改失败');
                    }
                }else{
                    $this->error('创建数据对象失败');
                }
            }
        }
    }
    /**
     * 创建菜单
     */
    public function doCreateMenu(){
        if(empty($_POST['AppId'])){
            $this->error('AppId必填');
        }if(empty($_POST['AppSecret'])){
            $this->error('AppSecret必填');
        }
        $data = $this->getData();
        if($data == ''){
            $this->error('您还没有创建任何菜单');
        }
        $acs_token = $this->access_token->find();
        if(empty($acs_token) || (time()-$acs_token['ctime']) > 7200){
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".trim($_POST['AppId'])."&secret=".trim($_POST['AppSecret']);
                $access_token_obj = file_get_contents($url);
                $access_token_obj = json_decode($access_token_obj);
                if(empty($access_token_obj->errcode)){
                    $access_token = $access_token_obj->access_token;
                    $this->addAccessToken($acs_token,$access_token);
                    $result = $this->createMenuApi($data,$access_token);
                    $result = json_decode($result);
                    if($result->errcode == 0){
                        $this->success('创建菜单成功，需重新关注或隔天才能看到效果','',3);
                    }else{
                        $this->error('创建菜单失败，稍后请重试');
                    }
                }else{
                    $this->error('获取ACCESS_TOKEN失败，检查您的AppId或AppSecret是否正确');
                }
        }else{
            $result = $this->createMenuApi($data,$acs_token['access_token']);
            $result = json_decode($result);
            if($result->errcode == 0){
                $this->success('创建菜单成功，需重新关注或隔天才能看到效果','',3);
            }else{
                $this->error('创建菜单失败，稍后请重试');
            }
        }
    }

    /**
     * 添加access_token记录
     */
    public function addAccessToken($acs_token,$access_token){
        if(empty($acs_token)){
            $data['app_id'] = $_POST['AppId'];
            $data['app_secret'] = $_POST['AppSecret'];
            $data['access_token'] = $access_token;
            $this->access_token->addAccessToken($data);
        }else{
            $where['at_id'] = 1;
            $data['app_id'] = $_POST['AppId'];
            $data['app_secret'] = $_POST['AppSecret'];
            $data['access_token'] = $access_token;
            $data['ctime'] = time();
            $this->access_token->editAccessToken($where,$data);
        }
    }

    /**
     * 获取数据
     */
    public function getData(){
        $data = '{"button" : [';
        $root_menu_list = $this->menu->searchMenu(array('parent_id'=>0),'sort desc','');
        if($root_menu_list){
            foreach($root_menu_list as $key1=>$root){
                $son_menu_list = $this->menu->searchMenu(array('parent_id'=>$root['id']),'sort desc','');
                if($son_menu_list){//二级分类
                    $data .= '{"name" : "'.$root['name'].'","sub_button" : [';
                    foreach($son_menu_list as $key2=>$son){
                        if(!empty($son['url'])){
                            $data .= '{
                                          "type" : "view",
                                          "name" : "'.$son['name'].'",
                                          "url"  : "'.$son['url'].'"
                                       }';
                        }if(empty($son['url']) && !empty($son['keywords'])){
                            $data .= '{
                                          "type" : "click",
                                          "name" : "'.$son['name'].'",
                                          "key"  : "'.$son['keywords'].'"
                                       }';
                        }
                        if(count($son_menu_list)-$key2 != 1){
                            $data .= ",";
                        }
                    }
                    $data .= "]}";
                    if(count($root_menu_list)-$key1 != 1){
                        $data .= ",";
                    }
                }else{
                    if(!empty($root['url'])){
                        $data .= '{
                                      "type" : "view",
                                      "name" : "'.$root['name'].'",
                                      "url"  : "'.$root['url'].'"
                                   }';
                    }if(empty($root['url']) && !empty($root['keywords'])){
                        $data .= '{
                                      "type" : "click",
                                      "name" : "'.$root['name'].'",
                                      "key"  : "'.$root['keywords'].'"
                                   }';
                    }
                    if(count($root_menu_list)-$key1 != 1){
                        $data .= ",";
                    }
                }
            }
            return $data."]}";
        }else{
            return  '';
        }
    }

    /**
     * 创建菜单方法
     */
    public function createMenuApi($data,$access_token){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $tmpInfo;
    }

    /**
     * 删除菜单
     */
    public function delMenu(){
        $where['id'] = $_GET['id'];
        $result = $this->menu->deleteMenu($where);
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


    /**
     * api
     */
    public function api(){
        $result = $this->token->find();
        if($result){
            $this->assign('token',$result);
        }else{
            $api_url = $this->getApiUrl();
            $token = $this->getToken();
            $api_url = $api_url.$token;
            $data['token'] = $token;
            $data['api_url'] = $api_url;
            $data['ctime'] = time();
            $this->token->addToken($data);
            $this->assign('token',array('token'=>$token,'api_url'=>$api_url));
        }
        $this->display('api');
    }
    /**
     * 获取token
     */
    public function getToken(){
        $num_array = array(1,2,3,4,5,6,7,8,9,0,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $token = '';
        for($j=0;$j<10;$j++){
            $i = rand(0,35);
            $token .= $num_array[$i];
        }
        return $token;
    }
    /**
     * 获取api_url
     */
    public function getApiUrl(){
        $api_url = 'http://';
        $server_ip = '您的域名';
        $api_url = $api_url.$server_ip.'/index.php/Api/WeiXin/responseMsg/';
        return $api_url;
    }
}