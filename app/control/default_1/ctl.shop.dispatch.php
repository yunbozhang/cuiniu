<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_dispatch extends mod_shop_dispatch {

	//默认浏览页
	function act_default() {
		$obj_shop = cls_obj::db()->get_one("select shop_dispatch_mode from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $this->shop_info["id"] . "'");
		if(empty($obj_shop) || empty($obj_shop["shop_dispatch_mode"]) ) {
			//默认模式
			$this->dispatch_mode = 0;
		} else {
			//自定义模式
			$this->dispatch_mode = 1;
			$this->arr_list = $this->sql_list();
			$this->this_pid = (int)fun_get::get("url_pid");
			$this->this_path = $this->get_path( $this->this_pid );
		}
		return $this->get_view(); //显示页面
	}
	//当编辑时，有改动，需要ajax刷新页面列表数据
	function act_refresh_list() {
		//分页列表
		return fun_format::json($this->sql_list());
	}

	//移动显示页
	function act_add() {
		$this->arr_list = $this->area_list();
		return $this->get_view(); //显示页面
	}
	//保存操作,返回josn数据
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	//保存添加操作,返回josn数据
	function act_add_save() {
		$arr_return = $this->on_add_save();
		return fun_format::json($arr_return);
	}
	//保存操作,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

}