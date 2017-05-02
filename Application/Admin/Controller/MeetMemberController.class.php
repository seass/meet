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
 * 会议人员管理控制器
 * @author sea <919873148.qq.com>
 */
class MeetMemberController extends AdminController {

    //Model(表名)
    public $_model="MeetMember";
    
    /**
     * 会议人员管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $_meet_id   =   I('_meet_id');
        $map['m.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['m.realname|me.meet_name|m.phone|m.user_no|s.store_name|s.store_code|m1.realname']    =   array('like', '%'.(string)$_key.'%');
        }
        if(!empty($_meet_id)){
            $map['me.id']=$_meet_id;
        }
        $list=M()->table(C('DB_PREFIX').strtolower('meet_member').' m' )
                       ->where($map)
                       ->order('m.id DESC')
                       ->join (' left join '.C('DB_PREFIX').('meet').' me ON me.id=m.meet_id' )
                       ->join (' left join '.C('DB_PREFIX').('classes').' c ON c.id=m.classes_id' )
                       ->join (' left join '.C('DB_PREFIX').('region').' r ON r.id=m.region_id' )
                       ->join (' left join '.C('DB_PREFIX').('city').' ci ON ci.id=m.city_id' )
                       ->join (' left join '.C('DB_PREFIX').('store').' s ON s.id=m.store_id' )
                        ->join (' left join '.C('DB_PREFIX').('meet_member').' m1 ON m1.id=m.room_meet_member_id' );
        $field='m.id,m.user_no,m.realname,m.phone,m.sex,m.idcard,m.headimg,m.position,m.qrcode,'.
            'm.status,m.create_time,me.meet_name,me.begin_time,me.end_time,c.classes_name,r.region_name,'.
            's.store_name,s.store_code,ci.city_name,m.hotel_type,m1.realname as roommate_name,m.is_audit';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('_meet_id', $_meet_id);
        $this->meta_title = '会议人员管理';
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
        //var_dump($id);
        //var_dump($method);exit;
        switch (strtolower($method)){
            case 'forbidmeetmember':
                $this->forbid($this->_model, $map );
                break;
            case 'resumemeetmember':
                $this->resume($this->_model, $map );
                break;
            case 'deletemeetmember':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    public function getSaveData(){
        //绑定门店
        $store_id=I('post.store_id',0);
        if(empty($store_id)){
            $this->error('请选择门店，若无选择项，请先去新增门店！');
        }
        //所属会议
        $meet_id=I('post.meet_id',0);
        if(empty($meet_id)){
            $this->error('请选择会议，若无选择项，请先去新增会议！');
        }
        //所属班级
        $classes_id=I('post.classes_id',0);
        if(empty($classes_id)){
            $this->error('请选择所属班级，若无选择项，请先去新增班级！');
        }
        $realname=I('post.realname');
        if(empty($realname)){
            $this->error('会议人员姓名必填！');
        }
        $phone=I('post.phone');
        if(empty($phone)){
            $this->error('会议人员手机号必填！');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
            $this->error('会议人员手机号格式错误！');
        }
        $idcard=I('post.idcard');
//         if(empty($idcard)){
//             $this->error('会议人员证件号必填！');
//         }
        $position=I('post.position');
        if(empty($position)){
            $this->error('会议人员职务必填！');
        }
        $store_info=M('Store')->where(['id'=>$store_id])->field('id,brand_id,region_id,city_id')->find();
        //$meet_id=M('Classes')->where(['id'=>$classes_id])->getField('meet_id');
        $data=[
            'brand_id'=>$store_info['brand_id'],//品牌id
            'region_id'=>$store_info['region_id'],//大区id
            'city_id'=>$store_info['city_id'],//城市ID
            'store_id'=>$store_info['id'],//门店ID
            
            'meet_id'=>$meet_id,//会议id
            'classes_id'=>$classes_id,
            'realname'=>$realname,
            'user_no'=>get_user_no($meet_id),//生成会议编号
            'sex'=>I('post.sex'),
            'phone'=>$phone,
            'idcard'=>$idcard,
            'headimg'=>I('post.headimg'),//存储的onethink_picture表的ID
            'position'=>$position,
            'score'=>I('post.score'),
            'food_req'=>I('post.food_req'),
            'status'=>I('post.status'),
            'is_audit'=>I('post.is_audit'),
            'hotel_type'=>0,//不住宿,
            'house_type'=>0,
            'checkin_date'=>'',
            'leave_date'=>''
        ];
        //是否住宿
        $hotel_type=$_POST['hotel_type'];
        if(!empty($hotel_type)){
            if(empty($_POST["house_type"])){
                 $this->error('请选择房型！');
            }
            $checkin_date=$_POST['checkin_date'];
            if(empty($checkin_date)){
                $this->error('请选择入住时间！');
            }
            $leave_date=$_POST['leave_date'];
            if(empty($leave_date)){
                $this->error('请选择离店时间！');
            }
            if(strtotime($checkin_date)>strtotime($leave_date)){
                $this->error('入住时间不能大于离店时间！');
            }
            $data['hotel_type']=$hotel_type;
            $data['house_type']=$_POST["house_type"];
            $data['checkin_date']=$checkin_date;
            $data['leave_date']=$leave_date;
        }
        //新增时初始密码
        if(empty(I('post.id'))){
            $data['password']=md5(substr($phone, -6));
         }
        return $data;
    }
    /**
     * 新增会议人员
     * @author sea
     */
    public function add(){
        if(IS_POST){
            //获取保存数据
            $save_data=$this->getSaveData();
            /*
             * 检查用户的手机号 是否已经存在次会议的班级下
             */
            $check_res=M($this->_model)->where([
                                'meet_id'=>$save_data['meet_id'],
                                'phone'=>$save_data['phone'],
                                'status'=>array('neq',-1)
            ])->getField("id");
            if(!empty($check_res)){
                $this->error('此用户已经存在本次会议班级,请重新输入新用户！');
            }
            //添加数据
            $add_res=M($this->_model)->add($save_data);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //生成二维码
            $qrcode=createQrcode($add_res);
            M($this->_model)->where(['id'=>$add_res])->save(['qrcode'=>$qrcode]);
            
            //记录行为(需要提前创建add_meetmember行为标记)
            action_log('add_meetmember',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            
            $this->meta_title = '新增会议人员';
            $this->display('edit');
        }
    }
    /**
     * 编辑会议人员
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            //获取保存数据
            $save_data=$this->getSaveData();
            //编辑数据
            M($this->_model)->where(['id'=>$id])->save($save_data);
            //记录行为(需要提前创建add_meetmember行为标记)
            action_log('edit_meetmember',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑会议人员';
            $this->display('edit');
        }
    }
    /**
     * 分配班级
     * @author sea 
     */
    public function allotClasses(){
        $id = array_unique((array)I('id',null));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $classes_id = I('classes_id',null);
        if (empty($classes_id)) {
            $this->error('请选择要分配的班级!');
        }
        //分配班级
        $up_res=M($this->_model)->where(['id'=>['in',$id]])->save(['classes_id'=>$classes_id]);
        if($up_res!==false){
            $this->success("分配成功");
        }
        $this->error('操作失败!');
    }
    /**
     * 设置酒店同住室友
     * @author sea 
     */
    public function setRoommate(){
        $ids = array_unique((array)I('id',null));
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        if (count($ids)>2){
            $this->error('选择的同住室友，最多不能超过两个！');
        }
        //检查两个人是否都是合住 
        $checkData=M($this->_model)->where([
                            'id'=>array('in',$ids)
                        ])->field("hotel_type")->select();
        $hotel_type_arr=array_unique(array_column($checkData, 'hotel_type'));
        if(count($hotel_type_arr)>1){
            $this->error('选择的用户中包含不住宿或者单住的，不能进行设置同住室友！');
        }
        //设置同住室友
        $up_res1=M($this->_model)->where(['id'=>$ids[0]])->save(['room_meet_member_id'=>$ids[1]]);
        $up_res2=M($this->_model)->where(['id'=>$ids[1]])->save(['room_meet_member_id'=>$ids[0]]);
        if($up_res1!==false && $up_res2!==false){
            $this->success("设置成功");
        }
        $this->error('操作失败!');
    }
    /**
     * 签到
     */
    public function sign(){
        $meet_member_id=I('post.meet_member_id','');
        $add_res=M('MeetMemberSign')->add(['meet_member_id'=>$meet_member_id]);
        $return['status']=false;
        if($add_res!==false){
            $return['status']=true;
        }
        $this->ajaxReturn($return);
    }
}
