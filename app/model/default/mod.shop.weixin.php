<?php
/* 蹇嵎璁㈤绯荤粺涔嬪搴楃増
 * 鐗堟湰鍙凤細3.9
 * 瀹樼綉锛歨ttp://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_weixin extends inc_mod_shop {
	function on_message_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$site_id = $this->shop_info['site_id'];
		$type = (int)fun_get::get("group");
		$arr_fields = array(
			"message_id"=>fun_get::post("id"),
			"message_type"=>fun_get::post("message_type"),
			"message_text"=>fun_get::post("message_text"),
			"message_media_id"=>fun_get::post("message_media_id"),
			"message_group"=>$type,
			"message_site_id" => $site_id,
		);
		$arr_fields = $this->get_message_text($arr_fields);
		$arr = tab_weixin_message::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	function get_message_text($arr_message) {
		switch($arr_message['message_type']) {
			case 'text':
				$arr_message['message_text'] = fun_get::get("message_text");
				break;
			case 'news':
				$message_title = fun_get::get("message_title");
				$message_url = fun_get::get("message_url");
				$message_pic = fun_get::get("message_pic");
				$message_desc = fun_get::get("message_desc");
				$ii = count($message_title);
				$arr_news = array();
				$len = count($message_title);
				for($ii = 0 ; $ii < $len ; $ii++) {
					$arr_news[] = array(
						'id' => $ii,
						'title' => $message_title[$ii],
						'url' => $message_url[$ii],
						'pic' => $message_pic[$ii],
						'desc' => $message_desc[$ii],
					);
				}
				$arr_message['message_text'] = serialize($arr_news);
				break;
			default:
				$arr_message['message_media_id'] = fun_get::get("message_media_id");
		}
		return $arr_message;
	}

	function on_site_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"id"     => $this->shop_info['site_id'],
			"site_domain" => fun_get::post("site_domain"),
			"site_wx_uname" => fun_get::post("site_wx_uname"),
			"site_wx_token"  => fun_get::post("site_wx_token"),
			"site_wx_appid"  => fun_get::post("site_wx_appid"),
			"site_wx_appsecret"  => fun_get::post("site_wx_appsecret"),
			"site_wx_certify"  => fun_get::post("site_wx_certify"),
			"site_wx_msgmode"  => fun_get::post("site_wx_msgmode"),
			"site_name"  => fun_get::post("site_name"),
			"site_shop_id"  => $this->shop_info['id'],
			"site_customview"  => (int)fun_get::post("site_customview"),
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
	function get_site() {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."weixin_site" , "site_shop_id='".$this->shop_info["id"]."'");
		return $obj_rs;
	}
	function get_keywords_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."weixin_keywords" , "keywords_id='".$msg_id."'");
		$id = (isset($obj_rs['keywords_message_id'])) ? $obj_rs['keywords_message_id'] : 0;
		$obj_message = tab_weixin_message::get_one(0 , $id , $this->shop_info["site_id"]);

		if(empty($obj_message['message_id'])) $obj_message['keywords_message_id'] = 0;
		foreach($obj_message as $item => $key) {
			$obj_rs[$item] = $key;
		}
		if(isset($obj_rs['message_text'])) $obj_rs['message_text'] = tab_weixin_message::format_text($obj_rs['message_text'] , 1);
		$obj_rs['message_text_html'] = fun_get::filter($obj_rs['message_text'],true);
		return $obj_rs;
	}
	function on_keywords_save() {
		$keywords_mode = 0;
		if(fun_is::set("keywords_mode")) $keywords_mode = fun_get::post('keywords_mode');
		$arr_keywords = array(
			"keywords_id" => fun_get::post("id"),
			"keywords_val" => fun_get::post("keywords_val"),
			"keywords_mode" => $keywords_mode,
			"keywords_message_id" => (int)fun_get::post("keywords_message_id"),
			"keywords_site_id" => $this->shop_info["site_id"],
		);
		if(empty($arr_keywords['keywords_val'])) return array('code' => 500 , 'msg' => '关键词不能为空');
		$arr_message = array(
			'message_id' => $arr_keywords['keywords_message_id'],
			'message_type' => fun_get::get("message_type"),
			'message_group' => 2,
			'message_site_id' => $this->shop_info["site_id"],
		);
		if(empty($arr_message['message_type'])) $arr_message['message_type'] = 'text';
		$arr_message = $this->get_message_text($arr_message);
		$arr_msg = tab_weixin_message::on_save($arr_message);
		if($arr_msg['code'] == 0) {
			$arr_keywords['keywords_message_id'] = $arr_msg['id'];
			$arr_msg = tab_weixin_keywords::on_save($arr_keywords);
		}
		return $arr_msg;
	}
	function on_keywords_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_weixin_keywords::on_delete($str_id , "keywords_site_id='" . $this->shop_info["site_id"] . "'");
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	function get_menu_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."weixin_menu" , "menu_id='".$msg_id."' and menu_site_id='" . $this->shop_info["site_id"] . "'");

		if(empty($obj_rs['menu_id'])) $obj_rs['menu_pid'] = (int)fun_get::get("pid");
		$id = (isset($obj_rs['menu_message_id'])) ? $obj_rs['menu_message_id'] : 0;
		$obj_message = tab_weixin_message::get_one(0 , $id);
		if(empty($obj_message['message_id'])) $obj_message['keywords_message_id'] = 0;
		foreach($obj_message as $item => $key) {
			$obj_rs[$item] = $key;
		}
		if(isset($obj_rs['message_text'])) $obj_rs['message_text'] = tab_weixin_message::format_text($obj_rs['message_text'] , 1);
		$obj_rs['message_text_html'] = fun_get::filter($obj_rs['message_text'],true);
		return $obj_rs;
	}
	function on_menu_save() {
		$mode = (int)fun_get::post('mode');
		$arr_menu = array(
			"menu_id" => fun_get::post("id"),
			"menu_name" => fun_get::post("menu_name"),
			"menu_pid" => (int)fun_get::post("menu_pid"),
			"menu_sort" => fun_get::post("menu_sort"),
			"menu_site_id" => $this->shop_info["site_id"],
		);
		if(empty($arr_menu['menu_name'])) return array('code' => 500 , 'msg' => '菜单名称不能为空');
		if($mode == 1) {
			$arr_message = array(
				'message_id' => (int)fun_get::post("menu_message_id"),
				'message_type' => fun_get::get("message_type"),
				'message_group' => 3,
				'message_site_id' => $this->shop_info["site_id"],
			);
			if(empty($arr_message['message_type'])) $arr_message['message_type'] = 'text';
			switch($arr_message['message_type']) {
				case 'text':
					$arr_message['message_text'] = fun_get::get("message_text");
					break;
				case 'news':
					$message_title = fun_get::get("message_title");
					$message_url = fun_get::get("message_url");
					$message_pic = fun_get::get("message_pic");
					$message_desc = fun_get::get("message_desc");
					$ii = count($message_title);
					$arr_news = array();
					$len = count($message_title);
					for($ii = 0 ; $ii < $len ; $ii++) {
						$arr_news[] = array(
							'id' => $ii,
							'title' => $message_title[$ii],
							'url' => $message_url[$ii],
							'pic' => $message_pic[$ii],
							'desc' => $message_desc[$ii],
						);
					}
					$arr_message['message_text'] = serialize($arr_news);
					break;
				default:
					$arr_message['message_media_id'] = fun_get::get("message_media_id");
			}
			$arr_msg = tab_weixin_message::on_save($arr_message);
			$arr_menu['menu_linkurl'] = '';
			if($arr_msg['code'] != 0) return $arr_msg;
			$arr_menu['menu_message_id'] = $arr_msg['id'];
		} else {
			$arr_menu['menu_message_id'] = 0;
			$arr_menu['menu_linkurl'] = fun_get::post("menu_linkurl");

		}
		$arr_msg = tab_weixin_menu::on_save($arr_menu);
		return $arr_msg;
	}
	function get_menu_select($site_id , $name = 'menu_id', $default = '' , $no_id = '') {
		$str_where = '';
		if(!empty($no_id)) $str_where = "menu_id not in(".$no_id.")";
		$arr = tab_weixin_menu::get_list_layer($site_id , 0 , 1 , $str_where , 2);
		$arr_select = array();
		//添加默认
		$arr_select[] = array("val" => 0 , "title" => cls_language::get("layer_top") , "layer" => 0);
		foreach($arr["list"] as $item) {
			$arr_select[] = array("val" => $item['menu_id'] , "title" => $item['menu_name'] , "layer" => $item["layer"]);
		}
		$str = fun_html::select($name , $arr_select ,$default);
		return $str;
	}
	function on_menu_save_all() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_menu_name = fun_get::get("menu_name");
		$arr_menu_sort = fun_get::get("menu_sort");
		$arr_menu_id   = fun_get::get("menu_id");
		$arr_menu_id_layer   = fun_get::get("group_id_layer");

		//循环统计已有 id
		$arr_id = array();
		$lng_count = count($arr_menu_id);
		for( $i = 1 ; $i < $lng_count ; $i++) {
			$lng_id = (int)$arr_menu_id[$i];
			if($lng_id > 0) $arr_id[] = $lng_id;
		}
		$str_ids = fun_format::arr_id($arr_id);
		$str_ids = fun_format::arr_id($arr_id);
		$str_where = "menu_site_id='" . $this->shop_info["site_id"] . "'";
		if( !empty($str_ids) ) {
			$str_where = " and not menu_id in(".$str_ids.")";
		}
		//首先删除没在保存id中的所有记录
		tab_weixin_menu::on_delete(array(),$str_where);
		
		$arr_resave = array();
		$lng_count = count($arr_menu_name);
		
		for( $i = 1 ; $i < $lng_count ; $i++) {
			$arr_menu_id[$i] = (int)$arr_menu_id[$i];
			if(empty($arr_menu_id[$i])) continue;
			$arr_fields = array(
				"menu_id" => $arr_menu_id[$i],
				"menu_name" => $arr_menu_name[$i],
				"menu_sort" => $arr_menu_sort[$i]
			);
			$arr_msg = tab_weixin_menu::on_save($arr_fields);
			if($arr_msg["code"]!=0) return $arr_msg;
		}
		//完成事务
		return $arr_return;
	}

	function on_sendmsg() {
		$openid = fun_get::get("openid");
		$type = fun_get::post("message_type");
		if(empty($type)) $type = 'text';
		$arr_fields = array(
			"message_type"=>$type,
			"message_text"=>fun_get::post("message_text"),
			"message_media_id"=>fun_get::post("message_media_id"),
		);
		$arr_fields = $this->get_message_text($arr_fields);
		$arr_return = cls_weixin::on_send($this->shop_info["site_id"] , $openid , $type , $arr_fields);
		return $arr_return;
	}

	function get_userlist() {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'key' => fun_get::get("s_key"),
			'area' => (int)fun_get::get("s_area"),
			'state' => (int)fun_get::get("s_state",-999),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "user_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "user_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "user_name like '%" . $arr_search_key['key'] . "%'";
		if( $arr_search_key['area'] != '' ) $arr_where_s[] = "user_area like '%" . $arr_search_key['area'] . "%'";
		if($arr_search_key['state'] !=-999 ) $arr_where_s[] = "user_state='" . $arr_search_key['state'] . "'";
		$site_id = $this->shop_info["site_id"];
		$arr_where[] = "user_site_id='" . $site_id . "'";
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->user_sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function user_sql_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("weixin.user" , $this->app_dir , "weixin");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("weixin.user"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."weixin_user" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."weixin_user " . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs['user_state'])) {
				$obj_rs['user_state'] = ($obj_rs['user_state']==1) ? "关注中" : "<font style='color:#ff0000'>已取消关注</font>";
			}
			if(isset($obj_rs['user_addtime'])) $obj_rs['user_addtime'] = date("Y-m-d H:i" , $obj_rs['user_addtime']);
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
}