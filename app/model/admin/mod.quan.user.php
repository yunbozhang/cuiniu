<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_quan_user extends inc_mod_admin {
	/* 按模块查询用户信息并返回数组列表
	 * module : 指定查询模块
	 * isdel : 是否为回收站 , 1:是，0:非
	 */
	function get_pagelist($is_del = 0) {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'regtime1' => fun_get::get("s_regtime1"),
			'regtime2' => fun_get::get("s_regtime2"),
			'pubtime1' => fun_get::get("s_pubtime1"),
			'pubtime2' => fun_get::get("s_pubtime2"),
			'pub_state' => fun_get::get("s_pub_state"),
			'zan_state' => fun_get::get("s_zan_state"),
			'ping_state' => fun_get::get("s_ping_state"),
			'key' => fun_get::get("s_key"),
		);
		if( fun_is::isdate( $arr_search_key['regtime1'] ) ) $arr_where_s[] = "user_regtime >= '" . strtotime( $arr_search_key['regtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['regtime2'] ) ) $arr_where_s[] = "user_regtime <= '" . fun_get::endtime( $arr_search_key['regtime2'] ) . "'"; 
		$arr_where_pub = array();
		if( fun_is::isdate( $arr_search_key['pubtime1'] ) ) $arr_where_pub[] = "msg_addtime >= '" . strtotime($arr_search_key['pubtime1']) . "'"; 
		if( fun_is::isdate( $arr_search_key['pubtime2'] ) ) $arr_where_pub[] = "msg_addtime <= '" . fun_get::endtime( $arr_search_key['pubtime2'] ) . "'";
		if( !empty($arr_search_key['pub_state']) ) $arr_where_pub[] = "user_pub_state=0";
		if( !empty($arr_search_key['zan_state']) ) $arr_where_pub[] = "user_zan_state=0";
		if( !empty($arr_search_key['ping_state']) ) $arr_where_pub[] = "user_ping_state=0";
		if(!empty($arr_where_pub)) {
			$where = implode(" and " , $arr_where_pub);
			$arr_where_s[] = "user_id in(select msg_user_id from " . cls_config::DB_PRE . "quan_msg where " . $where . ")";
		}
		if( $arr_search_key['key'] != '' ) $arr_where_pub[] = "b.user_netname like '%" . $arr_search_key['key'] . "%' or user_sign like '%" . $arr_search_key['key'] . "%'";
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
		$arr_cfg_fields = tab_sys_user_config::get_fields("quan.user" , $this->app_dir , "quan");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		for($i = 0 ; $i < count($arr_return['tabtd']) ; $i++) {
			$arr = explode("." , $arr_return['tabtd'][$i]);
			$arr_return['tabtd'][$i] = (count($arr) > 1) ? $arr[1] : $arr[0];
		}
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("quan.user"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		//取分页信息
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."quan_user a left join " . cls_config::DB_PRE . "quan_user b on a.user_id=b.user_id" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."quan_user a left join " . cls_config::DB_PRE . "sys_user b on a.user_id=b.user_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['user_regtime'] = date("Y-m-d H:i" , $obj_rs['user_regtime']);
			$obj_rs['user_zan_state'] = ($obj_rs['user_zan_state'] == 0) ? '<font style="color:#ff0000">禁止</font>' : '';
			$obj_rs['user_ping_state'] = ($obj_rs['user_ping_state'] == 0) ? '<font style="color:#ff0000">禁止</font>' : '';
			$obj_rs['user_pub_state'] = ($obj_rs['user_pub_state'] == 0) ? '<font style="color:#ff0000">禁止</font>' : '';
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."quan_user" , "user_id='".$msg_id."'");
		if(empty($obj_rs['user_pics'])) {
			$obj_rs['user_pics'] = array();
		} else {
			$obj_rs['user_pics'] = explode("||" , $obj_rs['user_pics']);
		}
		$obj_rs['view_names'] = '';
		$obj_rs['pub_names'] = '';
		if(!empty($obj_rs['user_view_ids'])) {
			$arr_names = array();
			$obj_result = cls_obj::db()->select("select category_id,category_name from " . cls_config::DB_PRE . "quan_category where category_id in(" . $obj_rs['user_view_ids'] . ")");
			while($obj_rs2 = cls_obj::db()->fetch_array($obj_result)) {
				$arr_names[] = $obj_rs2['category_name'];
			}
			$obj_rs["view_names"] = implode("," , $arr_names);
		}
		if(!empty($obj_rs['user_pub_ids'])) {
			$arr_names = array();
			$obj_result = cls_obj::db()->select("select category_id,category_name from " . cls_config::DB_PRE . "quan_category where category_id in(" . $obj_rs['user_pub_ids'] . ")");
			while($obj_rs2 = cls_obj::db()->fetch_array($obj_result)) {
				$arr_names[] = $obj_rs2['category_name'];
			}
			$obj_rs["pub_names"] = implode("," , $arr_names);
		}
		return $obj_rs;
	}

	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$id = (int)fun_get::post("id");
		$arr_pic = fun_get::get("pic");
		if(!empty($arr_pic) && is_array($arr_pic)) {
			$arr = array();
			foreach($arr_pic as $pic) {
				if(empty($pic)) continue;
				$arr[] = $pic;
			}
			$pic = implode("||" , $arr);
		} else {
			$pic = '';
		}
		$user_view_ids = fun_get::get("user_view_ids");
		$user_pub_ids = fun_get::get("user_pub_ids");
		$user_view_ids = is_array($user_view_ids) ? implode("," , $user_view_ids) : '';
		$user_pub_ids = is_array($user_pub_ids) ? implode("," , $user_pub_ids) : '';
		$arr_fields = array(
			"id"     => $id,
			"user_sign" => fun_get::post("user_sign"),
			"user_zan_state"   => fun_get::post("user_zan_state"),
			"user_ping_state"   => fun_get::post("user_ping_state"),
			"user_pub_state"   => fun_get::post("user_pub_state"),
			"user_intro"    => fun_get::post("user_intro"),
			'user_pics' => $pic,
			'user_view_ids' => $user_view_ids,
			'user_pub_ids' => $user_pub_ids,
		);
		$arr = tab_quan_user::on_save($arr_fields);
		return $arr_return;
	}

	/* 删除指定  user_id 数据
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
		//删除用户
		$arr = cls_obj::get("cls_user")->delete_user($str_id);
		if($arr['code'] != 0) return $arr;
		//删除用户信息
		$arr = tab_quan_user::on_delete($str_id);
		if($arr['code'] != 0) return $arr;
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
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."quan_user" , array("user_state" => $state_val) , "user_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}

}