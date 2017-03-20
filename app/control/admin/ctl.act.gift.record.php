<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_act_gift_record extends mod_act_gift_record {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist( );
		return $this->get_view(); //显示页面
	}
	//发货
	function act_send() {
		$arr_return = $this->on_send();
		return fun_format::json($arr_return);
	}

}