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
        $map['m.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['m.realname|me.meet_name']    =   array('like', '%'.(string)$_key.'%');
        }
        $list=M()->table(C('DB_PREFIX').('meet_member').' m' )
                       ->where($map)
                       ->order('m.id DESC')
                       ->join (' left join '.C('DB_PREFIX').('meet').' me ON me.id=m.meet_id' )
                       ->join (' left join '.C('DB_PREFIX').('classes').' c ON c.id=m.classes_id' )
                       ->join (' left join '.C('DB_PREFIX').('region').' r ON r.id=m.region_id' )
                       ->join (' left join '.C('DB_PREFIX').('store').' s ON s.id=m.store_id' );
        $field='m.id,m.user_no,m.realname,m.phone,m.sex,m.idcard,m.position,m.status,m.create_time,me.meet_name,me.begin_time,me.end_time,c.classes_name,r.region_name,s.store_name';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->meta_title = '会议人员管理';
        $this->display();
    }
    /**
     * 状态修改
     * @author sea
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
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
            case 'forbidMeetMember':
                $this->forbid($this->_model, $map );
                break;
            case 'resumeMeetMember':
                $this->resume($this->_model, $map );
                break;
            case 'deleteMeetMember':
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
        if(empty($idcard)){
            $this->error('会议人员证件号必填！');
        }
        $position=I('post.position');
        if(empty($position)){
            $this->error('会议人员职务必填！');
        }
        //上传头像 $_FILES['headimg'];????????
        
        
        $region_id=M('Store')->where(['id'=>$store_id])->getField('region_id');
        $meet_id=M('Classes')->where(['id'=>$classes_id])->getField('meet_id');
        $data=[
            'region_id'=>$region_id,//大区id
            'store_id'=>$store_id,
            'meet_id'=>$meet_id,//会议id
            'classes_id'=>$classes_id,
            'realname'=>$realname,
            'user_no'=>get_user_no($meet_id),//生成会议编号
            'sex'=>I('post.sex'),
            'phone'=>$phone,
            'idcard'=>$idcard,
            'position'=>$position,
            'score'=>I('post.score'),
            'is_share'=>I('post.is_share'),
            'food_req'=>I('post.food_req'),
            'status'=>I('post.status')
        ];
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
            //添加数据
            $add_res=M($this->_model)->add($save_data);
            if($add_res==false){
                $this->error('新增失败！');
            }
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
}
