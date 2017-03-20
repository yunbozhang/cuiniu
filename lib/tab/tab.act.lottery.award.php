<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_act_lottery_award {
	static $perms;
	static $value;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['award_id'])) {
			$arr_fields['id'] = $arr_fields['award_id'];
			unset($arr_fields['award_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " award_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and award_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['award_lottery_id']) || $arr_fields['award_lottery_id'] == '' ) return array("code" => 500 , "msg" => "奖品必须指定所属活动");
			if(!isset($arr_fields['award_name']) || $arr_fields['award_name'] == '' ) return array("code" => 500 , "msg" => "奖品名称不能为空");
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."act_lottery_award",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$obj_rs = $obj_db->get_one("select award_id from ".cls_config::DB_PRE."act_lottery_award where award_name='" . $arr_fields["award_name"] . " and award_lottery_id='" . $arr_fields["award_lottery_id"] . "'");
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['award_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select award_id from ".cls_config::DB_PRE."act_lottery_award where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['award_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "award_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."act_lottery_award" , $arr_fields , $where);
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
		$arr_where = array();
		$arr_where[] = 'award_num_exchange=0';
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "award_id='".$str_id."'" : $arr_where[] = "award_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);
		$arr_return=$obj_db->on_delete(cls_config::DB_PRE."act_lottery_award" , $where);
		return $arr_return;
	}
}