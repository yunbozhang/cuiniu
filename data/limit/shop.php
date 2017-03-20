<?php
return array(
	"shop"    => array(
		"name" => "会员中心",
		"list" => array(
			"shop" => array( "act"=>array("info"=>'店铺资料',"extend"=>'订餐配置',"state"=>"运营状态") , "name"=>"店铺管理" ),
			"menu" => array( "act"=>array("edit","save","delete", "dellist" , "del" ,"reback" ,"state" , "group"=>"分组" , "mode") , "name"=>"菜品管理" ),
			"menu.today" => array( "act"=>array("add" , "save") , "name"=>"当日菜品" ),
			"order" => array( "act"=>array("confirm",'today'=>'来单显示','state'=>'处理订单','delete','award'=>'奖励积分','detail'=>'查看明细') , "name"=>"订单管理" ),
			"menu.group" => array( "act"=>array("save") , "name"=>"菜品分组" ),
			"dispatch" => array( "act"=>array("add" , "save" ,"delete") , "name"=>"派送范围" ),
			"report" => array( "act"=>array() , "name"=>"订单统计" ),
			"reportmenu" => array( "act"=>array() , "name"=>"销售统计" ),
			"act" => array( "act"=>array("save","edit","delete") , "name"=>"营销活动" ),
			"weixin" => array( "act"=>array("site"=>"申请开能公众号","message"=>"消息管理","keywords" => "关键词管理" , "menu" => "微信菜单" , "user" => "关注者管理" , "sendmsg" => "发送消息") , "name"=>"微信公众号" ),
			"article" => array( "act"=>array("del"=>'删除',"edit"=>'编辑',"save"=>"保存") , "name"=>"活动公告" ),
			"checkout" => array( "act"=>array() , "name"=>"结算记录" ),
		),
	),//订餐模块
);
?>