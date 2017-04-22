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
 * 会议资料管理控制器
 * @author sea <919873148.qq.com>
 */
class MeetAttchmentController extends AdminController {

    //Model(表名)
    public $_model="MeetAttchment";
    
    /**
     * 会议资料管理列表
     * @author sea 
     */
    public function index(){
        $meet_id       =   I('meet_id',0);
        if(empty($meet_id)){
            $this->error('操作异常!');//必须从某个会议入口进入
        }
        $_key       =   I('_key');
        $map['ma.meet_id']   =   $meet_id;
        $map['ma.status']    =   array('egt',0);
        //模糊搜索
        if(!empty($_key)){
            $map['f.savename|f.mime']    =   array('like', '%'.(string)$_key.'%');
        }
        $list=M($this->_model.' ma')->where($map)
                       ->order('ma.id DESC')
                       ->join (' left join '.C('DB_PREFIX').('file').' f ON f.id=ma.file_id' );
        $field='ma.id,ma.create_time,ma.status,f.name,f.savepath,f.savename,f.mime,f.size';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('meet_id', $meet_id);
        $this->meta_title = '会议资料管理';
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
            'meet_id'=>I('post.meet_id'),
            'file_id'=>I('post.file_id'),
        ]);
        $return = ['status'=>false];
        if($add_res!==false){
            $return['status'] = true;
        }
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
    
}
