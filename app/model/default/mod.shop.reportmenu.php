<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_reportmenu extends inc_mod_shop {
	/* 订单量
	 * 默认为当天统计
	 */
	function menu_num() {
		$mode = fun_get::get("mode");
		$viewmode = fun_get::get("viewmode");
		if( empty($viewmode)) {
			switch($mode) {
				case "year":
					//按月
					$arr_return = $this->menu_num_byyear();
					break;
				case "month":
					//按月
					$arr_return = $this->menu_num_bymonth();
					break;
				case "other":
					//按月
					$arr_return = $this->menu_num_byother();
					break;
				default:
					//按天
					$arr_return = $this->menu_num_byday();
			}
		} else {
			switch($mode) {
				case "year":
					//按月
					$arr_return = $this->menu_list_byyear();
					break;
				case "month":
					//按月
					$arr_return = $this->menu_list_bymonth();
					break;
				case "other":
					//按月
					$arr_return = $this->menu_list_byother();
					break;
				default:
					//按天
					$arr_return = $this->menu_list_byday();
			}
		}
		return $arr_return;
	}
	//按年
	function menu_num_byyear() {
		$arr_return = array("list" => '' , "sub"=> '');
		$date = fun_get::get("year" , date("Y"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_num)";
		if($channel == "money") $field = "sum(sold_total)";
		$where = " where left(sold_datetime,4)='" . $date . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . " group by menu_id order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date . "年";;
		$arr_return['datetime'] = $title;
		return $arr_return;
	}
	//按月
	function menu_num_bymonth() {
		$arr_return = array("list" => '' , "sub"=> '' , "max" => 0 , "min" => 0);
		$year = fun_get::get("year" , date("Y"));
		$month = fun_get::get("month" , date("m"));
		if(strlen($month)<2) $month = "0" . $month;
		$date = $year . "-" . $month;
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_num)";
		if($channel == "money") $field = "sum(sold_total)";
		$where = " where left(sold_datetime,7)='" . $date . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";

		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . "  group by menu_id order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date;;
		$arr_return['datetime'] = $title;
		return $arr_return;
	}
	//按天
	function menu_num_byday() {
		$arr_return = array("list" => '' , "sub"=> '');
		$date = fun_get::get("date" , date("Y-m-d"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_num)";
		if($channel == "money") $field = "sum(sold_total)";
		$where = " where left(sold_datetime,10)='" . $date . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . "  group by menu_id order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date;;
		$arr_return['datetime'] = $title;
		return $arr_return;
	}
	//时间间隔
	function menu_num_byother() {
		$arr_return = array("list" => '' , "sub"=> '');
		$date1 = fun_get::get("date1" , date("Y-m-d"));
		$date2 = fun_get::get("date2" , date("Y-m-d"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_num)";
		if($channel == "money") $field = "sum(sold_total)";
		$where = " where sold_datetime>='" . $date1 . "' and sold_datetime<='" . $date2 . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . "  group by menu_id order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date1 . " 到 " . $date2;
		$arr_return['datetime'] = $title;
		return $arr_return;
	}

	//按年
	function menu_list_byyear() {
		$arr_return = array();
		$date = fun_get::get("year" , date("Y"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_total) as 'price',sum(sold_num) as 'num'";
		$where = " where left(sold_datetime,4)='" . $date . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";
		$obj_result = $obj_db->select("SELECT  shop_id,shop_name,menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["shop_id"]])) $arr_list['id_'.$obj_rs["shop_id"]] = array('shop_name' => $obj_rs['shop_name'],'num' => 0,'price'=>0,'list'=>array());
			$arr_list['id_'.$obj_rs["shop_id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["shop_id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["shop_id"]]['price']+=$obj_rs['price'];
		}
		return $arr_list;
	}
	//按月
	function menu_list_bymonth() {
		$arr_return = array();
		$year = fun_get::get("year" , date("Y"));
		$month = fun_get::get("month" , date("m"));
		if(strlen($month)<2) $month = "0" . $month;
		$date = $year . "-" . $month;
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_total) as 'price',sum(sold_num) as 'num'";
		$where = " where left(sold_datetime,7)='" . $date . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";

		$obj_result = $obj_db->select("SELECT  shop_id,shop_name,menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["shop_id"]])) $arr_list['id_'.$obj_rs["shop_id"]] = array('shop_name' => $obj_rs['shop_name'],'num' => 0,'price'=>0,'list'=>array());
			$arr_list['id_'.$obj_rs["shop_id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["shop_id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["shop_id"]]['price']+=$obj_rs['price'];
		}
		return $arr_list;
	}
	//按天
	function menu_list_byday() {
		$arr_return = array();
		$date = fun_get::get("date" , date("Y-m-d"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_total) as 'price',sum(sold_num) as 'num'";
		$where = " where left(sold_datetime,10)='" . $date . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";
		$obj_result = $obj_db->select("SELECT  shop_id,shop_name,menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["shop_id"]])) $arr_list['id_'.$obj_rs["shop_id"]] = array('shop_name' => $obj_rs['shop_name'],'num' => 0,'price'=>0,'list'=>array());
			$arr_list['id_'.$obj_rs["shop_id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["shop_id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["shop_id"]]['price']+=$obj_rs['price'];
		}
		return $arr_list;
	}
	//时间间隔
	function menu_list_byother() {
		$arr_return = array();
		$date1 = fun_get::get("date1" , date("Y-m-d"));
		$date2 = fun_get::get("date2" , date("Y-m-d"));
		$obj_db = cls_obj::db();
		$arr_list = $arr_sub = array();
		$channel = fun_get::get("channel");
		$field = "sum(sold_total) as 'price',sum(sold_num) as 'num'";
		$where = " where sold_datetime>='" . $date1 . "' and sold_datetime<='" . $date2 . "' and order_state>0";
		$where .= " and sold_shop_id='" . $this->shop_info["id"] . "'";
		$obj_result = $obj_db->select("SELECT  shop_id,shop_name,menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["shop_id"]])) $arr_list['id_'.$obj_rs["shop_id"]] = array('shop_name' => $obj_rs['shop_name'],'num' => 0,'price'=>0,'list'=>array());
			$arr_list['id_'.$obj_rs["shop_id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["shop_id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["shop_id"]]['price']+=$obj_rs['price'];
		}
		return $arr_list;
	}
}