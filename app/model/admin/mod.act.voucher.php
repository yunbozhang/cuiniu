<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_voucher extends inc_mod_admin {
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
			'state' => (int)fun_get::get("url_state" , -999),
			's_uid' => (int)fun_get::get("s_uid"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "record_datetime >= '" . $arr_search_key['addtime1'] . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "record_datetime <= '" . date("Y-m-d H:i:s" , fun_get::endtime($arr_search_key['addtime2'])) . "'"; 
		if( $arr_search_key['state'] != -999 ) $arr_where_s[] = "voucher_state = '" . $arr_search_key['state'] . "'"; 
		if( !empty( $arr_search_key['s_uid'] ) ) $arr_where_s[] = "record_user_id= '" . $arr_search_key['s_uid'] . "'"; 
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
		$arr_cfg_fields = tab_sys_user_config::get_fields("act.voucher" , $this->app_dir , "act");
		$arr_cfg_fields["sel"] = substr(str_replace(",user_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("act.voucher"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_type = tab_act_voucher::get_perms("type");
		$arr_state = tab_act_voucher::get_perms("state");
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_voucher" , $str_where , $lng_page , $lng_pagesize,array("sum(voucher_val) as 'total_val'"));
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."act_voucher" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['user_name'] = '';
			if(!empty($obj_rs['voucher_user_id'])) $arr_uid[] = $obj_rs['voucher_user_id'];
			if(isset($obj_rs['voucher_addtime'])) $obj_rs['voucher_addtime'] = date("Y-m-d H:i:s" , $obj_rs['voucher_addtime']);
			if(isset($obj_rs['voucher_usetime']) && !empty($obj_rs['voucher_usetime']) ) $obj_rs['voucher_usetime'] = date("Y-m-d H:i:s" , $obj_rs['voucher_usetime']);
			if(isset($obj_rs['voucher_state'])) $obj_rs['voucher_state'] = array_search($obj_rs['voucher_state'] , $arr_state);
			if(isset($obj_rs['voucher_type'])) $obj_rs['voucher_type'] = array_search($obj_rs['voucher_type'] , $arr_type);
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$arr = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['voucher_user_id'] , $arr);
			}
		}

		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
}