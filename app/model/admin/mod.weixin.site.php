<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_weixin_site extends inc_mod_weixin {
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
			'state' => (int)fun_get::get("s_state",-999),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "site_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "site_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(site_name like '%" . $arr_search_key['key'] . "%' or site_domain like '%" . $arr_search_key['key'] . "%' or site_wx_uname like '%" . $arr_search_key['key'] . "%')";
		if($arr_search_key['state']!=-999 ) $arr_where_s[] = "site_state='" . $arr_search_key['state'] . "'";

		//管理权限
		$limit_area = $this->this_limit->get_perms('limit_area');
		if(!empty($limit_area) && cls_config::get('module','version','','') == 'meal_mall' ) {
			$arr = explode("," , $limit_area);
			$arr_x = array();
			foreach($arr as $areaid) {
				$arr_x[] = cls_obj::db_w()->concat(',','site_area_allid',',') . " like '%," . $areaid . ",%'";
			}
			$where = ' where (' . implode(" or " , $arr_x) . ')';
			$obj_result = $obj_db->select("select shop_id from " . cls_config::DB_PRE . "meal_shop" . $where);
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr[] = $obj_rs['shop_id'];
			}
			$arr_where[] = empty($arr) ? 'site_id=0' : 'site_shop_id in(' . implode("," , $arr) . ")";
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
		$arr_type = tab_weixin_site::get_perms("type");
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("weixin.site" , $this->app_dir , "weixin");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("weixin.site"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$arr_state = tab_weixin_site::get_perms("state");
		//取分页信息
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."weixin_site" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."weixin_site " . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["site_state"])) $obj_rs["site_state"] = array_search($obj_rs["site_state"] , $arr_state);
			if(isset($obj_rs["site_addtime"])) $obj_rs["site_addtime"] = date("Y-m-d H:i:s" , $obj_rs["site_addtime"]);
			if(isset($obj_rs['site_wx_certify'])) $obj_rs["site_wx_certify"] = empty($obj_rs["site_wx_certify"]) ? '否' : '是';
			if(isset($obj_rs['site_wx_msgmode'])) $obj_rs["site_wx_msgmode"] = empty($obj_rs["site_wx_certify"]) ? '否' : '是';
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$get_url_type = fun_get::get("url_type");
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."weixin_site" , "site_id='".$msg_id."'");
		if( empty($obj_rs["site_id"]) ) {
			$obj_rs["site_state"] = 1;
			if($this->weixin_site["id"]>=0) {
				$obj_rs["site_shop_id"] = $this->weixin_site["id"];
				$obj_rs["shop_name"] = $this->weixin_site["name"];
			} else {
				$obj_rs["site_shop_id"] = 0;
				$obj_rs["shop_name"] = "默认";
			}
		} else if($obj_rs["site_shop_id"]>0) {
			$obj_rs2 = cls_obj::db()->get_one("select shop_id,shop_name from " . cls_config::DB_PRE . "meal_shop where shop_id='" .$obj_rs["site_shop_id"] . "'");
			if(!empty($obj_rs)) {
				$obj_rs["site_shop_id"] = $obj_rs2["shop_id"];
				$obj_rs["shop_name"] = $obj_rs2["shop_name"];
			}
		} else {
			$obj_rs["site_shop_id"] = 0;
			$obj_rs["shop_name"] = "默认";
		}

		return $obj_rs;
	}
	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));

		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"site_domain" => fun_get::post("site_domain"),
			"site_wx_uname" => fun_get::post("site_wx_uname"),
			"site_wx_token"  => fun_get::post("site_wx_token"),
			"site_wx_appid"  => fun_get::post("site_wx_appid"),
			"site_wx_appsecret"  => fun_get::post("site_wx_appsecret"),
			"site_wx_certify"  => fun_get::post("site_wx_certify"),
			"site_wx_msgmode"  => fun_get::post("site_wx_msgmode"),
			"site_name"  => fun_get::post("site_name"),
			"site_state"  => (int)fun_get::post("site_state"),
			"site_shop_id"  => (int)fun_get::post("site_shop_id"),
			"site_customview"  => (int)fun_get::post("site_customview"),
			"site_wx_mch_id"  => fun_get::post("site_wx_mch_id"),
			"site_wx_mch_key"  => fun_get::post("site_wx_mch_key"),
			"site_wx_customer_open"  => (int)fun_get::post("site_wx_customer_open"),
		);
		$arr = tab_weixin_site::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

	/* 删除指定  site_id 数据
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
		$arr = tab_weixin_site::on_delete($str_id);
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
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE."weixin_site" , array("site_state" => $state_val) , "site_id in(" . $str_id . ")");
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr["code"];
			$arr_return["msg"] = $arr["msg"];
		}
		return $arr_return;
	}
}