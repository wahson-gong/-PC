<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
//require "application/controllers/show/WeixinController.class.php";
include "Uploader.class.php";
//include "WeixinController.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
             $a="public/img/".time();
        $config = array(
            "pathFormat" =>$a,
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */

//$up->getFileInfo()['url']=WeixinController::fabu1Action($up->getFileInfo()['url']);
$name=$up->getFileInfo();
$mysql=mysqli_connect('211.149.197.112','ceshi27','K5B8M7R6','ceshi27');
$sql="select *from sl_user WHERE yonghuming=18582479523";
$msg=mysqli_query($mysql,$sql);
$rs=mysqli_fetch_array($msg);
$name1=$rs['token'];
$name2='D:/wwwroot/ceshi28/wwwroot/'.$up->getFileInfo()['url'];
//         $name='D:/wwwroot/ceshi28/wwwroot/public/.png';

$post_data = array(

//             "media"=>new CURLFile('/wwwroot/public/1521631163.png')
    "media"=>'@'.$name2
);
//         var_dump($post_data);die;
$url="https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=$name1";
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


$name['url']=$_rs['url'];
//$name['url']=WeixinController::fabu1Action($up->getFileInfo()['url']);


return json_encode($name);


