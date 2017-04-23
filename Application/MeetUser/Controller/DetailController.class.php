<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------

namespace MeetUser\Controller;

use MeetUser\Service\MeetService;
/**
 * 会议前台首页控制器
 */
class DetailController extends MeetUserController{
    
    public static  $_field_config=[
        1=>'hyxz',//会议须知
        2=>'rcap',//日程安排
        3=>'gzry',//工作人员信息
        4=>'zsap',//住宿安排
        5=>'car',//车辆安排
        6=>'food',//用餐安排
        
        6=>'food',//班级座位图片
    ];
    
    /**
     * 图文混合的详情
     * @author sea 
     */
    public function graph_text(){
        $type=I("get.type");
        //获取信息
        $html_text=MeetService::getMeeetFieldByMUid(MUID,self::$_field_config[$type]);
        $this->assign('html_text', $html_text);
        $this->display();
    }
    /**
     * 班级座位图
     * @author sea
     */
    public function classes_seat_img(){
        //获取信息
        $seat_img=MeetService::getMeeetFieldByMUid(MUID,'seat_img');
        $img_path=MeetService::getImgUrlByid($seat_img);
        //var_dump($img_path);exit;
        $this->assign('img_path', $img_path);
        $this->display();
    }
    /**
     * 会议资料
     * @author sea
     */
    public function meet_attchment(){
        //获取信息
        $meet_id=MeetService::getMeeetFieldByMUid(MUID,'meet_id');
        $_list=MeetService::getMeetAttchmentList($meet_id);
        $this->assign('_list', $_list);
        $this->display();
    }
    

}