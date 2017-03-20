<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_meal_collect {
	static $perms;
	static $value;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"type" => array( "店铺" => 1) ,
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['collect_id'])) {
			$arr_fields['id'] = $arr_fields['collect_id'];
			unset($arr_fields['collect_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " collect_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and collect_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {
			//必填项检查
			if(!isset($arr_fields['collect_for_id']) || empty($arr_fields['collect_for_id']) ) {
				$arr_return['code'] = 113;
				$arr_return['msg']  = "收藏id不能为空 ";
				return $arr_return;
			}
			$obj_row = $obj_db->get_one("select collect_id from " . cls_config::DB_PRE . "meal_collect where collect_user_id='" . $arr_fields['collect_user_id'] . "' and collect_for_id='" . $arr_fields['collect_for_id'] . "' and collect_type='" . $arr_fields['collect_type'] . "'");
			if(!empty($obj_row)) {
				$arr_type = self::get_perms("type");
				$val = array_search($arr_fields['collect_type'] , $arr_type);
				return array("code" => 500 , "msg" => "您已经收藏过此" . $val);
			}
			//初始必要值
			$arr_fields['collect_addtime'] = TIME;
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."meal_collect",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$obj_rs = $obj_db->get_one("select collect_id from ".cls_config::DB_PRE."meal_collect where collect_addtime='" . $arr_fields["collect_addtime"] . " and collect_user_id='" . $arr_fields["collect_user_id"] . "'");
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['collect_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select collect_id from ".cls_config::DB_PRE."meal_collect where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['collect_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "collect_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."meal_collect" , $arr_fields , $where);
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
			$arr_return["msg"]=cls_language::get("not_where");
			return $arr_return;
		}
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "collect_id='".$str_id."'" : $arr_where[] = "collect_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."meal_collect" , $where);
		return $arr_return;
	}

}