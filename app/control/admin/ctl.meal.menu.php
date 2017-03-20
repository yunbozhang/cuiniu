<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_menu extends mod_meal_menu {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_state = tab_meal_menu::get_perms("state");
		$this->arr_type = tab_meal_menu::get_perms("type");
		$this->group_html = $this->get_group_select("group_val");
		$this->s_group_html = $this->get_group_select("s_group_id" ,(int)fun_get::get("s_group_id"));
		return $this->get_view(); //显示页面
	}
	//回收站数据
	function act_dellist() {
		//分页列表
		$this->arr_list = $this->get_pagelist(1);
		//类型数组
		$this->arr_menu_type = tab_meal_menu::get_type($this->admin_shop['mode']);
		$this->arr_state = tab_meal_menu::get_perms("state");
		$this->group_html = $this->get_group_select("group_val");
		$this->s_group_html = $this->get_group_select("s_group_id" , (int)fun_get::get("s_group_id"));
		return $this->get_view("default"); //显示页面
	}

	//编辑 新增 页面 ,有id时为编辑
	function act_edit() {

		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_type = tab_meal_menu::get_perms("type");
		$this->arr_attribute = tab_meal_menu::get_perms("attribute");
		$this->arr_unit = cls_config::get("menu_unit" , "meal");
		$this->arr_state = tab_meal_menu::get_perms("state");
		return $this->get_view();
	}

	//保存操作,返回josn数据
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	//删除到回收站操作,返回josn数据
	function act_del() {
		$arr_return = $this->on_del();
		return fun_format::json($arr_return);
	}
	//从回收站回收操作,返回josn数据
	function act_reback() {
		$arr_return = $this->on_del(0);
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
	//推荐
	function act_tj() {
		$arr_return = $this->on_tj();
		return fun_format::json($arr_return);
	}
	//设置分组
	function act_group() {
		$arr_return = $this->on_group();
		return fun_format::json($arr_return);
	}
	//设置模式
	function act_mode() {
		$arr_return = $this->on_mode();
		return fun_format::json($arr_return);
	}
	//排序
	function act_sort() {
		$arr_return = $this->on_sort();
		return fun_format::json($arr_return);
	}
	//分类
	function act_type() {
		$arr_return = $this->on_type();
		return fun_format::json($arr_return);
	}
	function act_excel() {
		$this->on_excel();
	}

}