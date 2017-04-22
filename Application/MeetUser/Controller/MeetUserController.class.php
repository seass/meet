<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------

namespace MeetUser\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class MeetUserController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}
    protected function _initialize(){
        //每次进入必须带会议id
        if(empty($_GET["Mid"])){
            $this->redirect('Index/error');
        }
        //获取当前会议用户ID
        define('MUID',is_muser_login($_GET["Mid"]));
        if( !MUID ){// 还没登录 跳转到登录页面
            $this->redirect('Index/login');
        }
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
    }
}
