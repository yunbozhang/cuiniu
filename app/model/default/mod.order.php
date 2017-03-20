<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_order extends inc_mod_default {
	/* 今日订单
	 */
	function get_today_list($where = '') {
		$obj_db = cls_obj::db();
		$arr_return = array("list" => array() , "area" => array() , "arrivetime" => array() , "newid" => 0);
		$arr_day = $arr_menu_ids = $arr_act_id = array();
		$str_where = " where 1=1";
		if( !empty($where) ) $str_where .= $where;
		$shop_id = (int)fun_get::get("shop_id");
		if( !empty($shop_id) ) $str_where .= " and order_shop_id='" . $shop_id . "'";
		$arr_state = tab_meal_order::get_perms('state');
		$arr_shop_id = array();
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "meal_order" . $str_where . " order by order_id desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($arr_return['newid'])) $arr_return['newid'] = $obj_rs['order_id'];
			$arr = explode("|" , $obj_rs["order_ids"]);
			$arr_x = array();
			foreach($arr as $item) {
				if(!in_array($item , $arr_x)) {
					$obj_rs["menu"][$item] = array( 'id'=> explode("," , $item) , 'num' => 1);
					$arr_x[] = $item;
				} else {
					$obj_rs["menu"][$item]['num']++;
				}
			}
			//取当时下单的定价
			if(!empty($obj_rs["order_detail"])) {
				$arr_detail = unserialize($obj_rs["order_detail"]);
				if(isset($arr_detail["menu_price"])) $arr_return["price"] = $arr_detail["menu_price"];
			}
			$obj_rs['state'] = array_search($obj_rs['order_state'] , $arr_state);
			$arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));
			if(isset($obj_rs["order_act_ids"])) $arr_act_id[] = $obj_rs["order_act_ids"];
			$obj_rs['shop_name'] = '';
			$arr_return["list"][] = $obj_rs;
			$arr_shop_id[] = $obj_rs['order_shop_id'];
		}
		$arr_menu_ids = array_unique($arr_menu_ids);
		$arr_shop_id = array_unique($arr_shop_id);
		$str_ids = implode("," , $arr_menu_ids);
		if(empty($str_ids)) $str_ids = '0';
		$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_day[] = $obj_rs;
			$arr_return["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
			if(!isset($arr_return["price"]["id_".$obj_rs["menu_id"]])) $arr_return["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
		}
		if(!isset($arr_return["list"])) $arr_return["list"] = array();
		if(!empty($arr_shop_id)) {
			$str_ids = implode("," , $arr_shop_id);
			$arr_shopname = array();
			$obj_result = $obj_db->select("select shop_name,shop_id from " . cls_config::DB_PRE . "meal_shop where shop_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_shopname['id_' . $obj_rs['shop_id']] = $obj_rs['shop_name'];
			}
			$arr = array();
			foreach($arr_return["list"] as $item) {
				if(isset($arr_shopname['id_' . $item['order_shop_id']])) $item['shop_name'] = $arr_shopname['id_' . $item['order_shop_id']];
				$arr[] = $item;
			}
			$arr_return["list"] = $arr;
		}
		if(count($arr_act_id)>0) {
			$str_ids = implode(",", $arr_act_id);
			$arr_act = $arr_act_id = array();
			$arr = explode(",", $str_ids);
			foreach($arr as $item) {
				if(is_numeric($item)) $arr_act_id[] = $item;
			}
			$str_ids = implode(",", $arr_act_id);
			if(empty($str_ids)) $str_ids = '0';
			$obj_result = $obj_db->select("select act_name,act_id from " . cls_config::DB_PRE . "meal_act where act_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_act["id_".$obj_rs["act_id"]] = $obj_rs["act_name"];
			}
			$arr = array();
			foreach($arr_return["list"] as $item) {
				if(!empty($item['order_act_ids'])){
					$str_ids = explode(",", $item['order_act_ids']);
					$arr_actname = array();
					foreach($str_ids as $actid) {
						if(isset($arr_act['id_' . $actid])) $arr_actname[] = $arr_act['id_' . $actid];
					}
					$item['order_act_ids'] = implode("<br>" , $arr_actname);
				}
				$arr[] = $item;
			}
			$arr_return["list"] = $arr;

		}

		return $arr_return;
	}
	//获取新订单
	function get_newnum() {
		$arr_return = array("num" => 0 , "newid" => 0);
		$obj_db = cls_obj::db();
		$date = date("Y-m-d",TIME);
		$str_where = " where order_day='" . $date . "' and order_state=0";
		$id = (int)fun_get::get("id");
		if( !empty($id) ) $str_where .= " and order_id>" . $id;
		$shop_id = (int)fun_get::get("shop_id");
		if( !empty($shop_id) ) $str_where .= " and order_shop_id='" . $shop_id . "'";
		$obj_rs = $obj_db->get_one("select count(1) as num,max(order_id) as newid from " . cls_config::DB_PRE . "meal_order" . $str_where);
		if(!empty($obj_rs)) {
			$arr_return["num"] = $obj_rs["num"];
			$arr_return["newid"] = $obj_rs["newid"];
		}
		return $arr_return;
	}

	/* 确认定单并奖励
	 */
	function on_ok() {
		$arr_return = array("code"=>0 , "msg"=> "确认成功");
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_order::on_ok($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	//接受预订
	function on_accept() {
		$id = (int)fun_get::get("id");
		$arr_return = array("code"=>0 , "msg"=> "确认成功" , "id" => $id);
		$obj_db = cls_obj::db_w();
		$arr = tab_meal_order::on_state($id , 1);
		if($arr["code"]!=0) {
			$arr_return["code"] = $arr['code'];
			$arr_return["msg"] = "处理失败";
			return $arr_return;
		}
		return $arr_return;
	}
	//接受预订
	function on_refuse() {
		$id = (int)fun_get::get("id");
		$beta = fun_get::get("beta");
		$arr_return = array("code"=>0 , "msg"=> "已拒绝预订" , "id" => $id);

		$arr = tab_meal_order::on_state($id , -1 , $beta , "order_state=0");
		if($arr['code'] != 0) return $arr;
		return $arr_return;
	}

	/* 设置订单状态
	 */
	function on_state() {
		$arr_return = array("code"=>0 , "msg"=> "处理成功");
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		$beta = fun_get::get("state_beta");
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$state = (fun_get::get("state_val") == 1) ? 1 : -4;
		$arr = tab_meal_order::on_state($str_id , $state , $beta , "order_state=0");
		if($arr['code'] != 0) return $arr;
		return $arr_return;
	}
	/* 确认定单并奖励
	 */
	function on_award() {
		$arr_return = array("code"=>0 , "msg"=> "奖励成功");
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = "未指定要处理的订单";
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_order::on_award($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	/* 删除指定  order_id 数据
	 */
	function on_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_order::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
}