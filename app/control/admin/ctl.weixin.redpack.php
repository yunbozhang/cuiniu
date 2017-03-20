<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * versionbeta:redpack
 * 2016-08-30
 */
class ctl_weixin_redpack extends mod_weixin_redpack {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_state = tab_weixin_redpack::get_perms("state");

		return $this->get_view(); //显示页面
	}
	//编辑 新增 页面 ,有id时为编辑
	function act_edit() {

		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_state = tab_weixin_redpack::get_perms("state");
		$this->arr_type = tab_weixin_redpack::get_perms("type");
		$this->arr_event = tab_weixin_redpack::get_perms("event");
		return $this->get_view();
	}

	//保存操作,返回josn数据
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}
	//设置状态
	function act_state() {
		$arr_return = $this->on_state();
		return fun_format::json($arr_return);
	}

}