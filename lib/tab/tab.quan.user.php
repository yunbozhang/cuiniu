<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_quan_user {
	static function on_insert($arr_fields) {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(!isset($arr_fields['user_id']) || empty($arr_fields['user_id']) ) return array('code' => 500 , 'msg' =>'用户id不能为空');
		if(!isset($arr_fields['user_zan_state'])) $arr_fields['user_zan_state'] = cls_config::get("user_zan_state" , "quan");
		if(!isset($arr_fields['user_ping_state'])) $arr_fields['user_ping_state'] = cls_config::get("user_ping_state" , "quan");
		if(!isset($arr_fields['user_pub_state'])) $arr_fields['user_pub_state'] = cls_config::get("user_pub_state" , "quan");
		$obj_db = cls_obj::db_w();
		//插入到用户表
		$arr = $obj_db->on_insert(cls_config::DB_PRE."quan_user",$arr_fields);
		if($arr['code'] == 0) {
			$arr_return['id'] = $arr_fields['user_id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
		}
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['user_id'])) {
			$arr_fields['id'] = $arr_fields['user_id'];
			unset($arr_fields['user_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " user_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and user_id='" . $arr_return['id'] . "'";
				}
			}
		}
		if( empty($where) ) {
			return array('code' => 500 , 'msg' =>'保存条件不能为空');
		} else {
			$obj_db = cls_obj::db_w();
			//注销账号修改
			if( isset($arr_fields["user_id"]) ) unset($arr_fields["user_id"]);

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select user_id from ".cls_config::DB_PRE."quan_user where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['user_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "user_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."quan_user" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
}