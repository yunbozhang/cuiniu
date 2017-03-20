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
		$obj_rs['opentime'] = $obj_rs['arrivetime'] = $obj_rs['weekday'] = array();
		$obj_rs["extend"] = array( "arrivedelay" => "" , "arrivetime" => "" , "opentime" => "" , 'weekday' => '' );
		if(!empty($obj_rs["shop_extend"])) $obj_rs["extend"] = unserialize($obj_rs["shop_extend"]);
		if(!empty($obj_rs['extend']['opentime'])) {
			foreach($obj_rs['extend']['arr_opentime'] as $item=>$key) {
				$arr = explode(",",$item);
				if($key == $item) $key = '';
				$obj_rs['opentime'][] = array('hour1'=>substr($arr[0],0,-2) , 'minu1'=>intval(substr($arr[0],-2)) , 'hour2'=>substr($arr[1],0,-2),'minu2'=>intval(substr($arr[1],-2)),'name'=>$key);
			}
		}
		if(!empty($obj_rs['extend']['arr_arrivetime'])) {
			foreach($obj_rs['extend']['arr_arrivetime'] as $item=>$key) {
				if($key == $item) $key = '';
				$obj_rs['arrivetime'][] = array('hour'=>substr($item,0,-2) , 'minu'=>intval(substr($item,-2)) , 'name'=>$key);
			}
		}
		if(!empty($obj_rs['extend']['weekday'])) $obj_rs['weekday'] = explode("," , $obj_rs['extend']['weekday']);
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
		//总商品数
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu where menu_state>=0 and menu_isdel=0 and menu_shop_id='" . $this->shop_info["id"] . "'");
		$arr_return["menu_num"] = $obj_rs["num"];
		//今日总订单
		$obj_rs = $obj_db->get_one("select count(1) as 'num',sum(order_total) as 'total' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_addtime>'" . $today_time . "' and order_state>=0");
		$arr_return["order_today"] = $obj_rs["num"];
		$arr_return["order_today_money"] = (empty($obj_rs["total"])) ? 0 : $obj_rs["total"];
		//总订单
		$obj_rs = $obj_db->get_one("select count(1) as 'num',sum(order_total) as 'total' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_state>=0");
		$arr_return["order_total"] = $obj_rs["num"];
		$arr_return["order_total_money"] = (empty($obj_rs["total"])) ? 0 : $obj_rs["total"];
		//未处理订单
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_state=0");
		$arr_return["order_noconfirm"] = (empty($obj_rs["num"])) ? 0 : $obj_rs["num"];
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
				"shop_desc" => fun_get::post("shop_desc"),
			);
		} else if($flag == 1){
			$arr_extend = array(
				"arrivedelay" => (int)fun_get::post("arrivedelay"),
			);
			$opentime_hour1 = fun_get::post("opentime_hour1");
			$opentime_minu1 = fun_get::post("opentime_minu1");
			$opentime_hour2 = fun_get::post("opentime_hour2");
			$opentime_minu2 = fun_get::post("opentime_minu2");
			$opentime_name = fun_get::post("opentime_name");
			$arr = array();
			for($i = 0 ; $i < count($opentime_hour1) ; $i++) {
				$x1 = (intval($opentime_minu1[$i])<10) ? '0'.$opentime_minu1[$i] : $opentime_minu1[$i];
				$x2 = (intval($opentime_minu2[$i])<10) ? '0'.$opentime_minu2[$i] : $opentime_minu2[$i];
				$v = $opentime_hour1[$i] . $x1 . "," . $opentime_hour2[$i] . $x2;
				if(!empty( $opentime_name[$i])) {
					$v .= '=&gt;' . $opentime_name[$i];
				} else {
					$v .= '=&gt;' . $opentime_hour1[$i] . ":" . $x1 . "-" . $opentime_hour2[$i] . ":" . $x2;
				}
				$arr[] = $v;
			}
			$arr_extend['opentime'] = implode(chr(10) , $arr);
			$arr_weekday = fun_get::post("shop_weekday");
			$arr_extend['weekday'] = is_array($arr_weekday) ? implode("," , $arr_weekday) : '';
			$arr_extend["arr_opentime"] = tab_sys_config::get_array($arr_extend['opentime']);
			$arrivetime_hour = fun_get::post("arrivetime_hour");
			$arrivetime_minu = fun_get::post("arrivetime_minu");
			$arrivetime_name = fun_get::post("arrivetime_name");
			$arr = array();
			for($i = 0 ; $i < count($arrivetime_hour) ; $i++) {
				$x1 = (intval($arrivetime_minu[$i])<10) ? '0'.$arrivetime_minu[$i] : $arrivetime_minu[$i];
				$v = $arrivetime_hour[$i] . $x1;
				if(!empty( $arrivetime_name[$i])) {
					$v .= '=&gt;' . $arrivetime_name[$i];
				} else {
					$v .= '=&gt;' . $arrivetime_hour[$i] . ":" . $x1;
				}
				$arr[] = $v;
			}
			$arr_extend['arrivetime'] = implode(chr(10) , $arr);
			$arr_extend["arr_arrivetime"] = tab_sys_config::get_array($arr_extend['arrivetime']);
			$arr_fields = array(
				"id"     => $this->shop_info["id"],
				"shop_dispatch_price"  => fun_get::post("shop_dispatch_price"),
				"shop_oneminleast"  => fun_get::post("shop_oneminleast"),
				"shop_extend" => $arr_extend,
				"shop_ticket"  => (int)fun_get::post("shop_ticket"),
				"shop_addprice"  => fun_get::post("shop_addprice"),
				"shop_day_limit"  => fun_get::post("shop_day_limit"),
			);
			$obj_dispatch = cls_obj::db()->get_one("select count(1) as num from " . cls_config::DB_PRE . "meal_dispatch where dispatch_shop_id='" . $this->shop_info["id"] ."'");
			if(!empty($obj_dispatch) && $obj_dispatch["num"]>0) {
				if(!in_array(3 , $arr_progress)) $arr_progress[] = 3;
			} else {
				//设置送餐范围状态为未填写
			}
			if(!in_array(2 , $arr_progress)) $arr_progress[] = 2;
			$arr_fields["shop_progress"] = implode("," , $arr_progress);
		} else if($flag==3) {
			$arr_fields = array(
				"id"     => $this->shop_info["id"],
				"shop_verifytel" => (int)fun_get::post("shop_verifytel"),
				"shop_sms" => fun_get::post("shop_sms"),
				"shop_sms_tel" => fun_get::post("shop_sms_tel"),
				"shop_email" => fun_get::post("shop_email"),
				"shop_print_id" => fun_get::post("shop_print_id"),
				"shop_print_auto" => (int)fun_get::post("shop_print_auto"),
				"shop_print_cancel" => (int)fun_get::post("shop_print_cancel"),
				"shop_print_tongbu" => (int)fun_get::post("shop_print_tongbu"),
			);
			if(fun_is::set("shop_printinfo")) $arr_fields['shop_printinfo'] = fun_get::post("shop_printinfo");

		} else {
			$arr_fields = array(
				"id"     => $this->shop_info["id"],
			);
			if(fun_is::set("shop_name")) $arr_fields['shop_name'] = fun_get::post("shop_name");
			if(fun_is::set("shop_linkname")) $arr_fields['shop_linkname'] = fun_get::post("shop_linkname");
			if(fun_is::set("shop_linktel")) $arr_fields['shop_linktel'] = fun_get::post("shop_linktel");
			if(fun_is::set("shop_tel")) $arr_fields['shop_tel'] = fun_get::post("shop_tel");
			if(fun_is::set("shop_address")) $arr_fields['shop_address'] = fun_get::post("shop_address");
			if(fun_is::set("shop_area_id")) $arr_fields['shop_area_id'] = fun_get::post("shop_area_id");
			if(fun_is::set("shop_area_allid")) $arr_fields['shop_area_allid'] = fun_get::post("shop_area_allid");
			if(fun_is::set("shop_area")) $arr_fields['shop_area'] = fun_get::post("shop_area");
			if(fun_is::set("shop_pic")) $arr_fields['shop_pic'] = fun_get::post("shop_pic");
			if(fun_is::set("shop_pic_small")) $arr_fields['shop_pic_small'] = fun_get::post("shop_pic_small");
			if(fun_is::set("shop_desc")) $arr_fields['shop_desc'] = fun_get::post("shop_desc");
			if(fun_is::set("shop_state")) $arr_fields['shop_state'] = fun_get::post("shop_state");

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