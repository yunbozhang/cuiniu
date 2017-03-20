<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_weixin_menu extends inc_mod_weixin {
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."weixin_menu" , "menu_id='".$msg_id."'");

		if( empty($obj_rs["menu_id"]) ) {
			if($this->weixin_site["id"]>=0) {
				$obj_rs["menu_site_id"] = $this->weixin_site["id"];
				$obj_rs["site_name"] = $this->weixin_site["name"];
			} else {
				$obj_rs["menu_site_id"] = 0;
				$obj_rs["site_name"] = "默认";
			}
		} else if($obj_rs["menu_site_id"]>0) {
			$obj_rs2 = cls_obj::db()->get_one("select site_id,shop_name from " . cls_config::DB_PRE . "weixin_site a left join " . cls_config::DB_PRE . "meal_shop b on a.site_shop_id=b.shop_id where site_id='" .$obj_rs["menu_site_id"] . "'");
			if(!empty($obj_rs)) {
				$obj_rs["menu_site_id"] = $obj_rs2["site_id"];
				$obj_rs["site_name"] = $obj_rs2["shop_name"];
			}
		} else {
			$obj_rs["menu_site_id"] = 0;
			$obj_rs["site_name"] = "默认";
		}


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
	function on_save() {
		$mode = (int)fun_get::post('mode');
		$arr_menu = array(
			"menu_id" => fun_get::post("id"),
			"menu_name" => fun_get::post("menu_name"),
			"menu_pid" => (int)fun_get::post("menu_pid"),
			"menu_sort" => fun_get::post("menu_sort"),
			"menu_site_id" => (int)fun_get::post("menu_site_id"),
		);
		if(empty($arr_menu['menu_name'])) return array('code' => 500 , 'msg' => '菜单名称不能为空');
		if($mode == 1) {

			$arr_message = array(
				'message_id' => (int)fun_get::post("menu_message_id"),
				'message_type' => fun_get::get("message_type"),
				'message_group' => 3,
				'message_site_id' => $arr_menu['menu_site_id'],
			);
			if(empty($arr_message['message_type'])) $arr_message['message_type'] = 'text';
			switch($arr_message['message_type']) {
				case 'text':
					$arr_message['message_text'] = fun_get::get("message_text");
					break;
				case 'news':
					$message_title = fun_get::get("message_title");
					$message_url = fun_get::filter(fun_get::get("message_url"),true);
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
	function on_save_all() {
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
		if( !empty($str_ids) ) {
			$str_where = "not menu_id in(".$str_ids.")";
		} else {
			$str_where = "1>0";//绝对成立条件
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
}