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
 * 城市管理控制器
 * @author sea <919873148.qq.com>
 */
class CityController extends AdminController {

    //Model(表名)
    public $_model="City";
    
    /**
     * 城市管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $brand_id   =   I('brand_id',null);
        $region_id   =   I('region_id',null);
        $map['c.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['c.city_name|r.region_name|b.brand_name']    =   array('like', '%'.(string)$_key.'%');
        }
        if(!empty($brand_id)){
            $map['c.brand_id']=$brand_id;
        }
        if(!empty($region_id)){
            $map['c.region_id']=$region_id;
        }
        
        $list=M()->table(C('DB_PREFIX').strtolower($this->_model).' c' )
                       ->where($map)
                       ->order('c.id DESC')
                       ->join (C('DB_PREFIX').('region').' r ON r.id=c.region_id' )
                       ->join (C('DB_PREFIX').('brand').' b ON b.id=c.brand_id' );
        $field='c.id,c.city_name,c.status,c.create_time,r.region_name,b.brand_name';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('brand_id', $brand_id);
        $this->assign('region_id', $region_id);
        $this->meta_title = '城市管理';
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
            case 'forbidcity':
                $this->forbid($this->_model, $map );
                break;
            case 'resumecity':
                $this->resume($this->_model, $map );
                break;
            case 'deletecity':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 新增城市
     * @author sea
     */
    public function add(){
        if(IS_POST){
            //绑定品牌
            $brand_id=I('post.brand_id',0);
            if(empty($brand_id)){
                $this->error('请选择品牌，若无选择项，请先去新增品牌！');
            }
            //绑定大区
            $region_id=I('post.region_id',0);
            if(empty($region_id)){
                $this->error('请选择大区，若无选择项，请先去新增大区！');
            }
            $city_name=I('post.city_name');
            if(empty($city_name)){
                $this->error('城市名称必填！');
            }
            //添加数据
            $add_res=M($this->_model)->add([
                'city_name'=>$city_name,
                'region_id' =>$region_id,
                'brand_id' =>$brand_id,
                'status'=>I('post.status')
            ]);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //记录行为(需要提前创建add_city行为标记)
            action_log('add_city',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            $this->meta_title = '新增城市';
            $this->display('edit');
        }
    }
    /**
     * 编辑城市
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            //绑定品牌
            $brand_id=I('post.brand_id',0);
            if(empty($brand_id)){
                $this->error('请选择品牌，若无选择项，请先去新增品牌！');
            }
            //绑定大区
            $region_id=I('post.region_id',0);
            if(empty($region_id)){
                $this->error('请选择大区，若无选择项，请先去新增大区！');
            }
            $city_name=I('post.city_name');
            if(empty($city_name)){
                $this->error('城市名称必填！');
            }
            //原数据
            $old_info= M($this->_model)->where(['id'=>$id])->find();
            
            
            //编辑数据
            $up_res=M($this->_model)->where(['id'=>$id])->save([
                'city_name'=>$city_name,
                'region_id' =>$region_id,
                'brand_id' =>$brand_id,
                'status'=>I('post.status')
            ]);
            if($up_res!==false){
                //若绑定的品牌发生变更 同步冗余数据
                $up_old_data=[];
                if($old_info['brand_id']!=$brand_id){
                    $up_old_data['brand_id']=$brand_id;
                }
                if($old_info['region_id']!=$region_id){
                    $up_old_data['region_id']=$region_id;
                }
                if (!empty($up_old_data)){
                    //更新表
                    M('Store')->where(['city_id'=>$id])->save($up_old_data);
                    M('MeetMember')->where(['city_id'=>$id])->save($up_old_data);
                }
            }
            
            
            //记录行为(需要提前创建edit_city行为标记)
            action_log('edit_city',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑城市';
            $this->display('edit');
        }
    }
}
