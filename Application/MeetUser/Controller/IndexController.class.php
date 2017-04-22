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
class IndexController extends \Think\Controller{
	//系统首页
    public function index(){
        //var_dump($_GET['Mid']);exit;
        //每次进入必须带会议id
        if(empty($_GET["Mid"])){
            $this->redirect('Index/error');
        }
        //获取当前会议用户ID
//         define('MUID',is_muser_login());
//         if( !MUID ){// 还没登录 跳转到登录页面
//             $this->redirect('Index/login',$_GET);
//         }
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
        
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        
        $this->display();
    }
    /**
     * 检查是否登录
     */
    public function login(){
        if(IS_POST){
            $this->redirect('Index/index',['Mid'=>$_POST['Mid']]);
        }else{
            //每次进入必须带会议id
            if(empty($_GET["Mid"])){
                $this->redirect('Index/error');
            }
            if(is_muser_login()){
                $this->redirect('Index/index',$_GET);
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置
                $this->display();
            }
        }
    }
        

}