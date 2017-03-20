<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_table_reserve extends inc_mod_meal {
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
			'time1' => fun_get::get("s_addtime1"),
			'time2' => fun_get::get("s_addtime2"),
			'state' => (int)fun_get::get("s_state" , -999),
			'key' => fun_get::get("s_key"),
		);
		if($this->admin_shop["id"] != -999) $arr_where[] = "reserve_shop_id='" . $this->admin_shop["id"] . "'";
		if( fun_is::isdate( $arr_search_key['time1'] ) ) $arr_where_s[] = "reserve_datetime >= '" . $arr_search_key['time1'] . "'"; 
		if( fun_is::isdate( $arr_search_key['time2'] ) ) $arr_where_s[] = "reserve_datetime <= '" . date("Y-m-d H:i:s",fun_get::endtime($arr_search_key['time2'])) . "'"; 
		if( $arr_search_key['state'] != -999 ) $arr_where_s[] = "reserve_state = '" . $arr_search_key['state'] . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(reserve_name like '%" . $arr_search_key['key'] . "%' or reserve_tel like '%" . $arr_search_key['key'] . "%')";
		//合并查询数组
		if($this->admin_shop["id"] != -999) $arr_where[] = "reserve_shop_id='" . $this->admin_shop["id"] . "'";
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
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.table.reserve" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.table.reserve"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_state = tab_meal_table_reserve::get_perms("state");
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_table_reserve" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_table_reserve a left join ".cls_config::DB_PRE."meal_shop b on a.reserve_shop_id=b.shop_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["reserve_datetime"])) $obj_rs["reserve_datetime"] = date("Y-m-d H:i" , strtotime($obj_rs["reserve_datetime"]));
			if(isset($obj_rs["reserve_state"])) {
				$obj_rs["state:style"] = $this->get_state_style($obj_rs["reserve_state"]);
				$obj_rs["reserve_state"] = array_search($obj_rs["reserve_state"],$arr_state);
				if(!empty($obj_rs["state:style"])) $obj_rs["reserve_state"] = "<font ".$obj_rs["state:style"].">" . $obj_rs["reserve_state"] . "</font>";
			}
			$arr_return["list"][] = $obj_rs;
		}

		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 删除指定  act_id 数据
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
		$arr = tab_meal_table_reserve::on_del($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

}