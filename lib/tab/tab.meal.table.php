<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_meal_table {
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
		if(isset($arr_fields['table_id'])) {
			$arr_fields['id'] = $arr_fields['table_id'];
			unset($arr_fields['table_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " table_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and table_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['table_name']) || empty($arr_fields['table_name'])) return array("code" => 500 , "msg" => "分类名称不能为空");
			
			//初始默认值
			$arr_fields['table_addtime'] = $arr_fields['table_updatetime'] = TIME;
			if(!isset($arr_fields['table_sort']) || empty($arr_fields['table_sort'])) {
				$obj_rs = $obj_db->get_one("select max(table_sort) as sort from " . cls_config::DB_PRE . "meal_table where table_shop_id='" . $arr_fields['table_shop_id'] . "'");
				(!empty($obj_rs))? $arr_fields['table_sort'] = $obj_rs["sort"] + 1 : $arr_fields['table_sort'] = 1;
			}
			if(!isset($arr_fields['table_number']) || empty($arr_fields['table_number'])) $arr_fields['table_number'] = TIME . rand(1000,9999);
			//插入到用户表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."meal_table",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$where  = "table_sort='" . $arr_fields['table_sort'] . " and table_addtime=" . $arr_fields['table_addtime'] . " and table_name='".$arr_fields['table_name'] . "'";
					$obj_rs = $obj_db->get_one("select table_id from ".cls_config::DB_PRE."meal_table where ".$where);
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['table_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select table_id from ".cls_config::DB_PRE."meal_table where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['table_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "table_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."meal_table" , $arr_fields , $where);
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
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "table_id='".$str_id."'" : $arr_where[] = "table_id in(".$str_id.")";
		}
		$obj_db = cls_obj::db_w();
		$where = implode(" and " , $arr_where);
		$arr_return=$obj_db->on_delete(cls_config::DB_PRE."meal_table" , $where);
		return $arr_return;
	}
	static function get_state($shop_id , $ids , $datetime = '') {
		$arr = array("可预订" => 0 , "用餐中" => 1 , "已预订" => 2);
		if(empty($datetime)) $datetime = date("Y-m-d H:i:s");
		if(empty($ids)) return array();
		if(is_array($ids)) $ids = implode("," , $ids);
		$arr_return = array();
		$arr_id = explode("," , $ids);
		foreach($arr_id as $item) {
			$arr_return['id_'.$item] = 0;
		}
		$obj_db = cls_obj::db();
		$nowtime = strtotime($datetime);
		$obj_shop = $obj_db->get_one("select shop_reserve_time from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(empty($obj_shop)) return array();
		if(empty($obj_shop['shop_reserve_time'])) $obj_shop['shop_reserve_time'] = 120;
		$starttime = $nowtime - $obj_shop['shop_reserve_time']*60;
		$endtime = $nowtime + $obj_shop['shop_reserve_time']*60;
		$obj_result = $obj_db->select("select reserve_state,reserve_addtime,reserve_datetime,reserve_id,reserve_table_id,order_id from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_order b on a.reserve_id=b.order_reserve_id where  reserve_state>=0 and reserve_state<10 and reserve_table_id in(" . $ids . ")");
		while ($obj_rs = $obj_db->fetch_array($obj_result)) {
			if($obj_rs['reserve_state'] == 0 && TIME-$obj_rs['reserve_addtime']>300) continue;
			$time = strtotime($obj_rs["reserve_datetime"]);
			if($time>$starttime && $time <= $endtime) {
				if(TIME > $nowtime) {
					$state = 1;
				} else {
					$state = 2;
				}
			} else {
				continue;
			}
			$arr_return['id_' . $obj_rs['reserve_table_id']] = array("state" => $state , "oid" => $obj_rs['order_id'],"rid" => $obj_rs['reserve_id']);
		}
		return $arr_return;
	}
}