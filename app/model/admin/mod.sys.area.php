<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_sys_area extends inc_mod_admin {

	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件
	 */
	function sql_list( $pid = 0 ) {
		$arr_return = array("list" => array());
		$str_where = " where area_pid='" . $pid . "'";
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("sys.area" , $this->app_dir , "sys");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("sys.area"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_return["list"] = array();
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."sys_area" . $str_where . $sort);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		return $arr_return;
	}
	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_area_id = fun_get::get("area_id");
		if(fun_is::set("area_sort")) $arr_area_sort = fun_get::get("area_sort");
		if(fun_is::set("area_name")) $arr_area_name = fun_get::get("area_name");
		if(fun_is::set("area_val")) $arr_area_val = fun_get::get("area_val");
		if(fun_is::set("area_tag")) $arr_area_tag = fun_get::get("area_tag");
		if(fun_is::set("area_pin")) $arr_area_pin = fun_get::get("area_pin");
		if(fun_is::set("area_jian")) $arr_area_jian = fun_get::get("area_jian");
		if(fun_is::set("area_pic")) $arr_area_pic = fun_get::get("area_pic");
		if(fun_is::set("area_position_lng")) $arr_area_position_lng = fun_get::get("area_position_lng");
		if(fun_is::set("area_position_lat")) $arr_area_position_lat = fun_get::get("area_position_lat");
		$get_url_pid = (int)fun_get::get("url_pid");
		$arr_resave = array();
		$lng_count = count($arr_area_id);

		for( $i = 1 ; $i < $lng_count ; $i++) {
			$arr_fields = array(
				"area_id" => (int)$arr_area_id[$i],
			);
			if(isset($arr_area_sort)) $arr_fields["area_sort"] = $arr_area_sort[$i];
			if(isset($arr_area_name)) $arr_fields["area_name"] = $arr_area_name[$i];
			if(isset($arr_area_val)) $arr_fields["area_val"] = $arr_area_val[$i];
			if(isset($arr_area_tag)) $arr_fields["area_tag"] = $arr_area_tag[$i];
			if(isset($arr_area_pin)) $arr_fields["area_pin"] = $arr_area_pin[$i];
			if(isset($arr_area_jian)) $arr_fields["area_jian"] = $arr_area_jian[$i];
			if(isset($arr_area_pic)) $arr_fields["area_pic"] = $arr_area_pic[$i];
			if(isset($arr_area_position_lng)) $arr_fields["area_position_lng"] = $arr_area_position_lng[$i];
			if(isset($arr_area_position_lat)) $arr_fields["area_position_lat"] = $arr_area_position_lat[$i];
			//不直接修改 pid,只在新增时保存 pid
			if( $arr_fields["area_id"]<1 ) {
				$arr_fields["area_pid"] = $get_url_pid;
			}
			if(empty($arr_fields['area_pin'])) {
				$words_val = (empty($arr_area_name[$i])) ? $arr_area_val[$i] : $arr_area_name[$i];
				$arr_ping = cls_pinyin::get($words_val , cls_config::DB_CHARSET);
				$arr_fields["area_pin"] = $arr_ping["style2"];
				$arr_fields["area_jian"] = $arr_ping["style3"];
			}
			$arr = tab_sys_area::on_save($arr_fields);
			if($arr["code"] != 0) {
				$obj_db->rollback("sys_area");
				$arr_return['code'] = $arr["code"];
				$arr_return['msg'] = $arr["msg"];
				return $arr_return;
			}
		}
		return $arr_return;
	}
	function get_path($pid) {
		$str_val = "";
		$str_sql="select area_id,area_name,area_pid from " . cls_config::DB_PRE . "sys_area where area_id='".$pid."'";
		$obj_result = cls_obj::db()->query($str_sql);
		if($obj_rs = cls_obj::db()->fetch_array($obj_result))	{
			$str_val = " -> <a href=\"javascript:kj.set('#id_url_pid','value','" . $obj_rs['area_id'] . "');admin.refresh();\">" . $obj_rs['area_name'] . "</a>";
			$str_val = $this->get_path($obj_rs['area_pid']) . $str_val;
		}
		return $str_val;
	}
	/* 删除指定  area_id 数据
	 */
	function on_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_sys_area::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
}