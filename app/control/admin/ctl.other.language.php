<?php
class ctl_other_language extends mod_other_language {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist( );
		return $this->get_view(); //显示页面
	}
	//编辑 新增 页面 ,有id时为编辑
	function act_edit() {
		$this->arr_language = $this->get_language_list();
		$this->editinfo = $this->get_editinfo( fun_get::get('id') , $this->arr_language);
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
}