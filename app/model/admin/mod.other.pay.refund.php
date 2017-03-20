<?php
/**
 * 区域 关联表名：sys_area
 * 
 */
class mod_other_pay_refund extends inc_mod_admin {
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
			'number' => fun_get::get("s_number"),
			'val1' => (int)fun_get::get("s_val1"),
			'val2' => (int)fun_get::get("s_val2"),
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'type' => (int)fun_get::get("s_type"),
			'state' => (int)fun_get::get("s_state",-999),
			'method' => fun_get::get("s_method"),
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
					$arr_where_s[] = "refund_uid in(" . $ids . ")";
				} else if(is_numeric($arr_search_key['uname']) ){
					$arr_where_s[] = "refund_uid='" . $arr_search_key['uname'] . "'";
				} else {
					$arr_where_s[] = "pay_id=0";
				}
			} else {
				$arr_x = cls_obj::get("cls_user")->get_user($arr_search_key['uname'],false);
				$arr_x = array_values($arr_x);
				$ids = implode(",",$arr_x);
				if(!empty($ids)) {
					$arr_where_s[] = "refund_uid in (" . $ids . ")";
				} else {
					$arr_where_s[] = "pay_id=0";
				}
			}
		}
		if( !empty($arr_search_key['number']) ) $arr_where_s[] = "pay_number like '%" . $arr_search_key['number'] . "%'";
		if( !empty($arr_search_key['val1']) ) $arr_where_s[] = "refund_total>='" . $arr_search_key['val1'] . "'";
		if( !empty($arr_search_key['val2']) ) $arr_where_s[] = "refund_total<='" . $arr_search_key['val2'] . "'";
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "refund_addtime >= '" . $arr_search_key['addtime1'] . "'";
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "refund_addtime <= '" . date("Y-m-d H:i:s" , fun_get::endtime($arr_search_key['addtime2'])) . "'";
		if( !empty($arr_search_key['type']) ) $arr_where_s[] = "pay_type='" . $arr_search_key['type'] . "'";
		if( $arr_search_key['state']!=-999 ) $arr_where_s[] = "refund_state='" . $arr_search_key['state'] . "'";
		if( !empty($arr_search_key['method']) ) $arr_where_s[] = "pay_method='" . $arr_search_key['method'] . "'";
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
		$arr_cfg_fields = tab_sys_user_config::get_fields("other.pay.refund" , $this->app_dir , "other");
		$arr_cfg_fields["sel"] = substr(str_replace(",uname," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",admin_uname," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("other.pay.refund"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		//相关属性
		$arr_return['pay_type'] =  cls_obj::get('cls_com')->pay("get_config" , "type" );
		$arr_return['refund_state'] =  tab_other_pay_refund::get_perms("state");
		$arr_pay_method = cls_config::get("" , "pay" , "" , "");
		//取分页信息
		$arr_return["list"] = $arr_uid = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."other_pay_refund a left join " . cls_config::DB_PRE . "other_pay b on a.refund_pay_id=b.pay_id" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."other_pay_refund a left join " . cls_config::DB_PRE . "other_pay b on a.refund_pay_id=b.pay_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs['pay_type'])) $obj_rs['pay_type'] = array_search($obj_rs['pay_type'] , $arr_return['pay_type']);
			$obj_rs['state'] = $obj_rs['refund_state'];
			$obj_rs['refund_state'] = array_search($obj_rs['refund_state'] , $arr_return['refund_state']);
			if(isset($obj_rs['pay_method']) && isset($arr_pay_method[$obj_rs['pay_method']])) $obj_rs['pay_method'] = $arr_pay_method[$obj_rs['pay_method']]["fields"]['title'];
			$obj_rs['uname'] = $obj_rs['admin_uname'] = '';
			if(!empty($obj_rs['refund_uid'])) $arr_uid[] = $obj_rs['refund_uid'];
			if(!empty($obj_rs['refund_admin_uid'])) $arr_uid[] = $obj_rs['refund_admin_uid'];
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['uname'] = array_search($arr_return["list"][$i]['refund_uid'] , $user_info);
				$arr_return["list"][$i]['admin_uname'] = array_search($arr_return["list"][$i]['refund_admin_uid'] , $user_info);
			}
		}

		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
}