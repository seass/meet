<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Util\Excel;
/**
 * 品牌管理控制器
 * @author sea <919873148.qq.com>
 */
class BrandController extends AdminController {

    //Model(表名)
    public $_model="Brand";
    
    /**
     * 品牌管理列表
     * @author sea 
     */
    public function index(){
        $brand_name      =   I('brand_name');
        $map['status']  =   array('egt',0);
        //模糊搜索
        if(!empty($brand_name)){
            $map['brand_name']    =   array('like', '%'.(string)$brand_name.'%');
        }
        $field='id,brand_name,status,create_time';
        $list   = $this->lists($this->_model, $map,null,null,$field);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '品牌管理';
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
            case 'forbidbrand':
                $this->forbid($this->_model, $map );
                break;
            case 'resumebrand':
                $this->resume($this->_model, $map );
                break;
            case 'deletebrand':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 新增品牌
     * @author sea
     */
    public function add(){
        if(IS_POST){
            $brand_name=I('post.brand_name');
            if(empty($brand_name)){
                $this->error('品牌名称必填！');
            }
            //添加数据
            $add_res=M($this->_model)->add(['brand_name'=>$brand_name,'status'=>I('post.status')]);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //记录行为(需要提前创建add_brand行为标记)
            action_log('add_brand',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            $this->meta_title = '新增品牌';
            $this->display('edit');
        }
    }
    /**
     * 编辑品牌
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $brand_name=I('post.brand_name');
            if(empty($brand_name)){
                $this->error('品牌名称必填！');
            }
            //编辑数据
            M($this->_model)->where(['id'=>$id])->save(['brand_name'=>$brand_name,'status'=>I('post.status')]);
          
            //记录行为(需要提前创建edit_brand行为标记)
            action_log('edit_brand',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑品牌';
            $this->display('edit');
        }
    }
    public $import_title=[
        0=>'大区*',
        1=>'城市*',
        2=>'经销店名称*',
        3=>'经销店代码*',
    ];
    /**
     * 导入信息
     * @author sea
     */
    public function import(){
        if(!IS_POST){
            $this->meta_title = '导入信息';
            $this->display('import');
        }else{
            $brand_id=$_POST['brand_id'];
            $excel = new Excel();
            $file_data = $excel->readerExcel($_FILES['import']['tmp_name'],0,14);
            // var_dump($file_data);exit;
            
            M("Region")->where(['brand_id'=>$brand_id])->setField('status',0);
            M("City")->where(['brand_id'=>$brand_id])->setField('status',0);
            M("Store")->where(['brand_id'=>$brand_id])->setField('status',0);
            
            $result_data=[];
            foreach ($file_data as $_info){
                $region_name=$_info[0];
                $city_name=$_info[1];
                $store_name=$_info[2];
                $store_code=$_info[3];
                if(empty($region_name)){
                    continue;
                }
                //检查文件格式
                $is_type_error=false;
                $key_error=null;
                foreach ($_info as $key=>$_val){
                    if(gettype($_val)=='object'){
                        $is_type_error=true;
                        $key_error=$key;
                    }
                }
                if($is_type_error){
                    $_info[4]='导入的列格式错误，导入失败！错误列：'.$this->import_title[$key_error];
                    $result_data[] = $_info;
                    continue;
                }
                if(empty($region_name) || empty($city_name) || empty($store_name) ||
                    empty($store_code)){
                        $_info[4]='必填项数据项为空,导入失败，请看表头*号！';
                        $result_data[] = $_info;
                        continue;
                }
                //检查大区是否存在
                $region_id=M("Region")->where(['region_name'=>$region_name,'brand_id'=>$brand_id,'status'=>1])->getField('id');
                if(empty($region_id)){
                    $region_id=M("Region")->add(['region_name'=>$region_name,'brand_id'=>$brand_id]);
                }
                //检查城市是否存在
                $city_id=M("City")->where(['city_name'=>$city_name,'brand_id'=>$brand_id,'region_id'=>$region_id,'status'=>1])->getField('id');
                if(empty($city_id)){
                    $city_id=M("City")->add(['city_name'=>$city_name,'brand_id'=>$brand_id,'region_id'=>$region_id]);
                }
                //检查门店是否存在
                $store_id=M("Store")->where(['store_name'=>$store_name,'brand_id'=>$brand_id,'region_id'=>$region_id,'city_id'=>$city_id,'store_code'=>$store_code,'status'=>1])->getField('id');
                if(empty($store_id)){
                    $store_id=M("Store")->add(['store_name'=>$store_name,
                        'brand_id'=>$brand_id,'region_id'=>$region_id,
                        'city_id'=>$city_id,'store_code'=>strtoupper($store_code)]);
                }
                
                if($store_id==false){
                    $_info[15]='导入失败！';
                    $result_data[] = $_info;
                    continue;
                }
                $_info[4]='导入成功！';
                $result_data[] = $_info;
            }
            //下载结果集
            $this->import_result_download($result_data);
        }
    }
    /**
     * 导入结果下载
     * @author sea
     */
    public function import_result_download($data){
        set_time_limit(0);
        $style = 'size:12;width:25;font:宋体;color:ffffff;text-align:center;font-weight:bold;height:25;vertical-align:center;type:string;full:0070C0';
        $style2 = 'size:12;width:45;font:宋体;color:ffffff;text-align:center;font-weight:bold;height:25;vertical-align:center;type:string;full:0070C0';
        $body_style = 'size:12;width:25;font:宋体;text-align:center;type:string;height:15;vertical-align:center';
        $title = "导入人员结果" . date("Y-m-d H:i:s");
        $header_data = array(
            array('大区*', $style),
            array('城市*', $style),
            array('经销店名称*', $style),
            array('经销店代码*', $style),
            array('导入结果', $style),
        );
        $body_data = array();
        foreach ($data as $key=>&$row) {
            $body_data[] = array(
                array($row[0], $body_style),
                array($row[1], $body_style),
                array($row[2], $body_style),
                array($row[3], $body_style),
                array($row[4], $body_style),
            );
        }
        array_unshift($body_data, $header_data);
        $excel = new Excel();
        $excel->renderData($body_data)->download($title);
    }
}
