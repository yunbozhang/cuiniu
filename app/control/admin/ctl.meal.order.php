<?php
/* 快捷订餐系统之多店版
 * 版本号：3.6
 * 官网：http://www.kjcms.com
 * 2014-03-17
 */
class ctl_meal_order extends mod_meal_order {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_state = tab_meal_order::get_perms("state");
		$reserve_num = 0;
		if(fun_get::get('url_channel')!='reserve') {
			$obj_one = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order where order_time>='" . date("Y-m-d",strtotime("1 day")) . "'");
			$reserve_num = $obj_one['num'];
		}
		$this->reserve_num = $reserve_num;
		return $this->get_view(); //显示页面
	}
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}
	//确认定单,返回josn数据
	function act_award() {
		$arr_return = $this->on_award();
		return fun_format::json($arr_return);
	}
	//设置状态,返回josn数据
	function act_state() {
		$arr_return = $this->on_state();
		return fun_format::json($arr_return);
	}
	//订单明细
	function act_detail() {
		//订单列表
		$id = (int)fun_get::get("id");
		$this->print_width = cls_config::get("width" , "print" , 200);
		$this->order_list = $this->get_detail($id);
		return $this->get_view(); //显示页面
	}
	function act_excel() {
		$this->on_excel();
	}
	//评论订单
	function act_comment() {
		$id = fun_get::get("id");
		$this->arr_list = $this->get_comment($id);
		$arr = cls_config::get("comment_menu" , "meal");
		$this->arr_comment_menu = array_values($arr);
		$arr = cls_config::get("comment_shop" , "meal" , array());
		$arr_comment_shop = array();
		foreach($arr as $item => $key) {
			$arr_comment_shop[$item] = explode("||" , $key);
		}
		$this->arr_comment_shop = $arr_comment_shop;
		return $this->get_view();
	}
	//保存评论
	function act_comment_save() {
		$arr = $this->save_comment();
		return fun_format::json($arr);
	}

}