<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 *	检测函数类
 */
class fun_is {
	// request 是否有值
	static function set($msg_name) {
		if(isset($_REQUEST[$msg_name])) {
			return true;
		}else{
			return false;
		}
	}
	// 组件是否安装
	static function com($msg_name) {
		return cls_com::is($msg_name);
	}

	// 按配置文件的正则来校验用户名
	static function uname($msg_val) {
		$rule = fun_get::rule_uname("php");
		if(empty($rule)) return true;
		if($rule == 'email') {
			return (self::email($msg_val)) ? true : false;
		}
		if($rule == 'mobile') {
			return (self::mobile($msg_val)) ? true : false;
		}
		if( !preg_match($rule,$msg_val) ) return false;
		return true;
	}

	// 按配置文件的正则来校验用户名
	static function pwd($msg_val) {
		$rule = fun_get::rule_pwd("php");
		if(empty($rule)) return true;
		if( !preg_match($rule,$msg_val) ) return false;
		return true;
	}

	static function number($msg_val , $msg_len1 = 1 , $msg_len2 = "") {
		if(!preg_match("/^[0-9]{".$msg_len1.",".$msg_len2."}$/is",$msg_val)) return false;
		return true;
	}

	static function isdate($msgval) {
		list($year, $month, $day) = sscanf($msgval, '%d-%d-%d');
		$strx=checkdate($month, $day, $year);
		return $strx;
	}

	static function chinaness($msg_val,$msg_len1=1,$msg_len2=""){
		if(strtolower(DB_CHARSET) == "gbk") {
			$str_utf8_u = "";
			$str_chinacode = chr(0xa1)."-".chr(0xff);
		}else{
			$str_utf8_u = "u";
			$str_chinacode = "\x{4e00}-\x{9fa5}";
		}
		if(!preg_match("/^[".$str_chinacode."]{".$msg_len1.",".$msg_len2."}$/".$str_utf8_u."is",$msg_val)) return false;
		return true;
	}

	static function tel($msg_val) {
		if(!preg_match("/^([0-9]{3,4}[-|\s]{0,1}){0,1}[0-9]{7,8}$/is",$msg_val) && !self::mobile($msg_val)) return false;
		return true;
	}

	static function mobile($msg_val) {
		if(!preg_match("/^[1][0-9]{10}$/is",$msg_val)) return false;
		return true;
	}

	static function email($msg_val) {
		$msg_val = strtolower($msg_val);
		if(!preg_match("/^[a-zA-Z0-9]+([.a-zA-Z0-9_-])*@([a-zA-A0-9_-])+(.[a-zA-Z0-9_-]+)+[a-zA-Z0-9_-]$/",$msg_val)) {
			return false;
		}
		return true;
	}

	static function domain($msg_url) {
		if(preg_match('/^([a-z-0-9_-]*\.){0,1}[a-z0-9_-]+\.[a-z]{2,3}$/is',$msg_url)) {
			return true;
		}else{
			return false;
		}
	}

	static function pic($msgval , $msgext = "") {
		if( $msgval == "" && $msgext == "" ) return false;
		$str_ext = $msgext;
		$arr = explode("." , $msgval);
		if($str_ext == "") $str_ext = end($arr);
		$str_ext = strtolower($str_ext);
		if(in_array($str_ext , array("jpg","jpeg","gif","png","bmp","ico"))) {
			return true;
		}else{
			return false;
		}
	}

	static function flash($msgval , $msgext="") {
		if( $msgval == "" && $msgext == "" ) return false;
		$str_ext = $msgext;
		if( $str_ext == "" ) $str_ext = end(explode($msgval,"."));
		$str_ext = strtolower($str_ext);
		if( in_array($str_ext , array("swf")) ) {
			return true;
		}else{
			return false;
		}
	}

	static function media($msgval , $msgext="") {
		if($msgval == "" && $msgext == "") return false;
		$str_ext=$msgext;
		if($str_ext == "") $str_ext = end(explode($msgval,"."));
		$str_ext = strtolower($str_ext);
		if( in_array($str_ext,array("mp3","flv","rm","wmv")) ) {
			return true;
		}else{
			return false;
		}
	}
	static function rar($msgval , $msgext="") {
		if($msgval == "" && $msgext == "") return false;
		$str_ext=$msgext;
		if($str_ext == "") $str_ext = end(explode($msgval,"."));
		$str_ext = strtolower($str_ext);
		if( in_array($str_ext,array("rar","zip","7z","cab")) ) {
			return true;
		}else{
			return false;
		}
	}
	static function doc($msgval , $msgext="") {
		if($msgval == "" && $msgext == "") return false;
		$str_ext=$msgext;
		if($str_ext == "") $str_ext = end(explode($msgval,"."));
		$str_ext = strtolower($str_ext);
		if( in_array($str_ext,array("doc","xls","xlsx","docx","txt")) ) {
			return true;
		}else{
			return false;
		}
	}
	static function assoc($arr) { 
		if(!is_array($arr)) return false;
		return array_keys($arr) !== range(0, count($arr) - 1); 
	} 
	static function serialized( $data ) { 
		// if it isn't a string, it isn't serialized 
		if(empty($data)) return false;
		if ( !is_string( $data ) ) 
			return false; 
		$data = trim( $data ); 
		if ( 'N;' == $data ) 
			return true; 
		if ( !preg_match( '/^([adObis]):/', $data, $badions ) ) 
			return false; 
		switch ( $badions[1] ) { 
			case 'a' : 
			case 'O' : 
			case 's' : 
				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) ) 
					return true; 
				break; 
			case 'b' : 
			case 'i' : 
			case 'd' : 
				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) ) 
					return true; 
				break; 
		} 
		return false; 
	}
	static function match($msg_val,$arr_filter){
		if(!is_array($arr_filter)){
			if($arr_filter=="") return false;
			$str_val=$arr_filter;
		}else{
			$str_val="(".implode("|",$arr_filter).")+";
		}
		if(preg_match("/".$str_val."/is",$msg_val)){
			return true;
		}else{
			return false;
		}
	}
	static function wap() {
		$agent = fun_get::agent();
		if(!in_array($agent,array('','ipad')) || cls_config::get("basename") == 'wap.php' || cls_app::$perms['viewdir'] == KJ_WAP_DIR || cls_obj::get("cls_session")->get("weixin")) {
			return true;
		} else {
			return false;
		}
	}
	static function idcard($idcard){
		// 只能是18位
		if(strlen($idcard)!=18) {
			return false;
		}
		// 取出本体码
		$idcard_base = substr($idcard, 0, 17);
		// 取出校验码
		$verify_code = substr($idcard, 17, 1);
		// 加权因子
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		// 校验码对应值
		$verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
		// 根据前17位计算校验码
		$total = 0;
		for($i=0; $i<17; $i++){
			$total += substr($idcard_base, $i, 1)*$factor[$i];
		}
		// 取模
		$mod = $total % 11;
		// 比较校验码
		if($verify_code == $verify_code_list[$mod]){
			return true;
		}else{
			return false;
		}
	}
	static function weixin() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) return false;
		return true;
	}

}