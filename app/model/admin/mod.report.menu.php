<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_report_menu extends inc_mod_meal {
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$bysn = (int)fun_get::get("bysn");
		$groupby = ($bysn==1) ? " group by menu_number" : " group by menu_id";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . $groupby . " order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date . "年";;
		if($this->admin_shop["id"]>0) $title = $this->admin_shop["name"] . " " . $title;
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$bysn = (int)fun_get::get("bysn");
		$groupby = ($bysn==1) ? " group by menu_number" : " group by menu_id";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . $groupby . " order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date;;
		if($this->admin_shop["id"]>0) $title = $this->admin_shop["name"] . " " . $title;
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$bysn = (int)fun_get::get("bysn");
		$groupby = ($bysn==1) ? " group by menu_number" : " group by menu_id";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where . $groupby . " order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date;;
		if($this->admin_shop["id"]>0) $title = $this->admin_shop["name"] . " " . $title;
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$bysn = (int)fun_get::get("bysn");
		$groupby = ($bysn==1) ? " group by menu_number" : " group by menu_id";
		$obj_result = $obj_db->select("SELECT  menu_title as 'tips'," . $field . " as 'val' FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id" . $where  . $groupby . " order by val desc limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_list[$obj_rs["tips"]] = $obj_rs["val"];
			$arr_sub[] = $obj_rs["tips"];
		}
		$arr_list = array_values($arr_list);
		$arr_return['data'] = str_replace('"' , '' , fun_format::json( $arr_list ));
		$arr_return['sub'] = fun_format::json( $arr_sub );
		$title =  $date1 . " 到 " . $date2;
		if($this->admin_shop["id"]>0) $title = $this->admin_shop["name"] . " " . $title;
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
		$arr_shop_id = array();
		$field = "sum(sold_total) as 'price',sum(sold_num) as 'num'";
		$where = " where left(sold_datetime,4)='" . $date . "' and order_state>0";
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$bysn = (int)fun_get::get("bysn");
		if($bysn == 1) {
			$obj_result = $obj_db->select("SELECT  area_id as 'id',menu_number as 'sn',area_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id left join " . cls_config::DB_PRE . "sys_area e on d.shop_area_id=e.area_id" . $where . " group by area_id,menu_number order by num desc,price desc");
		} else {
			$obj_result = $obj_db->select("SELECT  shop_id as 'id',menu_id as 'sn',shop_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		}
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]] = array('name' => $obj_rs['name'],'num' => 0,'price'=>0,'list'=>array(),'ordernum'=>0,'sn'=>$obj_rs['sn']);
			$arr_list['id_'.$obj_rs["id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["id"]]['price']+=$obj_rs['price'];
			if(!in_array($obj_rs['id'] , $arr_shop_id)) $arr_shop_id[] = $obj_rs['id'];
		}
		if(!empty($arr_shop_id)) {
			$arr_shop_order = array();
			$ids = implode("," , $arr_shop_id);
			if($bysn == 1) {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',area_id as 'id' from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id left join " . cls_config::DB_PRE . "sys_area c on b.shop_area_id=c.area_id where order_shop_id in(" . $ids . ") and left(order_time,4)='" . $date . "' and order_state>0 group by order_shop_id");
			} else {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',order_shop_id as 'id' from " . cls_config::DB_PRE . "meal_order where order_shop_id in(" . $ids . ") and left(order_time,4)='" . $date . "' and order_state>0 group by order_shop_id");
			}
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				if(isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]]['ordernum'] = $obj_rs['num'];
			}
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}

		$arr_shop_id = array();
		$bysn = (int)fun_get::get("bysn");
		if($bysn == 1) {
			$obj_result = $obj_db->select("SELECT  area_id as 'id',menu_number as 'sn',area_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id left join " . cls_config::DB_PRE . "sys_area e on d.shop_area_id=e.area_id" . $where . " group by area_id,menu_number order by num desc,price desc");
		} else {
			$obj_result = $obj_db->select("SELECT  shop_id as 'id',menu_id as 'sn',shop_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		}
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]] = array('name' => $obj_rs['name'],'num' => 0,'price'=>0,'list'=>array(),'ordernum'=>0,'sn'=>$obj_rs['sn']);
			$arr_list['id_'.$obj_rs["id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["id"]]['price']+=$obj_rs['price'];
			if(!in_array($obj_rs['id'] , $arr_shop_id)) $arr_shop_id[] = $obj_rs['id'];
		}
		if(!empty($arr_shop_id)) {
			$arr_shop_order = array();
			$ids = implode("," , $arr_shop_id);
			if($bysn == 1) {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',area_id as 'id' from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id left join " . cls_config::DB_PRE . "sys_area c on b.shop_area_id=c.area_id where order_shop_id in(" . $ids . ") and left(order_time,7)='" . $date . "' and order_state>0 group by order_shop_id");
			} else {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',order_shop_id as 'id' from " . cls_config::DB_PRE . "meal_order where order_shop_id in(" . $ids . ") and left(order_time,7)='" . $date . "' and order_state>0 group by order_shop_id");
			}
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				if(isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]]['ordernum'] = $obj_rs['num'];
			}
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$arr_shop_id = array();
		$bysn = (int)fun_get::get("bysn");
		if($bysn == 1) {
			$obj_result = $obj_db->select("SELECT  area_id as 'id',menu_number as 'sn',area_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id left join " . cls_config::DB_PRE . "sys_area e on d.shop_area_id=e.area_id" . $where . " group by area_id,menu_number order by num desc,price desc");
		} else {
			$obj_result = $obj_db->select("SELECT  shop_id as 'id',menu_id as 'sn',shop_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		}
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]] = array('name' => $obj_rs['name'],'num' => 0,'price'=>0,'list'=>array(),'ordernum'=>0,'sn'=>$obj_rs['sn']);
			$arr_list['id_'.$obj_rs["id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["id"]]['price']+=$obj_rs['price'];
			if(!in_array($obj_rs['id'] , $arr_shop_id)) $arr_shop_id[] = $obj_rs['id'];
		}
		if(!empty($arr_shop_id)) {
			$arr_shop_order = array();
			$ids = implode("," , $arr_shop_id);
			if($bysn == 1) {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',area_id as 'id' from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id left join " . cls_config::DB_PRE . "sys_area c on b.shop_area_id=c.area_id where order_shop_id in(" . $ids . ") and left(order_time,10)='" . $date . "' and order_state>0 group by order_shop_id");
			} else {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',order_shop_id as 'id' from " . cls_config::DB_PRE . "meal_order where order_shop_id in(" . $ids . ") and left(order_time,10)='" . $date . "' and order_state>0 group by order_shop_id");
			}
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				if(isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]]['ordernum'] = $obj_rs['num'];
			}
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
		if($this->admin_shop["id"] > 0) {
			$where .= " and sold_shop_id='" . $this->admin_shop["id"] . "'";
		} else {
			//管理权限
			$limit_area = $this->this_limit->get_perms('limit_area');
			if(!empty($limit_area)) {
				$arr = explode("," , $limit_area);
				$arr_x = array();
				foreach($arr as $areaid) {
					$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
				}
				$where .= " and sold_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
			}
		}
		$arr_shop_id = array();
		$bysn = (int)fun_get::get("bysn");
		if($bysn == 1) {
			$obj_result = $obj_db->select("SELECT  area_id as 'id',menu_number as 'sn',area_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id left join " . cls_config::DB_PRE . "sys_area e on d.shop_area_id=e.area_id" . $where . " group by area_id,menu_number order by num desc,price desc");
		} else {
			$obj_result = $obj_db->select("SELECT  shop_id as 'id',menu_id as 'sn',shop_name as 'name',menu_title," . $field . " FROM " . cls_config::DB_PRE . "meal_menu_sold a left join " . cls_config::DB_PRE . "meal_menu b on a.sold_menu_id=b.menu_id left join " . cls_config::DB_PRE . "meal_order c on a.sold_order_id=c.order_id left join " . cls_config::DB_PRE . "meal_shop d on a.sold_shop_id=d.shop_id" . $where . " group by shop_id,menu_id order by num desc,price desc");
		}
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]] = array('name' => $obj_rs['name'],'num' => 0,'price'=>0,'list'=>array(),'ordernum'=>0 , 'sn' => $obj_rs['sn']);
			$arr_list['id_'.$obj_rs["id"]]['list'][] = $obj_rs;
			$arr_list['id_'.$obj_rs["id"]]['num']+=$obj_rs['num'];
			$arr_list['id_'.$obj_rs["id"]]['price']+=$obj_rs['price'];
			if(!in_array($obj_rs['id'] , $arr_shop_id)) $arr_shop_id[] = $obj_rs['id'];
		}
		if(!empty($arr_shop_id)) {
			$arr_shop_order = array();
			$ids = implode("," , $arr_shop_id);
			if($bysn == 1) {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',area_id as 'id' from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id left join " . cls_config::DB_PRE . "sys_area c on b.shop_area_id=c.area_id where order_shop_id in(" . $ids . ") and order_time>='" . $date1 . "' and left(order_time,10)<='" . $date2 . "' and order_state>0 group by order_shop_id");
			} else {
				$obj_result = cls_obj::db()->select("select count(1) as 'num',order_shop_id as 'id' from " . cls_config::DB_PRE . "meal_order where order_shop_id in(" . $ids . ") and order_time>='" . $date1 . "' and left(order_time,10)<='" . $date2 . "' and order_state>0 group by order_shop_id");
			}
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				if(isset($arr_list['id_'.$obj_rs["id"]])) $arr_list['id_'.$obj_rs["id"]]['ordernum'] = $obj_rs['num'];
			}
		}
		return $arr_list;
	}

}