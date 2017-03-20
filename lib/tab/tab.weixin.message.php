<?php
/**
 * 
 */
class tab_weixin_message {
	static $perms;

	//获取表配置参数
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}

	/* 保存操作
	 * arr_fields : 为字段数据，默认如果包函 id，则为修改，否则为插入
	 * where : 默认为空，用于有时候条件修改
	 */
	static function on_save($arr_fields , $where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['message_id'])) {
			$arr_fields['id'] = $arr_fields['message_id'];
			unset($arr_fields['message_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " message_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and message_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if(isset($arr_fields['message_text'])) $arr_fields['message_text'] = self::format_text($arr_fields['message_text']);
		if( empty($where) ) {
			if(!isset($arr_fields["message_addtime"])) $arr_fields["message_addtime"] = TIME;
			if(!isset($arr_fields["message_updatetime"])) $arr_fields["message_updatetime"] = TIME;
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."weixin_message",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$where  = "message_text='" . $arr_fields['message_text'] . " and message_addtime='".$arr_fields['message_addtime'] . "'";
					$obj_rs = $obj_db->get_one("select message_id from ".cls_config::DB_PRE."weixin_message where ".$where);
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['message_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select message_id from ".cls_config::DB_PRE."weixin_message where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['message_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "message_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."weixin_message" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
	/* 删除函数
	 * arr_id : 要删除的 id数组
	 * where : 删除附加条件
	 */
	static function on_delete($arr_id , $where = '') {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if( empty($str_id) && empty($where) ){
			$arr_return["code"] = 22;
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "message_id='".$str_id."'" : $arr_where[] = "message_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);
		$arr_return=$obj_db->on_delete(cls_config::DB_PRE."weixin_message" , $where);
		return $arr_return;
	}
	static function get_one($group = 0 , $id = null , $site_id = 0) {
		$where = ($id === null) ? " where message_group='" . $group . "'" : " where message_id='" . $id . "'";
		$where .= " and message_site_id='" . $site_id . "'";
		$obj_rs = cls_obj::db_w()->get_one("select * from " . cls_config::DB_PRE . "weixin_message" . $where);
		if(empty($obj_rs)) return array('message_id' => '' , 'message_type' => '' , 'message_text' => '' , 'message_media_id' => '' , 'message_group' => '' , 'message_text_html' => '' , 'media_name' => '' , 'media_url' => '');
		if($obj_rs['message_type'] != 'text' && !empty($obj_rs['message_media_id'])) {
			$obj_media = cls_obj::db()->get_one('select * from ' . cls_config::DB_PRE . "weixin_media where media_id='" . $obj_rs['message_media_id'] . "'");
			if(!empty($obj_media)) {
				$obj_rs['media_name'] = basename($obj_media['media_file']);
				$obj_rs['media_url'] = fun_get::html_url($obj_media['media_file']);
			}
		}
		if($obj_rs['message_type'] == 'news') {
			$obj_rs['news'] = (!empty($obj_rs['message_text'])) ? unserialize($obj_rs['message_text']) : array();
		}
		return $obj_rs;
	}
	static function get_guanzhu($site_id = 0) {
		return self::get_one(0 , null  , $site_id);
	}
	static function get_remsg($site_id = 0) {
		return self::get_one(1,null ,$site_id);
	}
	static function get_rekeywords($key = '' , $site_id = 0) {
		$obj_db = cls_obj::db();
		$arr_list = array();
		$arr_message_id = array();
		$where = " where keywords_site_id='" . $site_id . "'";
		if(!empty($key)) $where .= " and  keywords_val like '%" . $key . "%'";
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "weixin_keywords a left join " . cls_config::DB_PRE . "weixin_message b  on a.keywords_message_id=b.message_id left join " . cls_config::DB_PRE . "weixin_media c on b.message_media_id=c.media_id" . $where);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!empty($key) && $obj_rs['keywords_mode'] == 1 && $obj_rs['keywords_val'] != $key) continue;
			if(!empty($obj_rs['media_file'])) {
				$obj_rs['media_name'] = basename($obj_rs['media_file']);
				$obj_rs['media_url'] = fun_get::html_url($obj_rs['media_file']);
			}
			if($obj_rs['message_type'] == 'news' && !empty($obj_rs['message_text']) ) {
				$obj_rs['news'] = (!empty($obj_rs['message_text'])) ? unserialize($obj_rs['message_text']) : array();
			}
			$arr_list[] = $obj_rs;
		}
		return $arr_list;
	}
	static function format_text($text , $type = 0) {
		if(empty($text)) return $text;
		$arr_ico = cls_config::get('','weixin.ico','','');
		$count = count($arr_ico);
		if(empty($type)) {
			for($i = 0; $i < $count ; $i++) {
				$text = preg_replace("/&lt;img\ssrc=&quot;https:\/\/res.wx.qq.com\/mpres\/htmledition\/images\/icon\/emotion\/" . $i . "\.gif&quot;&gt;/is",$arr_ico[$i]['code'],$text);
			}
		} else {
			for($i = 0; $i < $count ; $i++) {
				$text = str_replace($arr_ico[$i]['code'],"&lt;img src=&quot;https://res.wx.qq.com/mpres/htmledition/images/icon/emotion/" . $i . ".gif&quot;&gt;" ,$text);
			}
		}
		return $text;
	}
}