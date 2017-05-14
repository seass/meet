<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
/**
 * 会议人员签到管理控制器
 * @author sea <919873148.qq.com>
 */
class MeetMemberSignController extends AdminController {

    //Model(表名)
    public $_model="MeetMemberSign";
    
    /**
     * 会议人员签到管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $_meet_id   =   I('_meet_id');
        $classes_id =   I('classes_id');
        //模糊搜索
        if(!empty($_key)){
            $map['m.realname|m.phone|m.user_no']    =   array('like', '%'.(string)$_key.'%');
        }
        if(!empty($_meet_id)){
            $map['me.id']=$_meet_id;
        }
        if(!empty($classes_id)){
            $map['c.id']=$classes_id;
        }
        $map['m.status']= 1;
        $map['me.status']= 1;
        $map['mms.status']= 1;
        $list=M()->table(C('DB_PREFIX').strtolower('meet_member_sign').' mms' )
                       ->where($map)
                       ->order('m.id DESC,mms.create_time desc')
                       ->join (' left join '.C('DB_PREFIX').('meet_member').' m ON m.id=mms.meet_member_id' )
                       ->join (' left join '.C('DB_PREFIX').('meet').' me ON me.id=m.meet_id' )
                       ->join (' left join '.C('DB_PREFIX').('classes').' c ON c.id=m.classes_id' )
                       ->join (' left join '.C('DB_PREFIX').('region').' r ON r.id=m.region_id' )
                       ->join (' left join '.C('DB_PREFIX').('city').' ci ON ci.id=m.city_id' )
                       ->join (' left join '.C('DB_PREFIX').('store').' s ON s.id=m.store_id' )
                       ->join (' left join '.C('DB_PREFIX').('meet_member').' m1 ON m1.id=m.room_meet_member_id' );
        $field='mms.id,m.user_no,m.realname,m.phone,m.sex,m.idcard,m.headimg,m.position,mms.create_time,'.
                'me.meet_name,c.classes_name,r.region_name,s.store_name,s.store_code,ci.city_name';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('_meet_id', $_meet_id);
        $this->assign('classes_id', $classes_id);
        $this->meta_title = '会议人员签到管理';
        $this->display();
    }
    /**
     * 状态修改
     * @author sea
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',null));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        if(empty($method)){
            $method=I('get.method',null);
        }
        //         var_dump($id);
        //         var_dump();exit;
        switch (strtolower($method)){
            case 'forbidmeetmembersign':
                $this->forbid($this->_model, $map );
                break;
            case 'resumemeetmembersign':
                $this->resume($this->_model, $map );
                break;
            case 'deletemeetmembersign':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
}
