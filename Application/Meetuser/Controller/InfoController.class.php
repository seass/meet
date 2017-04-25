<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------

namespace Meetuser\Controller;

use Meetuser\Service\MeetService;
/**
 * 用户扫码跳转控制器
 */
class InfoController extends \Think\Controller{
    
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
	 * 展示个人信息
	 * @author sea 
	 */
    public function index(){
        if(empty($_GET["MUid"])){
            $this->redirect('Index/error');
        }
        $userInfo=M("MeetMember")->field("id,realname,phone,idcard,position,sex,headimg,food_req")->where(['id'=>$_GET['MUid']])->find();
        //获取图片地址
        $userInfo['headimg']=MeetService::getImgUrlByid($userInfo['headimg']);
        //var_dump($userInfo);exit;
        $this->assign('info', $userInfo);
        $this->display();
    }
    
    

}