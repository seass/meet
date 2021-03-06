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
        $status=   I('status');
        $meet_date_s=   I('meet_date_s');
        $meet_date_e=   I('meet_date_e');
        if($status==''){
            $status=1;//默认展示启用的会议
        }
        $map['status']  =   $status;
        if($status==-2){
            $map['status']    =   array('egt',0);//不显示删除的会议
        }
        if(!empty($meet_date_s)){
            $map['meet_date'] =   array('egt',$meet_date_s);
        }
        if(!empty($meet_date_e)){
            $map['meet_date'] =   array('elt',$meet_date_e);
        }
        
        //模糊搜索
        if(!empty($_key)){
            $map['meet_name']    =   array('like', '%'.(string)$_key.'%');
        }
        $field='id,meet_name,status,create_time,begin_time,end_time,is_open_register,qrcode,meet_date';
        $list   = $this->lists($this->_model, $map,null,null,$field);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->assign('status', $status);
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
            //会议时间
            if(!empty(I('post.meet_date'))){
                $data['meet_date']=I('post.meet_date');
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
            //生成二维码
            $qrcode=createMeetQrcode($add_res);
            M($this->_model)->where(['id'=>$add_res])->save(['qrcode'=>$qrcode]);
            
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
            7=>'客户',
        ];
        
        //获取用户信息
        $userlist=M('Member m')->field('m.*,um.email,um.mobile,um.group_type,mc.id as mc_id')
                ->join (' left join '.C('DB_PREFIX').('ucenter_member').' um ON um.id=m.uid' )
                ->join (' left join '.C('DB_PREFIX').('meet_client').' mc ON mc.uid=um.id and mc.meet_id='.I('get.id'))
                ->where(['um.group_type'=>3,'m.status'=>['egt',0]])->order('m.uid DESC')->select();
        
        $this->assign('userlist', $userlist);
        $this->assign('meet_id', I('get.id'));
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
    
    /**
     * 操作客户
     */
    public function meet_client(){
        $op=I('post.op','');
        $meet_id=I('post.meet_id','');
        $uid=I('post.uid','');
        
        $return['status']=false;
        if(empty($op) || empty($meet_id) || empty($uid)){
            $return['smg']='操作异常，参数错误！';
            $this->ajaxReturn($return);
        }
        if($op=='add'){
            $add_res=M('MeetClient')->add(['uid'=>$uid,'meet_id'=>$meet_id]);
            if($add_res!==false){
                $return['status']=true;
            }else{
                $return['smg']='添加异常！';
            }
        }elseif($op=='del'){
            $del_res=M('MeetClient')->where(['uid'=>$uid,'meet_id'=>$meet_id])->delete();
            if($del_res!==false){
                $return['status']=true;
            }else{
                $return['smg']='移除异常！';
            }
        }
        $this->ajaxReturn($return);
    }
    
}
