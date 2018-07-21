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
        include "public/weibo/index.php";
    }

    public function listAction(){
        include "public/weibo/weibolist.php";
    }

    public function fabuAction(){
        require  'public/weibo/config.php';
        require  'public/weibo/saetv2.ex.class.php';

        $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
        $ms  = $c->home_timeline(); // done
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];
        $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

        if( isset($_REQUEST['text']) ) {
            // 注意至少要带上一个链接。
            $ret = $c->share( $_REQUEST['text']."http://ceshi28.jileiyun.com" );	//发送微博
            if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
                echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
            } else {
                echo "<p>发送成功</p>";
            }
        }
    }


}