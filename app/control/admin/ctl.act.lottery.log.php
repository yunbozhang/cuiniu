<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_act_lottery_log extends mod_act_lottery_log {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_state = tab_act_lottery::get_perms("log_state");
		return $this->get_view(); //显示页面
	}
	//领取
	function act_receive() {
		$id = (int)fun_get::get("id");
		$arr = $this->on_state($id , 1);
		return fun_format::json($arr);
	}
	//领取
	function act_invalid() {
		$id = (int)fun_get::get("id");
		$arr = $this->on_state($id , -1);
		return fun_format::json($arr);
	}
}