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
 * 会议人员管理控制器
 * @author sea <919873148.qq.com>
 */
class MeetMemberController extends AdminController {

    //Model(表名)
    public $_model="MeetMember";
    
    /**
     * 会议人员管理列表
     * @author sea 
     */
    public function index(){
        $_key       =   I('_key');
        $_meet_id   =   I('_meet_id');
        $brand_id   =   I('brand_id',null);
        $region_id   =   I('region_id',null);
        $city_id   =   I('city_id',null);
        $store_id   =   I('store_id',null);
        $hotel_type =   I('hotel_type');
        $is_room_user=   I('is_room_user');
        //$_sign   =   I('_sign');
        $map['m.status']    =   array('eq',1);
        $map['me.status']    =   array('eq',1);
        //模糊搜索
        if(!empty($_key)){
            $arr_key=explode(',',trim($_key));
            if(count($arr_key)>1){
                $str_like="";
                foreach ($arr_key as $i=>$_itemK){
                    if($i>0){
                        $str_like.=" or ";
                    }
                    $str_like.=" m.phone like '%".trim($_itemK)."%'";
                }
                $map['_string']=$str_like;
                //查询多个数据 ，号分割出来
               // $map['m.phone']=['in',explode(',',trim($_key))];
            }else{
                 $map['m.realname|me.meet_name|m.phone|m.user_no|s.store_name|s.store_code|m1.realname']    =   array('like', '%'.(string)$_key.'%');
                 
           }
        }
        if(!empty($_meet_id)){
            $map['me.id']=$_meet_id;
        }
        if(!empty($hotel_type)){
            $map['m.hotel_type']=($hotel_type==-1?0:$hotel_type);
        }
        if(!empty($is_room_user)){
            if($is_room_user==1){
                $map['m.room_meet_member_id']=['neq',0];
            }else{
                $map['m.room_meet_member_id']=['eq',0];
            }
            
        }
        if(!empty($brand_id)){
            $map['m.brand_id']=$brand_id;
        }
        if(!empty($region_id)){
            $map['m.region_id']=$region_id;
        }
        if(!empty($city_id)){
            $map['m.city_id']=$city_id;
        }
        if(!empty($store_id)){
            $map['m.store_id']=$store_id;
        }
        
//         if(!empty($_sign)){
//             if($_sign==1){
//                 $map['_string']=' mms.id is not null';
//             }else{
//                 $map['_string']=' mms.id is  null';
//             }
            
//         }
        $list=M()->table(C('DB_PREFIX').strtolower('meet_member').' m' )
                       ->where($map)
                       ->order('m.id DESC')
                       //->group('m.id')
                       ->join (' left join '.C('DB_PREFIX').('meet').' me ON me.id=m.meet_id' )
                       ->join (' left join '.C('DB_PREFIX').('classes').' c ON c.id=m.classes_id' )
                       ->join (' left join '.C('DB_PREFIX').('brand').' b ON b.id=m.brand_id' )
                       ->join (' left join '.C('DB_PREFIX').('region').' r ON r.id=m.region_id' )
                       ->join (' left join '.C('DB_PREFIX').('city').' ci ON ci.id=m.city_id' )
                       ->join (' left join '.C('DB_PREFIX').('store').' s ON s.id=m.store_id' )
                       //->join (' left join '.C('DB_PREFIX').('meet_member_sign').' mms ON mms.meet_member_id=m.id' )//,mms.id as mms_id
                        ->join (' left join '.C('DB_PREFIX').('meet_member').' m1 ON m1.id=m.room_meet_member_id' );
        $field='m.id,m.user_no,m.realname,m.phone,m.sex,m.idcard,m.headimg,m.position,m.qrcode,m.roommate_name,'.
            'm.status,m.create_time,me.meet_name,me.begin_time,me.end_time,c.classes_name,b.brand_name,r.region_name,'.
            's.store_name,s.store_code,ci.city_name,m.hotel_type,m1.realname as roommate_name2,m.is_audit';
        $list = $this->lists($list,null,null,null,$field);
        int_to_string($list);
        
        $this->assign('_list', $list);
        $this->assign('_meet_id', $_meet_id);
        $this->assign('brand_id', $brand_id);
        $this->assign('region_id', $region_id);
        $this->assign('city_id', $city_id);
        $this->assign('store_id', $store_id);
        $this->assign('hotel_type',$hotel_type);
        $this->assign('is_room_user',$is_room_user);
        $this->assign('_sign', $_sign);
        $this->meta_title = '会议人员管理';
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
//         var_dump($id);
//         var_dump();exit;
        switch (strtolower($method)){
            case 'forbidmeetmember':
                $this->forbid($this->_model, $map );
                break;
            case 'resumemeetmember':
                $this->resume($this->_model, $map );
                break;
            case 'deletemeetmember':
                $this->deleteMeetMember(null,$id);
                //$this->delete($this->_model, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    
    /**
     * 删除用户人员（清空）
     * @param unknown $meet_id
     * @param unknown $mm_ids
     */
    public function deleteMeetMember($meet_id=null,$mm_ids=null){
        //清空
        if($meet_id!=null){
           $up_res= M($this->_model)->where(['meet_id'=>$meet_id])->setField(['status'=>-1]);
            if($up_res!==false){
                $this->success('清空成功！');
            }
            $this->error('清空失败！');
        }
        if($mm_ids!=null){
             //更新删除
            $up_res= M($this->_model)
            ->where(['id'=>['in',$mm_ids]])->setField(['status'=>-1]);
            if($up_res!==false){
                //从最下的id往后的本此会议下用户 会议编号都从新计算
                //获取id最小的
                $min_id=min($mm_ids);
                $min_info=M($this->_model)->field('id,meet_id,user_no')->where(['id'=>$min_id])->find();
                //获取从最下的id往后的本此会议下用户
                $later_data=M($this->_model)
                    ->field('id,user_no')
                    ->where(
                        ['_string'=>' meet_id='.$min_info['meet_id'].' and id>'.$min_id.' and status<>-1  '])
                    ->order('id asc')->select();
                if(!empty($later_data)){
                    $curr_no=intval($min_info['user_no']);
                    foreach ($later_data as $key=>$info){
                        if($key==0){
                            //紧挨着最少删除的那个值  替换成最少的 会议编号
                            $new_start_no=$min_info['user_no'];
                        }else{
                            //开始＋＋
                            $new_start_no=get_inc_user_no($curr_no);
                        }
                        M($this->_model)->where(['id'=>$info['id']])->setField(['user_no'=>$new_start_no]);
                        $curr_no=intval($new_start_no);
                    }
                }
                $this->success('删除成功！');
            }
            $this->error('删除失败！');
        }
        
        
    }
    
    
    
    public function getSaveData(){
        //绑定门店
//         $store_id=I('post.store_id',0);
//         if(empty($store_id)){
//             $this->error('请选择门店，若无选择项，请先去新增门店！');
//         }
        //所属会议
        $meet_id=I('post.meet_id',0);
        if(empty($meet_id)){
            $this->error('请选择会议，若无选择项，请先去新增会议！');
        }
        //所属班级
        $classes_id=I('post.classes_id',0);
        if(empty($classes_id)){
            $this->error('请选择所属班级，若无选择项，请先去新增班级！');
        }
        $realname=I('post.realname');
        if(empty($realname)){
            $this->error('会议人员姓名必填！');
        }
        $phone=I('post.phone');
        if(empty($phone)){
            $this->error('会议人员手机号必填！');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
            $this->error('会议人员手机号格式错误！');
        }
        $idcard=I('post.idcard');
//         if(empty($idcard)){
//             $this->error('会议人员证件号必填！');
//         }
        $position=I('post.position');
        if(empty($position)){
            $this->error('会议人员职务必填！');
        }
        $brand_id=I('post.brand_id',0);
        $region_id=I('post.region_id',0);
        $city_id=I('post.city_id',0);
        $store_id=I('post.store_id',0);
        //$store_info=M('Store')->where(['id'=>$store_id])->field('id,brand_id,region_id,city_id')->find();
        //$meet_id=M('Classes')->where(['id'=>$classes_id])->getField('meet_id');
        $data=[
            'brand_id'=>$brand_id,//品牌id
            'region_id'=>$region_id,//大区id
            'city_id'=>$city_id,//城市ID
            'store_id'=>$store_id,//门店ID
            
            'meet_id'=>$meet_id,//会议id
            'classes_id'=>$classes_id,
            'realname'=>$realname,
            'user_no'=>get_user_no($meet_id),//生成会议编号
            'sex'=>I('post.sex'),
            'phone'=>$phone,
            'idcard'=>$idcard,
            'headimg'=>I('post.headimg'),//存储的onethink_picture表的ID
            'position'=>$position,
            'score'=>I('post.score'),
            'food_req'=>I('post.food_req'),
            'status'=>I('post.status'),
            'is_audit'=>I('post.is_audit'),
            'hotel_type'=>0,//不住宿,
            'house_type'=>0,//17-06-05废弃
            'checkin_date'=>'',
            'leave_date'=>''
        ];
        //是否住宿
        $hotel_type=$_POST['hotel_type'];
        if(!empty($hotel_type)){
//             if(empty($_POST["house_type"])){
//                  $this->error('请选择房型！');
//             }
            $checkin_date=$_POST['checkin_date'];
            if(empty($checkin_date)){
                $this->error('请选择入住时间！');
            }
            $leave_date=$_POST['leave_date'];
            if(empty($leave_date)){
                $this->error('请选择离店时间！');
            }
            if(strtotime($checkin_date)>strtotime($leave_date)){
                $this->error('入住时间不能大于离店时间！');
            }
            $data['hotel_type']=$hotel_type;
            //$data['house_type']=$_POST["house_type"];
            $data['checkin_date']=$checkin_date;
            $data['leave_date']=$leave_date;
        }
        //新增时初始密码
        if(empty(I('post.id'))){
            $data['password']=md5(substr($phone, -6));
         }
        return $data;
    }
    /**
     * 新增会议人员
     * @author sea
     */
    public function add(){
        if(IS_POST){
            //获取保存数据
            $save_data=$this->getSaveData();
            /*
             * 检查用户的手机号 是否已经存在次会议的班级下
             */
            $check_res=M($this->_model)->where([
                                'meet_id'=>$save_data['meet_id'],
                                'phone'=>$save_data['phone'],
                                'status'=>array('neq',-1)
            ])->getField("id");
            if(!empty($check_res)){
                $this->error('此用户已经存在本次会议班级,请重新输入新用户！');
            }
            //添加数据
            $add_res=M($this->_model)->add($save_data);
            if($add_res==false){
                $this->error('新增失败！');
            }
            //生成二维码
            $qrcode=createQrcode($add_res);
            M($this->_model)->where(['id'=>$add_res])->save(['qrcode'=>$qrcode]);
            
            //记录行为(需要提前创建add_meetmember行为标记)
            action_log('add_meetmember',$this->_model, $add_res, UID);
            $this->success('新增成功！',U('index'));
           
        } else {
            
            $this->meta_title = '新增会议人员';
            $this->display('edit');
        }
    }
    /**
     * 编辑会议人员
     * @author sea
     */
    public function edit(){
        if(IS_POST){
            $id=I('post.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            //获取保存数据
            $save_data=$this->getSaveData();
            //编辑数据
            M($this->_model)->where(['id'=>$id])->save($save_data);
            //记录行为(需要提前创建add_meetmember行为标记)
            action_log('edit_meetmember',$this->_model, $id, UID);
            $this->success('操作成功！',U('index'));
             
        } else {
            $id=I('get.id');
            if(empty($id)){
                $this->error('参数异常！');
            }
            $_info=M($this->_model)->where('id='.$id)->find();
            $this->assign('info', $_info);
            $this->meta_title = '编辑会议人员';
            $this->display('edit');
        }
    }
    /**
     * 分配班级
     * @author sea 
     */
    public function allotClasses(){
        $id = array_unique((array)I('id',null));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $classes_id = I('classes_id',null);
        if (empty($classes_id)) {
            $this->error('请选择要分配的班级!');
        }
        //分配班级
        $up_res=M($this->_model)->where(['id'=>['in',$id]])->save(['classes_id'=>$classes_id]);
        if($up_res!==false){
            $this->success("分配成功");
        }
        $this->error('操作失败!');
    }
    /**
     * 设置酒店同住室友
     * @author sea 
     */
    public function setRoommate(){
        $ids = array_unique((array)I('id',null));
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        if (count($ids)>2){
            $this->error('选择的同住室友，最多不能超过两个！');
        }
        //检查两个人是否都是合住 
        $checkData=M($this->_model)->where([
                            'id'=>array('in',$ids)
                        ])->field("hotel_type,sex")->select();
        $hotel_type_arr=array_unique(array_column($checkData, 'hotel_type'));
        if(count($hotel_type_arr)>1){
            $this->error('选择的用户中包含不住宿或者单住的，不能进行设置同住室友！');
        }
        //男女不能合住
        $sex_arr=array_unique(array_column($checkData, 'sex'));
        if(count($sex_arr)>1){
            $this->error('男女不能合住，不能进行设置同住室友！');
        }
        //设置同住室友
        $up_res1=M($this->_model)->where(['id'=>$ids[0]])->save(['room_meet_member_id'=>$ids[1]]);
        $up_res2=M($this->_model)->where(['id'=>$ids[1]])->save(['room_meet_member_id'=>$ids[0]]);
        if($up_res1!==false && $up_res2!==false){
            $this->success("设置成功");
        }
        $this->error('操作失败!');
    }
    /**
     * 签到
     */
    public function sign(){
        $meet_member_id=I('post.meet_member_id','');
        $return['status']=false;
        /*
         * 检查一个人一天 只能签到一次
         */
        $check_res=M('MeetMemberSign')->where(['meet_member_id'=>$meet_member_id,'_string'=>" date(create_time)='".date('Y-m-d')."' and status=1 "])->find();
        if(!empty($check_res)){
            $return['smg']='一次会议中，一个人一天只能签到一次！';
            $this->ajaxReturn($return);
        }
        
        $add_res=M('MeetMemberSign')->add(['meet_member_id'=>$meet_member_id]);
        
        if($add_res!==false){
            $return['status']=true;
        }
        $this->ajaxReturn($return);
    }
    
    public $import_title=[
        0=>'品牌*',
        1=>'大区*',
        2=>'城市*',
        3=>'经销店名称*',
        4=>'经销店代码*', 
        5=>'姓名*', 
        6=>'电话号码*', 
        7=>'性别', 
        8=>'身份证信息',
        9=>'职位', 
        10=>'住宿类型',
        11=>'房型', 
        12=>'入住时间', 
        13=>'离店时间',
    ];
    /**
     * 导入人员信息
     * @author sea 
     */
   public function import(){
       if(!IS_POST){
           $this->meta_title = '导入人员信息';
           $this->display('import');
       }else{
           $meet_id=$_POST['meet_id'];
           $excel = new Excel();
           $file_data = $excel->readerExcel($_FILES['import']['tmp_name'],0,14);
           // var_dump($file_data);exit;
           $result_data=[];
           foreach ($file_data as $_info){
               $brand_name=$_info[0];
               $store_code=$_info[1];
               $realname=$_info[2];
               $phone=$_info[3];
               $sex=$_info[4];
               if (!in_array($sex,['男','女'])) {
                    $_info[15] = '性别数据异常，正确格式： 男 or 女';
                    $result_data[] = $_info;
                    continue;
               }
               $idcard=$_info[5];
               $position=$_info[6];
               
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
                   $_info[10]='导入的列格式错误，导入失败！错误列：'.$this->import_title[$key_error];
                   $result_data[] = $_info;
                   continue;
               }
               if(empty($brand_name) || 
                   empty($store_code) || empty($realname) || empty($phone) || empty($sex)){
                   $_info[10]='必填项数据项为空,导入失败，请看表头*号！';
                   $result_data[] = $_info;
                   continue;
               }
               
               //住宿类型
               $hotel_type_val=$_info[7];
               if(!empty($hotel_type_val) && !in_array($hotel_type_val,['','不住宿','合住','单住'])){
                   $_info[10]='住宿类型有误，请按照模板选项值填写,导入失败！';
                   $result_data[] = $_info;
                   continue;
               }
               //房型
//                $house_type_val=$_info[11];
//                if(!empty($house_type_val) && !in_array($house_type_val,['','无','双床','大床'])){
//                    $_info[15]='房型有误，请按照模板选项值填写,导入失败！';
//                    $result_data[] = $_info;
//                    continue;
//                }
               if($hotel_type_val=='不住宿' || $hotel_type_val==''){
                   $hotel_type=0;
               }else if($hotel_type_val=='合住'){
                   $hotel_type=1;
               }else{
                   $hotel_type=2;
               }
               
//                if($house_type_val=='无' || $house_type_val==''){
//                    $house_type=0;
//                }else if($house_type_val=='双床'){
//                    $house_type=1;
//                }else{
//                    $house_type=2;
//                }
               
               $checkin_date=$_info[8];
               $leave_date=$_info[9];
               $is_stay=false;
               if(!empty($hotel_type)){
                   if(!empty($checkin_date) && !empty($leave_date)){
                       if(strtotime($checkin_date)>strtotime($leave_date)){
                           $_info[10]='入住时间不能大于离店时间,导入失败！';
                           $result_data[] = $_info;
                           continue;
                       }
                   }
                   $is_stay=true;
               }
               /*
                * 检查用户的手机号 是否已经存在次会议的班级下
                */
               $check_res=M($this->_model)->where([
                   'meet_id'=>$meet_id,
                   'phone'=>$phone,
                   'status'=>array('neq',-1)
               ])->getField("id");
               if(!empty($check_res)){
                   $_info[10]='此用户已经存在,导入失败！';
                   $result_data[] = $_info;
                   continue;
               }
               
               
               
               //检查品牌是否存在
               $brand_id=M("Brand")->where(['brand_name'=>$brand_name])->getField('id');
               if(empty($brand_id)){
                   $brand_id=M("Brand")->add(['brand_name'=>$brand_name]);
               }
               //检查大区是否存在
//                $region_id=M("Region")->where(['region_name'=>$region_name,'brand_id'=>$brand_id])->getField('id');
//                if(empty($region_id)){
//                    $region_id=M("Region")->add(['region_name'=>$region_name,'brand_id'=>$brand_id]);
//                }
//                //检查城市是否存在
//                $city_id=M("City")->where(['city_name'=>$city_name,'brand_id'=>$brand_id,'region_id'=>$region_id])->getField('id');
//                if(empty($city_id)){
//                    $city_id=M("City")->add(['city_name'=>$city_name,'brand_id'=>$brand_id,'region_id'=>$region_id]);
//                }
               //检查门店是否存在
               $store_Info=M("Store")->where(['store_code'=>$store_code])->field('id,city_id,region_id,brand_id')->find();
               if(empty($store_Info)){
                   $_info[10]='门店代码有误，未匹配到相对应的门店信息！';
                   $result_data[] = $_info;
                   continue;
               }
               $store_id=$store_Info['id'];
               $city_id=$store_Info['city_id'];
               $region_id=$store_Info['region_id'];
               $brand_id=$store_Info['brand_id'];
               
               $save_data=[
                   'brand_id'=>$brand_id,//品牌id
                   'region_id'=>$region_id,//大区id
                   'city_id'=>$city_id,//城市ID
                   'store_id'=>$store_id,//门店ID
                   'meet_id'=>$meet_id,//会议id
                   'realname'=>$realname,
                   'user_no'=>get_user_no($meet_id),//生成会议编号
                   'sex'=>(empty($sex) || $sex=='男')?1:2,
                   'phone'=>$phone,
                   'idcard'=>$idcard,
                   'position'=>$position,
                   'is_audit'=>1,
                   'password'=>md5(substr($phone, -6)),
               ];
               if($is_stay){
                   $save_data['hotel_type']=$hotel_type;
                   //$save_data['house_type']=$house_type;
                   $save_data['checkin_date']=$checkin_date;
                   $save_data['leave_date']=$leave_date;
               }
               $add_res=M($this->_model)->add($save_data);
               if($add_res==false){
                   $_info[10]='导入失败！';
                   $result_data[] = $_info;
                   continue;
               }
               //生成二维码
               $qrcode=createQrcode($add_res);
               M($this->_model)->where(['id'=>$add_res])->save(['qrcode'=>$qrcode]);
               
               $_info[10]='导入成功！';
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
           array('品牌*', $style),
           array('经销店代码*', $style),
           array('姓名*', $style),
           array('电话号码*', $style),
           array('性别', $style),
           array('身份证信息', $style),
           array('职位', $style),
           array('住宿类型', $style),
           array('入住时间', $style),
           array('离店时间', $style),
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
               array($row[5], $body_style),
               array($row[6], $body_style),
               array($row[7], $body_style),
               array($row[8], $body_style),
               array($row[9], $body_style),
               array($row[10], $body_style),
               array($row[11], $body_style),
               array($row[12], $body_style),
               array($row[13], $body_style),
               array($row[15], $body_style),
           );
       }
       array_unshift($body_data, $header_data);
       $excel = new Excel();
       $excel->renderData($body_data)->download($title);
       
       
   }

   public function refresh_qr() {
        $members = M('MeetMember')->select();
        dump($members);
        foreach ($members as $m) {
            $qrcode=createQrcode($m['id']);
            M('MeetMember')->where(['id'=>$m['id']])->save(['qrcode'=>$qrcode]);
        }
   }

   public function refresh_meet_qr() {
        $meets = M('Meet')->select();
        dump($meets);
        foreach ($meets as $m) {
            $qrcode=createMeetQrcode($m['id']);
            M('Meet')->where(['id'=>$m['id']])->save(['qrcode'=>$qrcode]);
        }
   }
}
