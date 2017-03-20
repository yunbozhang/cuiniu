<?php
/**
 * 区域 关联表名：sys_area
 * 
 */
class mod_other_share extends inc_mod_admin {
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
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'type' => (int)fun_get::get("s_type"),
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
					$arr_where_s[] = "share_user_id in(" . $ids . ")";
				} else if(is_numeric($arr_search_key['uname']) ){
					$arr_where_s[] = "share_user_id='" . $arr_search_key['uname'] . "'";
				} else {
					$arr_where_s[] = "share_id=0";
				}
			} else {
				$arr_x = cls_obj::get("cls_user")->get_user($arr_search_key['uname'],false);
				$arr_x = array_values($arr_x);
				$ids = implode(",",$arr_x);
				if(!empty($ids)) {
					$arr_where_s[] = "share_user_id in (" . $ids . ")";
				} else {
					$arr_where_s[] = "share_id=0";
				}
			}
		}
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "share_datetime >= '" . $arr_search_key['addtime1'] . "'";
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "share_datetime <= '" . date("Y-m-d H:i:s" , fun_get::endtime($arr_search_key['addtime2'])) . "'";
		if( !empty($arr_search_key['type']) ) $arr_where_s[] = "share_type='" . $arr_search_key['type'] . "'";
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;
		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页 , lng_pagesize : 分页大小
	 */
	function sql_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("other.share" , $this->app_dir , "other");
		$arr_cfg_fields["sel"] = substr(str_replace(",user_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("other.share"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		//相关属性
		$arr_return['share_type'] =  cls_obj::get('cls_com')->share("get_config" , "type" );
		//取分页信息
		$arr_return["list"] = $arr_uid = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."other_share" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."other_share" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs['share_type'])) $obj_rs['share_type'] = array_search($obj_rs['share_type'] , $arr_return['share_type']);
			$obj_rs['user_name'] = '';
			if(!empty($obj_rs['share_user_id'])) $arr_uid[] = $obj_rs['share_user_id'];
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['share_user_id'] , $user_info);
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
}