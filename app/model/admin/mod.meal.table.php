<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_table extends inc_mod_meal {
	function get_group_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."meal_table_group" , "group_id='".$msg_id."'");
		return $obj_rs;
	}
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."meal_table" , "table_id='".$msg_id."'");
		if(empty($obj_rs['table_id'])) $obj_rs['table_state'] = 1;
		return $obj_rs;
	}
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"table_name" => fun_get::post("table_name"),
			"table_num" => fun_get::post("table_num"),
			"table_state" => fun_get::post("table_state"),
			"table_group_id" => fun_get::post("table_group_id"),
			"table_shop_id"  => $this->admin_shop["id"],
		);
		$arr = tab_meal_table::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	function on_group_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"group_name" => fun_get::post("group_name"),
			"group_sort" => fun_get::post("group_sort"),
			"group_shop_id"  => $this->admin_shop["id"],
		);
		$arr = tab_meal_table_group::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	function get_group_list() {
		$arr_list = array();
		$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "meal_table_group where group_shop_id='" . $this->admin_shop["id"] . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_list[] = $obj_rs;
		}
		return $arr_list;
	}
	function get_table_list() {
		$arr_list = array();
		$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "meal_table where table_shop_id='" . $this->admin_shop["id"] . "'");
		$arr_id = array();
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_id[] = $obj_rs['table_id'];
			$obj_rs["qrcode"] = cls_qrcode::png(fun_get::html_url('wap.php?app_act=shop.table.qrcode&id=' . $obj_rs['table_shop_id'] . '&table_number=' . $obj_rs['table_number'],1),8,false,$obj_rs['table_name']);
			$arr_list[] = $obj_rs;
		}
		if(!empty($arr_id)) {
			$arr_list2 = array();
			$ids = implode("," , $arr_id);
			$arr_table_state = tab_meal_table::get_state($this->admin_shop["id"] , $ids , date("Y-m-d H:i:s"));
			foreach($arr_list as $item => $key) {
				if(isset($arr_table_state['id_'.$key['table_id']])) $key['state'] = $arr_table_state['id_'.$key['table_id']];
				$arr_list2[] = $key;
			}
			$arr_list = $arr_list2;
		}
		return $arr_list;
	}
	//获取当前用户订单列表
	function get_table_reserve() {
		$arr_menu_ids = array();
		$arr_return = array("menu" => array());
		$table_id = (int)fun_get::get("id");
		$r_id = (int)fun_get::get("rid");
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page",1);
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.table.reserve"  , $this->app_dir);
		$pagesize = $arr_config_info["pagesize"];
		if(!empty($table_id)) {
			$str_where = " where reserve_table_id='" . $table_id . "'";
		} else {
			$str_where = " where reserve_id='" . $r_id . "'";
		}
		$arr_return["pageinfo"] = $obj_db->get_pageinfo( cls_config::DB_PRE."meal_table_reserve",$str_where, $page , $pagesize);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_order b on a.reserve_id=b.order_reserve_id" . $str_where . " order by reserve_datetime desc " . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$obj_rs["order_act"] = !empty($obj_rs["order_act"]) ? unserialize($obj_rs["order_act"]) : array();
				$arr = explode("|" , $obj_rs["order_ids"]);
				$obj_rs['menunum'] = array_count_values($arr);
				$arr = array_unique($arr);
				$obj_rs["menu"] = array();
				foreach($arr as $item) {
					if(!empty($item)) {
						$obj_rs["menu"][] = explode("," , $item);
					}
				}
				//取当时下单的定价
				if(!empty($obj_rs["order_detail"])) {
					$arr_detail = unserialize($obj_rs["order_detail"]);
					if(isset($arr_detail["menu_price"])) $arr_return["price"] = $arr_detail["menu_price"];
				}
				if(!empty($obj_rs["order_ids"])) $arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));
				$obj_rs['order_total'] = (float)$obj_rs['order_total'];
				$obj_rs['order_total_pay'] = (float)$obj_rs['order_total_pay'];
				$obj_rs['order_favorable'] = (float)$obj_rs['order_favorable'];
				$obj_rs['reserve_datetime'] = date("Y-m-d H:i" , strtotime($obj_rs['reserve_datetime']));
				$arr_return["list"][] = $obj_rs;
		}
		$arr_menu_ids = array_unique($arr_menu_ids);
		$str_ids = implode("," , $arr_menu_ids);
		$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_return["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
			if(!isset($arr_return["price"]["id_".$obj_rs["menu_id"]])) $arr_return["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
		}
		if(!isset($arr_return["list"])) $arr_return["list"] = array();
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],5);
		return $arr_return;
	}
	function on_reserve_cancel($id) {
		$obj_reserve = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $id . "'");
		if(empty($obj_reserve)) return array('code' => 500 , 'msg' => '预订信息不存在');
		if($obj_reserve['reserve_state']==10)  return array('code' => 500 , 'msg' => '已完成的订单不能取消');
		$arr = tab_meal_order::on_state($id , -4 , '后台取消');
		if($arr['code'] == 0) {
			$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=-1 where reserve_id='" . $id . "'");
		}
		return $arr;
	}
	function on_upmenu() {
		$id = fun_get::get("id");
		$obj_reserve = cls_obj::db()->get_one("select reserve_state,reserve_id from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $id . "'");
		if(empty($obj_reserve)) return array("code" => 500 , "msg" => "预订信息不存在");
		if($obj_reserve['reserve_state']<1)  return array("code" => 500 , "msg" => "预订信息无效");
		if($obj_reserve['reserve_state']<2)  return array("code" => 500 , "msg" => "请先点菜");
		if($obj_reserve['reserve_state']>=3)  return array("code" => 0 , "msg" => "");
		$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_menutime='" . TIME . "',reserve_state=3 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		return $arr;
	}
	function on_reserve_pay() {
		$id = fun_get::get("id");
		$obj_reserve = cls_obj::db()->get_one("select reserve_state,reserve_id from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $id . "'");
		if(empty($obj_reserve)) return array("code" => 500 , "msg" => "预订信息不存在");
		if($obj_reserve['reserve_state']<1)  return array("code" => 500 , "msg" => "预订信息无效");
		if($obj_reserve['reserve_state']<2)  return array("code" => 500 , "msg" => "请先点菜");
		$arr_msg = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=10 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		if($arr_msg['code']==0) {
			$arr_msg = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state=10 where order_reserve_id='" . $obj_reserve['reserve_id'] . "'");
		}
		return $arr_msg;
	}
}