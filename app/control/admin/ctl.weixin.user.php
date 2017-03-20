<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_weixin_user extends mod_weixin_user {

	//默认浏览页
	function act_default() {
		$this->arr_list = $this->get_pagelist();
		return $this->get_view(); //显示页面
	}
	function act_sendmsg_page() {
		$arr_list = $this->get_pagelist();
		$page = (int)fun_get::get("page");
		if($page>$arr_list["pageinfo"]['pages']) {
			$arr_return = array("code" => 500 , "msg" => "发送完毕");
		} else {
			$arr_return = array('code' => 0 , 'pages' => $arr_list['pageinfo']['pages']);
			foreach($arr_list['list'] as $item) {
				$arr_return['list'][] = array("openid" => $item['user_openid'] , "name" => $item['user_name']);
			}
		}

		return fun_format::json($arr_return);
	}
	function act_sendmsg_go() {
		$arr_return = $this->on_sendmsg();
		return fun_format::json($arr_return);
	}
	function act_tongbu() {
		$arr_return = $this->on_tongbu();
		return fun_format::json($arr_return);
	}
	function act_tongbu_info() {
		$arr_return = $this->on_tongbu_info();
		return fun_format::json($arr_return);
	}
}