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
 * 会议管理控制器
 * @author sea <919873148.qq.com>
 */
class MeetController extends AdminController {

    //Model(表名)
    public $_model="Meet";
    
    /**
     * 会议管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $map['status']  =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['meet_name']    =   array('like', '%'.(string)$_key.'%');
        }
        $field='id,meet_name,status,create_time,begin_time,end_time,is_open_register';
        $list   = $this->lists($this->_model, $map,null,null,$field);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '会议管理';
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
            case 'forbidmeet':
                $this->forbid($this->_model, $map );
                break;
            case 'resumemeet':
                $this->resume($this->_model, $map );
                break;
            case 'deletemeet':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 获取保存数据并验证
     * @author sea 
     */
    public function getSaveData(){
        if(I('post.curr_type')==0){//检查基本信息
            $meet_name=I('post.meet_name');
            if(empty($meet_name)){
                $this->error('会议名称必填！');
            }
            //是否开放注册
            $is_open_register=I('post.is_open_register');
            if($is_open_register==1){
                $begin_time=I('post.begin_time');
                if(empty($begin_time)){
                    $this->error('报名开始时间必填！');
                }
                $end_time=I('post.end_time');
                if(empty($end_time)){
                    $this->error('报名结束时间必填！');
                }
                if(strtotime($begin_time)>strtotime($end_time)){
                    $this->error('报名开始时间不能大于报名结束时间！');
                }
            }
            $data=[
                'meet_name'=>$meet_name,
                'is_open_register'=>$is_open_register,
                'status'=>I('post.status'),
            ];
            if(!empty(I('post.begin_time'))){
                $data['begin_time']=I('post.begin_time');
            }
            if(!empty(I('post.end_time'))){
                $data['end_time']=I('post.end_time');
            }
        }
        if(!empty(I('post.hyxz'))){
            $data=[
                'hyxz'=>I('post.hyxz'),
             ];
        }
        if(!empty(I('post.rcap'))){
            $data=[
                'rcap'=>I('post.rcap'),
            ];
        }
        if(!empty(I('post.gzry'))){
            $data=[
                'gzry'=>I('post.gzry'),
            ];
        }
        if(!empty(I('post.zsap'))){
            $data=[
                'zsap'=>I('post.zsap'),
            ];
        }
        if(!empty(I('post.car'))){
            $data=[
                'car'=>I('post.car'),
            ];
        }
        if(!empty(I('post.food'))){
            $data=[
                'food'=>I('post.food'),
            ];
        }
        return $data;
    }
    
    /**
     * 新增会议
     * @author sea
     */
    public function add(){
        if(IS_POST){
            $saveData=$this->getSaveData();
            //添加数据
            $add_res=M($this->_model)->add($saveData);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //记录行为(需要提前创建add_meet行为标记)
            action_log('add_meet',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            $this->form();
            $this->meta_title = '新增会议';
            $this->display('edit');
        }
    }
    public function form(){
        $tablist=[
            0=>'基本信息',
            1=>'会议须知',
            2=>'日程安排',
            3=>'工作人员信息',
            4=>'住宿安排',
            5=>'车辆安排',
            6=>'用餐安排',
        ];
        $curr_type = I('curr_type',0);//默认0 显示基本信息
        $this->assign('tablist', $tablist);
        $this->assign('curr_type', $curr_type);
    }
    
    /**
     * 编辑会议
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $saveData=$this->getSaveData();
            //编辑数据
            M($this->_model)->where(['id'=>$id])->save($saveData);
          
            //记录行为(需要提前创建edit_meet行为标记)
            action_log('edit_meet',$this->_model, $id, UID);
            $this->success('操作成功！',$_SERVER['HTTP_REFERER']);
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            
            $this->form();
            
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑会议';
            $this->display('edit');
        }
    }
    
}
