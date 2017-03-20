<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_gift extends inc_mod_admin {
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
			's_key' => fun_get::get("s_key"),
			's_time1' => fun_get::get("s_time1"),
			's_time2' => fun_get::get("s_time2"),
			's_state' => fun_get::get("s_state" , -999),
			's_group' => fun_get::get("s_group"),
		);
		if( !empty($arr_search_key['s_key']) ) $arr_where_s[] = "gift_name like '%" . $arr_search_key['s_key'] . "%'"; 
		if( !empty($arr_search_key['s_group']) ) $arr_where_s[] = "gift_group='" . $arr_search_key['s_group'] . "'"; 
		if( fun_is::isdate( $arr_search_key['s_time1'] ) ) $arr_where_s[] = "gift_starttime <= '" . $arr_search_key['s_time1'] . "'"; 
		if( fun_is::isdate( $arr_search_key['s_time2'] ) ) $arr_where_s[] = "gift_endtime >= '" . date("Y-m-d H:i:s",fun_get::endtime( $arr_search_key['s_time2'] )) . "'";
		if($arr_search_key['s_state'] != -999) $arr_where_s[] = "gift_state='" . $arr_search_key['s_state'] . "'"; 
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
	function sql_list($str_where = "" , $lng_page = 1 , $lng_pagesize = 10) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("act.gift" , $this->app_dir , "act");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("act.gift"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_gift" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."act_gift" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["gift_addtime"]) && !empty($obj_rs["gift_addtime"])) $obj_rs["gift_addtime"] = date("Y-m-d H:i:s" , $obj_rs["gift_addtime"]);
			if(isset($obj_rs["gift_state"])) $obj_rs["gift_state"] = array_search($obj_rs["gift_state"] , tab_act_gift::get_perms("state"));
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."act_gift" , "gift_id='".$msg_id."'");
		if( empty($obj_rs["gift_id"]) ) {
			$obj_rs["gift_state"] = 1;
		}
		(empty($obj_rs["gift_starttime"])) ? $obj_rs["gift_starttime"] = '' : $obj_rs["gift_starttime"] = fun_get::showdate($obj_rs["gift_starttime"]);
		(empty($obj_rs["gift_endtime"])) ? $obj_rs["gift_endtime"] = '' : $obj_rs["gift_endtime"] = fun_get::showdate($obj_rs["gift_endtime"]);
		return $obj_rs;
	}

	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"gift_id"=>fun_get::post("id"),
			"gift_name"=>fun_get::post("gift_name"),
			"gift_pic"=>fun_get::post("gift_pic"),
			"gift_total"=>fun_get::post("gift_total"),
			"gift_total_day"=>fun_get::post("gift_total_day"),
			"gift_opentime"=>fun_get::post("gift_opentime"),
			"gift_desc"=>fun_get::post("gift_desc"),
			"gift_score"=>fun_get::post("gift_score"),
			"gift_starttime"=>fun_get::post("gift_starttime"),
			"gift_endtime"=>fun_get::post("gift_endtime"),
			"gift_state"=>fun_get::post("gift_state"),
			"gift_group"=>fun_get::post("gift_group"),
			"gift_desc"=>fun_get::post("gift_desc"),
			"gift_user_num"=>fun_get::post("gift_user_num"),
		);
		$arr = tab_act_gift::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

	/* 删除指定  ads_id 数据
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
		$arr = tab_act_gift::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

}