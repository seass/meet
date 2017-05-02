<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------

namespace Meetuser\Controller;

/**
 * 用户入口控制器
 */
class HomeController extends \Think\Controller{
    
    protected function _initialize(){
        /* 读取数据库中的配置 */
        $config	=	S('DB_CONFIG_DATA');
        if(!$config){
           $config	=	D('Config')->lists();
           S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
    }
    /**
     * 获取开启的大区列表
     * @author sea 
     */
    public function get_region_list(){
        $list = M('Region')->field("id,region_name")->where(['status'=>1])->select();
        return $list;
    }
    /**
     * 根据莫大区获取下属门店
     * @author sea
     */
    public function get_store_list(){
        $region_id=I("post.region_id");
        $list = M('Store')->field("id,store_name")->where(['status'=>1,'region_id'=>$region_id])->select();
        $this->ajaxReturn(['data'=>$list]);
    }
    
	/**
	 * 用户会议入口
	 * @author sea 
	 */
    public function index(){
        //会议ID
        if(empty($_GET["Mid"])){
            $this->redirect('Index/error');
        }
        $_info=M("Meet")->field("id,is_open_register,begin_time,end_time")->where(['id'=>$_GET['Mid']])->find();
        if(empty($_info)){
            $this->redirect('Index/error');
        }
        if($_info['is_open_register']==1){
            //检查注册开放时间
            if(strtotime($_info['begin_time'])>time()){
                $this->assign('grade',1);
                $this->assign('msg', '注册开始时间为'.$_info['begin_time'].'，请等候注册');
                $this->assign('login_url',U('/Meetuser/Index/index/Mid/'.$_GET["Mid"]));
                $this->display('Index/register');
                exit;
            }
            if(strtotime($_info['end_time'])<time()){
                $this->assign('grade',2);
                $this->assign('msg', '已超过注册开发时间');
                $this->assign('login_url',U('/Meetuser/Index/index/Mid/'.$_GET["Mid"]));
                $this->display('Index/register');
                exit;
            }
            $this->assign('login_url',U('/Meetuser/Index/index/Mid/'.$_GET["Mid"]));
            $this->assign('region_list',$this->get_region_list());
            $this->display('Index/register');
        }else{
            //未开放注册 直接登录
            $this->redirect('Index/login',$_GET);
        }
    }
    
    

}