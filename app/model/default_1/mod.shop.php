<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop extends inc_mod_shop {
	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo() {
		$get_url_type = fun_get::get("url_type");

		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."meal_shop" , "shop_id='".$this->shop_info["id"]."'");
		$obj_rs["extend"] = array( "arrivedelay" => "" , "arrivetime" => "" , "opentime" => "");
		if(!empty($obj_rs["shop_extend"])) $obj_rs["extend"] = unserialize($obj_rs["shop_extend"]);
		$obj_rs["area"] = fun_kj::get_area();
		return $obj_rs;
	}
	/* 取首页统计信息
	 *
	 */
	function get_count_info() {
		$arr_return = array();
		$obj_db = cls_obj::db();
		$today_time = strtotime(date("Y-m-d"));
		//总菜品数
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu where menu_state>=0 and menu_isdel=0 and menu_shop_id='" . $this->shop_info["id"] . "'");
		$arr_return["menu_num"] = $obj_rs["num"];
		//当天经营菜品数
		$where = $this->get_menu_today_where($this->shop_info["id"]);
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu " . $where);
		$arr_return["menu_today_num"] = $obj_rs["num"];
		//今日总订单
		$obj_rs = $obj_db->get_one("select count(1) as 'num',sum(order_total_pay) as 'total' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_addtime>'" . $today_time . "'");
		$arr_return["order_today"] = $obj_rs["num"];
		$arr_return["order_money"] = (empty($obj_rs["total"])) ? 0 : $obj_rs["total"];
		//今日未处理订单
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_addtime>'" . $today_time . "' and order_state=0");
		$arr_return["order_noconfirm"] = (empty($obj_rs["num"])) ? 0 : $obj_rs["num"];
		//回头客
		$arr_uid = array();
		$obj_result = $obj_db->select("select order_user_id from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_addtime>='" . $today_time . "' and order_state=0 group by order_user_id");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_uid[] = $obj_rs["order_user_id"];
		}
		$lng_count = count($arr_uid);
		if($lng_count>0) {
			$str_ids = implode("," , $arr_uid);
			$obj_rs = $obj_db->select("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_addtime<'" . $today_time . "' and order_user_id in(" . $str_ids . ")");
			$arr_return["user_repeat"] = $obj_rs["num"];
		} else {
			$arr_return["user_repeat"] = 0;
		}
		$arr_return["user_new"] = $lng_count - $arr_return["user_repeat"];
		return $arr_return;
	}

	/* 保存数据
	 * 
	 */
	function on_saveinfo($flag = 0) {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_progress = explode("," , fun_get::post("shop_progress"));
		if($flag == 2) {
			$arr_fields = array(
				"id"     => $this->shop_info["id"],
				"shop_intro" => fun_get::post("shop_intro"),
			);
		} else if($flag == 1){
			$arr_extend = array(
				"opentime" => fun_get::post("opentime"),
				"arrivetime" => fun_get::post("arrivetime"),
				"arrivedelay" => (int)fun_get::post("arrivedelay"),
				"arr_opentime" => tab_sys_config::get_array(fun_get::post("opentime")),
				"arr_arrivetime" => tab_sys_config::get_array(fun_get::post("arrivetime")),
			);
			$shop_dispatch_mode = (int)fun_get::post("shop_dispatch_mode");
			$arr_fields = array(
				"id"     => $this->shop_info["id"],
				"shop_dispatch_price"  => fun_get::post("shop_dispatch_price"),
				"shop_oneminleast"  => fun_get::post("shop_oneminleast"),
				"shop_extend" => $arr_extend,
				"shop_ticket"  => (int)fun_get::post("shop_ticket"),
				"shop_dispatch_mode"  => $shop_dispatch_mode,
			);
			if(empty($shop_dispatch_mode)) {
				if(!in_array(3 , $arr_progress)) $arr_progress[] = 3;
			} else {
				$obj_dispatch = cls_obj::db()->get_one("select count(1) as num from " . cls_config::DB_PRE . "meal_dispatch where dispatch_shop_id='" . $this->shop_info["id"] ."'");
				if(!empty($obj_dispatch) && $obj_dispatch["num"]>0) {
					if(!in_array(3 , $arr_progress)) $arr_progress[] = 3;
				} else {
					//设置送餐范围状态为未填写
					$index = array_search($k,$array);
					if($index) unset($array[$index]);
				}
			}
			if(!in_array(2 , $arr_progress)) $arr_progress[] = 2;
			$arr_fields["shop_progress"] = implode("," , $arr_progress);
		} else {
			$arr_fields = array(
				"id"     => $this->shop_info["id"],
				"shop_name" => fun_get::post("shop_name"),
				"shop_linkname"  => fun_get::post("shop_linkname"),
				"shop_linktel"  => fun_get::post("shop_linktel"),
				"shop_tel"  => fun_get::post("shop_tel"),
				"shop_address"  => fun_get::post("shop_address"),
				"shop_area_id"  => fun_get::post("shop_area_id"),
				"shop_area_allid"  => fun_get::post("shop_area_allid"),
				"shop_area"  => fun_get::post("shop_area"),
				"shop_pic"  => fun_get::post("shop_pic"),
				"shop_pic_small"  => fun_get::post("shop_pic_small"),
				"shop_state"  => fun_get::post("shop_state"),
			);
			if(!in_array(1 , $arr_progress)) $arr_progress[] = 1;
			$arr_fields["shop_progress"] = implode("," , $arr_progress);
			$arr_mode = fun_get::post("shop_mode",array());
			$lng_x = 0;
			foreach($arr_mode as $item) {
				$lng_x += (int)$item;
			}
			$arr_fields["shop_mode"] = $lng_x;
		}

		$arr = tab_meal_shop::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}

	/* 设置店铺运营状态
	 *
	 */
	function on_savestate() {
		$val = fun_get::get("val");
		$state = $this->shop_info["shop_state"];
		if(!in_array($state , array(1,-1)) ) {
			$arr_return["msg"] = "当前店铺处于" . $this->shop_info["state"] . "状态,没有修改权限";
			$arr_return["code"] = 500;
			return $arr_return;
		}
		if(!in_array($val , array(1,-1)) ) {
			$arr_return["msg"] = "设置店铺状态参数有误";
			$arr_return["code"] = 500;
			return $arr_return;
		}
		$arr_return = cls_obj::db_w()->on_update(cls_config::DB_PRE . "meal_shop" , array("shop_state"=>$val) , "shop_id=" . $this->shop_info['id']);
		return $arr_return;
	}

}