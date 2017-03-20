<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_weixin_message extends inc_mod_weixin {
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$type = (int)fun_get::get("url_type");
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

	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."weixin_keywords" , "keywords_id='".$msg_id."'");

		if( empty($obj_rs["keywords_id"]) ) {
			if($this->weixin_site["id"]>=0) {
				$obj_rs["keywords_site_id"] = $this->weixin_site["id"];
				$obj_rs["site_name"] = $this->weixin_site["name"];
			} else {
				$obj_rs["keywords_site_id"] = 0;
				$obj_rs["site_name"] = "默认";
			}
		} else if($obj_rs["keywords_site_id"]>0) {
			$obj_rs2 = cls_obj::db()->get_one("select site_id,shop_name from " . cls_config::DB_PRE . "weixin_site a left join " . cls_config::DB_PRE . "meal_shop b on a.site_shop_id=b.shop_id where site_id='" .$obj_rs["keywords_site_id"] . "'");
			if(!empty($obj_rs)) {
				$obj_rs["keywords_site_id"] = $obj_rs2["site_id"];
				$obj_rs["site_name"] = $obj_rs2["shop_name"];
			}
		} else {
			$obj_rs["keywords_site_id"] = 0;
			$obj_rs["site_name"] = "默认";
		}

		$id = (isset($obj_rs['keywords_message_id'])) ? $obj_rs['keywords_message_id'] : 0;
		$obj_message = tab_weixin_message::get_one(0 , $id , $this->weixin_site["id"]);
		if(empty($obj_message['message_id'])) $obj_message['keywords_message_id'] = 0;
		foreach($obj_message as $item => $key) {
			$obj_rs[$item] = $key;
		}
		if(isset($obj_rs['message_text'])) $obj_rs['message_text'] = tab_weixin_message::format_text($obj_rs['message_text'] , 1);
		$obj_rs['message_text_html'] = fun_get::filter($obj_rs['message_text'],true);
		return $obj_rs;
	}
	function get_message_text($arr_message) {
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
		return $arr_message;
	}
	function on_keywords_save() {
		$keywords_mode = 0;
		if(fun_is::set("keywords_mode")) $keywords_mode = fun_get::post('keywords_mode');
		$arr_keywords = array(
			"keywords_id" => fun_get::post("id"),
			"keywords_val" => fun_get::post("keywords_val"),
			"keywords_mode" => $keywords_mode,
			"keywords_message_id" => (int)fun_get::post("keywords_message_id"),
			"keywords_site_id" => (int)fun_get::post("keywords_site_id"),
		);
		if(empty($arr_keywords['keywords_val'])) return array('code' => 500 , 'msg' => '关键词不能为空');
		$arr_message = array(
			'message_id' => $arr_keywords['keywords_message_id'],
			'message_type' => fun_get::get("message_type"),
			'message_group' => 2,
			'message_site_id' => $arr_keywords['keywords_site_id'],
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
		$arr = tab_weixin_keywords::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

}