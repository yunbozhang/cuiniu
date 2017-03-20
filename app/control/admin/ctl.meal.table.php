<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_table extends mod_meal_table {

	//默认浏览页
	function act_default() {
		$this->arr_group = $this->get_group_list();
		$this->arr_table = $this->get_table_list();
		return $this->get_view(); //显示页面
	}
	function act_edit() {
		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_group = $this->get_group_list();
		return $this->get_view();
	}
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	function act_group_edit() {
		$this->editinfo = $this->get_group_editinfo( fun_get::get('id') );
		return $this->get_view();
	}
	function act_group_save() {
		$arr_return = $this->on_group_save();
		return fun_format::json($arr_return);
	}
	function act_reserve() {
		$this->order_list = $this->get_table_reserve();
		return $this->get_view();
	}
	function act_reserve_cancel() {
		//订单列表
		$id = (int)fun_get::get("id");
		$arr = $this->on_reserve_cancel($id);
		return fun_format::json($arr);
	}
	function act_upmenu() {
		$arr = $this->on_upmenu();
		return fun_format::json($arr);
	}
	function act_reserve_pay() {
		$arr = $this->on_reserve_pay();
		return fun_format::json($arr);
	}

}