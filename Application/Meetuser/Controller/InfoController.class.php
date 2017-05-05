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
        $userInfo=M()->table(C('DB_PREFIX').strtolower('meet_member').' m' )
            ->join (' left join '.C('DB_PREFIX').('meet').' me ON me.id=m.meet_id' )
            ->join (' left join '.C('DB_PREFIX').('classes').' c ON c.id=m.classes_id' )
            ->join (' left join '.C('DB_PREFIX').('region').' r ON r.id=m.region_id' )
            ->join (' left join '.C('DB_PREFIX').('city').' ci ON ci.id=m.city_id' )
            ->join (' left join '.C('DB_PREFIX').('store').' s ON s.id=m.store_id' )
            ->join (' left join '.C('DB_PREFIX').('meet_member').' m1 ON m1.id=m.room_meet_member_id' )
            ->field("m.id,m.user_no,m.realname,m.phone,m.sex,m.idcard,m.headimg,m.position,m.food_req,m.hotel_type,m.house_type,m.checkin_date,m.leave_date,
                r.region_name,ci.city_name,s.store_name,s.store_code,m1.realname as roommate_name,m1.phone as roommate_phone")
            ->where(['m.id'=>$_GET['MUid']])->find();
        //获取图片地址
        $userInfo['headimg']=MeetService::getImgUrlByid($userInfo['headimg']);
        //var_dump($userInfo);exit;
        $this->assign('info', $userInfo);
        $this->assign('slogan_title','个人信息');
        $this->display();
    }
    
    

}