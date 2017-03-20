<?php
class ctl_other_pay_refund extends mod_other_pay_refund {

	//默认浏览页
	function act_default() {
		$this->arr_list = $this->get_pagelist();
		//相关属性
		$this->arr_state =  tab_other_pay_refund::get_perms("state");
		$this->arr_method = cls_config::get("" , "pay" , "" , "");
		return $this->get_view();
	}
	function act_tongbu() {
		$obj_rs = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "other_pay_refund where refund_state=-1");
		$total = $obj_rs['num'];
		return fun_format::json(array('code'=>0,'total'=>$total));
	}
	function act_tongbu_go() {
		$id = (int)fun_get::get("id");
		$nextid = 0;
		$arr = array("code" => 0,"nextid" => 0);
		$obj_rs = cls_obj::db()->get_one("select refund_pay_id from " . cls_config::DB_PRE . "other_pay_refund where refund_state=-1 and refund_pay_id>'" . $id . "' order by refund_pay_id");
		if(!empty($obj_rs)) {
			$arr = cls_obj::get('cls_com')->pay("get_refund_state",$obj_rs['refund_pay_id']);
			$arr['nextid'] = $obj_rs['refund_pay_id'];
		}
		return fun_format::json($arr);
	}
}