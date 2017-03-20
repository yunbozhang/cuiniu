<?php
/**
 * 用户表操作类
 */
class tab_meal_menu {
	static $perms;
	/*
	 * 饭，汤，菜，套餐，饮料，甜点，
	 */
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"type" => array(
					"不限" => 0,
					"外送" => 1,
					"店内" => 2,
				),
				"attribute" => array(
					"特价" => 1,
					"独家" => 2,
					"新品" => 3,
					"人气" => 4,
					"会员" => 5,
					"限时" => 6,
				),
				"state" => array( "正常" => 1 , "下架" => 0) ,
				"mode" => array( "每天" => 0 , "按星期" => 1 , "按日期" => 3 , "自定义" => 2 ) ,
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function get_type($mode) {
		$arr = array(
			"自选" => 3 ,
			cls_language::get("rice" , 'meal') => 1 ,
			cls_language::get("soup" , 'meal') => 2 ,
			cls_language::get("drink" , 'meal') => 4 ,
			cls_language::get("dessert" , 'meal') => 5 ,
		);
		if($mode == 1) {
			return array("单品" => 6);
		} else if ($mode != 2) {
			return $arr + array("单品" => 6);
		} else {
			return $arr;
		}
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['menu_id'])) {
			$arr_fields['id'] = $arr_fields['menu_id'];
			unset($arr_fields['menu_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " menu_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and menu_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if(isset($arr_fields['menu_title']) && !isset($arr_fields["menu_jian"])) {
			$arr_ping = cls_pinyin::get($arr_fields["menu_title"] , cls_config::DB_CHARSET);
			$arr_fields["menu_jian"] = $arr_ping["style3"];
		}
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['menu_title']) || empty($arr_fields['menu_title'])) {
				$arr_return['code'] = 113;
				$arr_return['msg']  = "菜谱名称不能为空";
				return $arr_return;
			}
			if(!isset($arr_fields['menu_price']) || $arr_fields['menu_price'] <= 0 ) {
				$arr_return['code'] = 113;
				$arr_return['msg']  = "菜品价格不能为空";
				return $arr_return;
			}
			//设置菜品所属区域，方便查找
			if(!isset($arr_fields["menu_area_id"]) && isset($arr_fields["menu_shop_id"]) ) {
				$obj_shop = cls_obj::db()->get_one("select shop_area_id from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $arr_fields["menu_shop_id"] . "'");
				if(!empty($obj_shop)) $arr_fields["menu_area_id"] = $obj_shop["shop_area_id"];
			}
			//初始必要值
			$arr_fields['menu_addtime'] = TIME;
			$arr_fields['menu_updatetime'] = TIME;

			//插入到用户表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."meal_menu",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$obj_rs = $obj_db->get_one("select menu_id from ".cls_config::DB_PRE."meal_menu where menu_title = '" . $arr_fields["menu_title"] . "' and menu_addtime = '" . $arr_fields["menu_addtime"] . "'");
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['menu_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select menu_id from ".cls_config::DB_PRE."meal_menu where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['menu_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "menu_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."meal_menu" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
	/*
	 * 回收站或还原操作
	 * isdel 决定是回收还是还原，1:回收，0:还原
	 */
	static function on_del($arr_id , $isdel = 1) {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if($str_id == ""){
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$arr_fields = array("menu_isdel" => $isdel);
		if(is_numeric($str_id)) {
			$arr_return=cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu",$arr_fields,"menu_id='".$str_id."'");
		} else {
			$arr_return=cls_obj::db_w()->on_update(cls_config::DB_PRE."meal_menu",$arr_fields,"menu_id in(".$str_id.")");
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
			(is_numeric($str_id)) ? $arr_where[] = "menu_id='".$str_id."'" : $arr_where[] = "menu_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."meal_menu" , $where);
		return $arr_return;
	}
	//同步评分
	static function on_tongbu_comment($id) {
		$obj_db = cls_obj::db();
		$obj_rs = $obj_db->get_one("select sum(comment_val) as 'val',count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu_comment where comment_menu_id='" . $id . "'");
		if(empty($obj_rs)) return;
		$val = $obj_rs['val'] / $obj_rs['num'];
		$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_menu set menu_comment='" . $val . "',menu_comment_num='" . $obj_rs['num'] . "' where menu_id='" . $id . "'");
	}
}