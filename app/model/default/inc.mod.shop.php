<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class inc_mod_shop extends inc_mod_default {
	function __construct($arr_v = array() ) {
		//是否登录
		if(!cls_obj::get("cls_user")->is_login()) {
			if(isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"]  , $_SERVER['REQUEST_URI'])) {
				fun_base::url_jump(cls_config::get('url' , 'base'));
			}
			cls_error::on_error("no_login");
		}
		//是否为管理员
		if(cls_obj::get("cls_user")->type!='shop' && cls_obj::get("cls_user")->is_admin()==false) {
			cls_error::on_error("no_limit" , "您不是店主，没有店铺管理权限");
		}
		parent::__construct($arr_v);
		//权限控制
		$this->this_limit = new cls_limit("shop");
		if( !$this->this_limit->chk_mod("shop") && $this->app_act!='default') {
			cls_error::on_error("no_limit");
		}
		if( !$this->this_limit->chk_app($this->app , "shop")  && $this->app_act!='default') {
			cls_error::on_error("no_limit");
		}
		if( !$this->this_limit->chk_act($this->app_act , $this->app , "shop")  && $this->app_act!='default') {
			cls_error::on_error("no_limit");
		}
		$obj_shop = cls_obj::db()->get_one("select shop_id,shop_name,shop_state,shop_progress,shop_mode,site_id,site_wx_certify from " . cls_config::DB_PRE . "meal_shop a left join " . cls_config::DB_PRE . "weixin_site b on a.shop_id=b.site_shop_id where shop_user_id='" . cls_obj::get("cls_user")->uid . "'");
		$arr_info = array("id"=>'', "name"=>'','state'=>'','shop_state'=>0 ,'progress'=>array(),'shop_mode'=>0);
		if(!empty($obj_shop)) {
			$arr_info["id"] = $obj_shop["shop_id"];
			$arr_info["name"] = $obj_shop["shop_name"];
			$arr_info["shop_mode"] = $obj_shop["shop_mode"];
			$arr_info["shop_state"] = $obj_shop["shop_state"];
			$arr_info["state"] =  array_search($obj_shop["shop_state"] , tab_meal_shop::get_perms("state"));
			$arr_info["site_id"] = $obj_shop['site_id'];
			$arr_info["certify"] = $obj_shop['site_wx_certify'];
			if(!empty($obj_shop["shop_progress"])) $arr_info["progress"] = explode("," , $obj_shop["shop_progress"]);
		}
		$this->shop_info = $arr_info;
	}
	/*
	 * 获取状态样式
	 */
	function get_state_style($val) {
		$style = "";
		if($val < 1) {
			$style = " style='color:#ff0000'";
		}
		return $style;
	}

}