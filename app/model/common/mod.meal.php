<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal extends inc_mod_common {

	/* 取当前系统所有用户
	 * 
	 */
	function get_shop1() {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		$arr_where = array();
		$arr_where_s = array();
		$lng_issearch = 0;
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info(".meal.shop1"  , $this->app_dir , 0 , array('pagesize' => 50) );
		$lng_pagesize = $arr_config_info["pagesize"];
		//取分页信息
		$str_where = "";
		$lng_page = (int)fun_get::get("page");
		$sort = " order by shop_id desc";
		//取查询参数
		$arr_search_key = array(
			'key' => fun_get::get("s_key"),
		);
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "shop_name like '%" . $arr_search_key['key'] . "%' or shop_linkname like '%" . $arr_search_key['key'] . "%' or shop_tel like '%" . $arr_search_key['key'] . "%' or shop_linktel like '%" . $arr_search_key['key'] . "%' or shop_area like '%" . $arr_search_key['key'] . "%' or shop_address like '%" . $arr_search_key['key'] . "%'"; 
		$arr_where = array_merge($arr_where , $arr_where_s);

		//管理员分地区管理
		$obj_limit = new cls_limit($this->app_dir);
		$limit_area = $obj_limit->get_perms('limit_area');
		if(!empty($limit_area)) {
			$arr = explode("," , $limit_area);
			$arr_x = array();
			foreach($arr as $areaid) {
				$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
			}
			$arr_where[] = '(' . implode(" or " , $arr_x) . ')';
		}

		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);

		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_shop" , $str_where , $lng_page , $lng_pagesize);

		$obj_result = $obj_db->select("SELECT shop_id,shop_name FROM ".cls_config::DB_PRE."meal_shop" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;

	}

}