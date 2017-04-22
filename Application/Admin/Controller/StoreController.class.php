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
 * 门店管理控制器
 * @author sea <919873148.qq.com>
 */
class StoreController extends AdminController {

    //Model(表名)
    public $_model="Store";
    
    /**
     * 门店管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $map['s.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['s.store_name|r.region_name']    =   array('like', '%'.(string)$_key.'%');
        }
        $list=M()->table(C('DB_PREFIX').strtolower($this->_model).' s' )
                       ->where($map)
                       ->order('s.id DESC')
                       ->join (C('DB_PREFIX').('region').' r ON r.id=s.region_id' );
        $field='s.id,s.store_name,s.status,s.create_time,r.region_name';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->meta_title = '门店管理';
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
            case 'forbidstore':
                $this->forbid($this->_model, $map );
                break;
            case 'resumestore':
                $this->resume($this->_model, $map );
                break;
            case 'deletestore':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 新增门店
     * @author sea
     */
    public function add(){
        if(IS_POST){
            //绑定大区
            $region_id=I('post.region_id',0);
            if(empty($region_id)){
                $this->error('请选择大区，若无选择项，请先去新增大区！');
            }
            $store_name=I('post.store_name');
            if(empty($store_name)){
                $this->error('门店名称必填！');
            }
            //添加数据
            $add_res=M($this->_model)->add([
                'store_name'=>$store_name,
                'region_id' =>$region_id,
                'status'=>I('post.status')
            ]);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //记录行为(需要提前创建add_store行为标记)
            action_log('add_store',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            $this->meta_title = '新增门店';
            $this->display('edit');
        }
    }
    /**
     * 编辑门店
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            //绑定大区
            $region_id=I('post.region_id',0);
            if(empty($region_id)){
                $this->error('请选择大区，若无选择项，请先去新增大区！');
            }
            $store_name=I('post.store_name');
            if(empty($store_name)){
                $this->error('门店名称必填！');
            }
            //编辑数据
            M($this->_model)->where(['id'=>$id])->save([
                'store_name'=>$store_name,
                'region_id' =>$region_id,
                'status'=>I('post.status')
            ]);
          
            //记录行为(需要提前创建edit_store行为标记)
            action_log('edit_store',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑门店';
            $this->display('edit');
        }
    }
}
