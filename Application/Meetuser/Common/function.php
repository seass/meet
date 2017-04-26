<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}
/**
 * 检查手机号
 * @author sea
 */
function checkRegPhone($phone){
    if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
        return false;
    }
    return true;
}
/**
 * 检查身份证
 * @author sea
 * //1、15位或18位，如果是15位，必需全是数字。
 //2、如果是18位，最后一位可以是数字或字母Xx，其余必需是数字。
 */
function checkRegIdentity($identity){
    if(!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/",$identity)){
        return false;
    }
    return true;
}
/**
 * 创建某会议下的参会编号
 * @author sea
 */
function  get_user_no($meet_id){
    $count = M('MeetMember')->where(['meet_id'=>$meet_id])->count();
    $count++;
    if($count<10){
        return '000'.$count;
    }else if($count>=10 && $count<100){
        return '00'.$count;
    }else if($count>=100 && $count<1000){
        return '0'.$count;
    }
    return $count;
}
/**
 * 生成二维码
 * @author sea
 */
function  createQrcode($meet_member_id){
    $qr_text='http://'.$_SERVER['HTTP_HOST'].U('/Meetuser/Info/index/MUid/'.$meet_member_id);
    // 文件名称
    $file_name = date('YmdHis') . rand(100, 999)."_".$meet_member_id . '.png';
    $QrObj = new \Util\Qrcode\QR($qr_text, array(
        'QrName' => $file_name,
        'matrixPointSize' => 8
    ));
    return substr($QrObj->QR,1);
}