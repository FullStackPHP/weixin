<?php
namespace Home\Controller;
use Think\Controller;
class WechatController extends Controller{
    private $token = "wechat";
    private $appid = "wx9934a3ee9e9d47dc";
    private $secret = "0920b5c85c85e4b57ddd296b6b8087ee";
    /**构造方法 对成员属性进行赋值操作**/
//    public function __construct($appid="wx9934a3ee9e9d47dc",$secret="0920b5c85c85e4b57ddd296b6b8087ee"){
//        $this->appid  = $appid;
//        $this->secret = $secret;
//    }
    public function weixin(){
        $echostr = $_GET['echostr'];  //随机字符串
        if(isset($_GET['echostr'])){
            $this->valid();
        }else{
            $this->responseMsg();
        }
    }

    /**验证消息**/
    public function valid(){
        if($this->checkSignature()){
            echo $_GET['echostr'];
        }else{
            echo "Error";
        }
    }
    /**校验微信加密签名**/
    public function checkSignature(){
        /**1、接受微信服务器get请求过来的4个参数**/
        $signature = $_GET['signature'];   //微信加密签名
        $timestamp = $_GET['timestamp'];   //时间戳
        $nonce     = $_GET['nonce'];       //随机数

        /**2、加密 / 校验**/
        //(1)将token、timestamp、nonce三个参数进行字典排序
        $tmpArr = array($this->token,$timestamp,$nonce);
        sort($tmpArr,SORT_STRING);         //字典序排序

        //(2)将三个参数字符串拼接成一个字符串进行sha1加密
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        //(3)开发者获得加密后的字符串与signature对比
        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }
    /**响应消息**/
    public function responseMsg(){
        //1、接受xml数据包
        $postData = $GLOBALS[HTTP_RAW_POST_DATA];
        //2、处理xml数据包(把xml字符串写入对象中)
        $xmlObj   = simplexml_load_string($postData,"SimpleXMLElement",LIBXML_NOCDATA);
        $toUserName   = $xmlObj->ToUserName;     //获取开发者微信号
        $fromUserName = $xmlObj->FromUserName;   //获取用户的openId
        $msgType      = $xmlObj->MsgType;        //消息的类型
        //3、根据消息类型进行业务处理
        switch($msgType){
            case 'event':
                //接受事件推送
                echo $this->receiveEvent($xmlObj);
                break;
            case 'text':
                //接受文本消息
                echo $this->receiveText($xmlObj);
                break;
            case 'image':
                //接受图片消息
                echo $this->receiveImage($xmlObj);
                break;
            default;
                break;
        }
    }

    /**接受消息**/
    public function receiveText($obj){
        //获取消息
        $content = trim($obj->Content);
        switch($content){
            case "akon":
                return $this->replyText($obj,"Hello");
                break;
            case "年会":
                $url = "http://www.jiaruiyi.cn/wechat/sign-in/sign.php";
                $message = '<a href="'.$url.'">签到入口</a>';
                return $this->replyText($obj,$message);
                break;
            case "Hello":
                $picArr = array("mediaId"=>"FvKRFwqfdm9ZySVdI5u4_ZiQzcRXm_rLhiFGCVLxATBjnvz6Z_SjQBo0V-o35mCi");
                return $this->replyImage($obj,$picArr);
                break;
            case "图文":
                $newsArr = array(
                    array(
                        'Title'=>"约吗？亲！",
                        'Description'=>"玩的就是免费，军哥就是这么任性！",
                        'PicUrl'=>"http://1.moocba.applinzi.com/img/yuema.jpg",
                        'Url'=>"http://www.moocba.com/article/6"
                    ),
                    array(
                        'Title'=>"大圣归来之暑期来了",
                        'Description'=>"很久很久以前… 悟空被压在五指山下打工",
                        'PicUrl'=>"http://1.moocba.applinzi.com/img/shuqi.jpg",
                        'Url'=>"http://www.moocba.com/article/8"
                    ),
                    array(
                        'Title'=>"大圣归来之暑期来了",
                        'Description'=>"很久很久以前… 悟空被压在五指山下打工",
                        'PicUrl'=>"http://1.moocba.applinzi.com/img/shuqi.jpg",
                        'Url'=>"http://www.moocba.com/article/8"
                    )
                );
                return $this->replyText($obj,$newsArr);
                break;
            default:
                return $this->replyText($obj,$content);
                break;
        }
        $this->replyText($obj,$content);
    }

    /**事件推送**/
    public function receiveEvent($obj){
        switch($obj->Event){
            //关注事件
            case 'subscribe':
                $replyContent = "关注成功，回复【年会】，获取签到入口";
                return $this->replyText($obj,$replyContent);
                break;
            //取消关注
            case 'unsubscribe':
                break;
            //菜单事件推送
            default:
                # code...
                break;
        }
    }

    /**回复文本消息**/
    public function replyText123($obj,$content){
        $replyTextMsg = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[%s]]></Content>
						</xml>";
        echo sprintf($replyTextMsg,$obj->FromUserName,$obj->ToUserName,time(),$content);
    }

    /**接收图片消息**/
    public function receiveImage($obj){
        //获取图片的url
        $pic = $obj->PicUrl;
        //获取图片消息媒体id
        $mediaId = $obj->MediaId;
        $picArr = array("picUrl"=>$pic,"mediaId"=>$mediaId);
        //回复图片消息
        $this->replyImage($obj,$picArr);
    }

    /**回复图片消息**/
    public function replyImage($obj,$array){
        $replyImageMsg = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[image]]></MsgType>
                            <Image>
                            <MediaId><![CDATA[%s]]></MediaId>
                            </Image>
                          </xml>";
        echo sprintf($replyImageMsg,$obj->FromUserName,$obj->ToUserName,time(),$array['mediaId']);
    }

    /**回复图文及文本消息**/
    public function replyText($obj,$content){
        $itemStr = "";
        //回复图文消息
        if(is_array($content)){
            foreach($content as $item){
                $itemTpl = "<item>
                                <Title><![CDATA[%s]]></Title>
                                <Description><![CDATA[%s]]></Description>
                                <PicUrl><![CDATA[%s]]></PicUrl>
                                <Url><![CDATA[%s]]></Url>
							</item>";
                $itemStr .= sprintf($itemTpl,$item['Title'],$item['Description'],$item['PicUrl'],$item['Url']);
            }
            $replyNewsMsg = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[news]]></MsgType>
                                <ArticleCount>%s</ArticleCount>
                                <Articles>".$itemStr."</Articles>
                             </xml>";
            return sprintf($replyNewsMsg,$obj->FromUserName,$obj->ToUserName,time(),count($content));
        }else{
            //回复图文消息
            $replyTextMsg = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                            </xml>";
            echo sprintf($replyTextMsg,$obj->FromUserName,$obj->ToUserName,time(),$content);
        }
    }

    /**https请求(GET和POST)**/
    public function http_request($url,$data=null){
        //1、初始化curl
        $ch = curl_init();
        //2、设置传输选项
        curl_setopt($ch,CURLOPT_URL,$url);              //get请求
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);      //将页面以文件流的形式保存
        //post请求
        if(!empty($data)){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        //3、执行
        $outopt = curl_exec($ch);
        //4、关闭
        curl_close();
        //转化成数组
        return json_decode($outopt,true);
    }

    /**获取access_token**/
    public function getAccessToken1(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=" . $this->secret;
        //通过curl获取access_token
        $result = $this->http_request($url);
        return $result['access_token'];
    }
    public function getAccessToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=" . $this->secret;
        $access_token = $this->_memcache_get("access_token2");
        if(!$access_token){
            $res = $this->http_request($url);
            $access_token = $this->_memcache_set("access_token2",$res['access_token'],0,30);
            echo $access_token;
            return $access_token;
        }
        echo $access_token . "第er次";
        return $access_token;
    }

    /********************微信网页授权*****************/
    public function show_your_passion(){
        $myUrl = "http://www.jiaruiyi.cn/tp/index.php/Home/Wechat/getUserInfo";
        $redirect_uri = urlencode($myUrl);
        //$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=". $this->appid ."&redirect_uri=". $redirect_uri ."&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
        header("Location:".$url);
    }
    public function getUserInfo(){
        //第一步：获取openid和access_token
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->secret."&code=".$_GET['code']."&grant_type=authorization_code";

        $oauth2 = $this->getJson($oauth2Url);
        $get_new_access_token = $this->getJson("https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->appid."&grant_type=refresh_token&refresh_token=".$oauth2['refresh_token']);
        $access_token = $get_new_access_token['access_token'];

        $openid = $oauth2['openid'];
        //$get_user_info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        //第三步:根据全局access_token和openid查询用户信息
        $access_token = $oauth2["access_token"];
        //获取新的access_token
        //$get_new_access_token = $this->getJson("https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->appid."&grant_type=refresh_token&refresh_token=".$oauth2['refresh_token']);

        $userinfo = $this->getJson($get_user_info_url);

        //############存储用户信息到数据库############//
        //1、检查当前用户是否已经在数据库中（通过openid,因为openid是唯一的）
        $isset_openid = M("wechat_user")->where(array("openid"=>$userinfo['openid']))->find();
        if(!$isset_openid){
            $info = array(
                "openid"     => $userinfo['openid'],
                "nickname"   => $userinfo['nickname'],
                "headimgurl" => $userinfo['headimgurl']
            );
            $res = M("wechat_user")->add($info);
            if($res){
                echo "数据录入成功";
            }else{
                echo "数据录入失败";
            }
        }else{
            echo "用户已经存在";
        }



        $this->display();
    }

    public function getJson($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }


    /********************使用memcache缓存*****************/
    /**1、实例化memcache**/
    public function _memcache_init(){
        //实例化对象
        $memObj = new Memcache();
        //连接
        $memObj->connect("127.0.0.1",11211);
        return $memObj;
    }
    /**2、设置memcache**/
    public function _memcache_set($key,$value,$time=0){
        $memObj = $this->_memcache_init();
        $memObj->set($key,$value,0,$time);
    }
    /**3、获取memcahce**/
    public function _memcache_get($key){
        $memObj = $this->_memcache_init();
        return $memObj->get($key);
    }


    private function p($arr){
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
}