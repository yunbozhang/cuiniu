<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_pay extends cls_base {

	//支付成功跳转页
	function act_return() {
		$payinfo = cls_obj::get('cls_com')->pay("on_return");
		$view_dir = cls_config::get_env('view_dir');
		$mod_dir = cls_config::get_env('mod_dir');
		$action = array();
		if($payinfo['code'] == 0) {
			$arr_action = array(
				"time" => 10,
				"target" => "_self",
				"title" => "跳转",
			);
			if($payinfo['pay_type'] == 1) {//支付成功
				$arr_action['url'] = fun_get::html_url("/index.php?app_act=cart.ok&id=" . $payinfo['about_id']);
				$action[] = $arr_action;
			} else if($payinfo['pay_type'] == 2) {//预付款成功
				$arr_action['url'] = fun_get::html_url("/index.php?app=member&app_act=repayment");
				$action[] = $arr_action;
			} else if($payinfo['pay_type'] == 3) {//预付款成功
				$arr_action['url'] = fun_get::html_url("/index.php?app=member&rid=" . $payinfo['about_id']);
				$action[] = $arr_action;
			}
		}
		if(empty($view_dir)) $view_dir = (fun_is::wap()) ? KJ_WAP_DIR : 'default';
		if(empty($mod_dir)) $mod_dir = 'default';
		cls_error::on_display($payinfo['code'] , $payinfo['msg'] , $action , $mod_dir , $view_dir);//显示消息页面
	}
	//支付消息处理页
	function act_notify() {
		$arr = cls_obj::get('cls_com')->pay("on_notify");
		if($arr['code'] == 0) {
			return $arr['msg_ok'];
		} else {
			return $arr['msg_err'];
		}
	}
}