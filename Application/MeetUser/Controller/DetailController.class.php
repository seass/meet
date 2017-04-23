<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------

namespace MeetUser\Controller;

/**
 * 会议前台首页控制器
 */
class DetailController extends MeetUserController{
    
	//系统首页
    public function graph_text(){
        $type=I("get.type");
        var_dump($type);exit;
        $this->display();
    }
    

}