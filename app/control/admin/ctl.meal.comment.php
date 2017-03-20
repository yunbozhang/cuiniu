<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_comment extends mod_meal_comment {

	//默认浏览页
	function act_default() {
		$this->arr_list = $this->get_pagelist();
		return $this->get_view(); //显示页面
	}
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

	function act_recont() {
		$arr_return = $this->on_recont();
		return fun_format::json($arr_return);
	}

}