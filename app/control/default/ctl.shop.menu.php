<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_menu extends mod_shop_menu {

	//默认浏览页
	function act_default() {
		$get_url_type = fun_get::get("url_type");
		//类型数组
		$this->arr_menu_type = tab_meal_menu::get_type($this->shop_info['shop_mode']);
		if(empty($get_url_type)) {
			$arr = array_values($this->arr_menu_type);
			$get_url_type = $arr[0];
		}
		//分页列表
		$this->arr_list = $this->get_pagelist( $get_url_type );
		$this->get_url_type = $get_url_type;
		$this->arr_state = tab_meal_menu::get_perms("state");
		$this->group_html = $this->get_group_select("group_val");
		$this->s_group_html = $this->get_group_select("s_group_id" , 0,(int)fun_get::get("s_group_id"));
		return $this->get_view(); //显示页面
	}
	//当编辑时，有改动，需要ajax刷新页面列表数据
	function act_refresh_list() {
		//分页列表
		$get_url_type = fun_get::get("url_type");
		$this->arr_menu_type = tab_meal_menu::get_type($this->shop_info['shop_mode']);
		if(empty($get_url_type)) {
			$arr = array_values($this->arr_menu_type);
			$get_url_type = $arr[0];
		}
		if(empty($get_url_type)) $get_url_type = 1;
		return fun_format::json($this->get_pagelist( $get_url_type ));
	}
	//编辑 新增 页面 ,有id时为编辑
	function act_edit() {

		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_menu_type = tab_meal_menu::get_type($this->shop_info['shop_mode']);
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
}