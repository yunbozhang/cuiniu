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
		$arr_return = array("list" => array() , "area" => array() );
		$shop_id = $this->shop_info["id"];
		$url_pid = (int)fun_get::get("url_pid");
		$obj_db = cls_obj::db();
		$str_where = " where dispatch_shop_id='" . $shop_id . "'";
		if(!empty($url_pid)) $str_where .= " and " . $obj_db->concat(',','area_pids',',') . " like '%," . $url_pid . ",%'";
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.dispatch" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.dispatch"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$arr_return["list"] = array();
		$arr = explode("," , $arr_cfg_fields["sel"]);
		if(!in_array('area_pids' , $arr)) $arr[] = 'area_pids';
		if(!in_array('area_pid' , $arr)) $arr[] = 'area_pid';
		if(!in_array('area_depth' , $arr)) $arr[] = 'area_depth';
		$arr_dispatch = array();
		$arr_pids = array();
		$str = implode("," , $arr);
		$arr_str = $arr_list = array();
		$obj_result = $obj_db->select("SELECT " . $str . " FROM " . cls_config::DB_PRE."meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on a.dispatch_area_id=b.area_id" . $str_where . $sort);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr = explode(',',$obj_rs['area_pids']);
			if(!empty($arr)) unset($arr[count($arr)-1]);
			$ids = implode("," , $arr);
			if(!empty($arr) && !in_array($ids , $arr_str) && $ids!==(string)$url_pid ) $arr_str[$ids] = $arr;
			$arr_list['id_' . $obj_rs['area_pid']][] = $obj_rs;
		}
		$arr_pids = array();
		$maxkey = 0;
		$minkey = 10;
		for($ii = 0; $ii<10 ; $ii++) {
			$arr = array();
			foreach($arr_str as $item => $key) {
				if(isset($key[$ii]) && !in_array($key[$ii] , $arr)) $arr[] = $key[$ii];
				if(count($key)>$maxkey) $maxkey = count($key);
				if(count($key)<$minkey) $minkey = count($key);
			}
			if(count($arr)>1) {
				if(isset($key[$ii-1])) $url_pid = $key[$ii-1];
				$arr = fun_base::array_remove($arr , $url_pid);
				$arr_pids = $arr;
				break;
			} else if($maxkey == $ii+1) {
				//if( isset($key[$minkey-2]) ) $url_pid = $key[$minkey-2];
				$arr = fun_base::array_remove($arr , $url_pid);
				$arr_pids = $arr;
				break;
			} else if(empty($arr)) {
				break;
			}
			$x = ($ii>0) ? $key[$ii-1] : 0;
			if(isset($arr_list['id_' . $x])) {
				$url_pid = $x;
				$arr_pids = $arr;
				break;
			}
		}
		if(count($arr_pids)>0) {
			$ids = implode("," , $arr_pids);
			$obj_result = $obj_db->select("SELECT area_id,area_name,area_pid FROM " . cls_config::DB_PRE."sys_area where area_id in(" . $ids . ")");
			while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
				$arr_return['area'][] = $obj_rs;
				$url_pid = $obj_rs['area_pid'];
			}
		}
		if(isset($arr_list['id_' . $url_pid])) $arr_return['list'] = $arr_list['id_' . $url_pid];
		$arr_return['pid'] = $url_pid;
		return $arr_return;
	}
	function area_list($get_url_pid) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		$arr_id = array();
		$arr_pids = array();
		$obj_result = $obj_db->select("SELECT dispatch_area_id,area_pids FROM " . cls_config::DB_PRE . "meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on a.dispatch_area_id=b.area_id where dispatch_shop_id='" . $this->shop_info["id"] . "'");
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_id[] = $obj_rs["dispatch_area_id"];
			$arr_pids[] = $obj_rs['area_pids'];
		}
		$arr_pids = array_unique(explode("," , implode("," , $arr_pids)));
		$str_where = " where area_pid='" . $get_url_pid . "'";
		if(count($arr_id) > 0 ) $str_where .= " and area_id not in(" . implode(",", $arr_id) . ")";
		$obj_result = $obj_db->select("SELECT * FROM ".cls_config::DB_PRE."sys_area" . $str_where);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pids'] = $arr_pids;
		return $arr_return;
	}
	function on_add_save() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$get_url_pid = (int)fun_get::get("url_pid");
		$arr_area_id[] = fun_get::get("id");
		$arr_get_area_id = fun_get::post("selid");
		$shop_id = $this->shop_info["id"];
		$obj_db = cls_obj::db_w();
		if( $shop_id < 0 ) $shop_id = 0;
		if(!empty($arr_get_area_id) && count($arr_get_area_id)>0) $arr_area_id = $arr_get_area_id;
		foreach($arr_area_id as $item) {
			$arr_fields = array(
				"dispatch_area_id" => $item,
				"dispatch_shop_id" => $shop_id,
				"dispatch_pid" => $get_url_pid
			);
			$arr = tab_meal_dispatch::on_save($arr_fields);
			if( $arr["code"] != 0 ) {
				return $arr;
			}
		}
		$arr_return['id'] = $arr_fields['dispatch_area_id'];
		return $arr_return;
	}
	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 ,"id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_dispatch_id = fun_get::post("dispatch_id");
		if(fun_is::set("dispatch_price")) $arr_dispatch_price = fun_get::post("dispatch_price");
		if(fun_is::set("dispatch_addprice")) $arr_dispatch_addprice = fun_get::post("dispatch_addprice");
		if(fun_is::set("dispatch_number")) $arr_dispatch_number = fun_get::post("dispatch_number");
		if(fun_is::set("dispatch_time")) $arr_dispatch_time = fun_get::post("dispatch_time");
		if(fun_is::set("dispatch_smstel")) $arr_dispatch_smstel = fun_get::post("dispatch_smstel");
		$arr_resave = array();
		$lng_count = count($arr_dispatch_id);
		for( $i = 0 ; $i < $lng_count ; $i++) {
			$arr_fields = array(
				"dispatch_id" => (int)$arr_dispatch_id[$i],
			);
			if(isset($arr_dispatch_price)) $arr_fields["dispatch_price"] = $arr_dispatch_price[$i];
			if(isset($arr_dispatch_addprice)) $arr_fields["dispatch_addprice"] = $arr_dispatch_addprice[$i];
			if(isset($arr_dispatch_time)) $arr_fields["dispatch_time"] = $arr_dispatch_time[$i];
			if(isset($arr_dispatch_number)) $arr_fields["dispatch_number"] = $arr_dispatch_number[$i];
			if(isset($arr_dispatch_smstel)) $arr_fields["dispatch_smstel"] = $arr_dispatch_smstel[$i];
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
		$pid = fun_get::get("pid");
		if( empty($arr_id) && empty($str_id) && empty($pid) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		$where = '';
		$arr_return['id'] = $str_id;
		if(!empty($pid)) {
			$where = "dispatch_area_id in(select area_id from " . cls_config::DB_PRE . "sys_area where " . $obj_db->concat(',','area_pids',',') . " like '%," . $pid . ",%')";
			$arr_return['id'] = $pid;
		}

		$arr = tab_meal_dispatch::on_delete($str_id , $where);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	function get_path($pid , $topid = 0) {
		$arr_return = array();
		if($pid == $topid) return $arr_return;
		$str_sql="select area_id,area_name,area_pid from " . cls_config::DB_PRE . "sys_area where area_id='".$pid."'";
		$obj_result = cls_obj::db()->query($str_sql);
		if($obj_rs = cls_obj::db()->fetch_array($obj_result))	{
			$arr_return[] = $obj_rs;
			$arr = $this->get_path($obj_rs['area_pid'] , $topid);
			if(count($arr)>0) {
				$arr_return = array_merge($arr , $arr_return);
			}
		}
		return $arr_return;
	}
}