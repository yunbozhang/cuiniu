<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_weixin_user {
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
		if(!isset($arr_fields['user_openid']) || empty($arr_fields['user_openid'])) return array('code' => 500 , 'msg' => '微信openid不能为空');
		$obj_db = cls_obj::db_w();
		$obj_user = $obj_db->get_one("select user_openid from " . cls_config::DB_PRE . "weixin_user where user_openid='" . $arr_fields['user_openid'] . "'");
		if(empty($obj_user)) {
			$arr_return = cls_obj::db_w()->on_insert(cls_config::DB_PRE."weixin_user",$arr_fields);
		} else {
			$arr_return = cls_obj::db_w()->on_update(cls_config::DB_PRE."weixin_user",$arr_fields,"user_openid='" . $arr_fields['user_openid'] . "'");
		}
		return $arr_return;
	}
}