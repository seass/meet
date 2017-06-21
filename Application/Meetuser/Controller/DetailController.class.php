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
        1=>['key'=>'hyxz','name'=>'会议信息'],
        2=>['key'=>'rcap','name'=>'会议日程'],
        3=>['key'=>'gzry','name'=>'工作人员'],
        4=>['key'=>'zsap','name'=>'住宿安排'],
        5=>['key'=>'car','name'=>'车辆安排'],
        6=>['key'=>'food','name'=>'用餐安排'],
        7=>['key'=>'imgs_text','name'=>'会议照片'],
        8=>['key'=>'qrcode','name'=>'我的二维码'],
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
        
        //工作人员页面 展示班级负责人
        if($type==3){
            if(!empty(session('amuser_auth.mid'))){
                $where=['m.id'=>session('amuser_auth.mid')];
                $leader_list=M('ClassesLeader cl')->where($where)
                    ->field('um.username,um.mobile,um.email')
                    ->join (' inner join '.C('DB_PREFIX').('ucenter_member').' um ON um.id=cl.uid')
                    ->join (' inner join '.C('DB_PREFIX').('classes').' c ON c.id=cl.classes_id')
                    ->join (' inner join '.C('DB_PREFIX').('meet').' m ON m.id=c.meet_id')
                    ->group('um.id')
                    ->select();
            }else{
                $where=['mm.id'=>MUID];
                $leader_list=M('ClassesLeader cl')->where($where)
                    ->field('um.username,um.mobile,um.email')
                    ->join (' inner join '.C('DB_PREFIX').('ucenter_member').' um ON um.id=cl.uid')
                    ->join (' inner join '.C('DB_PREFIX').('classes').' c ON c.id=cl.classes_id')
                    ->join (' inner join '.C('DB_PREFIX').('meet').' m ON m.id=c.meet_id')
                    ->join (' inner join '.C('DB_PREFIX').('meet_member').' mm ON mm.meet_id=m.meet_id')
                    ->group('um.id')
                    ->select();
            }
            $this->assign('leader_list', $leader_list);
        }
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
        $info=MeetService::getMeeetFieldByMUid(MUID,'','array');
        
        if(isset($info['seat_img'])){
            $img_path=MeetService::getImgUrlByid($info['seat_img']);
            $this->assign('img_path', $img_path);
        }
        if(isset($info['classes_name'])){
            $this->assign('classes_name', $info['classes_name']);
        }
        if(isset($info['realname'])){
            $this->assign('realname', $info['realname']);
        }
        if(isset($info['is_show_info_before'])){
            $this->assign('is_show_info_before', $info['is_show_info_before']);
        }
        $this->assign('slogan_title','座位信息');
        $this->display();
    }
    /**
     * (会议照片)班级座位
     * @author sea
     */
    public function classes_imgs(){
        //获取信息
        $classes_id=MeetService::getMeeetFieldByMUid(MUID,'classes_id');
        $img_list=[];
        if(!empty($classes_id)){
            $map['ci.classes_id']   =   $classes_id;
            $map['ci.status']    =   array('egt',0);
            $img_list=M('ClassesImgs ci')
            ->field('ci.id,f.name,f.savepath,f.savename')
            ->where($map)
            ->order('ci.id DESC')
            ->join (' left join '.C('DB_PREFIX').('file').' f ON f.id=ci.file_id' )
            ->select();
        }
        $this->assign('img_list',$img_list);
        $this->assign('slogan_title','会议照片');
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
    /**
     * 我的二维码
     * @author sea 
     */
    public function qrcode(){
        $type=I("get.type");
        //获取信息
        $qrcode_url=MeetService::getMeeetFieldByMUid(MUID,self::$_field_config[$type]['key']);
        $this->assign('qrcode_url', $qrcode_url);
        $this->assign('slogan_title',self::$_field_config[$type]['name']);
        $this->display();
    }
    

}