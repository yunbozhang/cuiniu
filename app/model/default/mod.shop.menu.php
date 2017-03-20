<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_menu extends inc_mod_shop {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist($msg_type = '') {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		$arr_where[] = "menu_shop_id='" . $this->shop_info["id"] . "' and menu_isdel=0";
		//取查询参数
		$arr_search_key = array(
			'type' => (int)fun_get::get("s_type"),
			'group_id' => (int)fun_get::get("s_group_id"),
			'key' => fun_get::get("s_key"),
		);
		if( $arr_search_key['type'] != 0 ) $arr_where_s[] = "menu_type = '" . $arr_search_key['type'] . "'"; 
		if( $arr_search_key['group_id'] != 0 ) $arr_where_s[] = "menu_group_id = '" . $arr_search_key['group_id'] . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "menu_title like '%" . $arr_search_key['key'] . "%'";
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page',1));

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
		$arr_return["state"] = tab_meal_menu::get_perms("state");
		$arr_type = tab_meal_menu::get_perms("type");
		$arr_mode = tab_meal_menu::get_perms("mode");
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.menu" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.menu"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_menu" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_menu a left join ".cls_config::DB_PRE."meal_menu_group b on a.menu_group_id=b.group_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["menu_state"])) {
				$obj_rs["state:style"] = $this->get_state_style($obj_rs["menu_state"]);
				$obj_rs["menu_state"] = array_search($obj_rs["menu_state"],$arr_return["state"]);
				if(!empty($obj_rs["state:style"])) $obj_rs["menu_state"] = "<font ".$obj_rs["state:style"].">" . $obj_rs["menu_state"] . "</font>";
			}
			if(isset($obj_rs["menu_mode"])) $obj_rs["menu_mode"] = array_search($obj_rs["menu_mode"],$arr_mode);
			if(isset($obj_rs["menu_type"])) $obj_rs["menu_type"] = array_search($obj_rs["menu_type"],$arr_type);
			if(isset($obj_rs["menu_addtime"])) $obj_rs["menu_addtime"] = date("Y-m-d H:i:s" , $obj_rs["menu_addtime"]);
			if(isset($obj_rs["menu_updatetime"])) $obj_rs["menu_updatetime"] = date("Y-m-d H:i:s" , $obj_rs["menu_updatetime"]);
			if(isset($obj_rs['menu_tj'])) {
				$obj_rs['menu_tj'] = ($obj_rs['menu_tj'])? "是" : "否";
			}
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],8);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$get_url_type = fun_get::get("url_type");
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."meal_menu" , "menu_id='".$msg_id."' and menu_shop_id='" . $this->shop_info['id'] . "'");
		if( empty($obj_rs["menu_id"]) ) {
			$obj_rs["menu_state"] = 1;
			$obj_rs["menu_group_id"] = 0;
			$obj_rs["menu_type"] = $get_url_type;
			$obj_rs["menu_state"] = 1;
		}
		$obj_rs["menu_shop_id"] = $this->shop_info["id"];
		$obj_rs["shop_name"] = $this->shop_info["name"];
		$obj_rs["weekday"] = array();
		if(!empty($obj_rs["menu_weekday"])) $obj_rs["weekday"] = explode("," , $obj_rs["menu_weekday"]);
		$obj_rs["weekday"] = array();
		$obj_rs["date"] = array();
		if(!empty($obj_rs["menu_weekday"])) $obj_rs["weekday"] = explode("," , $obj_rs["menu_weekday"]);
		if(!empty($obj_rs["menu_date"])) $obj_rs["date"] = explode("," , $obj_rs["menu_date"]);
		$obj_rs["html_group"] = $this->get_group_select("menu_group_id" , $obj_rs["menu_shop_id"] , $obj_rs["menu_group_id"]);
		if(!empty($obj_rs['menu_pics'])) {
			$obj_rs['pics'] = explode("|" , $obj_rs['menu_pics']);
		} else {
			$obj_rs['pics'] = array();
		}
		return $obj_rs;
	}

	/* 获取，菜单分组列表 select组件
	 * name : 组件名称 , default : 默认选择值
	 */
	function get_group_select( $name = 'menu_group_id' , $shop_id = 0 ,$default = '') {
		if(empty($shop_id)) $shop_id = $this->shop_info['id'];
		$arr = tab_meal_menu_group::get_list_layer( 0 , 1 , "group_shop_id='" . $shop_id . "'");
		$arr_select = array();
		$arr_select[] = array("val" => '' , "title" => '' , "layer" => 1);
		foreach($arr["list"] as $item) {
			$arr_select[] = array("val" => $item['group_id'] , "title" => $item['group_name'] , "layer" => $item["layer"]);
		}
		$str = fun_html::select($name , $arr_select , $default);
		return $str;
	}

	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"menu_number" => fun_get::post("menu_number"),
			"menu_type" => fun_get::post("menu_type"),
			"menu_group_id"  => (int)fun_get::post("menu_group_id"),
			"menu_shop_id"  => $this->shop_info["id"],
			"menu_title"   => fun_get::post("menu_title"),
			"menu_price"  => fun_get::post("menu_price"),
			"menu_pic"  => fun_get::post("menu_pic"),
			"menu_pic_small"  => fun_get::post("menu_pic_small"),
			"menu_num"  => fun_get::post("menu_num"),
			"menu_mode"  => (int)fun_get::post("menu_mode"),
			"menu_state"  => (int)fun_get::post("menu_state"),
		);
		if(fun_is::set("menu_intro")) $arr_fields['menu_intro'] = fun_get::post("menu_intro");
		if(fun_is::set("menu_attribute")) $arr_fields['menu_attribute'] = fun_get::post("menu_attribute");
		if(fun_is::set("menu_holiday")) $arr_fields['menu_holiday'] = fun_get::post("menu_holiday");
		if(fun_is::set("menu_cont")) $arr_fields['menu_cont'] = fun_get::post("menu_cont");
		$arr_pic = fun_get::post("pic1_url");
		$pics = '';
		if(!empty($arr_pic) && is_array($arr_pic)) {
			$arr = array();
			foreach($arr_pic as $pic) {
				if(!empty($pic)) $arr[] = $pic;
			}
			$pics = implode("|" , $arr);
		}
		$arr_fields['menu_pics'] = $pics;
		$arr_fields["menu_weekday"] = '';
		$arr_fields["menu_date"] = '';
		if($arr_fields["menu_mode"] == 1) {
			$weekday = fun_get::post("menu_weekday" , array());
			$arr_fields["menu_weekday"] = implode("," , $weekday);
		} else if($arr_fields["menu_mode"] == 3){
			$date = fun_get::post("menu_date" , array());
			$arr_fields["menu_date"] = implode("," , $date);
		}
		$arr = tab_meal_menu::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
			$arr_progress = $this->shop_info["progress"];
			if(!in_array(4 , $arr_progress)) {
				$arr_progress[] = 4;
				cls_obj::db_w()->on_update(cls_config::DB_PRE . "meal_shop",array("shop_progress"=>implode(",", $arr_progress)) , "shop_id=" . $this->shop_info["id"]);
			}

		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

	/* 删除指定  menu_id 数据
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
		$arr = tab_meal_menu::on_delete($str_id);
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
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_state" => $state_val) , "menu_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	/* 设置分组
	 */
	function on_group() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$group_val = (int)fun_get::get("group_val");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_group_id" => $group_val) , "menu_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	/* 设置分组
	 */
	function on_mode() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$group_val = (int)fun_get::get("mode_val");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr_fields = array(
			"menu_mode" => (int)fun_get::post("mode_val")
		);
		if($arr_fields["menu_mode"] == 1) {
			$weekday = fun_get::post("mode_weekday" , array());
			$arr_fields["menu_weekday"] = implode("," , $weekday);
			$arr_fields["menu_holiday"] = (int)fun_get::post("menu_holiday");
		} else if($arr_fields["menu_mode"] == 3){
			$date = fun_get::post("mode_day" , array());
			$arr_fields["menu_date"] = implode("," , $date);
		} else {
			$arr_fields["menu_weekday"] = '';
			$arr_fields["menu_holiday"] = 0;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , $arr_fields , "menu_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	/* 推荐
	 */
	function on_tj() {
		$tj_val = (int)fun_get::get("tj_val");
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_tj" => $tj_val) , "menu_id in(" . $str_id . ") and menu_shop_id='" . $this->shop_info["id"] . "'");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	/* 排序
	 */
	function on_sort() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		foreach($arr_id as $item) {
			$id = (int)$item;
			$val = fun_get::get("sortval_" . $id);
			if(empty($id) || $val==='' ) continue;
			$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_sort" => (int)$val) , "menu_id='" . $id . "' and menu_shop_id='" . $this->shop_info["id"] . "'");
		}
		return $arr_return;
	}

}