<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_meal_shop {
	static $perms;
	static $value;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"state" => array( "运营中" => 1 , cls_language::get("wait_check") => 0 , "暂停运营" => -1) ,
				"sms" => array( "关闭来单短信" => 0 , "启用新单提醒短信" => 1 , "启用新单详情短信" => 2) ,
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['shop_id'])) {
			$arr_fields['id'] = $arr_fields['shop_id'];
			unset($arr_fields['shop_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " shop_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and shop_id='" . $arr_return['id'] . "'";
				}
			}
		}
		if(isset($arr_fields["shop_extend"])) $arr_fields["shop_extend"] = serialize($arr_fields["shop_extend"]);
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['shop_name']) || $arr_fields['shop_name'] == '' ) {
				$arr_return['code'] = 113;
				$arr_return['msg']  = cls_language::get("shop_name_is_null" , "meal");//店铺名称不能为空
				return $arr_return;
			}

			//初始必要值
			$arr_fields['shop_addtime'] = $arr_fields['shop_updatetime'] = TIME;

			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."meal_shop",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$obj_rs = $obj_db->get_one("select shop_id from ".cls_config::DB_PRE."meal_shop where shop_addtime='" . $arr_fields["shop_addtime"] . " and shop_name='" . $arr_fields["shop_name"] . "'");
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['shop_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select shop_id from ".cls_config::DB_PRE."meal_shop where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['shop_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "shop_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."meal_shop" , $arr_fields , $where);
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
			(is_numeric($str_id)) ? $arr_where[] = "shop_id='".$str_id."'" : $arr_where[] = "shop_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."meal_shop" , $where);
		return $arr_return;
	}
	/* 
	 * shopid : 店铺id , var : 变量名称 , shop_extend : 当指定此值，则直接取些值
	 */
	static function get_extend($shopid , $var , $shop_extend = '') {
		$arr_extend = array();
		if(empty($shop_extend)) {
			if(empty(self::$value) || !isset(self::$value['extend_'.$shopid])) {
				$obj_shop = cls_obj::db()->get_one("select shop_extend from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shopid . "'");
				if(!empty($obj_shop)) $arr_extend = unserialize($obj_shop["shop_extend"]);
				self::$value['extend_'.$shopid] = $arr_extend;
			} else {
				$arr_extend = self::$value['extend_'.$shopid];
			}
		} else {
			$arr_extend = unserialize($shop_extend);
			self::$value['extend_'.$shopid] = $arr_extend;
		}
		if(isset($arr_extend[$var])) {
			return $arr_extend[$var];
		} else {
			return '';
		}

	}
	/* 取店铺开放时间
	 * shopid : 店铺id , shop_extend : 当指定此值，则直接取些值
	 */
	static function get_opentime($shop_id , $shop_extend = '') {
		$str_opentime = self::get_extend($shop_id , "opentime" , $shop_extend);
		$arr_return = array("list" => array() );
		if(empty($str_opentime)) {
			$arr = array();
		} else {
			$str_opentime = str_replace( chr(13) , chr(10) , $str_opentime);
			$str_opentime = str_replace( chr(10).chr(10) , chr(10) , $str_opentime);
			$arr = explode(chr(10) , $str_opentime);
		}
		$index = $nextindex = 1;
		$nowtime = (int)date("Hi");
		$nowindex = 0;
		foreach($arr as $item) {
			$arr_1 = array("title" => "" );
			$arr_1["index"] = $index;
			$arr_x = explode("=&gt;" , $item);
			if(count($arr_x)>1) $arr_1["title"] = $arr_x[1];
			$arr_x = explode("," , $arr_x[0]);
			$arr_1["start"] = (int)$arr_x[0];
			$arr_1["end"] = (count($arr_x)>1)? (int)$arr_x[1] : 0;
			$arr_return["list"]['id_'.$index] = $arr_1;
			if($arr_1['start']<=$nowtime && $nowtime<=$arr_1['end']) $nowindex = $index;
			if($arr_1['start']<=$nowtime) $nextindex++;
			$index++;
		}
		$arr_return["nowindex"] = $nowindex;
		if($nowindex>0) {
			$arr_return["havenext"] = 0;//开放订餐状态
		} else if($nextindex==$index) {
			$arr_return["havenext"] = -1;//即将开始
		} else {
			$arr_return["havenext"] = 1;//明天继续
		}
		return $arr_return;
	}
}