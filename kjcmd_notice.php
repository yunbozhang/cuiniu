<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
require "inc.php";
$obj_db = cls_obj::db();
$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "sys_cmd_notice where notice_state=0 order by notice_id desc limit 0,5");
while($obj_rs = $obj_db->fetch_array($obj_result)) {
	tab_sys_cmd_notice::on_send($obj_rs['notice_id']);
	usleep(5);
}