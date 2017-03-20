<?php
class ctl_other_share extends mod_other_share {

	//默认浏览页
	function act_default() {
		$this->arr_list = $this->get_pagelist();
		//相关属性
		$this->arr_type =  tab_other_pay_refund::get_perms("type");
		return $this->get_view();
	}
}