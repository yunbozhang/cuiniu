<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_quan extends inc_mod_common {
	//取地区
	function get_category($pid) {
		$arr_return = array("list" => array() , "id" => $pid , "depth" => 0);
		$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "quan_category where category_pid='" . $pid . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_return['depth'] = $obj_rs['category_depth'];
			$arr_return['list'][] = $obj_rs;
		}
		return $arr_return;
	}

	function get_category_sel($pids) {
		$obj_db = cls_obj::db();
		$arr = array();
		if(empty($pids)) $pids = '0';
		$obj_result = $obj_db->select("select category_pids from " . cls_config::DB_PRE . "quan_category where category_id in(" . $pids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr[] = $obj_rs['category_pids'];
		}
		$ids = implode("," , $arr);
		$arr = explode("," , $ids);
		$arr = array_unique($arr);
		return $arr;
	}

}