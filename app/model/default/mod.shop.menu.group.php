<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_menu_group extends inc_mod_shop {

	function sql_list() {
		$arr_return = array("list" => array());
		$str_where = " where group_shop_id='" . $this->shop_info["id"] . "'";
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.menu.group" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.menu.group"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_return["list"] = array();
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_menu_group" . $str_where . $sort);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		return $arr_return;
	}

	function on_save() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_group_id = fun_get::get("group_id");
		if(fun_is::set("group_sort")) $arr_group_sort = fun_get::get("group_sort");
		if(fun_is::set("group_name")) $arr_group_name = fun_get::get("group_name");
		$get_url_pid = (int)fun_get::get("url_pid");
		$arr_resave = array();
		$lng_count = count($arr_group_id);
		for( $i = 1 ; $i < $lng_count ; $i++) {
			$arr_fields = array(
				"group_id" => (int)$arr_group_id[$i],
				"group_shop_id" => $this->shop_info["id"],
			);
			if(isset($arr_group_sort)) $arr_fields["group_sort"] = $arr_group_sort[$i];
			if(isset($arr_group_name)) $arr_fields["group_name"] = $arr_group_name[$i];
			//不直接修改 pid,只在新增时保存 pid
			if( $arr_fields["group_id"]<1 ) {
				$arr_fields["group_pid"] = $get_url_pid;
			}
			$arr = tab_meal_menu_group::on_save($arr_fields);
			if($arr["code"] != 0) {
				$obj_db->rollback("meal_menu_group");
				$arr_return['code'] = $arr["code"];
				$arr_return['msg'] = $arr["msg"];
				return $arr_return;
			}
		}
		return $arr_return;
	}

	function on_delete() {
		$id = (int)fun_get::get("id");
		$arr_msg = cls_obj::db_w()->on_exe("delete from " . cls_config::DB_PRE . "meal_menu_group where group_shop_id='" . $this->shop_info["id"]  . "' and group_id='" . $id . "'");
		$arr_msg['id'] = $id;
		return $arr_msg;
	}
}