<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
//环境配置文件
if(file_exists(KJ_DIR_DATA . '/config/env.php')) {
    include KJ_DIR_DATA . '/config/env.php';
}

if(!defined("ENV_SERVER")) {
	define("ENV_SERVER","online");//正式版
}

include KJ_DIR_DATA."/config/cfg.env.".ENV_SERVER.".php";
class cls_config extends cfg_env{
	static $perms = array();
	static function get($key , $type="base" , $default = '' , $dir = 'cfg/' ){
		$dirtype = $dir . $type;
		if(!isset(self::$perms["get-" . $dirtype][$type])){
			if(substr($dir , -1 , 1) != "/") $dir .= "/";
			if(file_exists(KJ_DIR_DATA . "/config/" . $dir . "cfg.".$type.".php")) {
				self::$perms["get-" . $dirtype][$type] = include(KJ_DIR_DATA . "/config/" . $dir . "cfg.".$type.".php");
			} else {
				self::$perms["get-" . $dirtype][$type] = array();
			}
		}
		if(empty($key)) {
			return self::$perms["get-" . $dirtype][$type];
		} else {
			if(isset(self::$perms["get-" . $dirtype][$type][$key])){
				return self::$perms["get-" . $dirtype][$type][$key];
			}else{
				return $default;
			}
		}
	}
	static function set($key , $val = '' , $type="base" , $dir = 'cfg/' ){
		if(!isset(self::$perms["get-" . $dir][$type])) {
			self::get($key , $type , '' , $dir);
		}
		self::$perms["get-" . $dir][$type][$key] = $val;
	}
	static function get_data($type , $key = '' , $default = '') {
		if(!isset(self::$perms["get_data"][$type])){
			if(file_exists(KJ_DIR_DATA . "/config/data/".$type.".php")) {
				self::$perms["get_data"][$type] = include(KJ_DIR_DATA . "/config/data/".$type.".php");
			} else {
				self::$perms["get_data"][$type] = array();
			}
		}
		if(!empty($key) && isset(self::$perms["get_data"][$type][$key])){
			return self::$perms["get_data"][$type][$key];
		} else if(empty($key)) {
			return self::$perms["get_data"][$type];
		} else {
			return $default;
		}
	}
	static function set_date($type , $key , $val) {
		if(empty($key)) {
			$arr = $val;
		} else {
			$arr = self::get_data($type);
			$arr[$key] = $val;
		}
		$val=var_export($arr,true);
		$val = '<'.'?php'.chr(10).'return '.$val.";";
		fun_file::file_create(KJ_DIR_DATA."/config/data/".$type.".php",$val,1);
	}
	/**当前访问客户端环境信息
	 * language : 语言
	 */
	static function get_env($key = '' , $env = '') {
		if(!isset(self::$perms["env"])) {
			self::$perms["env"]['list'] = include(KJ_DIR_DATA . "/config/cfg.env.php");
			$val = fun_get::get("app_env");
			if( fun_is::set("app_env") ) {
				self::$perms["env"]['sel'] = $val;
				cls_session::set_cookie('env' , $val);
			} else {
				self::$perms["env"]['sel'] = cls_session::get_cookie('env');
			}
			$v = self::$perms["env"]['sel'];
			if(empty($v)) $v = "id_0";
			if(isset(self::$perms["env"]['list'][$v]['cfg_set'])) {
				foreach(self::$perms["env"]['list'][$v]['cfg_set'] as $item) {
					self::set($item['key'] , $item['val'] , $item['type'] , $item['dir']);
				}
			}
		}
		if(empty($env)) $env = self::$perms["env"]['sel'];
		if(empty($env)) return '';
		if(empty($key)) return $env;
		return isset(self::$perms["env"]['list'][$env][$key]) ? self::$perms["env"]['list'][$env][$key] : '';
	}

}