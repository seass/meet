<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: sea <919873148.qq.com>
// +----------------------------------------------------------------------

namespace Meetuser\Controller;

/**
 * 会议前台首页控制器
 */
class IndexController extends \Think\Controller{
	//系统首页
    public function index(){
        //每次进入必须带会议id
        if(empty($_GET["Mid"])){
            $this->redirect('Index/error');
        }
        // 获取当前用户ID
        define('MUID',is_muser_login());
        
        if( !MUID && !is_amuser_login()){// 还没登录 跳转到登录页面
            $this->redirect('Index/login',$_GET);
        }
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
        
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        $this->display();
    }
    /**
     * 检查是否登录
     */
    public function login(){
        if(IS_POST){
            $return = ['status'=>false,'msg'=>''];
            //验证手机格式
            $phone=$_POST['phone'];
            if(!checkRegPhone($phone)){
                $return['msg']='手机号格式验证失败，请重新输入！';
                $this->ajaxReturn($return);
            }
            //验证身份证号格式
//             $idcard=$_POST['idcard'];
//             if(!checkRegIdentity($idcard)){
//                 $return['msg']='身份证号格式验证失败，请重新输入！';
//                 $this->ajaxReturn($return);
//             }
            $MUser=M("MeetMember")->where([
                'meet_id'=>$_POST['Mid'],
                'realname'=>$_POST['realname'],
                //'idcard'=>$idcard,
                'phone'=>$phone,
            ])->find();
            if(empty($MUser)){
                $return['msg']='信息验证失败，请重新输入！';
                $this->ajaxReturn($return);
            }
            if($MUser['is_audit']!=1){
                $return['msg']='用户未审核通过，请等候审核！';
                $this->ajaxReturn($return);
            }
            if($MUser['status']!=1){
                $return['msg']='用户不存在或被禁用，请联系管理员！';
                $this->ajaxReturn($return);
            }
            //登录成功 存储登录信息
            self::autoLogin($MUser);
            $return['status']=true;
            $return['msg']='登录成功！';
            $return['success_url']=U("/Meetuser/Index/index/Mid/".$_POST['Mid']);
            /* 返回JSON数据 */
            $this->ajaxReturn($return);
        }else{
            //每次进入必须带会议id
            if(empty($_GET["Mid"])){
                $this->redirect('Index/error');
            }
            if(is_muser_login()){
                $this->redirect('Index/index',$_GET);
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置

                $meet = M("Meet")->where(['id'=>$_GET['Mid']])->field("meet_name")->find();
                if (empty($meet)) {
                    $this->redirect('Index/error');
                }
                $this->assign('meet_name', $meet['meet_name']);
                $this->display();
            }
        }
    }

    public function adminlogin(){
        if(IS_POST){
            $return = ['status'=>false,'msg'=>''];
            // $passwd=$_POST['passwd'];
            // if(empty($passwd)){
            //     $return['msg']='密码异常，请重新输入！';
            //     $this->ajaxReturn($return);
            // }
            $nickname=$_POST['nickname'];
            if(empty($nickname)){
                $return['msg']='用户名异常，请重新输入！';
                $this->ajaxReturn($return);
            }
            $password=$_POST['password'];
            
            $UmUser=M("UcenterMember")->where([
                'nickname'=>$nickname,
            ])->find();
            if(empty($UmUser)){
                $return['msg']='信息验证失败，请重新输入！';
                $this->ajaxReturn($return);
            }
            if($UmUser['status']!=1){
                $return['msg']='用户不存在或被禁用！';
                $this->ajaxReturn($return);
            }
            $md5PassWord=md5(sha1($password).'E_TOnA/j5(u"8%gliw[:-H]{k}2bf#M.LpIK^|PD');
            
            /* 验证用户密码 */
            if($md5PassWord === $UmUser['password']){
               //登录成功 存储登录信息
                self::autoAdminLogin();
                $return['status']=true;
                $return['msg']='登录成功！';
                $return['success_url']=U("/Meetuser/Index/index/Mid/".$_POST['Mid']);
                /* 返回JSON数据 */
                $this->ajaxReturn($return);
            } else {
                $return['msg']='用户名和密码不一致！';
                $this->ajaxReturn($return);
            }
        }else{
            //每次进入必须带会议id
            if(empty($_GET["Mid"])){
                $this->redirect('Index/error');
            }
            if(is_amuser_login()){
                $this->redirect('Index/index',$_GET);
            }else{
                /* 读取数据库中的配置 */
                $config =   S('DB_CONFIG_DATA');
                if(!$config){
                    $config =   D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置

                $meet = M("Meet")->where(['id'=>$_GET['Mid']])->field("meet_name")->find();
                if (empty($meet)) {
                    $this->redirect('Index/error');
                }
                $this->assign('meet_name', $meet['meet_name']);
                $this->display();
            }
        }
    }

    /**
     * 注册
     */
    public function register(){
        if(IS_POST){
            $return = ['status'=>false,'msg'=>''];
            if(empty($_POST["Mid"])){
                 $return['msg']='异常，请重新进入！';
                $this->ajaxReturn($return);
            }
            //验证手机格式
            $phone=$_POST['phone'];
            if(!checkRegPhone($phone)){
                $return['msg']='手机号格式验证失败，请重新输入！';
                $this->ajaxReturn($return);
            }
            //验证身份证号格式
            $idcard=$_POST['idcard'];
            if(!checkRegIdentity($idcard)){
                $return['msg']='身份证号格式验证失败，请重新输入！';
                $this->ajaxReturn($return);
            }
            $data=[
                'realname'=>$_POST['realname'],
                'sex'=>$_POST['sex'],
                'phone'=>$_POST['phone'],
                'idcard'=>$_POST['idcard'],
                'position'=>$_POST['position'],
                'brand_id'=>$_POST['brand_id'],
                'region_id'=>$_POST['region_id'],
                'city_id'=>$_POST['city_id'],
                'store_id'=>$_POST['store_id'],
                'food_req'=>$_POST['food_req'],
                'meet_id'=>$_POST["Mid"],
                'user_no'=>get_user_no($_POST["Mid"]),//生成会议编号
                'password'=>md5(substr($_POST['phone'], -6)),
                'is_user_register'=>1,
            ];
            //是否住宿
            $hotel_type=$_POST['hotel_type'];
            if(!empty($hotel_type)){
                if(empty($_POST["house_type"])){
                    $return['msg']='请选择房型！';
                    $this->ajaxReturn($return);
                }
                $checkin_date=$_POST['checkin_date'];
                if(empty($checkin_date)){
                    $return['msg']='请选择入住时间！';
                    $this->ajaxReturn($return);
                }
                $leave_date=$_POST['leave_date'];
                if(empty($leave_date)){
                    $return['msg']='请选择离店时间！';
                    $this->ajaxReturn($return);
                }
                if(strtotime($checkin_date)>strtotime($leave_date)){
                    $return['msg']='入住时间不能大于离店时间！';
                    $this->ajaxReturn($return);
                }
                $data['hotel_type']=$hotel_type;
                $data['house_type']=$_POST["house_type"];
                $data['checkin_date']=$checkin_date;
                $data['leave_date']=$leave_date;
            }
            /*
             * 检查用户的手机号 是否已经存在次会议的班级下
             */
            $check_res=M("MeetMember")->where([
                'meet_id'=>$data['meet_id'],
                'phone'=>$data['phone'],
                'status'=>array('neq',-1)]
                )->getField("id");
            if(!empty($check_res)){
                $return['msg']='此用户已经存在本次会议中,请重新输入新用户！';
                $this->ajaxReturn($return);
            }
            M()->startTrans();
            $add_res=M("MeetMember")->add($data);
            if($add_res==false){
                $return['msg']='注册失败，请稍后重试！';
                $this->ajaxReturn($return);
            }
            //生成二维码
            $qrcode=createQrcode($add_res);
            $up_res=M('MeetMember')->where(['id'=>$add_res])->save(['qrcode'=>$qrcode]);
            if($add_res!=false && $up_res!==false){
                M()->commit();
                
                //注册成功 等待审核
                $return['msg']='注册成功，等待审核！';
                $this->ajaxReturn($return);
//                 //注册成功 自动存储登录信息
//                 $MUser=M("MeetMember")->where([
//                     'id'=>$add_res
//                 ])->find();
//                 self::autoLogin($MUser);
//                 $return['status']=true;
//                 $return['msg']='注册成功！';
//                 $return['success_url']=U("/Meetuser/Index/index/Mid/".$_POST['Mid']);
//                 /* 返回JSON数据 */
//                 $this->ajaxReturn($return);
            }
            M()->rollback();
            $return['msg']='注册失败，请稍后重试！';
            $this->ajaxReturn($return);
        }
    }
    /* 退出登录 */
    public function logout(){
        if(is_muser_login() || is_amuser_login()){
            session('muser_auth', null);
            session('muser_auth_sign', null);
            session('amuser_auth', null);
            session('amuser_auth_sign', null);
            session('[destroy]');
            $this->redirect('login',$_GET);
            //$this->success('退出成功！', U('login',$_GET));
        } else {
            $this->redirect('login',$_GET);
        }
    }
    
    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
                'login_count'     => array('exp', '`login_count`+1'),
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip'   => get_client_ip(1),
        );
        M("MeetMember")->where(['id'=>$user['id']])->setField($data);
    
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'              => $user['id'],
            'realname'        => $user['realname'],
            'phone'           => $user['phone'],
            'last_login_time' => $user['last_login_time'],
        );
        session('muser_auth', $auth);
        session('muser_auth_sign', data_auth_sign($auth));
    }
    
    /**
     * 自动登录admin
     * @param  integer $user 用户信息数组
     */
    private function autoAdminLogin(){
       
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'              => -1,
            'role'            => 'admin',
            'realname'        => '管理员',
            'phone'           => '10000000000',
            'last_login_time' => time(),
        );
        session('amuser_auth', $auth);
        session('amuser_auth_sign', data_auth_sign($auth));
    }
}