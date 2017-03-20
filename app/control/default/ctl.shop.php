<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop extends mod_shop{
	function act_default(){
		$this->count_info = $this->get_count_info();
		$shop_report = new mod_shop_report;
		$this->report = $shop_report->order_num();
		$this->editinfo = $this->get_editinfo();
		return $this->get_view();
	}
	function act_info(){
		$this->count_info = $this->get_count_info();
		$this->editinfo = $this->get_editinfo();
		return $this->get_view();
	}
	function act_extend(){
		$this->editinfo = $this->get_editinfo();
		return $this->get_view();
	}
	function act_intro(){
		$this->editinfo = $this->get_editinfo();
		return $this->get_view();
	}
	function act_printsms(){
		$this->arr_sms = tab_meal_shop::get_perms("sms");
		$this->editinfo = $this->get_editinfo();
		return $this->get_view();
	}
	//保存店铺资料
	function act_info_save(){
		$arr = fun_format::json( $this->on_saveinfo() );
		return $arr;
	}
	//保存店铺配置
	function act_extend_save(){
		$arr = fun_format::json( $this->on_saveinfo(1) );
		return $arr;
	}
	//保存店铺简介
	function act_info_save_intro(){
		$arr = fun_format::json( $this->on_saveinfo(2) );
		return $arr;
	}
	//保存店铺打印与短信设置
	function act_info_save_printsms(){
		$arr = fun_format::json( $this->on_saveinfo(3) );
		return $arr;
	}
	//保存店铺状态
	function act_state_save(){
		$arr = fun_format::json( $this->on_savestate() );
		return $arr;
	}

}