<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_ajax extends mod_ajax {
	function act_quan_msg_pub() {
		$arr_info = $this->on_quan_msg_pub();
		return fun_format::json($arr_info);
	}
	function act_quan_msg_zan() {
		$arr_info = $this->on_quan_msg_zan();
		return fun_format::json($arr_info);
	}
	function act_quan_msg_ping() {
		$arr_info = $this->on_quan_msg_ping();
		return fun_format::json($arr_info);
	}
	function act_quan_msg_del() {
		$id = (int)fun_get::get("id");
		$arr_info = $this->on_quan_msg_del($id);
		return fun_format::json($arr_info);
	}
	function act_quan_msg_ping_del() {
		$id = (int)fun_get::get("id");
		$arr_info = $this->on_quan_msg_ping_del($id);
		return fun_format::json($arr_info);
	}
	//注册
	function act_reg() {
		$arr = fun_format::json( $this->on_reg() );
		return $arr;
	}
	//找回密码第一步
	function act_findpwd_step1() {
		$arr = fun_format::json( $this->on_findpwd_step1() );
		return $arr;
	}
	//找回密码第二步
	function act_findpwd_step2() {
		$arr = fun_format::json( $this->on_findpwd_step2() );
		return $arr;
	}
	//重置新密码
	function act_findpwd_step3() {
		$arr = fun_format::json( $this->on_findpwd_step3() );
		return $arr;
	}
	//验证信息
	function act_verify_mobile() {
		$arr = fun_format::json( $this->on_verify_mobile() );
		return $arr;
	}
	function act_user_save() {
		$arr = fun_format::json( $this->on_user_save() );
		return $arr;
	}
	function act_wx_msg() {
		$arr = fun_format::json( $this->on_wx_msg() );
		return $arr;
	}
}