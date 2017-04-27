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
 * 公共信息控制器
 * @author sea <919873148.qq.com>
 */
class CommonController extends \Think\Controller {

    public function test(){
        $city_data=get_city_list('brand_id',3);
        var_dump($city_data);
    }
    /**
     * 获取品牌下json数据
     */
    public function get_brand_json(){
        $brand_id=I('post.brand_id','');
        //大区
        $region_data=get_region_list($brand_id);
        $data['region_data']=$region_data;
        //城市
        $city_data=get_city_list('brand_id',$brand_id);
        $data['city_data']=$city_data;
        //门店
        $store_data=get_store_list('brand_id',$brand_id);
        $data['store_data']=$store_data;
        
        $this->ajaxReturn(['data'=>$data]);
    }
    /**
     * 获取大区下json数据
     */
    public function get_region_json(){
        $region_id=I('post.region_id','');
        //城市
        $city_data=get_city_list('region_id',$region_id);
        $data['city_data']=$city_data;
        //门店
        $store_data=get_store_list('region_id',$region_id);
        $data['store_data']=$store_data;
        $this->ajaxReturn(['data'=>$data]);
    }
    /**
     * 获取城市下json数据
     */
    public function get_city_json(){
        $city_id=I('post.city_id','');
        //门店
        $store_data=get_store_list('city_id',$city_id);
        $data['store_data']=$store_data;
        $this->ajaxReturn(['data'=>$data]);
    }
}
