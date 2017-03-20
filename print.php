<?php
include("inc.php");
$print_url = cls_config::get("url" , "print" , "" , "");
if(empty($print_url)) exit("没有配置无线打印");

if(isset($_GET['sn']) && isset($_GET['status'])) {
	$status = $_GET['status'];
	$sn = fun_get::get('sn');
	if( in_array($status  , array('normal','online') )) {
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_state=1 where shop_print_id='" . $sn . "' and shop_print_tongbu=1");
	} else {
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_state=-1 where shop_print_id='" . $sn . "' and shop_print_tongbu=1");
	}
} else {
	$id = (int)fun_get::get("dingdanID");
	$arr = tab_meal_order::on_state($id , 1 ,'','order_state=0');
	$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_order set order_isprint=1 where order_id='" . $id . "'");
}