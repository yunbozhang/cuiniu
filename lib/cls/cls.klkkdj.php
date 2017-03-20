<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_klkkdj {
	const KJ_VERISION = "3.6";
	//根据账号与密码生成相关url
	static function get_url() {
		$cfg_version = cls_config::get("" , "version" , "" , "");
		$arr = array(
			'uname' => $cfg_version["web_uname"] ,
			'pwd' => $cfg_version["web_pwd"] ,
			'module' => $cfg_version["module"] ,
			'version' => $cfg_version["version"] ,
			'site' => cls_config::get("url" , "base"),
		);
		$arr_x = array();;
		foreach($arr as $item => $key) {
			$arr_x[] = $item . "=" . urlencode($key);
		}
		$str = implode("&" , $arr_x);
		$url = $cfg_version["web"] . "/api.php?verifyinfo=" . urlencode($str);
		return $url;
	}
	//登录官网www.klkkdj.com
	static function official_login() {
		$url = self::get_url();
		$arr = fun_base::post( $url , array( "app_act" => "login" ) );
		if( $arr['code'] == 0 && !empty($arr['cont']) ) {
			$arr_return = fun_format::toarray($arr['cont']);
			return $arr_return;
		} else {
			return array();
		}
	}
	//下载安装包
	static function down($name , $act = 'zip') {
		$arr_url = array(
			"app" => "down",
			"app_act" => $act,
			"app_ajax" => 1,
			"downname" => $name,
		);
		$url = self::get_url();
		$arr = fun_base::post($url , $arr_url);
		if( $arr['code'] == 0 && !empty($arr['cont']) ) {
			return $arr['cont'];
		} else {
			return '';
		}
	}
	static function get($app_act , $app = '', $arr = array() ) {
		$arr_url = array(
			"app_ajax" => 1,
			"app_act" => $app_act,
		);
		if(!empty($app)) $arr_url['app'] = $app;
		$arr_url = array_merge($arr_url , $arr);
		$url = self::get_url();

		$cache_key = $url . "&app=" . $app . "&app_act=" . $app_act;
		$arr = cls_cache::get($cache_key , 'klkkdj.api');
		if($arr === null) {
			$arr = fun_base::post($url , $arr_url);
			if( $arr['code'] == 0 && !empty($arr['cont']) ) {
				$arr_return = fun_format::toarray($arr['cont']);
				if(isset($arr_return['code']) && $arr_return['code'] != '0') {
					return array("code"=>500,"msg"=>"获取数据失败");
				}
				cls_cache::set($arr_return , $cache_key , 'klkkdj.api');
				return $arr_return;
			} else {
				cls_cache::set(array() , $cache_key , 'klkkdj.api');
				return array();
			}
		} else {
			return $arr;
		}
	}
	static function kj_verify_copy() {
		//本地测试，不验证
		$url = $_SERVER["HTTP_HOST"];
		if(stristr($url , 'localhost') || stristr($url , '127.0.0.1') || stristr($url , '192.168.') || stristr($url , 'kjcms')) return;
		$host = str_replace("." , "" , $url);
		if(is_numeric($host)) return;

		$valmd5 = cls_config::get("kjcms_key");
		$module = cls_config::get("module" , "version" , "" , "");
		$domain = cls_config::get("domain");
		if( empty($valmd5) ) {
			//试用过期，跳转到官网提示
			fun_base::url_jump("http://www.kjcms.com/api.php?app=copy&app_act=nokey&domain=" . urlencode($domain) . "&module=" . $module);
		}
		$arr_ext = cls_config::get("domainext" , "base" , array());
		//验证key
		$arr = parse_url($domain);
		if(isset($arr['host'])) $domain = $arr['host'];
		$arr = explode("." , $domain);
		$count = count($arr);
		if($count<2) return;
		$domain = $arr[$count-2] . "." . $arr[$count-1];
		$arr_ext = array('com.cn','net.cn','org.cn','gov.cn','com.hk','com.tw');
		if(in_array($domain , $arr_ext) && $count>=3 ) $domain = $arr[$count-3] . '.' . $domain;

		$md5 = md5("kjcms-" . $domain . "-K2013-11-11J-" . $module);
		if( trim($md5) != trim($valmd5)) {
			//跳转到官网提示
			fun_base::url_jump("http://www.kjcms.com/api.php?app=copy&app_act=verify.err&domain=" . urlencode($domain) . "&module=" . $module);
		}
	}
}