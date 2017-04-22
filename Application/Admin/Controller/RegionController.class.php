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
 * 大区管理控制器
 * @author sea <919873148.qq.com>
 */
class RegionController extends AdminController {

    //Model(表名)
    public $_model="Region";
    
    /**
     * 大区管理列表
     * @author sea 
     */
    public function index(){
        $region_name       =   I('region_name');
        $map['status']  =   array('egt',0);
        //模糊搜索
        if(!empty($region_name)){
            $map['region_name']    =   array('like', '%'.(string)$region_name.'%');
        }
        $field='id,region_name,status,create_time';
        $list   = $this->lists($this->_model, $map,null,null,$field);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '大区管理';
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
            case 'forbidregion':
                $this->forbid($this->_model, $map );
                break;
            case 'resumeregion':
                $this->resume($this->_model, $map );
                break;
            case 'deleteregion':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 新增大区
     * @author sea
     */
    public function add(){
        if(IS_POST){
            $region_name=I('post.region_name');
            if(empty($region_name)){
                $this->error('大区名称必填！');
            }
            //添加数据
            $add_res=M($this->_model)->add(['region_name'=>$region_name,'status'=>I('post.status')]);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //记录行为(需要提前创建add_region行为标记)
            action_log('add_region',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            $this->meta_title = '新增大区';
            $this->display('edit');
        }
    }
    /**
     * 编辑大区
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $region_name=I('post.region_name');
            if(empty($region_name)){
                $this->error('大区名称必填！');
            }
            //编辑数据
            M($this->_model)->where(['id'=>$id])->save(['region_name'=>$region_name,'status'=>I('post.status')]);
          
            //记录行为(需要提前创建edit_region行为标记)
            action_log('edit_region',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑大区';
            $this->display('edit');
        }
    }
}
