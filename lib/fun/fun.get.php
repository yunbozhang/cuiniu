<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class fun_get{
	static function get($msg_name , $default = null , $is_all = true) {
		static $arr_get = array();
		if(!isset($arr_get[$msg_name])) {
			$str_name = $default;
			if(isset($_GET[$msg_name])) {
				$str_name = self::filter($_GET[$msg_name]);
			}else if($is_all && isset($_REQUEST[$msg_name])) {
				$str_name = self::filter($_REQUEST[$msg_name]);
			}
			$arr_get[$msg_name] = $str_name;
		} else if(!empty($default) && empty($arr_get[$msg_name]) ) {
			$arr_get[$msg_name] = $default;
		}
		return $arr_get[$msg_name];
	}
	static function post($msg_name , $default = '') {
		static $arr_post=array();
		if(!isset($arr_post[$msg_name])) {
			$str_name = $default;
			if(isset($_POST[$msg_name])) $str_name = self::filter($_POST[$msg_name]);
			$arr_post[$msg_name] = $str_name;
		}
		return $arr_post[$msg_name];
	}
	static function filter($str_x,$is_reback=false) {
		if(is_array($str_x)) {
			for($i = 0 ; $i < count($str_x);$i++) {
				if(!isset($str_x[$i])) continue;
				$str_x[$i] = self::filter_base($str_x[$i],$is_reback);
			}
		}else{
			$str_x = self::filter_base($str_x , $is_reback);
		}
		return $str_x;
	}
	static function filter_base($str_x , $is_reback=false) {
		/*
        if (function_exists('htmlspecialchars')) {
 			if($is_reback) {
			  $str_x = htmlspecialchars_decode($str_x);
			} else {
			  $str_x = htmlspecialchars($str_x);
			}
			return $str_x;
        }*/
        $search = array("&amp;","&","/amp;/amp;/amp;",'"',"'","<",">",chr(13).chr(10));
        $replace = array("/amp;/amp;/amp;","&amp;","&amp;","&quot;","&#039;","&lt;","&gt;",chr(13));
		if($is_reback) {
			$str_x = str_replace($replace , $search , $str_x);
			$str_x = str_replace('\\\'',"'",$str_x);//替换经过mysql转义的格式
			$str_x = str_replace('\\"','"',$str_x);
		}else{
			$str_x = str_replace($search , $replace , $str_x);
		}
		return $str_x;
	}
	static function real_path($msgpath,$msgroot = "") {
		$str_path = fun_format::path($msgpath);
		(empty($msgroot)) ? $msgroot = fun_format::path(KJ_DIR_ROOT):$msgroot == fun_format::path($msgroot);
		if(strlen($str_path) > strlen($msgroot)) {
			$str_x = substr($str_path , 0 , strlen($msgroot));
			if($str_x != $msgroot) {
				if(substr($str_path,0,1)!="/") $str_path = "/" . $str_path;
				$str_path = $msgroot . $str_path;
			}
		} else {
			if(substr($str_path , 0 , 1) != "/") $str_path = "/" . $str_path;
			$str_path = $msgroot.$str_path;
		}
		if(substr($str_path , -1 , 1) == "/") $str_path = substr($str_path , 0 , -1);
		return $str_path;
	}
	//取当前url链接
	//arr_x : 数组 或 json 传递新参数
	static function url($arr_x = array() , $clear_param = false , $url = '') {
		if(empty($url)) {
			$str_url=$_SERVER["SCRIPT_NAME"];
			//清除参数
			$str_param=$_SERVER["QUERY_STRING"];
		} else {
			$arr = explode("?" , $url);
			$str_url = $arr[0];
			$str_param = '';
			if(count($arr)>0) {
				unset($arr[0]);
				$str_param = implode("?" , $arr);
			}
		}
		if($clear_param) return $str_url;
		$arr_param=explode("&",$str_param);
		if(!is_array($arr_x)) {
			$arr_x = array();
		}
		$arr_keys=array_keys($arr_x);
		$lng_count=count($arr_param);
		for($i=0;$i<$lng_count;$i++){
			if(empty($arr_param[$i])){
				unset($arr_param[$i]);
				continue;
			}
			$arr_y=explode("=",$arr_param[$i]);
			if(in_array($arr_y[0],$arr_keys)){
				if($arr_x[$arr_y[0]]!=""){
					$arr_param[$i]=$arr_y[0]."=".fun_format::url_encode($arr_x[$arr_y[0]]);
				}else{
					unset($arr_param[$i]);
				}
				unset($arr_x[$arr_y[0]]);
			}
		}
		foreach($arr_x as $item=>$key){
		   if($key!="") $arr_param[]=$item."=".fun_format::url_encode($key);
		}
		$str_url.="?".implode("&",$arr_param);
		return $str_url;
	}
	static function html_url($msg_url , $outlink = 0 , $arr_p = array()) {
		if(!isset($msg_url) || $msg_url == "") return "";
		$url = strtolower($msg_url);
		if(strstr($url , "http://") || strstr($url , "https://")){
		}else{
			$arr_x=explode("/" , $url);
			if(preg_match("/[a-z0-9_-]+[.]{1}[a-z]{2,3}[\/]/is" , $arr_x[0])) {
				$msg_url = "http://" . $msg_url;
			}else{
				if(substr($msg_url,0,1) != "/") $msg_url = "/" . $msg_url;
				$url = ($outlink == 0) ? "dirpath" : "url";
				$msg_url = cls_config::get($url) . $msg_url;
			}
		}
		return empty($arr_p) ? $msg_url : self::url($arr_p , false , $msg_url);
	}
	static function memory_size() {
		$arr_return = array("val"=>0 , "unit" => "");
		$lng_val = memory_get_usage(true);
		$unit = array('B' , 'K' , 'M' , 'G' , 'T' , 'P'); 
		$arr_return["val"] = @round($lng_val/pow(1024 , ($i = floor(log($lng_val , 1024)))) , 2);
		$arr_return["unit"] = $unit[$i];
		return $arr_return; 
	}
	static function ip($isall = false) {   
		$arr_ip = array();
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']!='unknown') $arr_ip[] = self::filter($_SERVER['HTTP_CLIENT_IP']);
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') $arr_ip[] = self::filter($_SERVER['HTTP_X_FORWARDED_FOR']);
		$arr_ip[] =  self::filter($_SERVER['REMOTE_ADDR']);
		if(!$isall) {
			$arr = explode(",",$arr_ip[0]);
			$ip = $arr[0];
		} else {
			$ip = implode(',',$arr_ip);
		}
        return $ip;   
    }
	static function safecode($msg_val,$msg_type="encode"){
		$str_key       = cls_config::MD5_KEY;
		$str_en_key    = base64_encode($str_key);
		$str_md5_key   = md5($str_key);
		$str_md5_key_1 = substr($str_md5_key , 0 , 1);
		$str_md5_key_2 = substr($str_md5_key , -1 , 1);
		$lng_key_1     = ord($str_md5_key_1);
		$lng_key_2     = ord($str_md5_key_2);
		$lng_x_key1    = substr($lng_key_1,-1,1);
		if($lng_key_1 > 9) {
			$lng_x_key2 = intval(substr($lng_key_1 , -2 , 1)) + $lng_x_key1;
		}else{
			$lng_x_key2 = $lng_x_key1 * 2;
		}
		$str_left      = base64_encode(substr($str_md5_key , $lng_x_key1 , $lng_x_key2));
		$lng_2_key1    = substr($lng_key_2 , -1 , 1);
		if($lng_2_key1 > 9){
			$lng_2_key2 = intval(substr($lng_key_2 , -2 , 1)) + $lng_2_key1;
		}else{
			$lng_2_key2 = $lng_2_key1 * 2;
		}
		$str_right = base64_encode(substr($str_md5_key , -$lng_2_key2));
		if($msg_type == "encode"){
			$str_en_id   = base64_encode($msg_val);
			$str_en_code = $str_left . $str_en_id . $str_right;
			$str_return  = str_replace("=" , "" , $str_en_code);
		}else{
			$str_left    = str_replace("=" , "" , $str_left);
			$str_right   = str_replace("=" , "" , $str_right);
			$str_llen    = strlen($str_left);
			$str_rlen    = strlen($str_right);
			$str_len     = strlen($msg_val);
			if($str_len < ($str_llen + $str_rlen)){
				$str_return = "";
			}else{
				$str_return = base64_decode(substr($msg_val , $str_llen , -$str_rlen));
			}
		}
		return $str_return;
	}
	/** 当 time = 今天时，即没有指定时间，只有日期 时，则为当天晚上 23:59:59 ，否则不变
	 *  返回 time 数字型值
	 */
	static function endtime($time) {
		if(!is_numeric($time)) {
			$time = strtotime($time);
		}
		$date = date("Y-m-d",$time);
		if( strtotime($date) == $time ) {
			return strtotime($date . " 23:59:59");
		} else {
			return $time;
		}
	}
	/** 当 date = 要显示的日期
	 *  返回 如果当前日期有时间则显示时间格式 否则显示 日期格式
	 */
	static function showdate($date) {
		if(!is_numeric($date)) {
			$date = strtotime($date);
		}
		$date1 = date("Y-m-d" , $date);
		$date2 = date("Y-m-d H:i:s" , $date);
		if( strtotime($date1) == strtotime($date2) ) {
			return $date1;
		} else {
			return $date2;
		}
	}
	//获取性别
	static function sex($val) {
		$arr_sex = array("a_1" => cls_language::get("male") , "a_2" => cls_language::get("female"));
		if(isset($arr_sex["a_".$val])) {
			return $arr_sex["a_".$val];
		} else {
			return "";
		}
	}
	//返回扩展名类型
	static function file_type($ext) {
		if(fun_is::pic("" , $ext)) return "pic";
		if(fun_is::flash("" , $ext)) return "flash";
		if(fun_is::media("" , $ext)) return "media";
		if(fun_is::doc("" , $ext)) return "doc";
		if(fun_is::rar("" , $ext)) return "rar";
		return "other";
	}
	static function editor($textareaid = 'content', $msgtype = "" , $msg_w = 0,$msg_h = 0)
	{
		if($msgtype=="admin"){
			$toolbar="full";
			$width=750;
			$height=400;
		}else if($msgtype=="user"){
			$toolbar="standard";
			$width=750;
			$height=400;
		}else{
			$toolbar="basic";
			$width=750;
			$height=400;
		}
		if($msg_w>0) $width=$msg_w;
		if($msg_h>0) $height=$msg_h;

		$cont = '<script type="text/javascript" charset="utf-8" src="' . cls_config::get("dirpath") . '/plus/bdeditor/ueditor.config.js"></script>';
		$cont .= '<script type="text/javascript" charset="utf-8" src="' . cls_config::get("dirpath") . '/plus/bdeditor/ueditor.all.min.js"></script>';
		$cont .= '<script type="text/javascript" charset="utf-8" src="' . cls_config::get("dirpath") . '/plus/bdeditor/lang/zh-cn/zh-cn.js"></script>';
		$cont .= '<script id="editor" type="text/plain" style="width:' . $width . 'px;height:' . $height . 'px;"></script>';
		$cont .= '<script>var objEditor = UE.getEditor("editor");objEditor.ready(function() {if(kj.obj("#'.$textareaid.'")){var cont=kj.obj("#'.$textareaid.'").value;objEditor.setContent(cont);}});</script>';
		return $cont;
		return "\n<script type=\"text/javascript\" src=\"" . cls_config::get("dirpath") . "/plus/fckeditor/fckeditor.js\"></script>\n<script language=\"JavaScript\" type=\"text/JavaScript\">var SiteUrl = \"" . cls_config::get("dirpath") . "\"; var Module = \"special\"; var sBasePath = \"" . cls_config::get("dirpath") . "\" + '/plus/fckeditor/'; var oFCKeditor = new FCKeditor( '".$textareaid."' ) ; oFCKeditor.BasePath = sBasePath ; oFCKeditor.Height = '".$height."px'; oFCKeditor.Width	= '".$width."px' ; oFCKeditor.ToolbarSet= '".$toolbar."' ;oFCKeditor.ReplaceTextarea() ;</script>";
	}
	static function weekday($date = 0) {
		$arr_week = array("周天" , "周一" , "周二" , "周三" , "周四" , "周五" , "周六");
		if(empty($date)) $date = TIME;
		if(!is_numeric($date)) $date = strtotime($date);
		$val = date("w" , $date);
		return $arr_week[$val];
	}
	static function xml($arr_xml){
		$str_docxml='<?xml version="1.0" encoding="utf-8"?>'.chr(10);
		$str_docxml .= fun_format::xml($arr_xml);
		return $str_docxml;
	}
	static function sql_escape($sql) {
		$sql = str_replace("'" , "\'" , $sql);
		return $sql;
	}
	//取某月第一天
	static function first_day($date = '') {
		if(empty($date)) {
			$date = TIME;
		} else if(!is_numeric($date)) {
			$date = strtotime($date);
		}
		return date("Y-m-01",$date);
	}
	//取某月最后一天
	static function end_day($date = '') {
		if(empty($date)) {
			$date = TIME;
		} else if(!is_numeric($date)) {
			$date = strtotime($date);
		}
		return date("d",strtotime("-1 day",strtotime(date("Y-m-01",strtotime("next Month" , $date)))));
	}
	//获取访问来源客户端信息
	static function agent() {
		$str = strtolower($_SERVER["HTTP_USER_AGENT"]);
		$arr = array("ucweb"=>"uc","iphone"=>"iphone","ipad"=>"ipad","android"=>"android","Windows Phone"=>"winphone");
		$agent = "";
		foreach($arr as $item=>$key) {
			if(stristr($str , $item)) {
				$agent = $key;
				break;
			}
		}
		return $agent;
	}
	static function ie_version() {
		$stragent = strtolower($_SERVER["HTTP_USER_AGENT"]);
		preg_match_all("/msie\s[0-9]+\.[0-9]+/",$stragent,$out, PREG_PATTERN_ORDER);
		if(empty($out) || empty($out[0])) return 9;
		$str = substr($out[0][0],-3);

		preg_match_all("/trident\/[0-9]/",$stragent,$out2, PREG_PATTERN_ORDER);
		if(!empty($out2) && !empty($out2[0])) {
			$str2 = substr($out2[0][0],-1);
			$x = (int)$str2;
			if($x>4) return 9;
		}		
		return (float)$str;
	}
	static function basename($val) {
		//if(PHP_OS=='WINNT') return basename($val);
		$arr = explode("/" , $val);
		return end($arr);
	}

	//账号规则
	static function rule_uname($key = '') {
		$rule = array(
			'default' => array(
				'php' => '/^[a-z0-9_\x{4e00}-\x{9fa5}\.]{2,16}$/uis',
				'js'  => '/^[a-z0-9\u4E00-\u9FA5_-]{2,16}$/i',
				'tips' => '账号长度在2-16位，不能包函特殊字符',
			),
			'rule1' => array(
				'php' => '/^[a-z0-9_}\.]{4,16}$/i',
				'js'  => '/^[a-z0-9_]{4,16}$/i',
				'tips' => '账号只能为英文、数字、下划线，长度在4-16位的字符',
			),
			'email' => array(
				'php' => 'email',
				'js'  => 'email',
				'tips' => '账号格式必须为邮箱',
			),
			'mobile' => array(
				'php' => 'mobile',
				'js'  => 'mobile',
				'tips' => '账号格式必须为手机号码',
			)
		);
		$type = cls_config::get('rule_uname' , 'user');
		if(!isset($rule[$type])) $type = 'default';
		return (!empty($key)) ? $rule[$type][$key] : $rule[$type];
	}
	//密码规则
	static function rule_pwd($key = '') {
		$rule_pwd = cls_config::get('rule_pwd' , 'user');
		$arr = explode("-" , $rule_pwd);
		if(empty($arr[0])) $arr[0] = 4;
		if(!isset($arr[1]) || empty($arr[1]) || $arr[1]<$arr[0]) $arr[1] = 16;
		$rule = array(
			"php" => '/^.{' . $arr[0] . ',' . $arr[1] . '}$/is',
			"js" => '/^.{' . $arr[0] . ',' . $arr[1] . '}$/i',
			"tips" => '密码长度必须在' . $arr[0] . '-' . $arr[1] . '之间'
		);
		return (!empty($key)) ? $rule[$key] : $rule;
	}
	static function domain($url) {
		if(empty($url)) return $url;
		$url = self::html_url($url);
		$arr = parse_url($url);
		$url = $arr['host'];
		$arr = explode("." , $url);
		$i = count($arr);
		$url = $arr[$i-2] . "." . $arr[$i-1];
		return $url;
	}
	static function timer($time) {
		$timelen = TIME - $time;
		if($timelen>86400*4) return date("m-d H:i" , $time);
		if($timelen>=86400*3) return '3天前';
		if($timelen>=86400*2) return '2天前';
		if($timelen>=86400) return '1天前';
		$hour = (int)($timelen/3600);
		if($hour>0) return $hour . "小时前";
		$fen = (int)($timelen/60);
		if($fen>0) return $fen . "分前";
		return $timelen . "秒前";
	}

}