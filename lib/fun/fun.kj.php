<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class fun_kj{
	/* 取指定id广告信息
	 * id 广告id
	 * type : 0 表示直接返回广告代码，1 表示反回数组
	 */
	static function get_ads($position , $type = 0 , $ads_shop_id = 0) {
		$obj_db = cls_obj::db();
		if(!empty($ads_shop_id)) {
			$position .= '-shop' . $ads_shop_id;
		}
		
		$area_id = (int)cls_session::get_cookie("area_id");
		$arr_area = array();
		if(!empty($area_id)) {
			$obj_area = $obj_db->get_one("select area_pids from " . cls_config::DB_PRE . "sys_area where area_id='" . $area_id . "'");
			if(!empty($obj_area)) {
				$arr_area = explode("," , $obj_area['area_pids']);
			}
		}
		$where = " where ads_position='" . $position . "' and ads_state>0 and ads_starttime<'" . TIME . "' and ads_endtime>'" . TIME . "'";
		if($ads_shop_id>0) $where .= " and ads_shop_id='" . (int)$ads_shop_id . "'";
		$obj_result = $obj_db->select("select ads_html,ads_cont,ads_area_id from " . cls_config::DB_PRE . "other_ads a left join " . cls_config::DB_PRE . "sys_area b on a.ads_area_id=b.area_id" . $where . " order by ads_area_id,area_depth desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!empty($obj_rs['ads_area_id'])) {
				if(!in_array($obj_rs['ads_area_id'] , $arr_area)) {
					continue;
				}
			}
			//看看是否指定区域
			$html = fun_get::filter($obj_rs['ads_html'],true);
			if($type == 1) {
				$arr = array();
				$obj_rs['ads_cont'] = unserialize($obj_rs['ads_cont']);
				$arr = tab_other_ads::get_html2cont($obj_rs['ads_cont']);
				$arr['html'] = $html;
				if(isset($arr['w'])) {
					$arr['w'] = is_numeric($arr['w']) ? $arr['w'] . 'px' : $arr['w'];
				}
				if(isset($arr['h'])) {
					$arr['h'] = is_numeric($arr['h']) ? $arr['h'] . 'px' : $arr['h'];
				}
				return $arr;
			} else {
				return $html;
			}
		}

		return ($type == 1)? array("html"=>'') : '';
	}
	//获取地区html列表
	static function get_area($pid = 0 , $depth = -1) {
		if($depth < 0 ) {
			$depth = (int)cls_config::get("area_depth", "meal");
		}
		$arr_return = array("list" => "" , "default" => array() ,"depth" => 0 , "area" => "");
		$obj_db = cls_obj::db();
		$arr_area = $arr_default = $arr_list = array();
		$arr_where = array();
		$str_where = '';
		if( $depth>0 ) $arr_where[] = "area_depth<=" . $depth;
		if( !empty($pid) ) {
			if(!is_array($pid)) $pid = array($pid);
			$arr_where_2 = array();
			foreach($pid as $pidval) {
				$arr_where_2[] = cls_db::concat("," , "area_pids" , ",") . " like '%," . $pidval . ",%'";
			}
			$arr_where[] = "(" . implode(" or "  , $arr_where_2) . ")";
		}
		$arr_pid = array();
		if(!empty($arr_where)) $str_where = " where " . implode(" and " , $arr_where);
		$obj_result = $obj_db->select("select area_id,area_name,area_pid,area_pids,area_depth,area_val from " . cls_config::DB_PRE . "sys_area" . $str_where . " order by area_depth,area_sort,area_name");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs["area_val"])) $obj_rs["area_val"] = $obj_rs["area_name"];
			if(empty($obj_rs["area_name"])) $obj_rs["area_name"] = $obj_rs["area_val"];
			if($obj_rs["area_name"]==$obj_rs["area_val"]) unset($obj_rs['area_val']);
			$arr_list["id_" . $obj_rs["area_pid"]][] = $obj_rs["area_id"];
			$arr_area["id_" . $obj_rs["area_id"]] = $obj_rs;
			//if($obj_rs["area_pid"] == 0) $arr_return["default"][] = $obj_rs;
			if($obj_rs['area_depth'] > $depth) $depth = $obj_rs['area_depth'];
			$arrx = explode(",",$obj_rs['area_pids']);
			$count = count($arrx)-1;
			if( $count>1 ) unset($arrx[$count]);
			$str_x = implode("," , $arrx);
			if(!in_array($str_x , $arr_pid) ) $arr_pid[] = $str_x; 
		}
		if(!empty($arr_pid)) {
			$str_x = implode("," , $arr_pid);
			$arr_pid = explode("," , $str_x);
			$arr_pid = array_unique($arr_pid);
			$str_x = implode("," , $arr_pid);
			$str_where = " where area_id in(" . $str_x . ")";
			$obj_result = $obj_db->select("select area_id,area_name,area_pid,area_pids,area_depth,area_val from " . cls_config::DB_PRE . "sys_area" . $str_where . " order by area_depth,area_sort,area_name");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(empty($obj_rs["area_val"])) $obj_rs["area_val"] = $obj_rs["area_name"];
				if(empty($obj_rs["area_name"])) $obj_rs["area_name"] = $obj_rs["area_val"];
				if($obj_rs["area_name"]==$obj_rs["area_val"]) unset($obj_rs['area_val']);
				if($obj_rs["area_pid"] == 0) $arr_return["default"][] = $obj_rs;
				if(isset($arr_area["id_" . $obj_rs["area_id"]])) continue;
				$arr_list["id_" . $obj_rs["area_pid"]][] = $obj_rs["area_id"];
				$arr_area["id_" . $obj_rs["area_id"]] = $obj_rs;
			}
		}
		if(count($arr_return["default"])>0) {
			$arr_return["depth"] = $depth - $arr_return["default"][0]["area_depth"] + 1;
		}
		$arr_return["area"] = fun_format::json($arr_area);
		$arr_return["list"] = fun_format::json($arr_list);
		return $arr_return;
	}
	/* 取缓存信息
	 * type : 缓存字段分类
     * format 表示输入格式：取值范围:空，json
	 */
	static function get_cache_words($type , $val , $format = '' ,$top = 0) {
		$arr_return = array();
		$obj_db = cls_obj::db();
		$arr_where = array("words_type like '" . $type . "%'");
		if(!empty($val)) {
			$arr_where[] = "(words_val like '" . $val . "%' or words_pin like '" . $val . "' or words_jian like '" . $val . "%')";
		}
		$limit = '';
		if(!empty($limit)) $limit = 'limit 0,' . $top;
		$where = implode(" and " , $arr_where);
		$obj_result = $obj_db->select("select words_val from " . cls_config::DB_PRE . "sys_cache_words where " . $where . " order by words_num desc" . $limit);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_return[] = $obj_rs["words_val"];
		}
		if($format == 'json') $arr_return = fun_format::json($arr_return);
		return $arr_return;
	}
	//取当前站点信息
	static function get_site($site_id = null) {
		$cfgdomain = cls_config::get('domain' , 'weixin' , '' , '');
		$domain = fun_format::domain($_SERVER["HTTP_HOST"]);
		$arr_return = array(
			"uname" => cls_config::get('uname' , 'weixin' , '' , '') ,
			"appid" => cls_config::get('appid' , 'weixin' , '' , '') ,
			"appsecret" => cls_config::get('appsecret' , 'weixin' , '' , '') ,
			"mch_id" => cls_config::get('mch_id' , 'weixin' , '' , '') ,
			"mch_key" => cls_config::get('mch_key' , 'weixin' , '' , '') ,
			"customer_open" => cls_config::get("customer_open" , "weixin" , '' , ''),
			"token" => cls_config::get('token' , 'weixin' , '' , '') ,
			"mediatype" => cls_config::get('mediatype' , 'weixin' , '' , '') ,
			"site_id" => 0,
			"shop_id" => 0,
			"msgmode" => cls_config::get("msgmode" , "weixin" , '' , ''),
			"certify" => cls_config::get("certify" , "weixin" , '' , ''),
			"site_name" => '默认',
			"customview" => 0,
			"pic" => '',
			"pic_small" => '',
		);
		$where = "";
		if($site_id !== null) {
			$where .= " and site_id='" . $site_id . "'";
		} else if($cfgdomain != $domain) {
			$where .= " and site_domain='" . $domain . "'";
		}
		if(!empty($where) ) {
			$obj_site = cls_obj::db()->get_one("select a.*,b.shop_pic,b.shop_pic_small from " . cls_config::DB_PRE . "weixin_site a left join " . cls_config::DB_PRE . "meal_shop b on a.site_shop_id=b.shop_id where site_state>0" . $where);
			if(!empty($obj_site)) {
				if(empty($obj_site['shop_pic'])) $obj_site['shop_pic'] = $obj_site['shop_pic_small'];
				if(empty($obj_site['shop_pic_small'])) $obj_site['shop_pic_small'] = $obj_site['shop_pic'];
				$arr_return = array(
					"uname" => $obj_site['site_wx_uname'] ,
					"appid" => $obj_site['site_wx_appid'] ,
					"appsecret" => $obj_site['site_wx_appsecret'] ,
					"mch_id" => $obj_site['site_wx_mch_id'] ,
					"mch_key" => $obj_site['site_wx_mch_key'] ,
					"customer_open" => $obj_site["site_wx_customer_open"],
					"token" => $obj_site['site_wx_token'] ,
					"mediatype" => cls_config::get('mediatype' , 'weixin' , '' , '') ,
					"site_id" => $obj_site['site_id'] ,
					"msgmode" => $obj_site['site_wx_msgmode'] ,
					"certify" => $obj_site['site_wx_certify'] ,
					"shop_id" => $obj_site['site_shop_id'],
					"site_name" => $obj_site['site_name'],
					"customview" => $obj_site['site_customview'],
					"pic" => fun_get::html_url($obj_site['shop_pic']),
					"pic_small" => fun_get::html_url($obj_site['shop_pic_small']),
				);
			}
		}
		return $arr_return;
	}
	//格式化价格
	static function get_price($price , $tag = 'i' , $coinsigntag = 'i') {
		$arr = explode("." , $price);
		$val = $arr[0];
		if(isset($arr[1]) && (int)$arr[1]>0) {
			if(substr($arr[1],1,1)=='0') $arr[1] = substr($arr[1],0,1);
			$val .= empty($tag) ? "." . $arr[1] : "<" . $tag . ">." . $arr[1] . "</" . $tag . ">";
		}
		$coinsign = cls_config::get("coinsign","sys","￥");
		if(!empty($coinsigntag)) $coinsign = "<" . $coinsigntag . ">" . $coinsign . "</" . $coinsigntag . ">";
		$val = $coinsign . $val;
		return $val;
	}
	//取当前登录用户所有收货信息
	static function get_address($shop_id = 0) {
		$obj_db = cls_obj::db();
		$arr_info = array() ;
		$arr_area_id = array();
		$lng_uid = cls_obj::get("cls_user")->uid;
		if(empty($lng_uid)) $lng_uid = -1;
		if(empty($shop_id)) {
			$sql = "select * from ".cls_config::DB_PRE."sys_user_address where address_user_id='" . $lng_uid . "' order by address_order_time desc";
		} else {
			$sql = "select * from ".cls_config::DB_PRE."sys_user_address where address_user_id='" . $lng_uid . "' and address_area_id in (select area_id from " . cls_config::DB_PRE . "meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on " . $obj_db->concat("," , 'b.area_pids' , ',') . " like " . $obj_db->concat("%," , "a.dispatch_area_id" , ",%") . " where a.dispatch_shop_id='" . $shop_id . "' order by address_order_time desc)";
		}
		$obj_result = $obj_db->query($sql);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_info[] = $obj_rs;
		}
		return $arr_info;
	}
	static function get_pagebtns( $arr_info , $listnum = 10 ) {
		$arr_return = array("pre" => 0, "next" => 0,"list" => array() , "start" => 1 , "end" => $arr_info['pages']);
		if($arr_info['total'] < 1) return $arr_return;
		$arr_return['pre'] = max(0,$arr_info['page']-1);
		$arr_return['next'] = $arr_info['page']+1;
		if($arr_return['next'] > $arr_info['pages']) $arr_return['next'] = 0;

		if($arr_info["pages"] > $listnum) {
			$ii = intval($listnum/2);
			if($arr_info['page'] > $ii){
				$lng_pre=$arr_info['page'] - $ii;
				$lng_next=$arr_info['page'] + $ii;
			}else{
				$lng_pre=1;
				$lng_next = $listnum;
			}
		}else{
			$lng_pre=1;
			$lng_next=$arr_info['pages'];
		}
		if($lng_pre < 1) $lng_pre = 1;
		if( $lng_next >= $arr_info['pages'] ) $lng_next = $arr_info['pages'];
		$arr_return['start'] = $lng_pre;
		$arr_return['end'] = $lng_next;

		for($i=$lng_pre;$i<=$lng_next;$i++){
			$arr_return['list'][] = $i;
		}
		return $arr_return;
	}
	static function get_folder_bykey($key) {
		$obj_db = cls_obj::db();
		$arr_return = array();
		$arr_list = array();
		$where = " where folder_key='$key'";
		$obj_result = $obj_db->select("select folder_id,folder_name from " . cls_config::DB_PRE . "article_folder" . $where . "");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[] = $obj_rs;
		}
		$arr_return = $arr_list;
		return $arr_return;
	}

}