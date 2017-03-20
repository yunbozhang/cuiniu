<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_dispatch extends inc_mod_shop {

	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件
	 */
	function sql_list( ) {
		$arr_return = array("list" => array());
		$lng_pid = (int)fun_get::get("url_pid");
		$shop_id = $this->shop_info["id"];
		if( $shop_id < 0 ) $shop_id = 0;
		$str_where = " where dispatch_shop_id='" . $shop_id . "' and dispatch_pid='" . $lng_pid . "'";
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.dispatch" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.dispatch"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$arr_return["list"] = array();
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on a.dispatch_area_id=b.area_id" . $str_where . $sort);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		return $arr_return;
	}
	function area_list() {
		$arr_return = array("list" => array());
		$get_url_area_pid = (int)fun_get::get("url_area_pid");
		$obj_db = cls_obj::db();
		$arr_id = array();
		$obj_result = $obj_db->select("SELECT dispatch_area_id FROM " . cls_config::DB_PRE . "meal_dispatch where dispatch_shop_id='" . $this->shop_info["id"] . "'");
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_id[] = $obj_rs["dispatch_area_id"];
		}
		$lng_shop_area_id = -1;
		if(empty($get_url_area_pid)) {
			$shop_id = $this->shop_info["id"];
			if( $shop_id < 0 ) $shop_id = 0;
			if(!empty($shop_id)) {//取店铺id
				$obj_rs = $obj_db->get_one("SELECT shop_area_id FROM ".cls_config::DB_PRE."meal_shop where shop_id='" . $shop_id . "'");
				if(!empty($obj_rs)) {
					$lng_shop_area_id = $obj_rs["shop_area_id"];
				}
			} else {
				$lng_shop_area_id = (int)cls_config::get("area_default_id","meal");//福田区

			}
		} else {
			$lng_shop_area_id = $get_url_area_pid;
		}
		$str_where = " where area_pid='" . $lng_shop_area_id . "'";
		if(count($arr_id) > 0 ) $str_where .= " and area_id not in(" . implode(",", $arr_id) . ")"; 
		$obj_result = $obj_db->select("SELECT * FROM ".cls_config::DB_PRE."sys_area" . $str_where);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		return $arr_return;
	}
	function on_add_save() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$get_url_pid = (int)fun_get::get("url_pid");
		$arr_area_id[] = fun_get::get("id");
		$arr_get_area_id = fun_get::post("selid");
		$shop_id = $this->shop_info["id"];
		$obj_db = cls_obj::db_w();
		$shop_dispatch = array();
		//同步到店铺shop_dispatch
		$obj_shop = $obj_db->get_one("select shop_dispatch from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(!empty($obj_shop) && !empty($obj_shop['shop_dispatch'])) {
			$shop_dispatch = explode("," , $obj_shop["shop_dispatch"]);
		}

		if( $shop_id < 0 ) $shop_id = 0;
		if(!empty($arr_get_area_id) && count($arr_get_area_id)>0) $arr_area_id = $arr_get_area_id;
		foreach($arr_area_id as $item) {
			$arr_fields = array(
				"dispatch_area_id" => $item,
				"dispatch_shop_id" => $shop_id,
				"dispatch_pid" => $get_url_pid
			);
			if(!in_array($item , $shop_dispatch)) $shop_dispatch[] = $item;
			$arr = tab_meal_dispatch::on_save($arr_fields);
			if( $arr["code"] != 0 ) {
				return $arr;
			} else {
				//添加子项
				$obj_result = $obj_db->select("select area_id from " . cls_config::DB_PRE ."sys_area where area_pid='" . $item . "'");
				while($obj_rs = $obj_db->fetch_array($obj_result)) {
					$arr_fields = array(
						"dispatch_area_id" => $obj_rs["area_id"],
						"dispatch_shop_id" => $shop_id,
						"dispatch_pid" => $arr["id"]
					);
					$arr_n = tab_meal_dispatch::on_save($arr_fields);
				}
			}
		}
		$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_dispatch='" . implode(",",$shop_dispatch) . "' where shop_id='" . $shop_id . "'");
		return $arr_return;
	}
	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_dispatch_id = fun_get::post("dispatch_id");
		if(fun_is::set("dispatch_price")) $arr_dispatch_price = fun_get::post("dispatch_price");
		if(fun_is::set("dispatch_number")) $arr_dispatch_number = fun_get::post("dispatch_number");
		if(fun_is::set("dispatch_time")) $arr_dispatch_time = fun_get::post("dispatch_time");
		$arr_resave = array();
		$lng_count = count($arr_dispatch_id);
		for( $i = 0 ; $i < $lng_count ; $i++) {
			$arr_fields = array(
				"dispatch_id" => (int)$arr_dispatch_id[$i],
			);
			if(isset($arr_dispatch_price)) $arr_fields["dispatch_price"] = $arr_dispatch_price[$i];
			if(isset($arr_dispatch_number)) $arr_fields["dispatch_number"] = $arr_dispatch_number[$i];
			if(isset($arr_dispatch_time)) $arr_fields["dispatch_time"] = $arr_dispatch_time[$i];
			$arr = tab_meal_dispatch::on_save($arr_fields);
			if($arr["code"] != 0) {
				$arr_return['code'] = $arr["code"];
				$arr_return['msg'] = $arr["msg"];
				return $arr_return;
			}
		}
		return $arr_return;
	}
	/* 删除指定  dispatch_id 数据
	 */
	function on_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		$shop_id = $this->shop_info["id"];
		$obj_db = cls_obj::db_w();

		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}

		$shop_dispatch = array();
		//同步到店铺shop_dispatch
		$obj_shop = $obj_db->get_one("select shop_dispatch from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(!empty($obj_shop) && !empty($obj_shop['shop_dispatch'])) {
			$shop_dispatch = explode("," , $obj_shop["shop_dispatch"]);
		}

		$arr_area_id = array();
		$obj_result = $obj_db->select("select dispatch_area_id from " . cls_config::DB_PRE . "meal_dispatch where dispatch_id in (" . fun_format::arr_id($str_id) . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_area_id[] = $obj_rs["dispatch_area_id"];
		}

		$arr = tab_meal_dispatch::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		} else {
			$count = count($shop_dispatch);
			for($i = 0 ; $i < $count ; $i++) {
				if( in_array($shop_dispatch[$i], $arr_area_id ) ) {
					array_splice($shop_dispatch , $i , 1);
				}
			}
			$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_dispatch='" . implode(",",$shop_dispatch) . "' where shop_id='" . $shop_id . "'");
		}
		return $arr_return;
	}
	function get_path($id) {
		if(empty($id)) return "";
		$str_val = "";
		$str_sql="select dispatch_id,dispatch_area_id,area_name,dispatch_pid from ".cls_config::DB_PRE . "meal_dispatch a , " . cls_config::DB_PRE . "sys_area b where a.dispatch_area_id=b.area_id and a.dispatch_id='".$id."'";
		$obj_result = cls_obj::db()->query($str_sql);
		if($obj_rs = cls_obj::db()->fetch_array($obj_result))	{
			$str_val = " -> <a href=\"javascript:shop.refresh('url_pid=" .  $obj_rs['dispatch_id'] . "&url_area_pid=" . $obj_rs['dispatch_area_id'] . "');\">" . $obj_rs['area_name'] . "</a>";
			$val = $this->get_path($obj_rs['dispatch_pid']);
			if(!empty($val)) $str_val = $val . $str_val;
		}
		return $str_val;
	}

}