<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_sys_verify {
	static $perms;

	//获取表配置参数
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"type" => array("邮箱找回密码" => 1 , "短信找回密码" => 2 , "邮箱验证" => 3 , "手机验证" => 4),
				"state" => array("未验证" => 0 , "验证成功" => 1),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	//生成唯一认证关键字符
	static function get_key($uid , $type , $val) {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		$obj_db = cls_obj::db_w();
		$arr_fields = array('verify_type'=>$type , "verify_user_id" => $uid);
		$arr_fields["verify_time"] = date("Y-m-d H:i:s",TIME);
		if(empty($arr_fields['verify_user_id'])) $arr_fields['verify_user_id'] = cls_obj::get("cls_user")->uid;
		if($arr_fields["verify_type"] == 1) {
			//邮件认证字符串
			$arr_return['key'] = $arr_fields['verify_key'] = md5($arr_fields['verify_user_id'].$arr_fields["verify_time"].rand(10000,99999));
		} else if( $arr_fields["verify_type"] == 2) {
			//短信认证字符
			$arr_return['key'] = $arr_fields['verify_key'] = rand(10000,99999);
		} else if( $arr_fields["verify_type"] == 3) {
			//邮箱注册
			$arr_return['key'] = $arr_fields['verify_key'] = dechex(rand(100000,999999));
		} else if( $arr_fields["verify_type"] == 4) {
			//短信注册
			$arr_return['key'] = $arr_fields['verify_key'] = dechex(rand(1000,9999));
		} else {
			return array("code" => 500 , "msg" => "认证类型不存在");
		}
		$obj_one = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "sys_verify where verify_type='" . $arr_fields['verify_type'] . "' and verify_val='" . $val  . "' and verify_time>='" . date("Y-m-d") . "'");
		if($obj_one['num']>=20) return array("code" => 500 , "msg" => "验证频率限制");
		$arr_fields['verify_val'] = $val;
		//插入到表
		$arr = $obj_db->on_insert(cls_config::DB_PRE."sys_verify",$arr_fields);
		if($arr['code'] == 0) {
			$arr_return['id'] = $obj_db->insert_id();
			//其它非mysql数据库不支持insert_id 时
			if(empty($arr_return['id'])) {
				$where  = "verify_user_id='" . $arr_fields['verify_user_id'] . " and verify_type='".$arr_fields['verify_type'] . "' and verify_time='".$arr_fields["verify_time"]."' and verify_val='" . $arr_fields['verify_val'] . "'";
				$obj_rs = $obj_db->get_one("select verify_id from ".cls_config::DB_PRE."sys_verify where ".$where);
				if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['verify_id'];
			}
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
		}
		return $arr_return;
	}

	//static function on_verify($key , $uid = 0 , $type = 0 , $is_verify = true) {
	static function on_verify($val , $key , $type , $time = 0 , $is_verify = true) {
		$where = "verify_key='" . $key . "' and verify_val='" . $val . "'";
		if(!empty($type)) $where .= " and verify_type=" . $type;
		$obj_rs = cls_obj::db()->get_one("select verify_user_id,verify_time,verify_state,verify_type,verify_val from " . cls_config::DB_PRE . "sys_verify where " . $where);
		if(empty($obj_rs)) return array("code"=>500 , "msg"=>"验证失败");
		//已认证过
		if($is_verify) {
			if( $obj_rs['verify_state'] != 0 ) {
				 return array("code"=>500 , "msg"=>"验证无效");
			}
			if( !empty($time) && $obj_rs['verify_time'] < date("Y-m-d H:i:s" , TIME-$time) ) {
				 return array("code"=>500 , "msg"=>"验证超时");
			}
			$arr_fields = array(
				"verify_state" => 1 , 
				"verify_retime" => date("Y-m-d H:i:s") , 
			);
			if(empty($obj_rs['verify_user_id']) && cls_obj::get('cls_user')->is_login()==false) {//空用户，则表示非登录情况，自动登录
				$obj_one = cls_obj::db()->get_one("select verify_user_id from " . cls_config::DB_PRE . "sys_verify where verify_val='" . $obj_rs['verify_val'] . "' and (verify_type=4 or verify_type=2) and verify_user_id>0");
				if(!empty($obj_one)) {
					$arr_fields['verify_user_id'] = $obj_one["verify_user_id"];
					//自动登录
					cls_obj::get('cls_user')->on_login(array('user_id'=>$obj_one["verify_user_id"]) , 1);
				} else {
					cls_obj::get('cls_user')->on_login(array('user_name'=>$obj_rs["verify_val"],'user_pwd'=>$obj_rs["verify_val"]) , 2);
				}
				
			}
			cls_obj::db_w()->on_update(cls_config::DB_PRE . "sys_verify" , $arr_fields , $where);
		}
		return array("code"=>0,"msg"=>'',"uid"=>$obj_rs['verify_user_id']);
	}
}