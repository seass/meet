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
        //每次进入必须带会议id
        if(empty($_GET["Mid"])){
            $this->redirect('Index/error');
        }
        //获取当前会议用户ID
        define('MUID',is_muser_login($_GET["Mid"]));
        if( !MUID ){// 还没登录 跳转到登录页面
            $this->redirect('Index/login',$_GET);
        }
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
            $this->error('站点已经关闭，请稍后访问~');
            $MUser=M("MeetMember")->where(['id'=>$_POST['Mid']])->find();
            //self::autoLogin($MUser);
            
            $this->redirect('Index/index',['Mid'=>$_POST['Mid']]);
        }else{
            //每次进入必须带会议id
            if(empty($_GET["Mid"])){
                $this->redirect('Index/error');
            }
            if(is_muser_login($_GET["Mid"])){
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
    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
                'login_count'     => array('exp', '`login_count`+1'),
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip'   => get_client_ip(1),
        );
        M("MeetMember")->where(['id'=>$user['id']])->setField($data);
    
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'              => $user['id'],
            'realname'        => $user['realname'],
            'phone'           => $user['phone'],
            'last_login_time' => $user['last_login_time'],
        );
        session('muser_auth_'.$user['id'], $auth);
        session('muser_auth_sign_'.$user['id'], data_auth_sign($auth));
    
    }
    

}