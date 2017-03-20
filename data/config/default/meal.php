<?php
/* 数据字典
 * 数据库 -> 表 -> 字段
 * 值：大于 10 表示不参与用户配置 , 1表示显示，0表示不显示
 */
return array(
	"meal.menu" => array(
	//用户表,sys_user
		"menu_id" => array("val" => 0,"w" => 0), //菜谱id
		"menu_sort" => array("val" => 1,"w" => 20), //菜谱id
		"menu_number" => array("val" => 11,"w" => 120), //编号
		"menu_title" => array("val" => 1,"w" => 100), //标题
		"group_name" => array("val" => 1,"w" => 80), //分组
		"menu_price" => array("val" => 1,"w" => 50), //单价
		"menu_unit" => array("val" => 11,"w" => 100), //单位
		"menu_pic" => array("val" => 0,"w" => 0), //图片
		"menu_pic_small" => array("val" => 0,"w" => 50), //图片
		"menu_addtime" => array("val" => 1,"w" => 120), //添加时间
		"menu_updatetime" => array("val" => 0,"w" => 120), //更新时间
		"menu_group_id" => array("val" => 0,"w" => 50), //组id
		"menu_intro" => array("val" => 0,"w" => 50), //简介
		"menu_num" => array("val" => 1,"w" => 50), //默认数量
		"menu_attribute" => array("val" => 11,"w" => 80), //属性
		"menu_state" => array("val" => 1,"w" => 80), //状态
		"menu_mode" => array("val" => 1,"w" => 80), //提供模式
		"menu_tj" => array("val" => 1,"w" => 40), //是否推荐
	),
	"meal.shop" => array(
	//用户表,sys_user
		"shop_id" => array("val" => 0,"w" => 0), //店铺id
		"shop_name" => array("val" => 1,"w" => 50), //店铺名称
		"shop_user_id" => array("val" => 11,"w" => 120), //用户id
		"user_name" => array("val" => 1,"w" => 50), //所属用户
		"shop_addtime" => array("val" => 1,"w" => 80), //添加时间
		"shop_updatetime" => array("val" => 0,"w" => 80), //修改时间
		"shop_intro" => array("val" => 11,"w" => 50), //介绍
		"shop_linkname" => array("val" => 1,"w" => 50), //联系人
		"shop_linktel" => array("val" => 1,"w" => 50), //电话
		"shop_tel" => array("val" => 1,"w" => 50), //电话
		"shop_address" => array("val" => 1,"w" => 150), //店铺地址
		"shop_area_id" => array("val" => 11,"w" => 0), //地区id
		"shop_area_allid" => array("val" => 11,"w" => 0), //地区id树
		"shop_area" => array("val" => 1,"w" => 80), //所在区域
		"shop_dispatch_price" => array("val" => 1,"w" => 50), //起送价格
		"shop_state" => array("val" => 1,"w" => 80), //状态
	),
	"meal.dispatch" => array(
		"dispatch_id" => array("val" => 0,"w" => 0), //店铺id
		"area_name" => array("val" => 1,"w" => 120), //店铺名称
		"dispatch_price" => array("val" => 1,"w" => 120), //起送价
		"dispatch_addprice" => array("val" => 1,"w" => 120), //配送费
		"dispatch_number" => array("val" => 1,"w" => 50), //所属用户
		"dispatch_time" => array("val" => 1,"w" => 80), //添加时间
		"dispatch_smstel" => array("val" => 1,"w" => 120), //短信电话
		"dispatch_area_id" => array("val" => 13 , "w"=>0),
	),
	"meal.menu.today" => array(
		"today_id" => array("val" => 0,"w" => 0), //id
		"today_menu_id" => array("val" => 11,"w" => 50), //菜品id
		"menu_title" => array("val" => 1,"w" => 120), //菜品名称
		"menu_group_id" => array("val" => 11,"w" => 120), //所属组id
		"today_num" => array("val" => 1,"w" => 120), //数量
		"today_date" => array("val" => 11,"w" => 50), //日期
		"today_date_period" => array("val" => 11,"w" => 80), //时间段
	),
	"meal.order" => array(
	//用户表,sys_user
		"order_id" => array("val" => 11,"w" => 0), //订单自动id
		"order_state" => array("val" => 1,"w" => 80), //状态
		"order_isaward" => array("val" => 1,"w" => 80), //是否奖励
		"order_addtime" => array("val" => 1,"w" => 120), //添加时间
		"order_area" => array("val" => 1,"w" => 150), //楼号1
		"order_louhao1" => array("val" => 1,"w" => 50), //楼号1
		"order_louhao2" => array("val" => 1,"w" => 50), //楼号2
		"order_company" => array("val" => 1,"w" => 100), //公司
		"order_depart" => array("val" => 1,"w" => 50), //部门
		"order_name" => array("val" => 1,"w" => 50), //姓名
		"order_sex" => array("val" => 1,"w" => 50), //性别
		"order_tel" => array("val" => 1,"w" => 50), //电话
		"order_telext" => array("val" => 0,"w" => 30), //分机号
		"order_mobile" => array("val" => 1,"w" => 50), //手机
		"order_arrive" => array("val" => 1,"w" => 80), //抵达时间
		"order_user_id" => array("val" => 11,"w" => 0), //用户id
		"user_name" => array("val" => 1,"w" => 50), //用户
		"order_ids" => array("val" => 11,"w" => 0), //订单列表
		"order_total" => array("val" => 1,"w" => 50), //应付金额
		"order_score_money" => array("val" => 1,"w" => 50), //消耗积分
		"order_favorable" => array("val" => 1,"w" => 50), //优惠金额
		"order_total_pay" => array("val" => 1,"w" => 50), //应付金额
		"order_number" => array("val" => 1,"w" => 80), //订单号
		"order_ticket" => array("val" => 1,"w" => 50), //发票
		"order_beta" => array("val" => 0,"w" => 0), //备注
		"order_act_ids" => array("val" => 1,"w" => 200), //享受活动
	),
	"meal.act" => array(
	//活动表
		"act_id" => array("val" => 0,"w" => 0), //结算id
		"act_shop_id" => array("val" => 0,"w" => 150), //店铺id
		"act_name" => array("val" => 1,"w" => 150), //活动名称
		"act_where" => array("val" => 0,"w" => 80), //条件
		"act_where_val" => array("val" => 0,"w" => 80), //条件值
		"act_method" => array("val" => 0,"w" => 80), //优惠
		"act_method_val" => array("val" => 0,"w" => 80), //优惠值
		"act_starttime" => array("val" => 1,"w" => 120), //开始时间
		"act_endtime" => array("val" => 1,"w" => 120), //结束时间
		"act_addtime" => array("val" => 0,"w" => 120), //添加时间
		"act_state" => array("val" => 1,"w" => 100), //状态
		"act_beta" => array("val" => 0,"w" => 200), //备注
	),
	"meal.checkout.no" => array(
	//未结算表
		"shop_id" => array("val" => 0,"w" => 0), //店铺id
		"shop_name" => array("val" => 1,"w" => 200), //店铺名称
		"moneytotal" => array("val" => 1,"w" => 60), //货品金额
		"moneyrepay" => array("val" => 1,"w" => 60), //预付金额
		"moneyadd" => array("val" => 1,"w" => 60), //配送金额
		"moneyfav" => array("val" => 1,"w" => 60), //优惠金额
		"moneyall" => array("val" => 1,"w" => 60), //有效金额
		"moneypay" => array("val" => 1,"w" => 60), //在线支付
		"moneyaffter" => array("val" => 1,"w" => 60), //货到付款
		"num" => array("val" => 1,"w" => 120), //订单数量
		"paynum" => array("val" => 1,"w" => 120), //在线支付数量
		"affternum" => array("val" => 1,"w" => 120), //货到付款数量
		"shop_rebate" => array("val" => 1,"w" => 60), //抽成比例
		"shop_checkout_money" => array("val" => 1,"w" => 60), //最低结款
		"money" => array("val" => 1,"w" => 60), //抽成
	),
	"meal.checkout" => array(
	//结算表
		"checkout_id" => array("val" => 1,"w" => 0), //结算id
		"checkout_moneytotal" => array("val" => 1,"w" => 60), //货品金额
		"checkout_moneyrepay" => array("val" => 1,"w" => 60), //预付款
		"checkout_moneyadd" => array("val" => 1,"w" => 60), //配送费
		"checkout_moneyfav" => array("val" => 1,"w" => 60), //优惠金额
		"checkout_moneyall" => array("val" => 1,"w" => 60), //有效金额
		"checkout_moneypay" => array("val" => 1,"w" => 60), //在线支付
		"checkout_moneyaffter" => array("val" => 1,"w" => 60), //货到付款
		"checkout_num" => array("val" => 1,"w" => 60), //订单数量数
		"checkout_numpay" => array("val" => 1,"w" => 60), //在线支付数量
		"checkout_rebate" => array("val" => 1,"w" => 60), //抽成比例
		"checkout_rebate_money" => array("val" => 1,"w" => 60), //起抽金额
		"checkout_money" => array("val" => 1,"w" => 60), //抽成
		"checkout_date1" => array("val" => 1,"w" => 120), //结算日期
		"checkout_date2" => array("val" => 1,"w" => 120), //结算日期
		"checkout_addtime" => array("val" => 1,"w" => 120), //操作时间
		"checkout_user_id" => array("val" => 1,"w" => 60), //结算日期
		"checkout_beta" => array("val" => 0,"w" => 300), //备注
	),
	"meal.menu.group" => array(
	//未结算表
		"group_id" => array("val" => 0,"w" => 0), //id
		"group_pid" => array("val" => 11,"w" => 0), //id
		"group_sort" => array("val" => 1,"w" => 60), //排序
		"group_name" => array("val" => 1,"w" => 200), //名称
	),
);