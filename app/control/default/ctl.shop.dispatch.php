<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_dispatch extends mod_shop_dispatch {

	//默认浏览页
	function act_default() {
		//自定义模式
		$topid = (int)fun_get::get("topid");
		$this->dispatch_mode = 1;
		$this->arr_list = $this->sql_list();
		$this->this_pid = (int)fun_get::get("url_pid");
		if(empty($topid) && $this->this_pid == 0) $topid = $this->arr_list['pid'];
		$this->topid = $topid;
		$this->arr_path = $this->get_path( $this->this_pid , $topid);
		return $this->get_view(); //显示页面
	}
	//当编辑时，有改动，需要ajax刷新页面列表数据
	function act_refresh_list() {
		//分页列表
		return fun_format::json($this->sql_list());
	}

	//移动显示页
	function act_add() {
		if(fun_is::set('url_pid')) {
			$get_url_pid = (int)fun_get::get("url_pid");
		} else {
			$get_url_pid = cls_config::get("area_city_id","meal");
		}
		$this->this_pid = $get_url_pid;
		$this->arr_path = $this->get_path( $this->this_pid ,0);
		$this->arr_list = $this->area_list($get_url_pid);
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