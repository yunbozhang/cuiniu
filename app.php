<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
require "inc.php";
$agent = "iphone";
cls_obj::get("cls_session")->set("isapp" , 1);
cls_app::on_load("default" , "wap");