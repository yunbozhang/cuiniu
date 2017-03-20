<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_sys_user_address extends inc_mod_admin {
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
			'uname' => fun_get::get("s_uname"),
			'name' => fun_get::get("s_name"),
			'tel' => fun_get::get("s_tel"),
			'address' => fun_get::get("s_address"),
		);
		if( !empty($arr_search_key['uname']) ) {
			if(cls_config::USER_CENTER=='user.klkkdj') {
				$arr_ids = array();
				$obj_result = cls_obj::db()->select("select user_id from " .cls_config::DB_PRE . "user where user_name like '%" . $arr_search_key['uname'] . "%'");
				while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
					$arr_ids[] = $obj_rs['user_id'];
				}
				$ids = implode("," , $arr_ids);
				if(!empty($ids)) {
					$arr_where_s[] = "address_user_id in(" . $ids . ")";
				} else if(is_numeric($arr_search_key['uname']) ){
					$arr_where_s[] = "address_user_id='" . $arr_search_key['uname'] . "'";
				} else {
					$arr_where_s[] = "address_id=0";
				}
			} else {
				$arr_x = cls_obj::get("cls_user")->get_user($arr_search_key['uname'],false);
				$arr_x = array_values($arr_x);
				$ids = implode(",",$arr_x);
				if(!empty($ids)) {
					$arr_where_s[] = "address_user_id in (" . $ids . ")";
				} else {
					$arr_where_s[] = "address_id=0";
				}
			}
		}
		if( !empty($arr_search_key['name']) ) $arr_where_s[] = "address_name like '%" . $arr_search_key['name'] . "%'"; 
		if( !empty($arr_search_key['tel']) ) $arr_where_s[] = "(address_tel like '%" . $arr_search_key['tel'] . "%')";
		if( !empty($arr_search_key['address']) ) $arr_where_s[] = "(address_area like '%" . $arr_search_key['louhao1'] . "%' or address_address like '%" . $arr_search_key['louhao1'] . "%')";
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
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("sys.user.address" , $this->app_dir , "sys");
		$arr_cfg_fields["sel"] = substr(str_replace(",user_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("sys.user.address"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		//取分页信息
		$arr_return["list"] = $arr_uid = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."sys_user_address" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."sys_user_address" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_uid[] = $obj_rs['address_user_id'];
			$obj_rs['user_name'] = '';
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['address_user_id'] , $user_info);
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
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."sys_user_address" , "address_id='".$msg_id."'");
		$obj_rs['user_name'] = '';
		if(!empty($obj_rs['address_user_id'])) {
			$arr_user = cls_obj::get("cls_user")->get_user($obj_rs['address_user_id']);
			$obj_rs['user_name'] = array_search($obj_rs['address_user_id'] , $arr_user);
		}
		$obj_rs["area"] = fun_kj::get_area(0,0);
		return $obj_rs;
	}

	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"address_address" => fun_get::post("address_address"),
			"address_name" => fun_get::post("address_name"),
			"address_sex" => fun_get::post("address_sex"),
			"address_tel"  => fun_get::post("address_tel"),
			"address_area_id"  => fun_get::post("address_area_id"),
			"address_area_allid"  => fun_get::post("address_area_allid"),
			"address_area"  => fun_get::post("address_area"),
			"address_email"  => fun_get::post("address_email"),
		);
		$arr = tab_sys_user_address::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
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
		$arr = tab_sys_user_address::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
}