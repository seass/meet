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
class meetController extends AdminController {

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
        $field='id,meet_name,status,create_time,begin_time,end_time';
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
        $meet_name=I('post.meet_name');
        if(empty($meet_name)){
            $this->error('会议名称必填！');
        }
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
        //var_dump($_POST['hyxz']);exit;
        $data=[
            'meet_name'=>$meet_name,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
            'hyxz'=>I('post.hyxz'),
            'rcap'=>I('post.rcap'),
            'gzry'=>I('post.gzry'),
            'zsap'=>I('post.zsap'),
            'car'=>I('post.car'),
            'food'=>I('post.food'),
            'status'=>I('post.status')
        ];
        return $data;
    }
    
    /**
     * 新增会议
     * @author sea
     */
    public function add(){
        if(IS_POST){
            $meet_name=I('post.meet_name');
            if(empty($meet_name)){
                $this->error('会议名称必填！');
            }
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
            $this->meta_title = '新增会议';
            $this->display('edit');
        }
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
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑会议';
            $this->display('edit');
        }
    }
    
}
