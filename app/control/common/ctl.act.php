<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_act extends mod_act {
	function act_voucher_get() {
		//验证码验证
		$verifycode = fun_get::get("verifycode");
		if(cls_verifycode::on_verify($verifycode , 'voucher') == false) return fun_format::json(array('code'=>500,'msg'=>'验证码不正确'));
		$pwd = fun_get::get("pwd");
		$uid = cls_obj::get("cls_user")->uid;
		if(empty($uid)) return array("code" => 500 , "msg" => "请登录后再来领取");
		$arr_msg = tab_act_voucher::get_voucher($uid , $pwd);
		return fun_format::json($arr_msg);
	}

}