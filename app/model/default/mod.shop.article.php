<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_article extends inc_mod_shop {
	function get_channel_id() {
		$channle_key = 'activitie';
		//分页列表
		$obj_channel = cls_obj::db()->get_one("select channel_id from " . cls_config::DB_PRE . "article_channel where channel_key='" . $channle_key . "'");
		$channel_id = (!empty($obj_channel)) ? $obj_channel['channel_id'] : 0;
		return $channel_id;
	}
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist( $arr_where = array() ) {
		$channel_id = $this->get_channel_id();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		$arr_where[] = "article_channel_id='" . $channel_id ."'";
		$arr_where[] = "article_about_id='" . cls_obj::get('cls_user')->shop_id ."'";
		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'updatetime1' => fun_get::get("s_updatetime1"),
			'updatetime2' => fun_get::get("s_updatetime2"),
			'state' => (int)fun_get::get("s_state",-999),
			'key' => fun_get::get("s_key"),
			'islink' => fun_get::get("s_islink"),
			'attribute' => fun_get::get("s_attribute"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "article_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "article_addtime <= '" . fun_get::endtime( $arr_search_key['addtime2'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['updatetime1'] ) ) $arr_where_s[] = "article_updatetime >= '" . strtotime( $arr_search_key['updatetime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['updatetime2'] ) ) $arr_where_s[] = "article_updatetime <= '" . fun_get::endtime( $arr_search_key['updatetime2'] ) . "'"; 
		if( $arr_search_key['state'] != -999 ) $arr_where_s[] = "article_state = '" . $arr_search_key['state'] . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(article_title like '%" . $arr_search_key['key'] . "%' or article_source like '%" . $arr_search_key['key'] . "%' or article_author like '%" . $arr_search_key['key'] . "%' or article_tag like '%" . $arr_search_key['key'] . "%')"; 
		if($arr_search_key["islink"] != '' ) $arr_where_s[] = "article_islink='" . $arr_search_key["islink"] . "'";
		if(!empty($arr_search_key["attribute"])) $arr_where_s[] = "article_attribute like '%" . $arr_search_key["attribute"] . "%'";
		$article_about_id = fun_get::get("url_about_id");
		if(!empty($article_about_id)) $arr_where[] = "article_about_id='" . $article_about_id . "'";
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page',1));

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
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("shop.article"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];

		$arr_uid = array();
		//取分页信息
		$arr_return["list"] = $arr_list = $arr_topic_id = $arr_uname = $arr_topic = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."article" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT article_id,article_title,article_state,article_addtime,article_uid,folder_pids FROM ".cls_config::DB_PRE."article a left join " . cls_config::DB_PRE . "article_folder b on a.article_folder_id=b.folder_id left join " . cls_config::DB_PRE . "article_channel c on a.article_channel_id=c.channel_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["article_addtime"])) $obj_rs["article_addtime"] = date("Y-m-d H:i" , $obj_rs["article_addtime"]);
			if(isset($obj_rs["article_state"])) $obj_rs["article_state"] = array_search($obj_rs["article_state"],tab_article::get_perms("state"));
			$arr_list[] = $obj_rs;
		}
		$arr_return["list"] = $arr_list;
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'] , 5);
		$arr_return['state'] = tab_article::get_perms("state");
		return $arr_return;
	}
	/*取频道列表
	 * pid : 上级目录id , isdel : 为０查正常目录，为1查回收站目录 , arr_where : 条件数组 , channel_mode : 频道模式　当为1即图片模式时，分页查询，否则不分页
	 */
	function get_dirlist($pid = 0 , $arr_where = array()) {
		$channel_id = $this->get_channel_id();
		$arr_return = array();
		$obj_db = cls_obj::db();
		$arr_where[] = "folder_channel_id='" . $channel_id ."'";
		if($pid>=0) $arr_where[] = "folder_pid='" . $pid . "'";
		$arr_where[] = "folder_isdel='0'";

		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'state' => fun_get::get("s_state",-999),
			'key' => fun_get::get("s_key"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where[] = "folder_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where[] = "folder_addtime <= '" . fun_get::endtime( $arr_search_key['addtime2'] ) . "'";
		if($arr_search_key['state'] != -999) $arr_where[] = "article_state='" . $arr_search_key['state'] . "'";
		if( $arr_search_key['key'] != '' ) $arr_where[] = "folder_name like '%" . $arr_search_key['key'] . "%'"; 

		$str_where  = " where " . implode(" and " , $arr_where);
		$str_limit = '';


		$arr_list = array();
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "article_folder " . $str_where . $str_limit);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_list[] = $obj_rs;
		}
		return $arr_list;
	}
	/* 查询配置表指定id信息
	 */
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."article" , "article_id='".$msg_id."'");
		if( empty($obj_rs["article_id"]) ) {
			$obj_rs["article_state"]=1;
			$obj_rs["article_channel_id"]=fun_get::get("url_channel_id");
			$obj_rs["article_folder_id"]=fun_get::get("url_folder_id");
			$obj_rs['article_about_id'] = fun_get::get("url_about_id");
		}
		$obj_rs["article_attribute"] = explode("|",$obj_rs["article_attribute"]);
		$obj_rs["article_css"] = explode(";",$obj_rs["article_css"]);
		$obj_rs["pic"] = fun_get::html_url($obj_rs["article_pic"]);
		$obj_rs["pic_big"] = fun_get::html_url($obj_rs["article_pic_big"]);

		if(!empty($obj_rs['article_about_id'])) {
			$obj_rs['article_about_name'] = $this->get_about_name($obj_rs['article_channel_id'] , $obj_rs['article_about_id']);
		}
		return $obj_rs;
	}
	/*取频道列表
	 */
	function get_folder_select($name = 'folder_id', $default = '' , $no_id = '') {
		$channel_id = $this->get_channel_id();
		$str_where  = " folder_channel_id='" . $channel_id ."'";
		if(!empty($no_id)) $str_where .= " and folder_id not in(".$no_id.")";
		$arr = tab_article_folder::get_list_layer( 0 , 1 , $str_where);
		$arr_select = array();
		//添加默认
		$arr_select[] = array("val" => 0 , "title" => cls_language::get("layer_top") , "layer" => 0);
		foreach($arr["list"] as $item) {
			$arr_select[] = array("val" => $item['folder_id'] , "title" => $item['folder_name'] , "layer" => $item["layer"]);
		}
		$str = fun_html::select($name , $arr_select ,$default);
		return $str;
	}
	function on_save_article() {
		$channel_id = $this->get_channel_id();
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"article_title" => fun_get::post("article_title"),
			"article_intro" => fun_get::post("article_intro"),
			"article_content" => fun_get::post("article_content"),
			"article_pic_big"   => fun_get::post("article_pic_big"),
			"article_pic"   => fun_get::post("article_pic"),
			"article_islink"   => fun_get::post("article_islink"),
			"article_linkurl"   => fun_get::post("article_linkurl"),
			"article_attribute"   => implode("|" , fun_get::post("article_attribute" , array())),
			"article_channel_id"   => $channel_id,
			"article_source"   => fun_get::post("article_source"),
			"article_author"   => fun_get::post("article_author"),
			"article_updateuid"   => cls_obj::get("cls_user")->uid,
			"article_css"   => implode(";" , fun_get::post("article_css" , array())),
			"article_tag"   => fun_get::post("article_tag"),
			"article_about_id" => cls_obj::get('cls_user')->shop_id,
			"article_state" => cls_config::get("article_state" ,"meal"),
		);
		$arr = tab_article::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	/* 删除或还原指定  文章 数据
	 * isdel 决定是删除还是还原,1为删除，0为回收
	 */
	function on_del_article($isdel = 1) {
		$arr_return = array("code"=>0,"msg" => cls_language::get("delete_ok") );
		if($isdel == 0 ) $arr_return["msg"] = cls_language::get("act_ok") ;
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");

		//未指定删除id
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			($isdel == 1)? $arr_return['msg'] = cls_language::get("delete_no_id") : $arr_return['msg'] = cls_language::get("reback_no_id");
			return $arr_return;
		}

		 //删除文章
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		if(!empty($str_id)) {
			$arr = tab_article::on_del($str_id,$isdel,array('article_about_id=' . cls_obj::get('cls_user')->shop_id));
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = $arr['msg'];
			}
		}

		return $arr_return;
	}
}