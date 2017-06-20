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
 * 班级管理控制器
 * @author sea <919873148.qq.com>
 */
class ClassesController extends AdminController {

    //Model(表名)
    public $_model="Classes";
    
    /**
     * 班级管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $_meet_id   =   I('_meet_id');
        $map['c.status']    =   array('eq',1);
        //模糊搜索
        if(!empty($_key)){
            $map['c.classes_name|m.meet_name']    =   array('like', '%'.(string)$_key.'%');
        }
        if(!empty($_meet_id)){
            $map['m.id']=$_meet_id;
        }
        
        $list=M()->table(C('DB_PREFIX').strtolower($this->_model).' c' )
                       ->where($map)
                       ->order('c.id DESC')
                       ->join (C('DB_PREFIX').('meet').' m ON m.id=c.meet_id' );
        $field='c.id,c.classes_name,c.seat_img,c.status,c.create_time,m.meet_name,m.begin_time,m.end_time';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('_meet_id', $_meet_id);
        $this->meta_title = '班级管理';
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
            case 'forbidclasses':
                $this->forbid($this->_model, $map );
                break;
            case 'resumeclasses':
                $this->resume($this->_model, $map );
                break;
            case 'deleteclasses':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 新增班级
     * @author sea
     */
    public function add(){
        if(IS_POST){
            //绑定会议
            $meet_id=I('post.meet_id',0);
            if(empty($meet_id)){
                $this->error('请选择会议，若无选择项，请先去新增会议！');
            }
            $classes_name=I('post.classes_name');
            if(empty($classes_name)){
                $this->error('班级名称必填！');
            }
            //添加数据
            $add_res=M($this->_model)->add([
                'classes_name'=>$classes_name,
                'meet_id' =>$meet_id,
                'seat_img'=>I('post.seat_img'),//存储的onethink_picture表的ID
                'status'=>1,
                'imgs_text'=>I('post.imgs_text'),
                'is_show_info_before'=>I('post.is_show_info_before'),
            ]);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //记录行为(需要提前创建add_classes行为标记)
            action_log('add_classes',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            $this->meta_title = '新增班级';
            $this->display('edit');
        }
    }
    /**
     * 编辑班级
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            if(I('post.curr_type')==0){//检查基本信息
                //绑定会议
                $meet_id=I('post.meet_id',0);
                if(empty($meet_id)){
                    $this->error('请选择会议，若无选择项，请先去新增会议！');
                }
                $classes_name=I('post.classes_name');
                if(empty($classes_name)){
                    $this->error('班级名称必填！');
                }
                //编辑数据
                M($this->_model)->where(['id'=>$id])->save([
                    'classes_name'=>$classes_name,
                    'meet_id' =>$meet_id,
                    'seat_img'=>I('post.seat_img'),//存储的onethink_picture表的ID
                    'imgs_text'=>I('post.imgs_text'),
                    'is_show_info_before'=>I('post.is_show_info_before'),
                ]);
            }
            
            //记录行为(需要提前创建edit_classes行为标记)
            action_log('edit_classes',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $this->form();
            
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑班级';
            $this->display('edit');
        }
    }
    public function form(){
        $tablist=[
            0=>'基本信息',
            1=>'会议工作人员',
        ];
    
        //获取用户信息
        $userlist=M('Member m')->field('m.*,um.email,um.mobile,um.group_type,cl.id as cl_id')
        ->join (' left join '.C('DB_PREFIX').('ucenter_member').' um ON um.id=m.uid' )
        ->join (' left join '.C('DB_PREFIX').('classes_leader').' cl ON cl.uid=um.id and cl.classes_id='.I('get.id'))
        ->where(['um.group_type'=>2,'m.status'=>['egt',0]])->order('m.uid DESC')->select();
    
        $this->assign('userlist', $userlist);
        $this->assign('classes_id', I('get.id'));
        $curr_type = I('curr_type',0);//默认0 显示基本信息
        $this->assign('tablist', $tablist);
        $this->assign('curr_type', $curr_type);
    }
    /**
     * 操作班级负责人
     */
    public function classes_leader(){
        $op=I('post.op','');
        $classes_id=I('post.classes_id','');
        $uid=I('post.uid','');
    
        $return['status']=false;
        if(empty($op) || empty($classes_id) || empty($uid)){
            $return['smg']='操作异常，参数错误！';
            $this->ajaxReturn($return);
        }
        if($op=='add'){
            $add_res=M('ClassesLeader')->add(['uid'=>$uid,'classes_id'=>$classes_id]);
            if($add_res!==false){
                $return['status']=true;
            }else{
                $return['smg']='添加异常！';
            }
        }elseif($op=='del'){
            $del_res=M('ClassesLeader')->where(['uid'=>$uid,'classes_id'=>$classes_id])->delete();
            if($del_res!==false){
                $return['status']=true;
            }else{
                $return['smg']='移除异常！';
            }
        }
        $this->ajaxReturn($return);
    }
}
