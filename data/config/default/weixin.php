<?php
/* 数据字典
 * 数据库 -> 表 -> 字段
 * 值：大于 10 表示不参与用户配置 , 1表示显示，0表示不显示
 */
return array(
	"weixin.user" => array(
		"user_pic" => array("val" => 1,"w" => 60), //分组
		"user_openid" => array("val" => 1,"w" => 200), //id
		"user_name" => array("val" => 1,"w" => 150), //名称
		"user_sex" => array("val" => 1,"w" => 50), //总数
		"user_area" => array("val" => 1,"w" => 150), //每天数量
		"user_addtime" => array("val" => 1,"w" => 120), //已兑换
		"user_canceltime" => array("val" => 0,"w" => 120), //今日兑换
		"user_state" => array("val" => 0,"w" => 100), //最后兑换时间
	),
	"weixin.site" => array(
		"site_id" => array("val" => 1,"w" => 60), //ID
		"site_name" => array("val" => 1,"w" => 120), //站点名称
		"site_domain" => array("val" => 1,"w" => 200), //id
		"site_wx_uname" => array("val" => 1,"w" => 120), //名称
		"site_wx_token" => array("val" => 1,"w" => 60), //总数
		"site_wx_appid" => array("val" => 1,"w" => 100), //每天数量
		"site_wx_appsecret" => array("val" => 1,"w" => 100), //已兑换
		"site_wx_certify" => array("val" => 1,"w" => 50), //今日兑换
		"site_wx_msgmode" => array("val" => 1,"w" => 50), //最后兑换时间
	),
);