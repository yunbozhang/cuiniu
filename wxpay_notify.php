<?php
/* 快捷订购系统之多店版
 * 版本号：3.7
 * 官网：http://www.kjcms.com
 * 2014-06-25
 */
require "inc.php";
fun_get::get("paymethod","weixin");
$arr = cls_obj::get('cls_com')->pay("on_notify");
if($arr['code'] == 0) {
	echo $arr['msg_ok'];
} else {
	echo $arr['msg_err'];
}