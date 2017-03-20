<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_quan_category extends inc_mod_admin {

	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件
	 */
	function sql_list( $pid = 0 ) {
		$arr_return = array("list" => array());
		$str_where = " where category_pid='" . $pid . "'";
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("quan.category" , $this->app_dir , "quan");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("quan.category"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_return["list"] = array();
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."quan_category" . $str_where . $sort);
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
		$arr_category_id = fun_get::get("category_id");
		if(fun_is::set("category_sort")) $arr_category_sort = fun_get::get("category_sort");
		if(fun_is::set("category_name")) $arr_category_name = fun_get::get("category_name");
		$get_url_pid = (int)fun_get::get("url_pid");
		$arr_resave = array();
		$lng_count = count($arr_category_id);

		for( $i = 1 ; $i < $lng_count ; $i++) {
			$arr_fields = array(
				"category_id" => (int)$arr_category_id[$i],
			);
			if(isset($arr_category_sort)) $arr_fields["category_sort"] = $arr_category_sort[$i];
			if(isset($arr_category_name)) $arr_fields["category_name"] = $arr_category_name[$i];
			//不直接修改 pid,只在新增时保存 pid
			if( $arr_fields["category_id"]<1 ) {
				$arr_fields["category_pid"] = $get_url_pid;
			}
			$arr = tab_quan_category::on_save($arr_fields);
			if($arr["code"] != 0) {
				$arr_return['code'] = $arr["code"];
				$arr_return['msg'] = $arr["msg"];
				return $arr_return;
			}
		}
		return $arr_return;
	}
	function get_path($pid) {
		$str_val = "";
		$str_sql="select category_id,category_name,category_pid from " . cls_config::DB_PRE . "quan_category where category_id='".$pid."'";
		$obj_result = cls_obj::db()->query($str_sql);
		if($obj_rs = cls_obj::db()->fetch_array($obj_result))	{
			$str_val = " -> <a href=\"javascript:kj.set('#id_url_pid','value','" . $obj_rs['category_id'] . "');admin.refresh();\">" . $obj_rs['category_name'] . "</a>";
			$str_val = $this->get_path($obj_rs['category_pid']) . $str_val;
		}
		return $str_val;
	}
	/* 删除指定  category_id 数据
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
		$arr = tab_quan_category::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
}