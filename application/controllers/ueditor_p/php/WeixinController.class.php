<?php

// 文章模型控制器

include "framework/core/Controller.class.php";
class WeixinController extends Controller

{
    public function __construct()
    {
        ob_end_clean();
    }

    public function  wxAction()
    {

    }

    public function wxaAction(){
        include CUR_VIEW_PATH . "Sweixin" .DS ."index.html";
       
    }

    public function sqAction(){

    }

    public function sq1Action(){

    }
  //token
    public function tokenAction(){
        $model=new ModelNew('ticket');
        $ticket=$model->findBySql("select ticket from sl_ticket ORDER by id DESC LIMIT 1")[0]['ticket'];
        $params = [
            "component_appid"=>"wxe40cbd2b68b00386",
            "component_appsecret"=>"8e26ea66d0ea94f9a1084f25588ef1fb",
            "component_verify_ticket"=>$ticket
        ];
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,"https://api.weixin.qq.com/cgi-bin/component/api_component_token" );
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1 );
        curl_setopt($ch,CURLOPT_POST,1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($params));// 必须为字符串
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json;encoding=utf-8'));// 必须声明请求头
        $return = curl_exec($ch);
        curl_close ( $ch );
        $name=json_decode($return,true)['component_access_token'];
        $model=new ModelNew('token');
        $data['token']=$name;
        $data['time']=time();
        $model->insert($data);
    }
  //预授权pre_code
    public function codeAction(){
        $params = [
            "component_appid"=>"wxe40cbd2b68b00386",
        ];
        $model=new ModelNew('token');
        $token=$model->findBySql("select token,time from sl_token ORDER by id DESC LIMIT 1")[0]['token'];
        $url="https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=$token";
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1 );
        curl_setopt($ch,CURLOPT_POST,1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($params));// 必须为字符串
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json;encoding=utf-8'));// 必须声明请求头
        $return=curl_exec($ch);
        curl_close($ch );

        $code=json_decode($return,true);
        $prea_code=$code['pre_auth_code'];
        $_url="https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=wxe40cbd2b68b00386&redirect_uri=http://ceshi28.jileiyun.com/index.php?p=huidiao&pre_auth_code=$prea_code";
        $this->jump($_url,'',0);
    }

     public function textAction(){
         include CUR_VIEW_PATH . "Sweixin" .DS ."index.html";
     }


     public function fabuAction(){
         include CUR_VIEW_PATH . "Sweixin" .DS ."index_fabu.html";
     }

     public static function fabu1Action($lu){

         $model=new ModelNew('user');
         $people=$model->where(['yonghuming'=>18582479523])->find()->one();
         $token=$people['token'];
         $name='D:/wwwroot/ceshi28/wwwroot/'.$lu;
//         $name='D:/wwwroot/ceshi28/wwwroot/public/.png';

         $post_data = array(

//             "media"=>new CURLFile('/wwwroot/public/1521631163.png')
             "media"=>'@'.$name
         );
//         var_dump($post_data);die;
         $url="https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=$token";
         $ch = curl_init();
         curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, false);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1 );
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_URL,$url);
         curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         $return=curl_exec($ch);
         curl_close($ch);
         $_rs=json_decode($return,true);
         return $_rs['url'];
     }

     public function hahaAction(){
         $mysql=mysqli_connect('211.149.197.112','ceshi27','K5B8M7R6');
         $sql="select *from sl_user WHERE yonghuming=18582479523";
         $msg=mysqli_query($mysql,$sql);
         var_dump($msg);
     }

     //


}