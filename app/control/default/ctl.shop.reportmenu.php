<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_reportmenu extends mod_shop_reportmenu {

	//默认浏览页
	function act_default() {
		//订单量统计
		$this->report = $this->menu_num();
		$this->mode = fun_get::get("mode");
		return $this->get_view(); //显示页面
	}
}