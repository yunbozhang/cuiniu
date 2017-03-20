<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_menu extends inc_mod_meal {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist($is_del = 0 , $isexcel = false) {
		if(!defined('cls_klkkdj::KJ_VERISION')) {
			fun_base::url_jump("http://www.kjcms.com/api.php?app=copy&app_act=verify.none");
			exit;
		}
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		$arr_where[] = "menu_isdel='" . $is_del . "' and menu_about_id<=0";
		$url_type = fun_get::get("url_type");
		if(empty($url_type)) {
			$arr_where[] = "menu_type!=2";
		} else {
			$arr_where[] = "menu_type=2";
		}
		if($this->admin_shop["id"] != -999) $arr_where[] = "menu_shop_id='" . $this->admin_shop["id"] . "'";
		//取查询参数
		$arr_search_key = array(
			'group_id' => (int)fun_get::get("s_group_id"),
			'key' => fun_get::get("s_key"),
			'tj' => fun_get::get("s_tj"),
			'state' => (int)fun_get::get("s_state" , -999),
		);
		if( $arr_search_key['group_id'] != 0 ) $arr_where_s[] = "menu_group_id = '" . $arr_search_key['group_id'] . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "menu_title like '%" . $arr_search_key['key'] . "%'";
		if( fun_is::set("s_state") && $arr_search_key['state'] != -999 ) $arr_where_s[] = "menu_state = '" . $arr_search_key['state'] . "'"; 
		if( $arr_search_key['tj'] == '1' ) {
			 $arr_where_s[] = "menu_tj=1";
		} else if( $arr_search_key['tj'] == '-1' ) {
			 $arr_where_s[] = "menu_tj=0";
		}
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page') , $isexcel);

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1 , $isexcel = false) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		$arr_return["state"] = tab_meal_menu::get_perms("state");
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
		$arr_attribute = tab_meal_menu::get_perms("attribute");
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_menu" , $str_where , $lng_page , $lng_pagesize);
		$sql = "SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_menu a left join ".cls_config::DB_PRE."meal_menu_group b on a.menu_group_id=b.group_id" . $str_where . $sort;
		if(!$isexcel) $sql .= $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select($sql);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["menu_state"])) {
				$obj_rs["state:style"] = $this->get_state_style($obj_rs["menu_state"]);
				$obj_rs["menu_state"] = array_search($obj_rs["menu_state"],$arr_return["state"]);
				if(!empty($obj_rs["state:style"]) && !$isexcel) $obj_rs["menu_state"] = "<font ".$obj_rs["state:style"].">" . $obj_rs["menu_state"] . "</font>";
			}
			if(isset($obj_rs["menu_mode"])) $obj_rs["menu_mode"] = array_search($obj_rs["menu_mode"],$arr_mode);
			if(isset($obj_rs["menu_addtime"])) $obj_rs["menu_addtime"] = date("Y-m-d H:i:s" , $obj_rs["menu_addtime"]);
			if(isset($obj_rs["menu_updatetime"])) $obj_rs["menu_updatetime"] = date("Y-m-d H:i:s" , $obj_rs["menu_updatetime"]);
			if(isset($obj_rs["menu_attribute"])) $obj_rs["menu_attribute"] = array_search($obj_rs["menu_attribute"],$arr_attribute);
			if(isset($obj_rs['menu_tj'])) {
				$obj_rs['menu_tj'] = ($obj_rs['menu_tj'])? "是" : "否";
			}
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."meal_menu" , "menu_id='".$msg_id."'");
		if( empty($obj_rs["menu_id"]) ) {
			$obj_rs["menu_state"] = 1;
			$obj_rs["menu_group_id"] = 0;
			if($this->admin_shop["id"]>=0) {
				$obj_rs["menu_shop_id"] = $this->admin_shop["id"];
				$obj_rs["shop_name"] = $this->admin_shop["name"];
			} else {
				$obj_rs["menu_shop_id"] = 0;
				$obj_rs["shop_name"] = "默认";
			}
			$obj_rs["menu_state"] = 1;
		} else if($obj_rs["menu_shop_id"]>0) {
			$obj_rs2 = cls_obj::db()->get_one("select shop_id,shop_name from " . cls_config::DB_PRE . "meal_shop where shop_id='" .$obj_rs["menu_shop_id"] . "'");
			if(!empty($obj_rs)) {
				$obj_rs["menu_shop_id"] = $obj_rs2["shop_id"];
				$obj_rs["shop_name"] = $obj_rs2["shop_name"];
			}
		} else {
			$obj_rs["menu_shop_id"] = 0;
			$obj_rs["shop_name"] = "默认";
		}
		if(!empty($obj_rs['menu_pics'])) {
			$obj_rs['pics'] = explode("|" , $obj_rs['menu_pics']);
		} else {
			$obj_rs['pics'] = array();
		}
		$obj_rs["weekday"] = array();
		$obj_rs["date"] = array();
		if(!empty($obj_rs["menu_weekday"])) $obj_rs["weekday"] = explode("," , $obj_rs["menu_weekday"]);
		if(!empty($obj_rs["menu_date"])) $obj_rs["date"] = explode("," , $obj_rs["menu_date"]);
		$obj_rs["html_group"] = $this->get_group_select("menu_group_id" , $obj_rs["menu_group_id"] , $obj_rs["menu_shop_id"]);
		//规格
		$obj_rs['guige'] = array();
		if($obj_rs['menu_about_id'] == -1) {
			$obj_result = cls_obj::db()->select("select menu_id,menu_title,menu_about_name,menu_price,menu_num from " . cls_config::DB_PRE . "meal_menu where menu_about_id='" . $obj_rs['menu_id'] . "' and menu_isdel=0");
			while($obj_rsx = cls_obj::db()->fetch_array($obj_result)) {
				$obj_rs['guige'][] = $obj_rsx;
			}
		}
		return $obj_rs;
	}

	/* 获取，菜单分组列表 select组件
	 * name : 组件名称 , default : 默认选择值
	 */
	function get_group_select($name = 'menu_group_id' , $default = '' , $shop_id = 0) {
		if(empty($shop_id)) $shop_id = $this->admin_shop["id"];
		$arr = tab_meal_menu_group::get_list_layer( 0 , 1 ,"group_shop_id='". $shop_id . "'");
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
			"menu_state" => fun_get::post("menu_state"),
			"menu_group_id"  => (int)fun_get::post("menu_group_id"),
			"menu_shop_id"  => (int)fun_get::post("menu_shop_id"),
			"menu_title"   => fun_get::post("menu_title"),
			"menu_intro"    => fun_get::post("menu_intro"),
			"menu_price"  => fun_get::post("menu_price"),
			"menu_pic"  => fun_get::post("menu_pic"),
			"menu_pic_small"  => fun_get::post("menu_pic_small"),
			"menu_num"  => fun_get::post("menu_num"),
			"menu_attribute"  => fun_get::post("menu_attribute"),
			"menu_mode"  => (int)fun_get::post("menu_mode"),
			"menu_holiday"  => (int)fun_get::post("menu_holiday"),
			"menu_unit" => fun_get::post("menu_unit"),
			"menu_tj" => (int)fun_get::post("menu_tj"),
			"menu_cont" => fun_get::post("menu_cont"),
			"menu_type" => (int)fun_get::post("menu_type"),
		);
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
		//规格
		$menu_about_id = (int)fun_get::get("menu_about_id");
		$guige_name = fun_get::get("guige_name",array());
		$guige_num = fun_get::get("guige_num",array());
		$guige_price = fun_get::get("guige_price",array());
		$guige_id = fun_get::get("guige_id",array());
		if(!empty($menu_about_id)) {
			if(empty($guige_price) || empty($guige_price[0])) return array("code" => 500 , "msg" => "商品价格不能为空");
			$arr_fields['menu_price'] = $guige_price[0];
			$arr_fields['menu_num'] = $guige_num[0];
			$arr_fields['menu_about_id'] = -1;
		}
		$arr = tab_meal_menu::on_save($arr_fields);
		if($arr['code']==0) {
			if(empty($menu_about_id)) {
				cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_menu set menu_isdel=1 where menu_about_id='" . $arr['id'] . "'");
			} else {
				$arr_guigeid = array();
				foreach($guige_id as $itemx) {
					$itemx = (int)$itemx;
					if(empty($itemx)) continue;
					$arr_guigeid[] = $itemx;
				}
				if(empty($arr_guigeid)) {
					cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_menu set menu_isdel=1 where menu_about_id='" . $arr['id'] . "'");
				} else {
					$ids = implode("," , $arr_guigeid);
					cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_menu set menu_isdel=1 where menu_about_id='" . $arr['id'] . "' and not menu_id in(" . $ids . ")");
				}
				$arr_fields2 = $arr_fields;
				$arr_fields2['menu_about_id'] = $arr['id'];
				for($i = 0 ; $i < count($guige_name); $i++) {
					$arr_fields2['id'] = $guige_id[$i];
					$arr_fields2['menu_title'] = $arr_fields['menu_title']."(" . $guige_name[$i] . ")";
					$arr_fields2['menu_about_name'] = $guige_name[$i];
					$arr_fields2['menu_price'] = $guige_price[$i];
					$arr_fields2['menu_num'] = $guige_num[$i];
					tab_meal_menu::on_save($arr_fields2);
				}
			}

			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	/* 删除或还原指定  id 数据
	 * isdel 决定是删除还是还原,1为删除，0为回收
	 */
	function on_del($isdel = 1) {
		$arr_return = array("code"=>0,"msg" => cls_language::get("delete_ok") );
		if($isdel == 0 ) $arr_return["msg"] = cls_language::get("act_ok") ;
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			($isdel == 1)? $arr_return['msg'] = cls_language::get("delete_no_id") : $arr_return['msg'] = cls_language::get("reback_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_menu::on_del($str_id,$isdel);
		if($arr['code'] != 0) {
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
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_tj" => $tj_val) , "menu_id in(" . $str_id . ")");
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
	/* 排序
	 */
	function on_sort() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$id = (int)fun_get::get("id");
		$val = (int)fun_get::get("val");
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_sort" => (int)$val) , "menu_id='" . $id . "'");
		return $arr_return;
	}
	/* 设置分组
	 */
	function on_mode() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
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
	function on_type() {
		$arr_return = array("code" => 0 , "msg" => cls_language::get("set_ok"));
		$arr_id = fun_get::get("selid");
		$type_val = (int)fun_get::get("type_val");
		$str_id = fun_format::arr_id($arr_id);
		if(empty($str_id)) {
			$arr_return["code"] = 22;
			$arr_return["msg"] = cls_language::get("no_id");
			return $arr_return;
		}
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu" , array("menu_type" => $type_val) , "menu_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
	function on_excel() {
		$arr_return = $this->get_pagelist(0,true);
		$arr_excel=array();
		$arr = array();
		foreach($arr_return['tabtit'] as $item) {
			$arr[] = $item["name"];
		}
		$arr_excel[] = $arr;

		foreach($arr_return['list'] as $item) {
			$arr = array() ;
			foreach($arr_return['tabtit'] as $field) {
				$val = $item[$field["key"]];
				if($field["key"] == 'order_ticket' && empty($val)) $val = '';
				if($field["key"] == 'order_act_ids') $val = str_replace('<br>' , '；' , $val);
				$arr[] = $val;
			}
			$arr_excel[] = $arr;
		}
		$get_excel_name = fun_get::get("excel_name" , date("Y-m-d"));
		cls_excel::save_excel($get_excel_name.".xls",$arr_excel);
	}

}