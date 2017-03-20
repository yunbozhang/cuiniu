<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_dispatch extends mod_meal_dispatch {

	//默认浏览页
	function act_default() {
		$topid = (int)fun_get::get("url_topid");
		$this->arr_list = $this->sql_list();
		//if(!fun_is::set('url_topid')) $topid = $this->arr_list['pid'];
		$this->this_pid = (int)fun_get::get("url_pid");
		$this->topid = $topid;
		$this->arr_path = $this->get_area_path( $this->this_pid  , $topid);
		return $this->get_view(); //显示页面
	}
	//移动显示页
	function act_add() {
		if(fun_is::set('url_pid')) {
			$get_url_pid = (int)fun_get::get("url_pid");
		} else {
			$get_url_pid = cls_config::get("area_city_id","meal");
		}
		$this->this_pid = $get_url_pid;
		$this->arr_path = $this->get_area_path( $this->this_pid );
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