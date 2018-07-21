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
        $code = $_GET['code']?$_GET['code']:'';
        $state = $_GET['state']?$_GET['state']:'';
        $uid = $_SESSION['id'];
        if($state ==1&$code!=''){
            $client_id="30f1b6b58809e6b0426163c7bce64402";
            $client_secret="5a758e2527cf3812ee938daef43c3869e1cf8c14";
            $url =  "https://auth.om.qq.com/omoauth2/accesstoken?grant_type=authorization_code&client_id=$client_id&client_secret=$client_secret&code=$code";
            $return=file_get_contents($url);
            $result=json_decode($return,true);
            $model=new ModelNew('user');
            $data['ttoken']=$result['data']['access_token'];
            $data['ttime']=$result['data']['expires_in'];
            $model->where(['yonghuming'=>$_SESSION['yonghuming']])->update($data);
            //授权成功 获取数据 保存到数据库
            $re = $model->findBySql("select *from sl_task where uid = '{$uid}' and type = '企鹅'");
            $model2 = new ModelNew('task');
            if(empty($re)){
                //第一次  新增
                $data['uid'] = $_SESSION['id'];
                $data['token'] = $result['data']['access_token'];
                $data['n_token'] = $result['data']['refresh_token'];
                $data['type'] = '企鹅';
                $data['dtime'] = date('Y-m-d H:i:s',time());
                $model2->insert($data);
            }else{
                //新token 修改到n_token 中
                $token = $result['data']['access_token'];
                $model2->query("update sl_task set token = '{$token}' where uid =  '{$uid}' and type = '企鹅'");
            }
            $this->jump('index.php?p=show&c=admin&a=index','',0);
        }else{
            echo '授权失败,请稍后再试';
        }
    }


    public function shouquanAction(){
        $url="http://ceshi28.jileiyun.com/index.php?p=qq";
        $client_id="30f1b6b58809e6b0426163c7bce64402";
        $client_secret="5a758e2527cf3812ee938daef43c3869e1cf8c14";
//var_dump("https://auth.om.qq.com/omoauth2/authorize?response_type=code&client_id=$client_id&redirect_uri=$url&state=1");die;
        $this->jump("https://auth.om.qq.com/omoauth2/authorize?response_type=code&client_id=$client_id&state=1&redirect_uri=$url&state=1",'',0);
    }

    public function  shuaxinAction()
    {
        $client_id = "30f1b6b58809e6b0426163c7bce64402";
//      $client_secret="5a758e2527cf3812ee938daef43c3869e1cf8c14";
        $model = new ModelNew('user');
        $openid = $model->findBySql("select *from sl_user WHERE yonghuming=18582479523")[0];
        $refresh_token = $openid['qq_refresh_token'];
        $url = "https://auth.om.qq.com/omoauth2/refreshtoken?grant_type=refreshtoken&client_id=$client_id&refresh_token=$refresh_token";
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
    public function fabuAction(){
        header("Content-type:text/html;charset=utf-8");
        $client_id = "30f1b6b58809e6b0426163c7bce64402";
        $model = new ModelNew('user');
        $_mag= $model->findBySql("select *from sl_user WHERE yonghuming=18582479523")[0];
        $openid=$_mag['qqOpenid'];
        $access_token = $_mag['qq_access_token'];
        $title="nini97797niininini";
        $content=urlencode("王先生不爱吃鱼,<a style='color: red;'>你们到底有多烦啊</a>,<img src='https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1522322968831&di=af0710a593cf13a36488af958154869f&imgtype=0&src=http%3A%2F%2Fpic21.nipic.com%2F20120516%2F7459350_114708508123_2.jpg'>");
        $pic="http://puui.qpic.cn/tv/0/5856657_460358/0";
        $url = "https://api.om.qq.com/article/authpubpic?access_token=$access_token&openid=$openid&title=$title&content=$content&cover_pic=$pic";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);// 必须为字符串
//        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:text/html;encoding=utf-8'));// 必须声明请求头QQQQ
        $return = curl_exec($ch);
        curl_close($ch);
        var_dump($return);
    }

    public function checkAction(){
        $model = new ModelNew('user');
        $_mag= $model->findBySql("select *from sl_user WHERE yonghuming=18582479523")[0];
        $openid=$_mag['qqOpenid'];
        $access_token = $_mag['qq_access_token'];
        $url="https://api.om.qq.com/transaction/infoauth?access_token=$access_token&openid=$openid&transaction_id=6113115191415724002";
        $this->jump($url,'',0);
    }





}