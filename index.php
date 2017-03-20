<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
require "inc.php";
if(!file_exists(KJ_DIR_DATA."/install.inc")){
	fun_base::url_jump("install.php");
	exit;
}
if(!cls_obj::get("cls_user")->is_login() && isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] == 'weixinlogin') {
	$arr = cls_obj::get('cls_com')->userapi("login_token" , array("jump_url"=>'' , "plat" => 'wx'));
}
//是否整合微信
if(fun_is::set("app_weixin")) {
	cls_obj::get("cls_session")->set("weixin",1);
	$certify = cls_config::get("certify" , 'weixin' , '' ,'');
	if($certify == 1 && cls_obj::get("cls_user")->is_login() == false) {//未登录则自动跳转微信登录
		$url = cls_config::get("url");
		$jump_url = fun_get::url(array('app_weixin'=>''));
		$jump_url = urlencode(cls_config::get("domain") . $jump_url);
		fun_base::url_jump( $url . '/common.php?app=api.login&plat=weixin&jump_url=' . $jump_url);
		exit;
	}
} else if(fun_is::weixin()) {
	cls_obj::get("cls_session")->set("weixin",1);
}

$agent = fun_get::agent();
if(in_array($agent,array('','ipad'))) {
	$version = fun_get::ie_version();
	$mod_dir = cls_config::get_env('mod_dir');
	if($version < 9 && is_dir(KJ_DIR_APP . "/view/default_ie") ) {
		$view_dir = 'default_ie';
	} else {
		$view_dir = cls_config::get_env('view_dir');
	}
	cls_app::on_load($mod_dir , $view_dir);
} else {
	//目前手机支持的浏览器
	if(in_array($agent , array("qq","iphone","android"))) {
		cls_app::on_load("default" , "wap");
	} else {
		cls_app::on_load("default" , "wap" , array("app"=>'noagent' , "app_module"=>"" , "app_act"=>""));
	}
}