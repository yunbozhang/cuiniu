<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_report_menu extends mod_report_menu {

	//默认浏览页
	function act_default() {
		//订单量统计
		$this->report = $this->menu_num();
		$this->mode = fun_get::get("mode");
		return $this->get_view(); //显示页面
	}
}