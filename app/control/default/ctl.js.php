<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_js extends inc_mod_default {
	function act_shop_hit() {
		$shop_id = (int)fun_get::get("shop_id");
		cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_hits=shop_hits+1 where shop_id='" . $shop_id . "'");
		return "";
	}
}