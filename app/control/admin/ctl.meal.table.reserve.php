<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_table_reserve extends mod_meal_table_reserve {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_state = tab_meal_table_reserve::get_perms("state");
		$this->arr_list = $this->get_pagelist();
		return $this->get_view(); //显示页面
	}
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

}