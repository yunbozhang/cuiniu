<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_meal extends mod_meal {

	//店铺列表样式一
	function act_shop1() {
		//是否为管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			cls_error::on_error("no_limit");
		}
		$this->arr_list = $this->get_shop1();
		return $this->get_view(); //显示页面
	}
}