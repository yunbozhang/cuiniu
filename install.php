<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
require "inc.php";
if(file_exists(KJ_DIR_DATA."/install.inc")){
	exit('系统已安装，需要重新安装请删除：data/install.inc 文件');
}
cls_app::on_load("install");