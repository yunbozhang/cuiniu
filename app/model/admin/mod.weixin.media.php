<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_weixin_media extends inc_mod_weixin {
	function get_media_list($type) {
		$obj_db = cls_obj::db();
		$arr_list = array();
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "weixin_media where media_type='" . $type . "'");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['size'] = fun_format::size($obj_rs['media_size']);
			$obj_rs['name'] = basename($obj_rs['media_file']);
			$obj_rs['url'] = fun_get::html_url($obj_rs['media_file']);
			$obj_rs['addtime'] = date('Y-m-d H:i' , $obj_rs['media_time']);
			$arr_list[] = $obj_rs;
		}
		return $arr_list;
	}
}