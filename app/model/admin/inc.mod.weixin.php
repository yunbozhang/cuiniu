<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class inc_mod_weixin extends inc_mod_admin {
	function __construct($arr_v) {
		parent::__construct($arr_v);
		$this->weixin_site = $this->get_weixin_site();
	}
	//获取当前管理的店铺信息
	function get_weixin_site() {
		$arr_return = array("id"=>0 , "name" => "默认" , "mode" => 0);
		//是否指定店铺
		$site_id = (int)fun_get::get("url_site_id");
		if(empty($site_id)) {
			$site_id = intval(cls_session::get_cookie("weixin_site_id"));
		} else {
			cls_session::set_cookie("weixin_site_id" , $site_id);//将当前选择保有到cookie
		}
		//检查区域权限
		$limit_area = $this->this_limit->get_perms('limit_area');
		if(!empty($limit_area) && !empty($site_id) && cls_config::get('module','version','','') == 'meal_mall') {
			$arr = explode("," , $limit_area);
			$arr_x = array();
			foreach($arr as $areaid) {
				$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
			}
			$str_where = "(" . implode(" or " , $arr_x) . ") and site_id='" . $site_id . "'";
			$obj_site = cls_obj::db()->get_one("select site_id from " . cls_config::DB_PRE . "weixin_site a inner join " . cls_config::DB_PRE . "meal_shop b on a.site_shop_id=b.shop_id where" . $str_where);
			if(empty($obj_site)) $site_id = -999;
		}
		if(!empty($site_id) && $site_id>0) {
			$obj_rs = cls_obj::db()->get_one("select site_id,shop_name from " . cls_config::DB_PRE . "weixin_site a left join " . cls_config::DB_PRE . "meal_shop b on a.site_shop_id=b.shop_id where site_id='" . $site_id . "'");
			if(!empty($obj_rs)) {
				$arr_return["id"] = $obj_rs["site_id"];
				$arr_return["name"] = $obj_rs["shop_name"];
			} else {
				$arr_return["id"] = -1;
				$arr_return["name"] = "";
			}
		} else if($site_id == -999) {//所有
				$arr_return["id"] = -999;
				$arr_return["name"] = "所有";
		}
		return $arr_return;
	}

}