<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_shop extends inc_mod_admin {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist() {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'key' => fun_get::get("s_key"),
			'shop_area_id' => (int)fun_get::get("s_shop_area_id"),
			'shop_mode' => fun_get::get("s_shop_mode",-999),
			'state' => (int)fun_get::get("s_state",-999),
			'type' => fun_get::get("s_shop_type"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "shop_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "shop_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(shop_name like '%" . $arr_search_key['key'] . "%' or shop_linkname like '%" . $arr_search_key['key'] . "%' or shop_tel like '%" . $arr_search_key['key'] . "%')";
		if( !empty($arr_search_key['shop_area_id']) ) $arr_where_s[] = cls_obj::db()->concat(",","shop_area_allid",",") . " like '%," . $arr_search_key['shop_area_id'] . ",%'";
		if( $arr_search_key['shop_mode']!=-999 ) $arr_where_s[] = "shop_mode='" . $arr_search_key['shop_mode'] . "'";
		if($arr_search_key['state']!=-999 ) $arr_where_s[] = "shop_state='" . $arr_search_key['state'] . "'";
		if($arr_search_key['type']!="" ) $arr_where_s[] = "shop_type='" . $arr_search_key['type'] . "'";

		//管理权限
		$limit_area = $this->this_limit->get_perms('limit_area');
		if(!empty($limit_area)) {
			$arr = explode("," , $limit_area);
			$arr_x = array();
			foreach($arr as $areaid) {
				$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
			}
			$arr_where[] = '(' . implode(" or " , $arr_x) . ')';
		}

		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		$arr_type = tab_meal_shop::get_perms("type");
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.shop" , $this->app_dir , "meal");
		//取除 user_name 字段
		$arr_cfg_fields["sel"] = substr(str_replace(",user_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);

		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.shop"  , $this->app_dir);
		$sort = str_replace("user_name" , "shop_user_id" , $arr_config_info["sortby"]);
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$arr_state = tab_meal_shop::get_perms("state");
		//取分页信息
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_shop" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_shop " . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["shop_state"])) $obj_rs["shop_state"] = array_search($obj_rs["shop_state"] , $arr_state);
			if(isset($obj_rs["shop_addtime"])) $obj_rs["shop_addtime"] = date("Y-m-d H:i:s" , $obj_rs["shop_addtime"]);
			if(isset($obj_rs["shop_updatetime"])) $obj_rs["shop_updatetime"] = date("Y-m-d H:i:s" , $obj_rs["shop_updatetime"]);
			if(isset($obj_rs['shop_mode'])) {
				if($obj_rs['shop_mode']==1) {
					$obj_rs['shop_mode'] = '文字';
				} else if($obj_rs['shop_mode']==2) {
					$obj_rs['shop_mode'] = '图片';
				} else {
					$obj_rs['shop_mode'] = '默认';
				}
			}
			$arr_return["list"][] = $obj_rs;
			$arr_uid[] = $obj_rs['shop_user_id'];
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['shop_user_id'] , $user_info);
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$get_url_type = fun_get::get("url_type");
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."meal_shop" , "shop_id='".$msg_id."'");
		$obj_rs["extend"] = array( "arrivedelay" => "" , "arrivetime" => "" , "opentime" => "");
		$obj_rs["dispatch"] = array();
		if( empty($obj_rs["shop_id"]) ) {
			$obj_rs["shop_state"] = 1;
		}
		if(!empty($obj_rs["shop_user_id"])) {
			$arr = cls_obj::get("cls_user")->get_user($obj_rs['shop_user_id']);
			$obj_rs["user_name"] = array_search($obj_rs["shop_user_id"] , $arr);
		}
		if(!empty($obj_rs["shop_extend"])) $obj_rs["extend"] = unserialize($obj_rs["shop_extend"]);
		$obj_rs["area"] = fun_kj::get_area();
		$obj_rs['dispatch'] = array();
		if(!empty($msg_id)) {
			$obj_result = cls_obj::db()->select("SELECT a.*,b.area_name FROM " . cls_config::DB_PRE."meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on a.dispatch_area_id=b.area_id where dispatch_shop_id='" . $msg_id . "'");
			while($obj_dispatch = cls_obj::db()->fetch_array($obj_result)) {
				$obj_rs['dispatch'][] = $obj_dispatch;
			}
		}
		$obj_rs['opentime'] = $obj_rs['arrivetime'] = $obj_rs['weekday'] = array();
		if(!empty($obj_rs['extend']['opentime'])) {
			foreach($obj_rs['extend']['arr_opentime'] as $item=>$key) {
				$arr = explode(",",$item);
				if($key == $item) $key = '';
				$obj_rs['opentime'][] = array('hour1'=>substr($arr[0],0,-2) , 'minu1'=>intval(substr($arr[0],-2)) , 'hour2'=>substr($arr[1],0,-2),'minu2'=>intval(substr($arr[1],-2)),'name'=>$key);
			}
		}
		if(!empty($obj_rs['extend']['weekday'])) $obj_rs['weekday'] = explode("," , $obj_rs['extend']['weekday']);
		if(!empty($obj_rs['extend']['arr_arrivetime'])) {
			foreach($obj_rs['extend']['arr_arrivetime'] as $item=>$key) {
				if($key == $item) $key = '';
				$obj_rs['arrivetime'][] = array('hour'=>substr($item,0,-2) , 'minu'=>intval(substr($item,-2)) , 'name'=>$key);
			}
		}
		return $obj_rs;
	}
	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_extend = array(
			"arrivedelay" => (int)fun_get::post("arrivedelay"),
		);
		$opentime_hour1 = fun_get::post("opentime_hour1");
		$opentime_minu1 = fun_get::post("opentime_minu1");
		$opentime_hour2 = fun_get::post("opentime_hour2");
		$opentime_minu2 = fun_get::post("opentime_minu2");
		$opentime_name = fun_get::post("opentime_name");
		$arr = array();
		for($i = 0 ; $i < count($opentime_hour1) ; $i++) {
			$x1 = (intval($opentime_minu1[$i])<10) ? '0'.$opentime_minu1[$i] : $opentime_minu1[$i];
			$x2 = (intval($opentime_minu2[$i])<10) ? '0'.$opentime_minu2[$i] : $opentime_minu2[$i];
			$v = $opentime_hour1[$i] . $x1 . "," . $opentime_hour2[$i] . $x2;
			if(!empty( $opentime_name[$i])) {
				$v .= '=&gt;' . $opentime_name[$i];
			} else {
				$v .= '=&gt;' . $opentime_hour1[$i] . ":" . $x1 . "-" . $opentime_hour2[$i] . ":" . $x2;
			}
			$arr[] = $v;
		}
		$arr_extend['opentime'] = implode(chr(10) , $arr);
		$arr_weekday = fun_get::post("shop_weekday");
		$arr_extend['weekday'] = is_array($arr_weekday) ? implode("," , $arr_weekday) : '';
		$arr_extend["arr_opentime"] = tab_sys_config::get_array($arr_extend['opentime']);
		$arrivetime_hour = fun_get::post("arrivetime_hour");
		$arrivetime_minu = fun_get::post("arrivetime_minu");
		$arrivetime_name = fun_get::post("arrivetime_name");
		$arr = array();
		for($i = 0 ; $i < count($arrivetime_hour) ; $i++) {
			$x1 = (intval($arrivetime_minu[$i])<10) ? '0'.$arrivetime_minu[$i] : $arrivetime_minu[$i];
			$v = $arrivetime_hour[$i] . $x1;
			if(!empty( $arrivetime_name[$i])) {
				$v .= '=&gt;' . $arrivetime_name[$i];
			} else {
				$v .= '=&gt;' . $arrivetime_hour[$i] . ":" . $x1;
			}
			$arr[] = $v;
		}
		$arr_extend['arrivetime'] = implode(chr(10) , $arr);
		$arr_extend["arr_arrivetime"] = tab_sys_config::get_array($arr_extend['arrivetime']);
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"shop_name" => fun_get::post("shop_name"),
			"shop_user_id" => fun_get::post("shop_user_id"),
			"shop_intro"  => fun_get::post("shop_intro"),
			"shop_desc"  => fun_get::post("shop_desc"),
			"shop_linkname"  => fun_get::post("shop_linkname"),
			"shop_linktel"  => fun_get::post("shop_linktel"),
			"shop_tel"  => fun_get::post("shop_tel"),
			"shop_address"  => fun_get::post("shop_address"),
			"shop_area_id"  => fun_get::post("shop_area_id"),
			"shop_area_allid"  => fun_get::post("shop_area_allid"),
			"shop_area"  => fun_get::post("shop_area"),
			"shop_dispatch_price"  => fun_get::post("shop_dispatch_price"),
			"shop_pic"  => fun_get::post("shop_pic"),
			"shop_pic_small"  => fun_get::post("shop_pic_small"),
			"shop_state"  => fun_get::post("shop_state"),
			"shop_oneminleast"  => fun_get::post("shop_oneminleast"),
			"shop_extend" => $arr_extend,
			"shop_ticket"  => (int)fun_get::post("shop_ticket"),
			"shop_rebate"  => fun_get::post("shop_rebate"),
			"shop_checkout_money"  => fun_get::post("shop_checkout_money"),
			"shop_sms"  => fun_get::post("shop_sms"),
			"shop_sms_tel"  => fun_get::post("shop_sms_tel"),
			"shop_printinfo"  => fun_get::post("shop_printinfo"),
			"shop_tj"  => fun_get::post("shop_tj"),
			"shop_addprice"  => fun_get::post("shop_addprice"),
			"shop_type"  => fun_get::post("shop_type"),
			"shop_verifytel" => fun_get::post("shop_verifytel"),
			"shop_email"  => fun_get::post("shop_email"),
			"shop_weixin_id"  => fun_get::post("shop_weixin_id"),
			"shop_print_id"  => fun_get::post("shop_print_id"),
			"shop_print_auto"  => fun_get::post("shop_print_auto"),
			"shop_print_cancel"  => fun_get::post("shop_print_cancel"),
			"shop_print_tongbu"  => fun_get::post("shop_print_tongbu"),
			"shop_position_lat"  => fun_get::post("shop_position_lat"),
			"shop_position_lng"  => fun_get::post("shop_position_lng"),
			"shop_position_precision"  => fun_get::post("shop_position_precision"),
			"shop_mode"  => (int)fun_get::post("shop_mode"),
			"shop_day_limit"  => (int)fun_get::post("shop_day_limit"),
			"shop_print_pages"  => (int)fun_get::post("shop_print_pages"),
			"shop_close_tips"  => fun_get::post("shop_close_tips"),
			"shop_tips" => fun_get::post("shop_tips"),
			"shop_service"  => (int)fun_get::post("shop_service"),
			"shop_reserve_money"  => (float)fun_get::post("shop_reserve_money"),
			"shop_reserve_time"  => (int)fun_get::post("shop_reserve_time"),
			"shop_capitaprice" => fun_get::post("shop_capitaprice"),
			"shop_menutime" => fun_get::post("shop_menutime"),
			"shop_reserve_sms" => fun_get::post("shop_reserve_sms"),
		);
		$arr_ping = cls_pinyin::get($arr_fields["shop_name"] , cls_config::DB_CHARSET);
		$arr_fields["shop_jian"] = $arr_ping["style3"];
		if(empty($arr_fields['shop_print_pages'])) $arr_fields['shop_print_pages'] = 1;
		if(cls_config::get('score_mode','meal')==2) 	$arr_fields["shop_support_score"] = fun_get::post("shop_support_score");
		$arr = tab_meal_shop::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

	/* 删除指定  shop_id 数据
	 */
	function on_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_shop::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	/* 设置状态
	 */
	function on_state() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$state_val = (int)fun_get::get("state_val");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_shop" , array("shop_state" => $state_val) , "shop_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	/* 设置状态
	 */
	function on_mode() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$mode_val = (int)fun_get::post("mode_val");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_shop" , array("shop_mode" => $mode_val) , "shop_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	/* 设置分类*/
	function on_type() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$mode_val = fun_get::post("type_val");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_shop" , array("shop_type" => $mode_val) , "shop_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
}