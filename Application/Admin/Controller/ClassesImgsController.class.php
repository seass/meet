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
 * 班级图片管理控制器
 * @author sea <919873148.qq.com>
 */
class ClassesImgsController extends AdminController {

    //Model(表名)
    public $_model="ClassesImgs";
    
    /**
     * 班级图片管理列表
     * @author sea 
     */
    public function index(){
        $classes_id      =   I('classes_id',0);
        if(empty($classes_id)){
            $this->error('操作异常!');//必须从某个班级入口进入
        }
        $_key       =   I('_key');
        $map['ci.classes_id']   =   $classes_id;
        $map['ci.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['f.savename|f.mime']    =   array('like', '%'.(string)$_key.'%');
        }
        $list=M($this->_model.' ci')->where($map)
                       ->order('ci.id DESC')
                       ->join (' left join '.C('DB_PREFIX').('file').' f ON f.id=ci.file_id' );
        $field='ci.id,ci.create_time,ci.status,f.name,f.savepath,f.savename,f.mime,f.size';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('classes_id', $classes_id);
        $this->meta_title = '班级照片管理';
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
        switch (strtolower($method)){
            case 'forbidmeetattchment':
                $this->forbid($this->_model, $map );
                break;
            case 'resumemeetattchment':
                $this->resume($this->_model, $map );
                break;
            case 'deletemeetattchment':
                $this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 保存文件ID
     * @author sea 
     */
    public function saveFileId(){
        //添加数据
        $add_res=M($this->_model)->add([
            'classes_id'=>I('post.classes_id'),
            'file_id'=>I('post.file_id'),
        ]);
        $return = ['status'=>false];
        if($add_res!==false){
            $return['status'] = true;
        }
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
    /**
     * 检查图片上传次数
     * @author sea
     */
    public function checkImgCount(){
        //添加数据
        $checkCount=M($this->_model)->where([
            'classes_id'=>I('post.classes_id'),
            'status'=>1,
        ])->count();
        $return = ['status'=>false];
        if($checkCount>=20){
            $return['status'] = true;
        }
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
    
}
