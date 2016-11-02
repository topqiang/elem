<?php
namespace Api\Controller;
use Think\Controller;
/**
 * Class WeiXinController
 * 自动回复类
 */
class WeiXinController extends Controller {

    public $weixinart = '';
    public $token = '';
    public function _initialize(){
        $this->weixinart = D('WeiXinArticle');
        $this->token = D('Token');
    }

    public function valid(){
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj -> FromUserName;   //消息的来源
            $toUsername = $postObj -> ToUserName;       //消息往哪个公共平台发送
            $eventType = $postObj -> Event;             //事件的类型 CLICK-subscribe-unsubscribe
            $key = $postObj -> EventKey;                //点击事件的键值
            $msgType = $postObj->MsgType;				//image-location-image-link-event-music-news
            $keyWord = trim($postObj -> Content);       //用户发送的信息
            $time = time();

            if(!empty($keyWord)){
                $this->sendMsg($keyWord,$fromUsername,$toUsername,$time);
            }

            //关注时
            if($eventType == 'subscribe'){
                $where['wxa_type'] = 1;
                $result = $this->weixinart->findWeiXinArticle($where);
                if($result){
                    $tpl = $this->textTpl($result);
                    $resultStr = sprintf($tpl, $fromUsername, $toUsername, $time);
                    echo $resultStr;
                }
            }
            //点击事件
            if($eventType == 'CLICK'){
                $this->sendMsg($key,$fromUsername,$toUsername,$time);
            }
        }else{
            $echoStr = $_GET["echostr"];
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }
    }
    /**
     * 图文模板
     */
    public function picTextTpl($pic_text_back){
        $count = count($pic_text_back);
        $tpl_head = "<xml>
                     <ToUserName><![CDATA[%s]]></ToUserName>
                     <FromUserName><![CDATA[%s]]></FromUserName>
                     <CreateTime>%s</CreateTime>
                     <MsgType><![CDATA[news]]></MsgType>
                     <ArticleCount>$count</ArticleCount>
                     <Articles>";
        $tpl_content = '';
        foreach($pic_text_back as $value){
            $tpl_content .= "<item>
                                 <Title><![CDATA[".$value["wxa_title"]."]]></Title>
                                 <Description><![CDATA[".$value['wxa_description']."]]></Description>
                                 <PicUrl><![CDATA[".$value['wxa_picurl']."]]></PicUrl>
                                 <Url><![CDATA[".$value['wxa_url']."]]></Url>
                             </item>";
        }
        $tpl_foot = "</Articles>
                     <FuncFlag>1</FuncFlag>
                     </xml>";
        return $tpl_head.$tpl_content.$tpl_foot;
    }
    /**
     * 文本模板
     */
    public function textTpl($text){
        $tpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[".$text['wxa_description']."]]></Content>
                    <FuncFlag>0</FuncFlag>
                </xml>";
        return $tpl;
    }

    /**
     * 关键词查询
     */
    public function searchByKey($keywords){
        $order = 'wxa_sort desc';
        $where1['wxa_type'] = 2;$where1['wxa_keywords_type'] = 1;$where1['wxa_keywords'] = $keywords;
        $result = $this->weixinart->findWeiXinArticle($where1);
        if($result){
            return $result;
        }else{
            $where2['wxa_type'] = 2;$where2['wxa_keywords_type'] = 0;$where2['wxa_keywords'] = array('LIKE',"%".$keywords."%");
            $result = $this->weixinart->findWeiXinArticle($where2);
            if($result){
                return $result;
            }else{
                $where3['wxa_type'] = 3;$where3['wxa_keywords_type'] = 1;$where3['wxa_keywords'] = $keywords;
                $result = $this->weixinart->searchWeiXinArticle($where3,$order,8);
                if($result['list']){
                    return $result['list'];
                }else{
                    $where4['wxa_type'] = 3;$where4['wxa_keywords_type'] = 0;$where4['wxa_keywords'] = array('LIKE',"%".$keywords."%");
                    $result = $this->weixinart->searchWeiXinArticle($where4,$order,8);
                    if($result['list']){
                        return $result['list'];
                    }else{
                        $where['wxa_type'] = 0;
                        $result = $this->weixinart->findWeiXinArticle($where);
                        if($result){
                            if(empty($result['wxa_keywords']) && !empty($result['wxa_description'])){
                                return $result;
                            }elseif(!empty($result['wxa_keywords'])){
                                $where['wxa_type'] = 3;$where['wxa_keywords_type'] = 1;$where['wxa_keywords'] = $result['wxa_keywords'];
                                $result = $this->weixinart->searchWeiXinArticle($where,'wxa_sort desc',8);
                                if($result['list']){
                                    foreach($result['list'] as $key=>$value){
                                        if(!strpos($value['wxa_picurl'], '://')){
                                            $result['list'][$key]['wxa_picurl'] = C('API_URL').'/Uploads/wxaImg/'.$value['wxa_picurl'];
                                        }
                                    }
                                    return $result['list'];
                                }else{
                                    return null;
                                }
                            }
                        }else{
                            return null;
                        }
                    }
                }
            }
        }
    }

    public function sendMsg($keyWord,$fromUsername,$toUsername,$time){
        $result = $this->searchByKey($keyWord);
        if(!empty($result)){
            if(empty($result[0])){
                $tpl = $this->textTpl($result);
            }else{
                foreach($result as $key=>$value){
                    if(!strpos($value['wxa_picurl'], '://')){
                        $result[$key]['wxa_picurl'] = C('API_URL').'/Uploads/wxaImg/'.$value['wxa_picurl'];
                    }
                }
                $tpl = $this->picTextTpl($result);
            }
            $resultStr = sprintf($tpl, $fromUsername, $toUsername,$time);
            echo $resultStr;
        }else{
            //识别不到关键词，转发到多客服、
            $tmp = "<xml>
                         <ToUserName><![CDATA[%s]]></ToUserName>
                         <FromUserName><![CDATA[%s]]></FromUserName>
                         <CreateTime>%s</CreateTime>
                         <MsgType><![CDATA[transfer_customer_service]]></MsgType>
                     </xml>";
            $resultStr = sprintf($tmp,$fromUsername, $toUsername,$time,"xiaoqiang0672");
            echo $resultStr;
        }
    }

    private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token_obj = $this->token->find();
        $token = $token_obj['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}