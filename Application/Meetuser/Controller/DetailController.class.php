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
 * 会议前台首页控制器
 */
class DetailController extends MeetuserController{
    
    public static  $_field_config=[
        1=>['key'=>'hyxz','name'=>'会议须知'],
        2=>['key'=>'rcap','name'=>'会议日程'],
        3=>['key'=>'gzry','name'=>'工作人员'],
        4=>['key'=>'zsap','name'=>'住宿安排'],
        5=>['key'=>'car','name'=>'车辆安排'],
        6=>['key'=>'food','name'=>'用餐安排'],
    ];
    
    /**
     * 图文混合的详情
     * @author sea 
     */
    public function graph_text(){
        $type=I("get.type");
        //获取信息
        $html_text=MeetService::getMeeetFieldByMUid(MUID,self::$_field_config[$type]['key']);
        $this->assign('html_text', $html_text);
        $this->assign('slogan_title',self::$_field_config[$type]['name']);
        $this->assign('type', $type);
        if($type==4){
            //住宿安排 显示用户的住宿信息
            $stay_info=M('MeetMember mm')->where(['mm.id'=>MUID])
                ->field("mm.hotel_type,mm.house_type,mm.checkin_date,mm.leave_date,m1.realname,m1.phone")
                ->join (' left join '.C('DB_PREFIX').('meet_member').' m1 ON m1.id=mm.room_meet_member_id')
                ->find();
            $this->assign('stay_info', $stay_info);
        }
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
        $classes_name=MeetService::getMeeetFieldByMUid(MUID,'classes_name');
        $this->assign('classes_name', $classes_name);
        $this->assign('slogan_title','班级信息');
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
        $this->assign('slogan_title','会议资料');
        $this->display();
    }
    

}