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
        $state=empty($_GET['state'])?'':$_GET['state'];
        $code=empty($_GET['code'])?'':$_GET['code'];
        $uid = $_SESSION['id'];
        if ($state==1 && $code!=''){
            $client_key="32b62c641af8d439";
            $client_secret="e3e469386c86f99e55c04ec5579ce775";
            $url="https://open.snssdk.com/auth/token/?code=$code&client_key=$client_key&client_secret=$client_secret&grant_type=authorize_code";
            $return=file_get_contents($url);
            $result=json_decode($return,true);
            $model=new ModelNew('user');
            $data['ttoken']=$result['data']['access_token'];
            $data['ttime']=$result['data']['expires_in'];
            $model->where(['yonghuming'=>$_SESSION['yonghuming']])->update($data);
            //授权成功 获取数据 保存到数据库
            $re = $model->findBySql("select *from sl_task where uid = '{$uid}' and type = '今日头条'");
            $model2 = new ModelNew('task');
            if(empty($re)){
                //第一次  新增
                $data['uid'] = $_SESSION['id'];
                $data['token'] = $result['data']['access_token'];
                $data['type'] = '今日头条';
                $data['dtime'] = date('Y-m-d H:i:s',time());
                $model2->insert($data);
            }else{
                //新token 修改到n_token 中
                $token = $token['access_token'];
                $model2->query("update sl_task set token = '{$token}' where uid =  '{$uid}' and type = '今日头条'");
            }
            $this->jump('index.php?p=show&c=admin&a=index','',0);

        }else{
            echo '授权失败,请稍后再试';
        }
    }


    public function shouquanAction(){
        $client_key="32b62c641af8d439";
        $client_secret="e3e469386c86f99e55c04ec5579ce775";
        $redirect="http://ceshi28.jileiyun.com/index.php?p=toutiao";
        $url="https://open.snssdk.com/auth/authorize/?response_type=code&auth_only=1&client_key=$client_key&redirect_uri=$redirect&state=1";
        $this->jump($url,'',0);
    }


    public function fabuAction(){
        header("Content-type:text/html;charset=utf-8");
        $url = "https://mp.toutiao.com/open/new_article_post/?access_token=21650c995f72b0d1dc5b8fb0fbc42b9e0015&client_key=32b62c641af8d439";
        $params = [
            'title'=>'王先生不爱吃鱼',
            'content'=>'你们都是最棒的',
            'abstract'=>'我不爱吃鱼',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);// 必须为字符串
        $return = curl_exec($ch);
        curl_close($ch);
        $name = json_decode($return, true);
        var_dump($name);
    }

    public function  shuaxinAction()
    {

        $model = new ModelNew('user');
        $openid = $model->findBySql("select *from sl_user WHERE yonghuming=18582479523")[0];
        $refresh_token = $openid['qq_refresh_token'];
        $url = "https://auth.om.qq.com/omoauth2/refreshtoken?grant_type=refreshtoken&client_id=&refresh_token=$refresh_token";
        $params = [
            'openid' => $openid['qqOpenid']
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));// 必须为字符串
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json;encoding=utf-8'));// 必须声明请求头
        $return = curl_exec($ch);
        curl_close($ch);
        $name = json_decode($return, true);
        $data['qq_access_token'] = $name['data']['access_token'];
        $data['qq_refresh_token'] = $name['data']['refresh_token'];
        $data['qqOpenid'] = $name['data']['openid'];

        if ($data['qq_access_token'] == '') {
            echo '失败';
        } else {
            $model->where(['yonghuming' => 18582479523])->update($data);
            echo '成功';
        }

    }







}