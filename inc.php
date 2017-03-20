<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
define('KJ_DIR_ROOT',str_replace("\\","/",dirname(__FILE__)));       //web根目录
define('KJ_DIR_APP',KJ_DIR_ROOT."/app");
define('KJ_DIR_CACHE',KJ_DIR_ROOT."/cache");
define('KJ_DIR_DATA',KJ_DIR_ROOT."/data");
define('KJ_DIR_LIB',KJ_DIR_ROOT."/lib");
define('KJ_DIR_UPLOAD',KJ_DIR_ROOT."/upload");
define('KJ_DIR_UPLOAD_UEL',"/upload");
define('KJ_WEBCSS_PATH',"/webcss");
define('KJ_ADMIN_FILENAME',"master.php");
define('KJ_WAP_DIR',"wap");
require KJ_DIR_LIB."/base.php";