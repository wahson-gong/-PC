<?php

// 文章模型控制器

class IndexController extends BaseController

{
    public function __construct()
    {
        ob_end_clean();
    }

    public function  indexAction()
    {
        $model=new ModelNew('token');
        $token=$model->findBySql("select token,time from sl_token ORDER by id DESC LIMIT 1")[0]['token'];
        $code=$_REQUEST['auth_code'];
        $params = [
        "component_appid"=>"wxe40cbd2b68b00386",
         "authorization_code"=>$code
        ];
        $model=new ModelNew('token');
        $token=$model->findBySql("select token,time from sl_token ORDER by id DESC LIMIT 1")[0]['token'];
        $url="https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=$token";
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
        $result=json_decode($return,true);
//        var_dump($result);exit();
        $data['appid']=$result['authorization_info']['authorizer_appid'];
        $data['token']=$result['authorization_info']['authorizer_access_token'];
        $data['freshtoken']=$result['authorization_info']['authorizer_refresh_token'];
        $data['time']=time();
        $model=new ModelNew('user');
        $model->where(['yonghuming'=>$_SESSION['yonghuming']])->update($data);
        $this->jump('/','',0);
    }

    public function shuaxinAction(){
        $model=new ModelNew('user');
        $appid=$model->where(['yonghuming'=>18582479523])->find()->one()['appid'];
        $freshtoken=$model->where(['yonghuming'=>18582479523])->find()->one()['freshtoken'];
        $params = [
            "component_appid"=>"wxe40cbd2b68b00386",
            "authorizer_appid"=>$appid,
            "authorizer_refresh_token"=>$freshtoken
        ];
        $model=new ModelNew('token');
        $token=$model->findBySql("select token,time from sl_token ORDER by id DESC LIMIT 1")[0]['token'];
        $url="https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=$token";
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
        $result=json_decode($return,true);
        $data['token']=$result['authorizer_access_token'];
        $data['freshtoken']=$result['authorizer_refresh_token'];
        $data['time']=time();
        $model=new ModelNew('user');
        if ($data['freshtoken']!=''){
            $model->where(['yonghuming'=>18582479523])->update($data);
        }
    }






}