<?php
/**
 * 菜单模型类 关联表名：other_language
 * 
 */
class mod_other_language extends inc_mod_admin {
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
			'language_title' => fun_get::get("s_key"),
		);
		if( !empty($arr_search_key['language_title']) ) $arr_where_s[] = "language_title like '%" . $arr_search_key['language_title'] . "%'"; 
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
		$arr_cfg_fields = tab_sys_user_config::get_fields("other.language" , $this->app_dir , "other");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("other.language"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."other_language" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."other_language" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id , $arr_language = array()) {
		$obj_language = cls_obj::db()->edit(cls_config::DB_PRE."other_language" , "language_id='".$msg_id."'");
		foreach($arr_language as $item) {
			$obj_language[$item['key']] = array("version_id" =>"","version_val"=>"");
		}
		if(!empty($obj_language['language_id'])) {
			$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "other_language_version where version_language_id='" . $obj_language['language_id'] . "'");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				$obj_language[$obj_rs['version_key']] = $obj_rs;
			}
		}
		return $obj_language;
	}

	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"language_id"=>fun_get::post("id"),
			"language_val"=>fun_get::post("language_cn"),
			"language_beta"=>fun_get::post("language_beta"),
		);
		$arr = tab_other_language::on_save($arr_fields);
		if($arr['code']==0) {
			$language_id = $arr['id'];
			$arr_language = $this->get_language_list();
			foreach($arr_language as $item) {
				if($item['key'] == 'cn') continue;
				$arr_fields = array(
					"version_id"=>fun_get::post("language_" . $item['key'] . "_id"),
					"version_language_id"=>$language_id,
					"version_val"=>fun_get::post("language_".$item['key']),
					"version_key"=>$item['key'],
				);
				$arrx = tab_other_language::on_version_save($arr_fields);
		}
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

	/* 删除指定  language_id 数据
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
		$arr = tab_other_language::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	function get_language_list() {
		$obj_rs = cls_obj::db()->get_one("select config_list from " . cls_config::DB_PRE . "sys_config where config_name='language' and config_module='sys'");
		if(empty($obj_rs)) return array();
		$arr = explode(chr(10),$obj_rs['config_list']);
		$arr_v = array();
		foreach($arr as $item) {
			$arrx = explode("=&gt;",$item);
			if(count($arrx)<2) continue;
			$arr_v[] = array("name" => $arrx[1] , "key" => $arrx[0]);
		}
		return $arr_v;
	}
}