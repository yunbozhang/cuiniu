<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_act_voucher extends mod_act_voucher {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_state = tab_act_voucher::get_perms("state");
		return $this->get_view(); //显示页面
	}
	function act_create() {
		$val = (float)fun_get::get("create_val");
		$num = (int)fun_get::get("create_num");
		$arr_msg = tab_act_voucher::on_create($val , $num);
		return fun_format::json($arr_msg);
	}
}