<?php

// 文章模型控制器

class AdminController extends BaseController

{
          public function __construct()
          {
//              var_dump(isset($_SESSION['yonghuming']));exit();
              if (empty($_SESSION['yonghuming'])){
                  $this->jump('index.php?p=show&c=login&a=index','请先登陆',3);
              }
              ob_end_clean();
          }
          // 退出登录 (lzs.vip@qq.com)
          public function logout(){
              unset($_SESSION['id']);
              unset($_SESSION['touxiang']);
              unset($_SESSION['mingcheng']);
              unset($_SESSION['yonghuming']);
              session_destroy();
              $this->jump('index.php?p=show&c=index&a=index','成功通出登录',3);
          }

    public function  indexAction()
          {
//              var_dump($_SESSION);exit();
              $id=$_SESSION['id'];
              $model=new ModelNew('user');
              $rs=$model->where(['id'=>$id])->find()->one();
              $xiangzhen=$rs['xiangzhen'];
              $_model=new ModelNew('fengcai');
              $num=$_model->findBySql("select count(*) from sl_fengcai WHERE uid={$rs['id']} AND xiangzhen='".$xiangzhen."' ")[0]['count(*)'];
              $yuming = $_SERVER['SERVER_NAME'];
              include CUR_VIEW_PATH . "Sadmin" . DS . "admin_center.html";
          }
          //修改密码

          public function changeAction(){
              include CUR_VIEW_PATH . "Sadmin" . DS . "admin_change.html";
          }

          //授权中心
          public function shouquanAction(){
              include CUR_VIEW_PATH . "Sadmin" . DS . "admin_manage.html";
          }
          //我的发布
          public function fabuAction(){

              $yonghuming=$_SESSION['yonghuming'];
              $model=new ModelNew('user');
              $rs=$model->where(['yonghuming'=>$yonghuming])->find()->one();
              $xiangzheng=$rs['xiangzhen'];
              $_model=new ModelNew('fengcai');
              //>>获取乡镇的栏目
              $lanmus=$model->findBySql("select * from sl_dylm where xiangzhen='$xiangzheng' AND qiyong=1");


              $searchList = [];
              $urlList = [];
              if (!empty($_GET['tj'])){
                  $tj = $_GET['tj'];
                  $searchList[] = "biaoti like '%".$tj."%'";
                  $urlList[] = "tj=".$tj;
              }
              if (!empty($_GET['fenlei'])){
                  $fenlei = $_GET['fenlei'];
                  $searchList[] = "fenlei='".$fenlei."'";
                  $urlList[] = "fenlei=".$fenlei;
              }
              if (!empty($_GET['zhuangtai'])){
                  $zhuangtai = $_GET['zhuangtai'];
                  $searchList[] = "zhuangtai='".$zhuangtai."'";
                  $urlList[] = "fenlei=".$zhuangtai;
              }
              $where = '';
              $url = '';
              if (count($searchList)>0){
                $where .= " and ".implode(' and ',$searchList);
                $url = "&".implode("&",$urlList);
              }

//              $tj=empty($_GET['tj'])?'':$_GET['tj'];

//              if ($tj){
//                  $num=$_model->findBySql("select count(*) from sl_fengcai WHERE uid={$rs['id']} and xiangzhen='".$xiangzheng."' and biaoti like '%".$tj."%'")[0]['count(*)'];
//              }else{
//                  $num=$_model->findBySql("select count(*) from sl_fengcai WHERE uid={$rs['id']} and xiangzhen='".$xiangzheng."'")[0]['count(*)'];
//              }
//                var_dump($searchList);exit();
              $num = $_model->findBySql("select count(*) from sl_fengcai where uid={$rs['id']} AND xiangzhen='".$xiangzheng."' ".$where)[0]['count(*)'];

              $_num=3;
              $total=empty(ceil($num/$_num))?1:ceil($num/$_num);
              //--------------------------------
              $page=empty($_GET['page'])?1:$_GET['page'];
              if ($page<=1){
                  $page=1;
                  $_GET['page']=1;
              }
              if ($_GET['page']>$total){
                  $page=$total;
                  $_GET['page']=$total;
              }
              $min=$page-2;
              $max=$page+5;
              if ($min<=1){
                  $min=1;
              };
              if ($max>=$total){
                  $max=$total;
              }
              $act=($page-1)*$_num;
              $__model=new ModelNew('fengcai');
//              $result=$__model->where(['uid'=>$rs['id']])->find()->all();
//              if ($tj) {
//                  $result = $__model->findBySql("select *from sl_fengcai WHERE uid={$rs['id']} and xiangzhen='".$xiangzheng."' AND biaoti like '%".$tj."%' limit $act,$_num");
//              }else{
//                  $result = $__model->findBySql("select *from sl_fengcai WHERE uid={$rs['id']} and xiangzhen='".$xiangzheng."' limit $act,$_num");
//              }
//              var_dump("select * from sl_fengcai WHERE uid={$rs['id']} AND xiangzhen='".$xiangzheng."' ".$where." limie $act,$_num");exit();
                $result = $_model->findBySql("select * from sl_fengcai WHERE uid={$rs['id']} AND xiangzhen='".$xiangzheng."' ".$where." limit $act,$_num");

              include CUR_VIEW_PATH . "Sadmin" . DS . "admin_message.html";
          }
          //投消息
          public function touAction(){
              $_jc=new  ModelNew('token');
              $__time=$_jc->findBySql("select MAX(time) from sl_token ")[0]['MAX(time)'];
              $_cha_time=time()-$__time;
              if ($_cha_time>3600*1.5){
                  WeixinController::tokenAction();
                  self::qqshuaxinAction();
              }
              $model=new ModelNew('canshu');
              $yonghuming = $_SESSION['yonghuming'];
              $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
              $xiangzhen = $userData[0]['xiangzhen'];
              $lanmu=$model->findBySql("select * from sl_dylm where xiangzhen='$xiangzhen' AND qiyong=1");

              $_model=new ModelNew('user');
              $ttime=$_model->where(['yonghuming'=>$_SESSION['yonghuming']])->find()->one()['ttime'];
              if ((time()-$ttime)>=0){
                  $data['toutiao']='否';
                  $_model->where(['yonghuming'=>$_SESSION['yonghuming']])->update($data);
              }
              //查看是否有保存内容
              $model2 = new ModelNew('save');
              $uid = $_SESSION['id'];
              @$save = $model2->findBySql("select *from sl_save where uid = '{$uid}'")[0];

              include CUR_VIEW_PATH . "Sadmin" . DS . "admin_tou.html";
          }


            public function tou1Action(){
                include CUR_VIEW_PATH . "Sadmin" . DS . "admin_tou1.html";
            }


          //发布文章
          public function sendAction(){
//              var_dump($_POST);exit();
            $data=$_POST;
            $lujing='';

            if (isset($data['save'])){
                //用户点击的是保存按钮
                $user_id = $_SESSION['id'];
                $tp=empty($data['tp'])?'':$data['tp'];
                if ($tp){
                    foreach ($data['tp'] as $v){
                        $lujing.=$v."{title}{next}";
                    }
                }
                $model = new ModelNew('save');
                $_data['zutu'] = $lujing;
                $_data['uid'] = $user_id;
                $_data['biaoti']=$data['biaoti'];
                $biaoti = $data['biaoti'];
                $_data['jianjie']=$data['jianjie'];
                $jianjie = $data['jianjie'];
                $_data['fenlei']=$data['fenlei'];
                $fenlei = $data['fenlei'];
                $re = $model->findBySql("select *from sl_save where uid = '{$user_id}'");
                if(empty($re)){
                    $model->insert($_data);
                }else{
                    $model->query("update sl_save set zutu ='{$lujing}',biaoti = '{$biaoti}',jianjie = '{$jianjie}',fenlei = '{$fenlei}' where uid = '{$user_id}'");
                }
                $this->jump('index.php?p=show&c=admin&a=index','保存成功',3);

            } else{
                //用户点击的是提交按钮
                $tp=empty($data['tp'])?'':$data['tp'];
                if ($tp){
                    foreach ($data['tp'] as $v){
                      $lujing.=$v."{title}{next}";
                    }
                }
                if ($data['fenlei']=="")
                $shouji=$_SESSION['yonghuming'];
                $model=new ModelNew('user');
                $xiangzhen=$model->where(['yonghuming'=>$_SESSION['yonghuming']])->find('xiangzhen')->one()['xiangzhen'];
                $id=$model->where(['yonghuming'=>$_SESSION['yonghuming']])->find('id')->one()['id'];
                $_model=new ModelNew('fengcai');
                $_data['biaoti']=$data['biaoti'];
                $_data['jianjie']=$data['jianjie'];

                $_data['xiangzhen']=$xiangzhen;
                $_data['fenlei']=$data['fenlei'];
                $_data['uid']=$id;
                $_data['zutu']=$lujing;
                $_data['zhuangtai']='审核中';
                $_model->insert($_data);
                //查出是否save表中
                $model4 = new ModelNew('save');
                $uid = $_SESSION['id'];
                $re = $model4->query("delete from sl_save where uid = {$uid}");
//                var_dump($re);die;
                //加入到任务中
                $model2 = new ModelNew('renwu');
                $model3 = new ModelNew('fengcai');
                $article_id = $model3->findBySql("select *from sl_fengcai order by id desc limit 1")[0]['id'];
                $data1['dtime'] = date('Y-m-d H:i:s',time());

                $data1['uid'] = $id;
                $data1['article_id'] = $article_id;
                $data1['article_type'] = $data['fenlei'];
                $data1['status'] = '未处理';
                if($data['wx']==1){
                    $data1['type'] = '微信';
                    $model2->insert($data1);
                }
                if($data['tt']==1){
                    $data1['type'] = '今日头条';
                    $model2->insert($data1);
                }
                if($data['wb']==1){
                    $data1['type'] = '新浪微博';
                    $model2->insert($data1);
                }
                if($data['qe']==1){
                    $data1['type'] = '企鹅';
                    $model2->insert($data1);
                }


                $media_id=$data['media_id'];
                $author="请叫我王导";
                $title=$_data['biaoti'];
                $content=$data['jianjie'];
                $content1=htmlentities($data['jianjie']);
                $digest='';
                $news=array(
                     "articles"=>array(
                         array(
                        "thumb_media_id"=>urlencode($media_id),
                         "author"=>urlencode($author),
                         "title"=>urlencode($title),
                         "content_source_url"=>"",
                         "content"=>urlencode(str_replace("\"","'",$content)),
                         "digest"=>urlencode('成都思乐科技'),
                         "show_cover_pic"=>1
                        )
                     )
                );
                //               var_dump($news);die;

                //7-03注释
//                $model=new ModelNew('user');
//                $people=$model->where(['yonghuming'=>18582479523])->find()->one();
//                $token=$people['token'];
//                $url="https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=$token";
//                $ch=curl_init();
//                curl_setopt($ch,CURLOPT_URL,$url);
//                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1 );
//                curl_setopt($ch,CURLOPT_POST,1 );
//                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
//                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
//                curl_setopt($ch,CURLOPT_POSTFIELDS,urldecode(json_encode($news)));// 必须为字符串
//                curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json;encoding=utf-8'));// 必须声明请求头
//                $return=curl_exec($ch);
//                curl_close($ch );
//                $code=json_decode($return,true);
//    //              var_dump($code);exit();
//                $media_id=$code['media_id'];
//    //            self::weiboAction($content1);
//                self::heheAction($media_id);//输出到微信公众号
//    //            self::toutiaoAction($title,$content,'成都思乐');  //输出到头条
//    //            self::qqAction($title,$content1,$media_id);//输出到腾讯企鹅账号


                $this->jump('index.php?p=show&c=admin&a=index','添加成功',3);
            }
          }

          public function send1Action(){
              $model=new ModelNew('member');
              $xiangzhen=$model->where(['shouji'=>$_SESSION['shouji']])->find('xiangzhen')->one()['xiangzhen'];
              $data=$_POST;
              $data['xiangzhen']=$xiangzhen;
              $model=new ModelNew('ldfg');
              $rs=$model->insert($data);
              if ($rs){
                  $this->jump('index.php?p=show&c=admin&a=tou1','添加成功',3);
              }else{
                  $this->jump('index.php?p=show&c=admin&a=tou1','系统繁忙请稍后再试',3);
              }
          }

          //图片啊上传
          public function uploadAction(){
//              $filename ="http://".$_SERVER['HTTP_HOST']."/public/uploads/img/".time().$_FILES['file']['name'];
              $filename ="public/webuploader/upload/".time().$_FILES['file']['name'];
              move_uploaded_file($_FILES["file"]["tmp_name"],$filename);//将临时地址移动到指定地址
              $data['url']="http://".$_SERVER['HTTP_HOST']."/".$filename;
              //----------------------------------------------------------------------//
              $model=new ModelNew('user');
              $people=$model->where(['yonghuming'=>18582479523])->find()->one();
              $token=$people['token'];
              $name='D:/wwwroot/ceshi28/wwwroot/'.$filename;
//         $name='D:/wwwroot/ceshi28/wwwroot/public/.png';
              $post_data = array(
                  "media"=>'@'.$name
              );
              $url="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$token&type=image";
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
              $data['img']=$_rs['media_id'];
              echo json_encode($data);
          }

          function  upload1Action(){
              $filename ="public/webuploader/upload/".time().$_FILES['file']['name'];
              move_uploaded_file($_FILES["file"]["tmp_name"],$filename);//将临时地址移动到指定地址
              $data['url']="http://".$_SERVER['HTTP_HOST']."/".$filename;
//              $data['url']=urlencode($data['url']);
              echo json_encode($data,JSON_UNESCAPED_UNICODE);
          }
         //修改密码
          public function mimaAction(){
              $id=$_SESSION['id'];
              $model=new ModelNew('user');
              $rs=$model->where(['id'=>$id])->find('mima')->one()['mima'];
              $mima=md5($_POST['mima']);
              if ($rs!=$mima){
                  $this->jump('index.php?p=show&c=admin&a=change','密码不匹配',3);
              }else{
                  $data['mima']=md5($_POST['new']);
                  $jg=$model->where(['id'=>$id])->update($data);
                  if ($jg){
                      $this->jump('index.php?p=show&c=admin&a=change','修改成功',3);
                  }else{

                      $this->jump('index.php?p=show&c=admin&a=change','修改失败',3);
                  }
              }

          }

          //昵称
              public function ggAction(){
                  $id=$_SESSION['id'];
                  $data['touxiang'] = $_POST['link'];
                  $data['xingming']=$_POST['updName'];
                  $data['yonghuming']=$_POST['updTel'];
                  $model = new ModelNew('user');
                  $rs=$model->where(['id'=>$id])->update($data);
                  $_SESSION['touxiang']=$data['touxiang'];
                  $_SESSION['mingcheng']=$data['xingming'];
                  $_SESSION['yonghuming']=$data['yonghuming'];
                  echo '1';
              }

              //领导分工
            public function ldAction(){
                $model=new ModelNew('user');
                $xiangzhen=$model->where(['id'=>$_SESSION['id']])->find('xiangzhen')->one()['xiangzhen'];
                  $_model=new ModelNew('ldfg');
                  $rs=$_model->where(['xiangzhen'=>$xiangzhen])->find()->all();
                include CUR_VIEW_PATH . "Sadmin" . DS . "admin_ld.html";
            }

            //评论审核
            public function shAction(){
                $model=new ModelNew('user');
                $xiangzhen=$model->where(['yonghuming'=>$_SESSION['yonghuming']])->find('xiangzhen')->one()['xiangzhen'];

                $_model=new ModelNew('pinglun');
                $num=$_model->findBySql("select count(*) from sl_pinglun WHERE xiangzhen='".$xiangzhen."'")[0]['count(*)'];
                $_num=3;
                $total=empty(ceil($num/$_num))?1:ceil($num/$_num);
                //--------------------------------
                $page=empty($_GET['page'])?1:$_GET['page'];
                if ($page<=1){
                    $page=1;
                    $_GET['page']=1;
                }
                if ($_GET['page']>$total){
                    $page=$total;
                    $_GET['page']=$total;
                }
                $min=$page-2;
                $max=$page+5;
                if ($min<=1){
                    $min=1;
                };
                if ($max>=$total){
                    $max=$total;
                }
                $act=($page-1)*$_num;


                $msg=$_model->findBySql("select *from sl_pinglun WHERE xiangzhen='".$xiangzhen."' limit {$act},{$_num}");
                //>>获取评论人名称
                foreach ($msg as $k=>$v){
                    $uid = $v['uid'];
                    $uname = $_model->findBySql("select * from sl_member WHERE id=$uid")[0]['mingcheng'];
                    $msg[$k]['uname'] = $uname;
                }

//                var_dump($msg);exit();

                include CUR_VIEW_PATH . "Sadmin" . DS . "admin_shpl.html";
            }

            //lanmu
            public static function lmAction(){
                $model=new ModelNew('canshu');
                $lanmu=$model->where(['classid'=>281])->find()->all();
                return $lanmu;

            }

            public function hahaAction(){

                $news=array(
                  "filter"=>array(
                      "is_to_all"=>true,
                      "tag_id"=>""
                  ),
                    "mpnews"=>array(
                        "media_id"=>"RP6fJuemy8236W7TRVK_yuxQrF86qQrq21gFXFWpope3EcV48IBHobSv1J_hVuvE"
                    ),
                    "msgtype"=>"mpnews",
                    "send_ignore_reprint"=>1
                );
                $model=new ModelNew('user');
                $people=$model->where(['yonghuming'=>18582479523])->find()->one();
                $token=$people['token'];

                $url="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$token";
                $ch=curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1 );
                curl_setopt($ch,CURLOPT_POST,1 );
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($news));// 必须为字符串
                curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json;encoding=utf-8'));// 必须声明请求头
                $return=curl_exec($ch);
                curl_close($ch );
                $code=json_decode($return,true);
                var_dump($code);
            }

    public static function heheAction($url){

        $news=array(
         "towxname"=>"acwangjing9527",
            "mpnews"=>array(
                "media_id"=>$url
            ),
            "msgtype"=>"mpnews",
        );
        $model=new ModelNew('user');
        $people=$model->where(['yonghuming'=>18582479523])->find()->one();
        $token=$people['token'];

        $url="https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=$token";
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1 );
        curl_setopt($ch,CURLOPT_POST,1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($news));// 必须为字符串
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:application/json;encoding=utf-8'));// 必须声明请求头
        $return=curl_exec($ch);
        curl_close($ch );
        $code=json_decode($return,true);
    }


    function ceshiAction(){
        $name="<a>你们都是大帅哥</a>";
        $a=urldecode($name);
        var_dump($a);
    }

    //发布查看

    function chakanAction(){
        $id=$_GET['id'];
        $model=new  ModelNew('fengcai');
        $result=$model->where(['id'=>$id])->find()->one();

        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_chakan.html";
    }
    //修改文章的详细信息
    function xiugaiAction(){
        $model=new ModelNew('canshu');
        $id=$_GET['id'];
        $_model=new ModelNew('fengcai');
        $result=$_model->where(['id'=>$id])->find()->one();
        if ($result["zhuangtai"] == "通过"){
            $this->jump('index.php?p=show&c=admin&a=fabu','审核已通过，不能进行修改',3);
        }elseif($result["zhuangtai"] == "审核中"){
            $this->jump('index.php?p=show&c=admin&a=fabu','审核中',3);
        }else{
            $user_id = $_SESSION['id'];
            $userData = $model->findBySql("select xiangzhen from sl_user where id=$user_id");
            $xiangzhen = $userData[0]['xiangzhen'];
            $lanmu=$model->findBySql("select * from sl_dylm where xiangzhen='$xiangzhen' AND qiyong=1");
            include CUR_VIEW_PATH . "Sadmin" . DS . "admin_xiugai.html";
        }
    }

    //删除文章
    function shanchuAction(){
        $id=$_GET['id'];
        $model=new ModelNew('fengcai');
        $rs=$model->where(['id'=>$id])->delete();
        if ($rs){
            $this->jump('index.php?p=show&c=admin&a=fabu','删除成功',2);
        }else{
            $this->jump('index.php?p=show&c=admin&a=fabu','删除失败',2);
        }
    }
    //修改文章
    function editAction(){
        $id=$_GET['id'];
        $data=$_POST;
        $model=new ModelNew('fengcai');
        $rs=$model->where(['id'=>$id])->update($data);
        if ($rs){
            $this->jump('index.php?p=show&c=admin&a=fabu','更新成功',2);
        }else{
            $this->jump('index.php?p=show&c=admin&a=fabu','更新失败',2);
        }
    }

    //领导删除
    function shanchu1Action(){
       $id=$_GET['id'];
       $model=new ModelNew('ldfg');
       $rs=$model->where(['id'=>$id])->delete();
        if ($rs){
            $this->jump('index.php?p=show&c=admin&a=ld','删除成功',2);
        }else{
            $this->jump('index.php?p=show&c=admin&a=ld','删除失败',2);
        }
    }
    //领导详情的查看
    function chakan1Action(){
        $id=$_GET['id'];
        $model=new ModelNew('ldfg');
        $rs=$model->where(['id'=>$id])->find()->one();

        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_chakan1.html";
    }


    public static function toutiaoAction($title,$content,$abstract){
        header("Content-type:text/html;charset=utf-8");
        $model=new ModelNew('user');
        $people=$model->where(['yonghuming'=>18582479523])->find()->one();
        $token=$people['ttoken'];
        $url = "https://mp.toutiao.com/open/new_article_post/?access_token=$token&client_key=32b62c641af8d439";
        $params = [
            'title'=>$title,
            'content'=>$content,
            'abstract'=>$abstract,
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
//        var_dump($name);
    }

    public function qqAction($title1,$content1,$media_id){
        header("Content-type:text/html;charset=utf-8");
        $client_id = "30f1b6b58809e6b0426163c7bce64402";
        $model = new ModelNew('user');
        $_mag= $model->findBySql("select *from sl_user WHERE yonghuming=18582479523")[0];
        $openid=$_mag['qqOpenid'];
        $access_token = $_mag['qq_access_token'];
        $title=$title1;
        $content=urlencode($content1);
        $pic=$media_id;
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
//        var_dump($return);
    }


    public function  qqshuaxinAction()
    {
        $client_id = "30f1b6b58809e6b0426163c7bce64402";
//        $client_secret="5a758e2527cf3812ee938daef43c3869e1cf8c14";
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
//        var_dump($name);exit();
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

    //>>定义栏目
    public function dylmAction(){
        $model = new ModelNew();
        //>>获取栏目
        $yonghuming = $_SESSION['yonghuming'];
        $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
        $xiangzhen = $userData[0]['xiangzhen'];
        $lanmuDatas = $model->findBySql("select * from sl_dylm WHERE xiangzhen='$xiangzhen'");
//        var_dump($lanmuDatas);exit();

        //>>获取所有模板
        $model = new ModelNew("moban");
        $mobans = $model->findBySql("select * from sl_moban");

        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_dylm.html";
    }
    public function addlmAction(){
        //>>获取模板
        if (!isset($_GET['id'])){
            $id="";
            $m = new ModelNew();
            $mobans = $m->findBySql("select * from sl_moban");
            $biaoming="";
        }else{
            $id = $_GET['id'];
            //>>获取该条数据
            $m = new ModelNew();
            $lm = $m->findBySql("select * from sl_dylm WHERE id=$id");
            $mobans = $m->findBySql("select * from sl_moban");
            $biaoming=$lm[0]['biaoming'];
//            var_dump($lm);exit();
        }
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_addlm.html";
    }
    //>>查询改模板是否已被选中
    public static function sellmAction($moban,$biaoming=""){
        if ($baoming=""){
            return 1;
        }else{
            $model = new ModelNew();
            //>>获取乡镇
            $yonghuming = $_SESSION['yonghuming'];
            $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
            $xiangzhen = $userData[0]['xiangzhen'];
            //>>查询是否被选中
            $data = $model->findBySql("select * from sl_dylm WHERE xiangzhen='$xiangzhen' AND moban='$moban' AND biaoming='$biaoming'");
            if ($data){
                return 2;  //>>选中
            }else{
                return 1;  //>>未选中
            }
        }
    }
    //>>查询栏目状态
    public static function lmstatusAction($biaoming=""){
        if ($biaoming==""){
            return 1;
        }else{
            $model = new ModelNew();
            //>>获取乡镇
            $yonghuming = $_SESSION['yonghuming'];
            $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
            $xiangzhen = $userData[0]['xiangzhen'];
            //>>查询是否被选中
            $data = $model->findBySql("select * from sl_dylm WHERE xiangzhen='$xiangzhen' AND biaoming='$biaoming' limit 1");
            if ($data[0]['qiyong']==1){
                return 1;  //>>启用
            }else{
                return 2;  //>>未启用
            }
        }
    }
    //>>编辑栏目
    public function editlmAction(){
        $model = new ModelNew();
        if ($_POST['id']==""){   //>>新增
            $data = $_POST;
            unset($data["id"]);
            //>>获取乡镇
            $yonghuming = $_SESSION['yonghuming'];
            $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
            $xiangzhen = $userData[0]['xiangzhen'];
            $data["xiangzhen"]=$xiangzhen;
            $data["fenlei"]=$data["biaoming"];
            $data["upd"]=1;
            //>>判断重复
            $biaoming = $data["biaoming"];
            $chongfu = $model->findBySql("select * from sl_dylm where xiangzhen='$xiangzhen' AND biaoming='$biaoming'");
            if ($chongfu){
                //>>重复
                echo json_encode(array('code'=>2,'mes'=>"已存在该栏目"));
            }else{
                //>>添加数据
                $lmmodel = new ModelNew('dylm');
                if ($lmmodel->insert($data)){
                    echo json_encode(array('code'=>1,'mes'=>"添加成功"));
                }
            }

        }else{    //>>删除
            $id = $_POST["id"];
            //>>获取乡镇
            $yonghuming = $_SESSION['yonghuming'];
            $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
            $xiangzhen = $userData[0]['xiangzhen'];
            //>>判断重复
            $data = $_POST;
            $biaoming = $data["biaoming"];
            $chongfu = $model->findBySql("select * from sl_dylm where xiangzhen='$xiangzhen' AND biaoming='$biaoming' AND id!=$id");
            if ($chongfu){
                //>>重复
                echo json_encode(array('code'=>2,'mes'=>"已存在该栏目"));
            }else{
                //>>添加数据
                unset($data['id']);
                $data['dtime'] = date("Y-m-d H:i:s",time());
                $lmmodel = new ModelNew('dylm');
                if ($lmmodel->where(['id'=>$id])->update($data)){
                    echo json_encode(array('code'=>1,'mes'=>"修改成功"));
                }
            }
        }
    }
    public function dellmAction(){
        $id = $_GET["id"];
        $lmmodel = new ModelNew('dylm');
        $lmmodel->delete($id);
        $this->jump('?p=show&c=admin&a=dylm',"删除成功",3);
    }
//    //>>查询栏目是否已保存
//    public static function sellmAction($lm){
//        $model = new ModelNew("dylm");
//        $data = $model->findBySql("select * from sl_dylm WHERE fenlei='$lm'");
//        if ($data){
//            return 1;
//        }else{
//            return 0;
//        }
//    }
    //>>获取分类使用的模板
    public static function getmobanAction($lm){
        $model = new ModelNew("dylm");
        $data = $model->findBySql("select * from sl_dylm WHERE fenlei='$lm' limit 1");
        if ($data){
            return $data[0]['moban'];
        }else{
            return 0;
        }
    }

    //微博
   static function weiboAction($content){
       require  'public/weibo/config.php';
       require  'public/weibo/saetv2.ex.class.php';

       $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
       $ms  = $c->home_timeline(); // done
       $uid_get = $c->get_uid();
       $uid = $uid_get['uid'];
       $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息


           // 注意至少要带上一个链接。
           $ret = $c->share($content."http://ceshi28.jileiyun.com" );	//发送微博
           if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
               echo "<p>发送失败，错误：{$ret['error_code']}:{$ret['error']}</p>";
           } else {
               echo "<p>发送成功</p>";
           }

    }

    function renzhengAction(){
        $rz=$_GET['rz'];
        $model=new ModelNew('user');
        $_yhm=$_SESSION['yonghuming'];
        $msg=$model->where(['yonghuming'=>$_yhm])->find()->one();
        if ($msg[$rz]!="是"){
             if ($rz=='QQ'){
                 //跳转QQ
                 $this->jump('index.php?p=qq&c=index&a=shouquan','',0);
             }else if ($rz=="weibo"){
                 require 'public/weibo/config.php' ;
                 require 'public/weibo/saetv2.ex.class.php' ;
                 $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
                 $code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
                 //跳微博
                 $this->jump($code_url,'',0);
             }else if ($rz=="weixin"){
                 $this->jump('index.php?p=show&c=weixin&a=code','',0);
                 //跳转微信
             }else if ($rz=="toutiao"){
                 //跳转头条
                 $this->jump('index.php?p=toutiao&c=index&a=shouquan','',0);
             }
        }else{
            $this->jump('index.php?p=show&c=admin&a=index','您已经绑定过了',3);
        }

    }

    function  xiugai1Action(){
        if (empty($_GET['id'])){
            $id='';
            $rs=null;
        }else{
            $id=$_GET['id'];
            $model=new ModelNew('ldfg');
            $rs=$model->where(['id'=>$id])->find()->one();
        }
        $yuming = $_SERVER['SERVER_NAME'];
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_xiugai1.html";
    }

    function xiugaildAction(){
        $model=new ModelNew('ldfg');
        $id = $_POST['id'];
        if ($id==''){
            //>>添加
            $data['xingming'] = $_POST['xingming'];
            $data['zhineng'] = $_POST['zhineng'];
            $data['lianxifangshi'] = $_POST['lianxifangshi'];
            $data['jigoujieshao'] = $_POST['jigoujieshao'];
            $data['banshiliucheng'] = $_POST['banshiliucheng'];
            $data['link'] = $_POST['link'];
            $data['status'] = '审核中';
            //>>获取乡镇
            $yonghuming = $_SESSION['yonghuming'];
            $userData = $model->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
            $data['xiangzhen'] = $userData[0]['xiangzhen'];
            $model->insert($data);
            $this->jump('index.php?p=show&c=admin&a=ld','增加成功',3);
        }else{
            //>>修改
            $data['xingming'] = $_POST['xingming'];
            $data['zhineng'] = $_POST['zhineng'];
            $data['lianxifangshi'] = $_POST['lianxifangshi'];
            $data['jigoujieshao'] = $_POST['jigoujieshao'];
            $data['banshiliucheng'] = $_POST['banshiliucheng'];
            $data['link'] = $_POST['link'];
            $data['status'] = '审核中';
            $model->where(['id'=>$id])->update($data);
            $this->jump('index.php?p=show&c=admin&a=ld','修改成功',3);
        }

    }

     function xiugai2Action(){
         $id=$_GET['id'];
         $data=$_POST;
         $lujing='';
         $tp=empty($data['tp'])?'':$data['tp'];
         if ($tp){
             foreach ($data['tp'] as $v){
                 $lujing.=$v."{title}{next}";
             }
         }
         if ($data['fenlei']=="")
             $shouji=$_SESSION['yonghuming'];
         $model=new ModelNew('user');
         $_model=new ModelNew('fengcai');
         $_data['biaoti']=$data['biaoti'];
         $_data['jianjie']=$data['jianjie'];
         $_data['fenlei']=$data['fenlei'];
         $_data['zutu']=$lujing;
         $_model->where(['id'=>$id])->update($_data);
         $this->jump('index.php?p=show&c=admin&a=fabu','修改成功',3);

     }

     function dlAction(){
         $id=$_GET['id'];
         $model=new ModelNew('pinglun');
         $model->delete($id);
         $this->jump("index.php?p=show&c=admin&a=sh",'删除成功',3);
     }

     //>>广告位列表
    public function bannerAction(){
         //>>获取乡镇
        $m = new ModelNew();
        $yonghuming = $_SESSION['yonghuming'];
        $userData = $m->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
        $xiangzhen = $userData[0]['xiangzhen'];
        //>>获取广告列表
        $bannerList = $m->findBySql("select * from sl_banner WHERE xiangzhen='$xiangzhen'");
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_bannerList.html";
    }
    //>>编辑广告位
    public function editBannerAction(){
        if (empty($_GET['id'])){
            $id = '';
            $rs = null;
        }else{
            $id = $_GET['id'];
            $m = new ModelNew();
            $rs = $m->findBySql("select * from sl_banner WHERE id=$id");
            $rs = $rs[0];
        }
        $yuming = $_SERVER['SERVER_NAME'];
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_bannerEdit.html";
    }
    //>>保存广告位
    public function saveBannerAction(){
        $id = $_POST['id'];
        $m = new ModelNew();
        if ($id==''){
            //>>新增
            $yonghuming = $_SESSION['yonghuming'];
            $userData = $m->findBySql("select xiangzhen from sl_user where yonghuming=$yonghuming");
            $data['xiangzhen'] = $userData[0]['xiangzhen'];
            $data['title'] = $_POST['title'];
            $data['link'] = $_POST['link'];
            $model = new ModelNew('banner');
            $model->insert($data);
            $this->jump('index.php?p=show&c=admin&a=banner','增加成功',3);
        }else{
            //>>修改
            $data['title'] = $_POST['title'];
            $data['link'] = $_POST['link'];
            $model = new ModelNew('banner');
            $model->where(['id'=>$id])->update($data);
            $this->jump('index.php?p=show&c=admin&a=banner','修改成功',3);
        }
    }
    //>>保存图片
    public function savePicAction(){
        $base64_img = trim($_POST['data']);
        $up_dir = 'uploadtmp/';//存放在当前目录的upload文件夹下
        if(!file_exists($up_dir)){
            mkdir($up_dir,0777);
        }
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.date('YmdHis_').uniqid().'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    $img_path = str_replace('../../..', '', $new_file);
                    echo json_encode(array('code'=>200,'msg'=>'图片上传成功','path'=>$img_path));
                }else{
                    echo json_encode(array('code'=>501,'msg'=>'图片上传失败'));
                }
            }else{
                //文件类型错误
                echo json_encode(array('code'=>502,'msg'=>'图片上传类型错误'));
            }

        }else{
            //文件错误
            echo json_encode(array('code'=>503,'msg'=>'文件错误'));
        }
    }
    //>>删除广告位
    public function delBannerAction(){
        if (empty($_GET['id'])){
        $msg = '删除失败';
        }else{
            $id = $_GET['id'];
            $model = new ModelNew('banner');
            $model->delete($id);
            $msg = '删除成功';
        }
        $this->jump('index.php?p=show&c=admin&a=banner',$msg,3);
    }
    //>>用户关系管理
    public function userMemberAction(){
        //>>查询乡镇关联用户
        $m = new ModelNew();
        $yonghuming = $_SESSION['yonghuming'];
        $userData = $m->findBySql("select identification_code as code from sl_city as a JOIN sl_user as b on a.scenic_spot=b.xiangzhen  where b.yonghuming=$yonghuming ");
        $code = $userData[0]['code'];
        //>>通过乡镇的code获取数据用户数据
        $userData = $m->findBySql("select a.*,b.pinglun_status,b.sjx_status,b.id as mid from sl_member as a JOIN sl_user_member as b on a.id=b.uid where b.code='$code'");
//        var_dump($userData);exit();
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_user_member_list.html";
    }
    //>>设置用户权限
    public function memberQuanxianAction(){
        $field = $_POST['field'];
        $id = $_POST['id'];
        if ($field==1){
            $data['pinglun_status'] = $_POST['val'];
        }elseif ($field==2){
            $data['sjx_status'] = $_POST['val'];
        }
        $model = new ModelNew('user_member');
        $model->where(["id"=>$id])->update($data);
    }
    //>>设置名片
    public function cardAction(){
        //>>查询名片
        $user_id = $_SESSION["id"];
        $userModel = new ModelNew("user");
        $xiangzhen = $userModel->findBySql("select `xiangzhen` from sl_user WHERE id=$user_id")[0]['xiangzhen'];
        $mingpianModel = new ModelNew('mingpian');
        $cards = $mingpianModel->findBySql("select * from sl_mingpian WHERE xiangzhen='$xiangzhen'");
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_card.html";
    }
    public function setCardAction(){
        if (empty($_GET['id'])){
            $id = '';
            $rs = '';
            //>>查询该乡镇是否已拥有名片
            $user_id = $_SESSION["id"];
            $userModel = new ModelNew("user");
            $xiangzhen = $userModel->findBySql("select `xiangzhen` from sl_user WHERE id=$user_id")[0]['xiangzhen'];
            $mingpianModel = new ModelNew('mingpian');
            $isHave = $mingpianModel->findBySql("select * from sl_mingpian WHERE xiangzhen='$xiangzhen'");
            if ($isHave){
                $mingpianId = $isHave[0]['id'];
                $this->jump('index.php?p=show&c=admin&a=setCard&id='.$mingpianId,"已有名片，直接编辑",3);
            }else{
                include CUR_VIEW_PATH . "Sadmin" . DS . "admin_editCard.html";
            }
        }else{
            $id = $_GET['id'];
            $mingpianModel = new ModelNew('mingpian');
            $rs = $mingpianModel->findBySql("select * from sl_mingpian WHERE id=$id");
            $rs = $rs[0];
            include CUR_VIEW_PATH . "Sadmin" . DS . "admin_editCard.html";
        }
    }
    public function saveCardAction(){
        $id = $_POST['id'];
        if ($id == ''){
            //>>新增名片
            $data['biaoti'] = $_POST['biaoti'];
            $data['jianjie'] = $_POST['jianjie'];
            $user_id = $_SESSION["id"];
            $userModel = new ModelNew("user");
            $xiangzhen = $userModel->findBySql("select `xiangzhen` from sl_user WHERE id=$user_id")[0]['xiangzhen'];
            $data['xiangzhen'] = $xiangzhen;
            $mingpianModel = new ModelNew('mingpian');
            if ($mingpianModel->insert($data)){
                $this->jump('index.php?p=show&c=admin&a=card','添加名片成功',3);
            }else{
                $this->jump('index.php?p=show&c=admin&a=setCard','添加名片失败，请重试',3);
            }
        }else{
            //>>修改名片
            $data['biaoti'] = $_POST['biaoti'];
            $data['jianjie'] = $_POST['jianjie'];
            $data['dtime'] = date('Y-m-d H:i:s',time());
            $mingpianModel = new ModelNew('mingpian');
            if ($mingpianModel->where(['id'=>$id])->update($data)){
                $this->jump('index.php?p=show&c=admin&a=card','修改名片成功',3);
            }else{
                $this->jump('index.php?p=show&c=admin&a=setCard&id='.$id,'修改名片失败，请重试',3);
            }
        }
    }
    //>>意见收集页
    public function yjfkAction(){
        //>>获取用户乡镇
        $xiangzhen = self::getXiangzhenAction();
        $model = new ModelNew();
        $num = $model->findBySql("select count(*) from sl_yjfk WHERE xiangzhen='$xiangzhen'")[0]['count(*)'];
        $_num=5;
        $total=empty(ceil($num/$_num))?1:ceil($num/$_num);
        $page=empty($_GET['page'])?1:$_GET['page'];
        if ($page<=1){
            $page=1;
            $_GET['page']=1;
        }
        if ($_GET['page']>$total){
            $page=$total;
            $_GET['page']=$total;
        }
        $min=$page-2;
        $max=$page+5;
        if ($min<=1){
            $min=1;
        };
        if ($max>=$total){
            $max=$total;
        }
        $act=($page-1)*$_num;
        $rs=$model->findBySql("select * from sl_yjfk WHERE xiangzhen='".$xiangzhen."' limit {$act},{$_num}");
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_yjfk.html";
    }
    //>>意见详情
    public function yjxqAction(){
        //>>获取id
        $id = $_GET["id"];
        //>>获取意见详情
        $model = new ModelNew();
        $rs = $model->findBySql("select * from sl_yjfk WHERE id=$id")[0];
//        var_dump($yjxq);exit();
        include CUR_VIEW_PATH . "Sadmin" . DS . "admin_yjfk_xq.html";
    }
    //>>回复用户意见
    public function replyAction(){
        $id = $_POST["id"];
        $data["town_reply"] = $_POST["town_reply"];
        $model = new ModelNew("yjfk");
        if ($model->where(["id"=>$id])->update($data)){
            echo json_encode(["result"=>200]);
        }else{
            echo json_encode(["result"=>500]);
        }
    }
    //>>获取用户乡镇
    public static function getXiangzhenAction(){
        $user_id = $_SESSION["id"];
        $userModel = new ModelNew("user");
        $xiangzhen = $userModel->findBySql("select `xiangzhen` from sl_user WHERE id=$user_id")[0]['xiangzhen'];
        return $xiangzhen;
    }
    //检查用户是否有x权限
    public function checkAction(){
        $tpye = $_REQUEST['type'];
        if($tpye =='wx'){
            $tpye = '微信';
        }
        if ($tpye =='wb'){
            $tpye = '新浪微博';
        }
        if($tpye == 'tt'){
            $tpye = '今日头条';
        }
        if ($tpye =='qe'){
            $tpye = '企鹅';
        }
        $uid = $_SESSION['id'];

        $model = new ModelNew('task');
        $re = $model->findBySql("select *from sl_task where uid = '{$uid}' and `type` = '{$tpye}'");

        if(empty($re)){
            echo '你暂无此权限，请去绑定';die;
        }
    }
}