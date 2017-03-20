<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_checkout extends mod_meal_checkout {

	//默认浏览页,未结算
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		return $this->get_view(); //显示页面
	}
	//已结算
	function act_list() {
		//分页列表
		$this->arr_list = $this->get_list();
		$this->date_list = $this->get_date_list();
		return $this->get_view(); //显示页面
	}
	//结算
	function act_checkout() {
		$arr_return = $this->on_checkout();
		return fun_format::json($arr_return);
	}
	function act_excel() {
		$this->on_excel();
	}
}