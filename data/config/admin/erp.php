<?php
/* 数据字典
 * 数据库 -> 表 -> 字段
 * 值：大于 10 表示不参与用户配置 , 1表示显示，0表示不显示
 */
return array(
	"erp.asset" => array(
	//用户表,sys_user
		"asset_id" => array("val" => 0,"w" => 0), //id
		"asset_code" => array("val" => 1,"w" => 300), //资产编码
		"asset_name" => array("val" => 11,"w" => 50), //名称
		"asset_model" => array("val" => 1,"w" => 80), //型号
		"asset_buydate" => array("val" => 1,"w" => 80), //购买日期
		"asset_unit" => array("val" => 1,"w" => 80), //单位
		"asset_num" => array("val" => 1,"w" => 80), //数量
		"asset_price" => array("val" => 1,"w" => 80), //价格
		"depart_name" => array("val" => 1,"w" => 80), //所属部门
		"asset_position" => array("val" => 1,"w" => 80), //放置
		"asset_area" => array("val" => 1,"w" => 80), //放置区域
		"asset_duty_person" => array("val" => 1,"w" => 80), //责任人
		"asset_manager" => array("val" => 1,"w" => 80), //资产管理员
		"asset_state" => array("val" => 1,"w" => 80), //状态
	),
);
?>