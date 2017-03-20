<?php
/* 数据字典
 * 数据库 -> 表 -> 字段
 * 值：大于 10 表示不参与用户配置 , 1表示显示，0表示不显示
 */
return array(
	"quan.category" => array(
	//用户表,sys_user
		"category_id" => array("val" => 0,"w" => 0), //id
		"category_sort" => array("val" => 1,"w" => 60), //排序
		"category_name" => array("val" => 1,"w" => 150), //排序
	),
	"quan.user" => array(
	//用户表,sys_user
		"a.user_id" => array("val" => 1,"w" => 0), //id
		"b.user_netname" => array("val" => 1,"w" => 160), //排序
		"a.user_zan_state" => array("val" => 1,"w" => 80), //排序
		"a.user_ping_state" => array("val" => 1,"w" => 80), //排序
		"a.user_pub_state" => array("val" => 1,"w" => 80), //排序
		"a.user_zan_num" => array("val" => 1,"w" => 80), //排序
		"a.user_ping_num" => array("val" => 1,"w" => 80), //排序
		"a.user_bzan_num" => array("val" => 1,"w" => 80), //排序
		"a.user_bping_num" => array("val" => 1,"w" => 80), //排序
		"a.user_pub_num" => array("val" => 1,"w" => 80), //排序
		"b.user_regtime" => array("val" => 1,"w" => 120), //排序
	),
);