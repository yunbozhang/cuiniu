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
	"weixin.redpack" => array(
		"redpack_id" => array("val" => 1,"w" => 60), //ID
		"redpack_type" => array("val" => 1,"w" => 80), //类型
		"redpack_title" => array("val" => 1,"w" => 150), //活动名称
		"redpack_shopname" => array("val" => 1,"w" => 150), //商家名称
		"redpack_beta" => array("val" => 0,"w" => 200), //备注
		"redpack_greet" => array("val" => 0,"w" => 200), //祝福语
		"redpack_min_money" => array("val" => 1,"w" => 60), //最小值
		"redpack_max_money" => array("val" => 1,"w" => 60), //最大值
		"redpack_num" => array("val" => 1,"w" => 60), //发放次数
		"redpack_user_num" => array("val" => 1,"w" => 60), //领取次数
		"redpack_starttime" => array("val" => 1,"w" => 120), //开始时间
		"redpack_endtime" => array("val" => 1,"w" => 120), //结束时间
		"redpack_event" => array("val" => 1,"w" => 100), //触发事件
		"redpack_state" => array("val" => 1,"w" => 60), //状态
		"redpack_addtime" => array("val" => 1,"w" => 120), //添加时间
		"redpack_site_id" => array("val" => 1,"w" => 150), //站点
	),
);