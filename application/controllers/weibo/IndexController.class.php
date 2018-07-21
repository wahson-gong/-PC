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
        include "public/weibo/callback.php";
    }


}