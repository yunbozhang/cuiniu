<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_checkout extends inc_mod_admin {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist($isexcel = false) {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'key' => fun_get::get("s_key"),
		);
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(shop_name like '%" . $arr_search_key['key'] . "%' or shop_linkname like '%" . $arr_search_key['key'] . "%' or shop_tel like '%" . $arr_search_key['key'] . "%')";
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page') , $isexcel);

		$lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}

	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1 , $isexcel = false) {
		$arr_return = array("list" => array() , "moneytotal" => 0 , "moneyall" => 0 , "num" => 0 , "moneyfav" => 0 , "moneyrepay" => 0 , "moneyadd" => 0 , "moneypay" => 0 );
		$obj_db = cls_obj::db();
		$arr_type = tab_meal_shop::get_perms("type");
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.checkout.no" , $this->app_dir , "meal");
		$arr_cfg_fields["sel"] = substr(str_replace(",moneyaffter," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",paynum," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",affternum," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",money," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",date," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.checkout.no"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$arr_state = tab_meal_shop::get_perms("state");
		$arr_paynum = array();
		$date1 = fun_get::get("s_date1",date("Y-m-d"));
		$date2 = fun_get::get("s_date2",date("Y-m-d H:i:s"));
		$obj_result = $obj_db->select("select order_shop_id,count(1) as 'num' from " . cls_config::DB_PRE . "meal_order where order_state>0 and order_checkout_id=0 and order_pay_val>0 and order_time>='" . $date1 . "' and order_time<='" . $date2 . "' group by order_shop_id");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_paynum['id_' . $obj_rs['order_shop_id']] = $obj_rs['num'];
		}
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo("(select sum(order_total_pay) moneyall,count(1) as 'num',sum(order_pay_val) as 'moneypay',sum(order_total) as 'moneytotal',sum(order_favorable) as 'moneyfav',sum(order_repayment) as 'moneyrepay',sum(order_addprice) as 'moneyadd',order_shop_id from " . cls_config::DB_PRE . "meal_order where order_state>0 and order_checkout_id=0 and order_time>='" . $date1 . "' and order_time<='" . $date2 . "' group by order_shop_id) a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id" , $str_where , $lng_page , $lng_pagesize);
		$sql = "SELECT " . $arr_cfg_fields["sel"] . " FROM (select sum(order_total_pay) moneyall,count(1) as 'num',sum(order_pay_val) as 'moneypay',sum(order_total) as 'moneytotal',sum(order_favorable) as 'moneyfav',sum(order_repayment) as 'moneyrepay',sum(order_addprice) as 'moneyadd',order_shop_id from " . cls_config::DB_PRE . "meal_order where order_state>0 and order_checkout_id=0 and order_time>='" . $date1 . "' and order_time<='" . $date2 . "' group by order_shop_id) a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id" . $str_where . $sort;
		if(!$isexcel) $sql .= $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select($sql);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['moneyaffter'] = $obj_rs['moneyall'] - $obj_rs['moneypay'];
			$obj_rs['paynum'] = isset($arr_paynum['id_'.$obj_rs["shop_id"]]) ? $arr_paynum['id_'.$obj_rs["shop_id"]] : 0;
			$obj_rs['affternum'] = $obj_rs['num'] - $obj_rs['paynum'];
			$obj_rs['money'] = ($obj_rs['shop_checkout_money']<$obj_rs['moneyall']) ? number_format($obj_rs['moneyall'] * $obj_rs['shop_rebate'] / 100,2) : 0;
			$obj_rs['date'] = $date1 . "到" . $date2;
			$obj_rs['shop_rebate'] = $obj_rs['shop_rebate'] . "%";
			$arr_return["list"][] = $obj_rs;
			$arr_return['moneytotal'] += $obj_rs["moneytotal"];
			$arr_return['moneyall'] += $obj_rs["moneyall"];
			$arr_return['num'] += $obj_rs["num"];
			$arr_return['moneyfav'] += $obj_rs["moneyfav"];
			$arr_return['moneyrepay'] += $obj_rs["moneyrepay"];
			$arr_return['moneyadd'] += $obj_rs["moneyadd"];
			$arr_return['moneypay'] += $obj_rs["moneypay"];
		}
		$arr_return['pagebtns'] = $this->get_pagebtns($arr_return['pageinfo']);
		$arr_return['date'] = $date1 . "-" . $date2;
		return $arr_return;
	}
	/* 结算列表
	 * 
	 */
	function get_list() {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'key' => fun_get::get("s_key"),
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'date' => fun_get::get("s_date"),
		);
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(shop_name like '%" . $arr_search_key['key'] . "%' or shop_linkname like '%" . $arr_search_key['key'] . "%' or shop_tel like '%" . $arr_search_key['key'] . "%')";
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "checkout_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "checkout_addtime <= '" . fun_get::endtime( $arr_search_key['addtime2'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['date'] ) ) $arr_where_s[] = "checkout_date='" . $arr_search_key['date'] . "'"; 

		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_checkout_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}
	/* 结算列表实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_checkout_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		$arr_type = tab_meal_shop::get_perms("type");
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.checkout" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.checkout"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_uid = array();//用户id
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE . "meal_checkout a left join " . cls_config::DB_PRE . "meal_shop b on a.checkout_shop_id=b.shop_id" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM " . cls_config::DB_PRE . "meal_checkout a left join " . cls_config::DB_PRE . "meal_shop b on a.checkout_shop_id=b.shop_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["checkout_addtime"])) $obj_rs["checkout_addtime"] = date("Y-m-d H:i" , $obj_rs["checkout_addtime"]);
			if(isset($obj_rs["checkout_user_id"])) $arr_uid[] = $obj_rs["checkout_user_id"];
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$arr_uid = array_unique($arr_uid);
			$str_ids = implode("," , $arr_uid);
			$obj_result = $obj_db->select("select user_id , user_name , user_realname , user_netname from " . cls_config::DB_PRE . "sys_user where user_id in(" . $str_ids. ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$name = $obj_rs["user_realname"];
				if(empty($name)) $name = $obj_rs["user_netname"];
				if(empty($name)) $name = $obj_rs["user_name"];
				$arr_uname["id_" . $obj_rs["user_id"]] = $name;
			}
			for($i = count($arr_return['list'])-1 ; $i >= 0 ; $i--) {
				if(isset($arr_uname["id_" . $arr_return["list"][$i]["checkout_user_id"]])) {
					$arr_return["list"][$i]["checkout_user_id"] = $arr_uname["id_" . $arr_return["list"][$i]["checkout_user_id"]];
				}
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
	//获取结算日期列表
	function get_date_list() {
		$arr_return = array();
		$obj_db = cls_obj::db();
		$obj_result = $obj_db->select("select checkout_date from " . cls_config::DB_PRE . "meal_checkout group by checkout_date order by checkout_date desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_return[] = $obj_rs["checkout_date"];
		}
		return $arr_return;
	}
	//结算处理
	function on_checkout() {
		$arr_return = array();
		$shop_id = (int)fun_get::get("shop_id");
		$money = (float)fun_get::get("money");
		if(empty($shop_id)) {
			$arr_return["code"] = 500;
			$arr_return["msg"] = "请指定结算店铺";
			return $arr_return;
		}
		$date1 = fun_get::get('date1',date("Y-m-d"));
		$date2 = fun_get::get('date2',date("Y-m-d H:i:s"));
		$obj_db = cls_obj::db_w();
		//取结款金额
		$obj_order = $obj_db->get_one("select sum(order_total_pay) moneyall,count(1) as 'num',sum(order_pay_val) as 'moneypay',sum(order_total) as 'moneytotal',sum(order_favorable) as 'moneyfav',sum(order_repayment) as 'moneyrepay',sum(order_addprice) as 'moneyadd' from " . cls_config::DB_PRE . "meal_order where order_state>0 and order_checkout_id=0 and order_time>='" . $date1 . "' and order_time<='" . $date2 . "' and order_shop_id='" . $shop_id . "'");
		$obj_paynum = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order where order_state>0 and order_checkout_id=0 and order_pay_val>0 and order_time>='" . $date1 . "' and order_time<='" . $date2 . "' and order_shop_id='" . $shop_id. "'");
		$obj_shop = $obj_db->get_one("select shop_checkout_money,shop_rebate from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(empty($obj_shop)) return array("code" => 500 , "msg" => "店铺不存在");
		$beta = fun_get::get("beta");
		$money = $obj_order['moneyall']>$obj_shop['shop_checkout_money'] ? $obj_order['moneyall']*$obj_shop['shop_rebate']/100 : 0;
		//开始事务
		$obj_db->begin("checkout");
		$arr_fields = array(
			"checkout_shop_id" => $shop_id ,
			"checkout_money" => $money ,
			"checkout_user_id" => cls_obj::get("cls_user")->uid ,
			"checkout_beta"=>$beta,
			"checkout_date1" => $date1,
			"checkout_date2" => $date2,
			"checkout_moneyall" => $obj_order['moneyall'] ,
			"checkout_moneypay" => $obj_order['moneypay'] ,
			"checkout_moneyaffter" => $obj_order['moneyall']-$obj_order['moneypay'] ,
			"checkout_num" => $obj_order['num'] ,
			"checkout_numpay" => $obj_paynum['num'] ,
			"checkout_rebate" => $obj_shop['shop_rebate'] ,
			"checkout_rebate_money" => $obj_shop['shop_checkout_money'] ,
			"checkout_moneytotal" => $obj_order['moneytotal'] ,
			"checkout_moneyrepay" => $obj_order['moneyrepay'] ,
			"checkout_moneyadd" => $obj_order['moneyadd'] ,
			"checkout_moneyfav" => $obj_order['moneyfav'] ,
		);
		$arr = tab_meal_checkout::on_save($arr_fields);
		if($arr["code"] == 0 && $arr["id"]>0) {
			$arr_1 = $obj_db->on_update(cls_config::DB_PRE . "meal_order" , array("order_checkout_id" => $arr["id"]) , "order_state>0 and order_checkout_id=0 and order_time>='" . $date1 . "' and order_time<='" . $date2 . "' and order_shop_id='" . $shop_id . "'");
			if($arr_1["code"] == 0) {
				$obj_db->commit("checkout");
			} else {
				$obj_db->rollback("checkout");
			}
		} else {
			$obj_db->rollback("checkout");
			return $arr;
		}
		return array("code" => 0 , "msg"=>"结算成功");
	}
	function on_excel() {
		$arr_return = $this->get_pagelist(true);
		$arr_excel=array();
		$arr = array();
		foreach($arr_return['tabtit'] as $item) {
			$arr[] = $item["name"];
		}
		$arr_excel[] = $arr;

		foreach($arr_return['list'] as $item) {
			$arr = array() ;
			foreach($arr_return['tabtit'] as $field) {
				$val = $item[$field["key"]];
				$arr[] = $val;
			}
			$arr_excel[] = $arr;
		}
		$get_excel_name = fun_get::get("excel_name" , date("Y-m-d"));
		cls_excel::save_excel($get_excel_name.".xls",$arr_excel);
	}
}