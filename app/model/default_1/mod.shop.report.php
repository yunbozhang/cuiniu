<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_report extends inc_mod_shop {
	/* 订单量
	 * 默认为当天统计
	 */
	function order_num() {
		$mode = fun_get::get("mode");
		switch($mode) {
			case "year":
				//按月
				$arr_return = $this->order_num_byyear();
				break;
			case "month":
				//按月
				$arr_return = $this->order_num_bymonth();
				break;
			default:
				//按天
				$arr_return = $this->order_num_byday();
		}
		return $arr_return;
	}
	//按年
	function order_num_byyear() {
		$arr_return = array("list" => '' , "sub"=> '' , "splitX" => 12);
		$date = fun_get::get("year" , date("Y"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		for($i = 1; $i <= $arr_return["splitX"] ; $i++ ) {
			$ii = $i;
			if($i<10) $ii = "0" . $i;
			$arr_list[$ii] = 0;
			$arr_sub[] = $i . "月";
		}
		$channel = fun_get::get("channel");
		$field = "count(1)";
		if($channel == "money") $field = "sum(order_total_pay)";
		$obj_result = $obj_db->select("SELECT  left(order_day,7) as 'tips'," . $field . " as 'val' FROM kj_meal_order where order_shop_id='" . $this->shop_info["id"] . "' and left(order_day,4)='" . $date . "' group by left(order_day,7)");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$tips = substr($obj_rs["tips"] , -2);
			$arr_list[$tips] = $obj_rs['val'];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		return $arr_return;
	}
	//按月
	function order_num_bymonth() {
		$arr_return = array("list" => '' , "sub"=> '' , "max" => 0 , "min" => 0);
		$year = fun_get::get("year" , date("Y"));
		$month = fun_get::get("month" , date("m"));
		if(strlen($month)<2) $month = "0" . $month;
		$date = $year . "-" . $month;
		$arr_return["splitX"] = (int)fun_get::end_day();
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		for($i = 1; $i <= $arr_return["splitX"] ; $i++ ) {
			$ii = $i;
			if($i<10) $ii = "0" . $i;
			$arr_list[$ii] = 0;
			$arr_sub[] = $i;
		}
		$channel = fun_get::get("channel");
		$field = "count(1)";
		if($channel == "money") $field = "sum(order_total_pay)";
		$obj_result = $obj_db->select("SELECT  order_day as 'tips'," . $field . " as 'val' FROM kj_meal_order where order_shop_id='" . $this->shop_info["id"] . "' and left(order_day,7)='" . $date . "' group by order_day");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$tips = substr($obj_rs["tips"] , -2);
			$arr_list[$tips] = $obj_rs["val"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		return $arr_return;
	}
	//按天
	function order_num_byday() {
		$arr_return = array("list" => '' , "sub"=> '' , "splitX" => 24);
		$date = fun_get::get("date" , date("Y-m-d"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		for($i=0;$i<24;$i++) {
			$ii = $i;
			if($i<10) $ii = "0" . $i;
			$arr_list[$ii] = 0;
			$arr_sub[] = $ii;
		}
		$channel = fun_get::get("channel");
		$field = "count(1)";
		if($channel == "money") $field = "sum(order_total_pay)";
		$obj_result = $obj_db->select("SELECT  left(order_time,13) as 'tips'," . $field . " as 'val' FROM kj_meal_order where order_shop_id='" . $this->shop_info["id"] . "' and order_day='" . $date . "' group by left(order_time,13)");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$tips = substr($obj_rs["tips"] , -2);
			$arr_list[$tips] = $obj_rs["val"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		return $arr_return;
	}
}