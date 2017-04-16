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
        $map['c.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['c.classes_name|m.meet_name']    =   array('like', '%'.(string)$_key.'%');
        }
        $list=M()->table(C('DB_PREFIX').($this->_model).' c' )
                       ->where($map)
                       ->order('c.id DESC')
                       ->join (C('DB_PREFIX').('meet').' m ON m.id=c.meet_id' );
        $field='c.id,c.classes_name,c.seat_img,c.status,c.create_time,m.meet_name,m.begin_time,m.end_time';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->meta_title = '班级管理';
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
                'status'=>I('post.status')
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
                'status'=>I('post.status')
            ]);
            //记录行为(需要提前创建edit_classes行为标记)
            action_log('edit_classes',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑班级';
            $this->display('edit');
        }
    }
}
