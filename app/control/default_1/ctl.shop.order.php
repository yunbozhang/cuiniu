<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_order extends mod_shop_order {

	//默认浏览页
	function act_default() {
		//分页列表
		$this->arr_list = $this->get_pagelist();
		$this->arr_state = tab_meal_order::get_perms("state");
		return $this->get_view(); //显示页面
	}
	//订单明细
	function act_detail() {
		//订单列表
		$id = (int)fun_get::get("id");
		$this->order_list = $this->get_today_list(" and order_id='" . $id . "'");
		return $this->get_view(); //显示页面
	}
	//今日订单
	function act_today() {
		//订单列表
		$state = (int)fun_get::get("state");
		$date = fun_get::get("s_date" , date("Y-m-d",TIME));
		if(!fun_is::isdate($date)) $date = date("Y-m-d",TIME);
		$where = " and order_day='" . $date . "'";
		($state>0) ? $where .= " and order_state>0" : $where .= " and order_state='" . $state . "'";
		$this->order_list = $this->get_today_list( $where );
		return $this->get_view(); //显示页面
	}
	//接受预定
	function act_accept() {
		$arr_return = $this->on_accept();
		return fun_format::json($arr_return);
	}
	//拒绝预定
	function act_refuse() {
		$arr_return = $this->on_refuse();
		return fun_format::json($arr_return);
	}
	//获取新订单
	function act_getnew() {
		$id = (int)fun_get::get("id");
		$date = fun_get::get("s_date" , date("Y-m-d",TIME));
		if(!fun_is::isdate($date)) $date = date("Y-m-d",TIME);
		$this->order_list = $this->get_today_list(" and order_id>'" . $id . "' and order_day='" . $date . "'");
		if(count($this->arr_list) > 0) {
			return $this->get_view(); //显示页面
		} else {
			return ''; //返回空
		}
	}
	//订时查看是否有新单
	function act_isnew() {
		$arr_return = $this->get_newnum();
		return fun_format::json($arr_return);
	}

}