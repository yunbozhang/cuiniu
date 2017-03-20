<?php
/* KLKKDJ订餐之单店版
 * 版本号：3.0测试版
 * 官网：http://www.klkkdj.com
 * 2012-12-11
 */

class ctl_other_uc extends mod_other_uc {

	//默认浏览页
	function act_default() {
		$this->is_install = fun_get::get("isinstall");
		$this->isstart = (cls_config::USER_CENTER == 'user.uc') ? 1 : 0;
		$this->editinfo = $this->get_editinfo();
		return $this->get_view(); //显示页面
	}

	//保存操作,返回josn数据
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}

}