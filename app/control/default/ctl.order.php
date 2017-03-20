<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_order extends mod_order {
	//订单明细
	function act_detail() {
		//订单列表
		$id = (int)fun_get::get("id");
		$this->order_list = $this->get_today_list(" and order_id='" . $id . "'");
		return $this->get_view(); //显示页面
	}
	//今日订单
	function act_today() {
		$this->hide_handle = tab_sys_user_config::get_var("call.hide.handle"  , $this->app_dir);
		$state = (int)fun_get::get("state");
		$date = fun_get::get("s_date" , date("Y-m-d",TIME));
		if(!fun_is::isdate($date)) $date = date("Y-m-d",TIME);
		$where = " and order_day='" . $date . "'";
		$area_info = tab_sys_user_config::get_var("call.area"  , $this->app_dir);
		$this->area_val = $area_info;
		$this->area_list = fun_kj::get_area();
		if(!empty($area_info)) {
			$arr = explode(",",$area_info);
			$area_id = (int)$arr[count($arr)-1];
			$where .= " and " . cls_obj::db()->concat("," , "order_area_allid" , ",") . " like '%," . $area_id . ",%'";
		}
		$this->order_list = $this->get_today_list( $where);
		return $this->get_view(); //显示页面
	}
	//接受预定
	function act_state_accept() {
		$arr_return = $this->on_accept();
		return fun_format::json($arr_return);
	}
	//拒绝预定
	function act_state_refuse() {
		$arr_return = $this->on_refuse();
		return fun_format::json($arr_return);
	}
	//获取新订单
	function act_today_getnew() {
		$id = (int)fun_get::get("id");
		$shop_id = (int)fun_get::get("shop_id");
		$date = fun_get::get("s_date" , date("Y-m-d",TIME));
		if(!fun_is::isdate($date)) $date = date("Y-m-d",TIME);
		$area_info = tab_sys_user_config::get_var("call.area"  , $this->app_dir);
		$where = " and order_id>'" . $id . "' and order_day='" . $date . "'";
		if(!empty($area_info)) {
			$arr = explode(",",$area_info);
			$area_id = (int)$arr[count($arr)-1];
			$where .= " and " . cls_obj::db()->concat("," , "order_area_allid" , ",") . " like '%," . $area_id . ",%'";
		}
		if(!empty($shop_id)) $where .= " and order_shop_id='" . $shop_id . "'";
		$order_list = $this->get_today_list($where);
		return fun_format::json($order_list);
	}
	//订时查看是否有新单
	function act_isnew() {
		$arr_return = $this->get_newnum();
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
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

}