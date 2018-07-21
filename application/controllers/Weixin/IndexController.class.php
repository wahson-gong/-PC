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
        include CUR_VIEW_PATH . "Sindex" .DS ."index.html";
    }

    public function wxaAction(){
        include CUR_VIEW_PATH . "Sweixin" .DS ."index.html";
       
    }

    public function textAction(){

    }


}