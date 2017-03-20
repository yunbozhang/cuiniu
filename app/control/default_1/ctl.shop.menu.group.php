<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_menu_group extends mod_shop_menu_group {

	//默认浏览页
	function act_default() {
		$shop_id = $this->shop_info["id"];
		if($shop_id <0 ) $shop_id = 0;
		$where = "group_shop_id='" . $shop_id . "'";

		$this->arr_group = tab_meal_menu_group::get_list_layer(0 , 1 , $where);
		return $this->get_view(); //显示页面
	}
	//保存操作,返回josn数据
	function act_save_all() {
		$arr_return = $this->on_save_all();
		return fun_format::json($arr_return);
	}
	//保存操作,返回josn数据
	function act_move_save() {
		$arr_return = $this->on_move_save();
		return fun_format::json($arr_return);
	}

}