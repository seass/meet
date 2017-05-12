<?php
namespace  Meetuser\Service;
/**
 * 会议服务Service
 */
class MeetService {
    
    /**
     * 获取会议和会议用户的某字段信息
     * @param unknown $muid 
     * $field:获取的字段
     */
    public static function getMeeetFieldByMUid($muid,$field){
        //var_dump(session('amuser_auth.mid'));exit;
        //管理员登录  看会议的信息
        if(!empty(session('amuser_auth.mid'))){
            //拦截admin  只能看会议的信息
            if(in_array($field, ['hyxz','rcap','gzry','zsap','car','food'])){
                $where=['m.id'=>session('amuser_auth.mid')];
            }else{
                return '';
            }
        }else{
            $where=['mm.id'=>$muid];
        }
        $info=M('MeetMember mm')->where($where)
                ->field("mm.meet_id,mm.classes_id,m.hyxz,m.rcap,m.gzry,m.zsap,m.car,m.food,c.seat_img,c.classes_name,c.imgs_text,mm.qrcode,mm.realname")
                ->join (' left join '.C('DB_PREFIX').('meet').' m ON m.id=mm.meet_id')
                ->join (' left join '.C('DB_PREFIX').('classes').' c ON c.id=mm.classes_id')
                ->find();
        if(!isset($info[$field])){
            return null;
        }
        return $info[$field];
    }
    /**
     * 获取图片地址
     * @author sea 
     */
    public static function getImgUrlByid($picture_id){
        if(empty($picture_id)){
            return null;
        }
        return M("Picture")->where(['id'=>$picture_id])->getField('path');
    }
    /**
     * 获取会议资料列表
     * @author sea
     */
    public static function getMeetAttchmentList($meet_id){
        if(empty($meet_id)){
            return null;
        }
        $field='ma.id,ma.create_time,ma.status,f.name,f.savepath,f.savename,f.mime,f.size';
        $list=M('MeetAttchment ma')->where(['ma.meet_id'=>$meet_id,'ma.status'=>1])
                ->order('ma.id DESC')
                ->join (' left join '.C('DB_PREFIX').('file').' f ON f.id=ma.file_id' )
                ->field($field)
                ->select();
        return $list;
    }
}
