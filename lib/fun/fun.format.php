<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class fun_format {
	static function path($msg_path , $real = true) {
		if($msg_path=="") return "";
		$str_path=str_replace("\\","/",$msg_path);
		while(strstr($str_path,"//"))
		{
			$str_path=str_replace("//","/",$str_path);
		}
		if(substr($str_path, -1,1) == '/') $str_path = substr($str_path,0,-1);
		if($real == false && strlen($str_path) > strlen(KJ_DIR_ROOT) && substr($str_path,0,strlen(KJ_DIR_ROOT)) == KJ_DIR_ROOT) $str_path = substr($str_path,strlen(KJ_DIR_ROOT));
		return $str_path;
	}
	static function tohtml($msg_cont) {
		$msg_cont = nl2br($msg_cont);
		return $msg_cont;
	}
	static function utf8_gbk($value) { 
		if(is_null($value)||empty($value)) return "";
		return mb_convert_encoding($value,"gbk","UTF-8"); 
	} 
	static function gbk_utf8($value) { 
		if(is_null($value)||empty($value)) return "";
		return iconv("gbk", "UTF-8", $value); 
	}
	static function new_stripslashes($string) {
		if(!is_array($string)) return stripslashes($string);
		foreach($string as $key => $val) $string[$key] = self::new_stripslashes($val);
		return $string;
	}

	static function json($arr) {
		$arr_item = array();
		if( fun_is::assoc($arr) ) {
			$str_cont = "{";
			$str_end = "}";
			foreach( $arr as $item => $key ) {
				if( is_array($key) ) {
					$arr_item[] = '"' . $item . '":' . self::json($key);
				}else{
					$type = gettype($key);
					if( in_array($type , array('integer' ,'double' , 'float'))) {
						$arr_item[] = '"' . $item . '":' . $key . '';
					}else{
						$key = str_replace(chr(10) , '' , $key);
						$key = str_replace(chr(13) , '' , $key);
						$key = str_replace('"' , '' , $key);
						$arr_item[] = '"' . $item . '":"' . $key . '"';
					}
				}
			}
		} else {
			$str_cont = "[";
			$str_end = "]";
			foreach( $arr as $item ) {
				if( is_array($item) ) {
					$arr_item[] = self::json($item);
				}else{
					$item = str_replace(chr(10) , '' , $item);
					$item = str_replace(chr(13) , '' , $item);
					$arr_item[] = '"' . $item . '"';
				}
			}
		}
		$str_cont .= implode(",",$arr_item) . $str_end;
		return $str_cont;
	}
	static function toarray($cont) {
		if(gettype($cont) == "string") $cont = json_decode($cont);
		$arr = (array)$cont;
		foreach($arr as $item=>$key) {
			if(gettype($key) == 'object' ) {
				$key = self::toarray($key);
			} else if(gettype($key) == 'array'){
				$key = self::objtoarray($key);
			}
			$arr[$item] = $key;
		}
		return $arr;
	}
	static function objtoarray($arr) {
		$arr_new = array();
		foreach($arr as $item) {
			if(gettype($item) == 'object' ) {
				$item = self::toarray($item);
			} else if(gettype($item) == 'array'){
				$item = self::objtoarray($item);
			}
			$arr_new[] = $item;
		}
		return $arr_new;
	}
	static function xml_array($xml) {
		$xml = simplexml_load_string($xml);
		$arr = json_decode(json_encode($xml),TRUE);
		return $arr;
	}
	static function arr_id($arr_id) {
		$str_id="";
		if(is_array($arr_id)) {
			$arr_x=array();
			foreach($arr_id as $item=>$key) {
				$arr_x[]=intval($key);
			}
			$str_id=implode(",",$arr_x);
		} else if ($arr_id != "") {
			$arr_id = explode(",",$arr_id);
			$lng_count = count($arr_id);
			if( $lng_count > 0 ) {
				for( $i = 0 ; $i < $lng_count ; $i++ ) {
					$arr_id[$i] = intval( $arr_id[$i] );
				}
				$str_id = implode(",",$arr_id);
			}
		}
		return $str_id;
	}
	static function pwd($val , $key = '') {
		return md5($val.$key);
	}
	static function url_query($query) {
		$new_query = $query;
		if(is_array($query)) {
			if(fun_is::assoc($query)) {
				$arr_x = array();
				foreach($query as $item => $key) {
					$arr_x[]=$item . "=" . urlencode($key);
				}
				$query = $arr_x;
			}
			$new_query = implode("&" , $query);
		}
		return $new_query;
	}
	static function url_encode($key) {
		if(is_array($key)) {
			if(fun_is::assoc($key)) {
				foreach($key as $item => $key_next) {
					$arr_return[$item] = self::url_encode($key_next);
				}
			} else {
				foreach($key as $item) {
					$arr_return[] = self::url_encode($item);
				}
			}
		} else {
			if(preg_match('/^[a-z0-9%]+$/is',$key)) {
				return $key;
			} else {
				return urlencode($key);
			}
		}
		return $arr_return;
	}
	static function size($size) { 
		$unit = array('B','K','M','G','T','P'); 
		return @round( $size / pow( 1024 , ( $i = floor( log ( $size , 1024 ) ) ) ) , 2 ) . ' ' . $unit[$i]; 
	} 
	static function len($str , $len , $append = '') {
		for( $i = 0 ; $i < $len ; $i++ ) {
			$temp_str = substr($str , 0 , 1);
			if(ord($temp_str) > 127) {
				$i++;
				if( $i < $len ) {
					$new_str[] = substr($str , 0 , 3);
					$str = substr($str , 3);
				}
			} else {
				$new_str[] = substr($str , 0 , 1);
				$str = substr($str,1);
			}
		}
		return join($new_str).$append;
	}

	static function js($txt) {
		$txt = str_replace(chr(10) , '' , str_replace("'" , "\'" , $txt) );
		$txt = str_replace(chr(13) , "" , $txt) ;
		$txt = "document.write('" . $txt . "');";
		return $txt;
	}
	static function xml($arr_xml,$msg_len=0){
		$str_xml = $str_space = "";
		for($i = 0 ; $i < $msg_len ; $i++) {
			$str_space .= "	";
		}
		foreach($arr_xml as $item => $key) {
			$str_xml = $str_space . "<" . $item;
			$str_property = "";
			if( isset($key["property"]) && is_array($key["property"]) ) {
				$arr_x = array();
				foreach($key["property"] as $item_n => $key_n) {
					$arr_x[] = $item_n . '="' . $key_n . '"';
				}
				$str_property = implode( " " , $arr_x );
				if( !empty($str_property) ) $str_property = " " . $str_property;
			}
			$str_xml .= $str_property;
			$str_body = "";
			if( isset($key["body"]) && is_array($key["body"]) ) {
				foreach($key["body"] as $item_body) {
					$str_body .= chr(10) . self::xml( $item_body , $msg_len + 1 );
				}
			}
			if( !empty($str_body) ) {
				$str_xml .= ">" . $str_body.chr(10);
				$str_xml .= $str_space . "</" . $item . ">";
			}else{
				$str_xml .= " />";
			}
		}
		return $str_xml;
	}
	static function ip($ip) {   
		$ips = explode('.',$ip);
		if (count($ips)>=4) {   
			$ip = $ips[0].".".$ips[1].".*.*";   
		}
		return $ip;   
	}   
	static function email($email) {
		$arr = explode('@',$email);
		if(count($arr) < 2) return $email;
		$val = $arr[0];
		unset($arr[0]);
		$email = implode("@" , $arr);
		if(strlen($val) > 5) {
			$val = substr($val,0,-3) . "***";
		} else {
			$val = substr($val,0,3) . "**";
		}
		return $val . '@' . $email;   
	}
	static function domain($domain) {
		$domain = strtolower($domain);
		if(substr($domain,0,4) == 'http') return $domain;
		return "http://" . $domain;
	}
	static function wap_cont($cont) {
		$cont=preg_replace("/(<img\s[^>]*)max-width:[\s0-9]+px;/is", "\\1", $cont);
		$cont=preg_replace("/(<img\s[^>]*)width:[\s0-9]+px;/is", "\\1", $cont);
		$cont=preg_replace("/(<div\s[^>]*)width:[\s0-9]+px;/is", "\\1", $cont);
		$cont=preg_replace("/(<p\s[^>]*)width:[\s0-9]+px;/is", "\\1", $cont);
		$cont=preg_replace("/(<table\s[^>]*)width:[\s0-9]+px;/is", "\\1", $cont);
		$cont=preg_replace("/(<table\s[^>]*)width=['|\"][\s0-9]+[px]*['|\"]/is", "\\1", $cont);
		$cont=preg_replace("/(<img\s[^>]*)width=['|\"][\s0-9]+[px]*['|\"]/is", "\\1", $cont);
		$cont=preg_replace("/(<div\s[^>]*)width=['|\"][\s0-9]+[px]*['|\"]/is", "\\1", $cont);
		$cont=preg_replace("/(<p\s[^>]*)width=['|\"][\s0-9]+[px]*['|\"]/is", "\\1", $cont);
		return $cont;
	}
	static function wap_tel($cont) {
		$cont=preg_replace("/([1][0-9]{10})/is", "<a href='tel:\\1'>\\1</a>", $cont);
		$cont=preg_replace("/([1][0-9\s]{12})/is", "<a href='tel:\\1'>\\1</a>", $cont);
		$cont=preg_replace("/([1][0-9-]{12})/is", "<a href='tel:\\1'>\\1</a>", $cont);
		$cont=preg_replace("/([0-9]{4}[\s|-]{1,2}[0-9]{6,8})/is", "<a href='tel:\\1'>\\1</a>", $cont);
		$cont=preg_replace("/(400[\s|-]{0,2}[0-9\s-]{5,10})/is", "<a href='tel:\\1'>\\1</a>", $cont);
		$cont=preg_replace("/(tel:)([0-9]{0,11})[\s|-]{1,3}([0-9]{0,11})[\s|-]{0,3}([0-9]{0,11})/is", "\\1\\2\\3\\4", $cont);
		return $cont;
	}
	static function emoji($str) {
		$arr_emoji = cls_config::get("" , "emoji" ,"","");
		$x=json_encode($str);
		$arr_key = array_keys($arr_emoji);
		$arr_val = array_values($arr_emoji);
		$x = str_replace($arr_key , $arr_val , $x);
		return json_decode($x);
	}
	static function toemoji($str , $ishtml = 0) {
		if($ishtml || !fun_is::wap()) {
			$str=preg_replace("/\[em\:([0-9a-z]+)\]/is", '<i class="emoji emoji\\1"></i>', $str);
		} else {
			preg_match_all("/\[em\:([0-9a-z]+)\]/is" , $str , $arr);
			if(isset($arr[1])) {
				$arr[1] = array_unique($arr[1]);
				foreach($arr[1] as $item) {
					$x = json_decode('"\\u' . $item . '"');
					$str = str_replace('[em:' . $item . ']' , $x , $str);
				}
			}
		}
		return $str;
	}
	static function uni2utf8( $c ) {

		if(!is_numeric($c)) {
			if(strlen($c) == 10) {
				$x1 = substr($c , 0,5);
				$x2 = substr($c , 5);
				$val = self::uni2utf8($x1) . self::uni2utf8($x2);
				return $val;
			}
		}
		$c = hexdec($c);
		if($c<0x10000) {
			return '\u' . dechex($c);
		} else {
			$c = $c - 0x10000;
			$c1 = ($c &  0xFFC00) >> 10 ;
			$c2 = $c & 0x3ff;
			$c1x =  0xD800 | $c1;
			$c2x =  0xDC00 | $c2;
			return '\u' . dechex($c1x) . '\u' . dechex($c2x);
		}
	}
	static function get($url) {
		$url = str_replace("ksplitj" , "&" , $url);
		$url = str_replace("&amp;" , "&" , $url);
		$arr = explode("&" , $url);
		foreach($arr as $item) {
			$arrx = explode("=" , $item);
			if(!isset($_GET[$arrx[0]]) || stristr($_GET[$arrx[0]] , 'ksplitj')) {
				$x = $arrx[0];
				unset($arrx[0]);
				$_GET[$x] = implode("=" , $arrx);
			}
		}
	}
	static function number($val , $num = 2) {
		if(intval($val) == (float)$val) return intval($val);
		for($i = 0 ; $i < $num ; $i++) {
			$val1 = str_replace("," , "" , number_format($val , $i));
			$val2 = str_replace("," , "" , number_format($val , $i+1));
			if( (float)$val1 == (float)$val2 ) return (float)$val1;
		}
		$val = str_replace("," , "" , number_format($val , $num));
		return (float)$val;
	}
	static function date($date) {
		$date = date("Y-m-d H:i:s",strtotime($date));
		if(substr($date , -2) == '00') $date = substr($date , 0 , -3);
		if(substr($date , -2) == '00') $date = substr($date , 0 , -3);
		if(substr($date , -2) == '00') $date = substr($date , 0 , -3);
		return $date;
	}
}