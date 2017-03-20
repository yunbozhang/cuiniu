<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_weixin extends mod_weixin {

	//店铺列表样式一
	function act_dialog_site() {
		//是否为管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			cls_error::on_error("no_limit");
		}
		$this->arr_list = $this->get_site_list();
		return $this->get_view(); //显示页面
	}
	function act_sing_js() {
		$url = urldecode($_GET['url']);
		$arr = cls_weixin::sign_js($url);
		return fun_format::json($arr);
	}
}