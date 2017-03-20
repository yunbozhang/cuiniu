<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_user extends mod_user {
	function act_default(){
		$this->arr_isread = $this->get_isread();
		return $this->get_view();
	}
	function act_edit() {
		return $this->get_view();
	}
	function act_quan_zan() {
		$this->arr_list = $this->get_quan_zan();
		if(fun_get::get("app_ajax")=='1') {
			return $this->get_view('quan.zan.ajax');
		} else {
			return $this->get_view();
		}
	}
	function act_quan_ping() {
		$this->arr_list = $this->get_quan_ping();
		if(fun_get::get("app_ajax")=='1') {
			return $this->get_view('quan.ping.ajax');
		} else {
			return $this->get_view();
		}
	}
	function act_quan_bzan() {
		$this->arr_list = $this->get_quan_bzan();
		$arr_isread = $this->get_isread();
		if($arr_isread['zan']>0) {
			cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "quan_zan set zan_isread=1 where zan_to_uid='" . cls_obj::get("cls_user")->uid . "' and zan_isread=0");
		}
		if(fun_get::get("app_ajax")=='1') {
			return $this->get_view('quan.bzan.ajax');
		} else {
			return $this->get_view();
		}
	}
	function act_quan_bping() {
		$arr_isread = $this->get_isread();
		if($arr_isread['ping']>0) {
			cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "quan_ping set ping_isread=1 where ping_to_uid='" . cls_obj::get("cls_user")->uid . "' and ping_isread=0");
		}
		$this->arr_list = $this->get_quan_bping();
		if(fun_get::get("app_ajax")=='1') {
			return $this->get_view('quan.bping.ajax');
		} else {
			return $this->get_view();
		}
	}
}