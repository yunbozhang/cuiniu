<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_sys_user_repayment extends mod_sys_user_repayment {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_type = tab_sys_user_repayment::$this_type;
		return $this->get_view(); //显示页面
	}
}