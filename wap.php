<?php
/* å¿«æ·è®¢é¤ç³»ç»Ÿä¹‹å¤šåº—ç‰ˆ
 * ç‰ˆæœ¬å·ï¼š3.9
 * å®˜ç½‘ï¼šhttp://www.kjcms.com
 * 2016-08-30
 */
require "inc.php";
if(!cls_obj::get("cls_user")->is_login() && isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] == 'weixinlogin') {
	$arr = cls_obj::get('cls_com')->userapi("login_token" , array("jump_url"=>'' , "plat" => 'wx'));
}
//ÊÇ·ñÕûºÏÎ¢ÐÅ
if(fun_is::set("app_weixin")) {
	cls_obj::get("cls_session")->set("weixin",1);
	$certify = cls_config::get("certify" , 'weixin' , '' ,'');
	if($certify == 1 && cls_obj::get("cls_user")->is_login() == false) {//Î´µÇÂ¼Ôò×Ô¶¯Ìø×ªÎ¢ÐÅµÇÂ¼
		$url = cls_config::get("url");
		$jump_url = fun_get::url(array('app_weixin'=>''));
		$jump_url = urlencode(cls_config::get("domain") . $jump_url);
		fun_base::url_jump( $url . '/common.php?app=api.login&plat=weixin&jump_url=' . $jump_url);
		exit;
	}
} else if(fun_is::weixin()) {
	cls_obj::get("cls_session")->set("weixin",1);
}
$agent = "iphone";
cls_app::on_load("default" , "wap");