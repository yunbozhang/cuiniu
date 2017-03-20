<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_app_client {
	/* 保存操作
	 * arr_fields : 为字段数据，默认如果包函 id，则为修改，否则为插入
	 * where : 默认为空，用于有时候条件修改
	 */
	static function on_save($arr_fields , $where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['client_id'])) {
			$arr_fields['id'] = $arr_fields['client_id'];
			unset($arr_fields['client_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = $arr_fields['id'];
			unset($arr_fields['id']);
			if( !empty($arr_return['id']) ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " client_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and client_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {
			if(!isset($arr_fields["client_code"]) || empty($arr_fields["client_code"])) return array("code" => 500 , "msg" => "设备号不能为空");
			$obj_one = $obj_db->get_one("select client_id from " . cls_config::DB_PRE . "app_client where client_code='" . $arr_fields["client_code"] . "'");
			if(!empty($obj_one)) return array("code" => 500 , "msg" => "设备号已经存在");
			$arr_fields['client_loadnum'] = 1;
			$arr_fields['client_addtime'] = TIME;
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."app_client",$arr_fields);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = $arr['msg'];
			} else {
				$arr_return['id'] = $obj_db->insert_id();
			}
		} else {
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."app_client" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = $arr['msg'];
			}

		}
		return $arr_return;
	}
}