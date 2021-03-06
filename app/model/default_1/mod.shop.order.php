<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_shop_order extends inc_mod_shop {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist() {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'user_id' => (int)fun_get::get("s_user_id"),
			'state' => (int)fun_get::get("s_state" , -999),
			'key' => fun_get::get("s_key"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "order_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "order_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if(!fun_is::isdate( $arr_search_key['addtime1'] ) && !fun_is::isdate( $arr_search_key['addtime2'] ) && fun_get::get('channel')!='all') {
			$arr_where[] = "order_addtime >='" . strtotime(date("Y-m-d")) . "' and order_addtime<='" . fun_get::endtime(date("Y-m-d")) ."'"; 
		}
		if( $arr_search_key['state'] != -999 ) $arr_where_s[] = "order_state = '" . $arr_search_key['state'] . "'"; 
		if( $arr_search_key['user_id'] != 0 ) $arr_where_s[] = "order_user_id = '" . $arr_search_key['user_id'] . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "(order_name like '%" . $arr_search_key['key'] . "%' or order_tel like '%" . $arr_search_key['key'] . "%' or order_mobile like '%" . $arr_search_key['key'] . "%')";
		//合并查询数组
		$arr_where[] = "order_shop_id='" . $this->shop_info["id"] . "'";
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;
		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.order" , $this->app_dir , "meal");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("shop.order"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_state = tab_meal_order::get_perms("state");
		//取分页信息
		$arr_return["list"] = $arr_act_id = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_order" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_order a left join ".cls_config::DB_PRE."sys_user b on a.order_user_id=b.user_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["order_addtime"])) $obj_rs["order_addtime"] = date("Y-m-d H:i:s" , $obj_rs["order_addtime"]);
			if(isset($obj_rs["order_act_ids"])) $arr_act_id[] = $obj_rs["order_act_ids"];
			if(isset($obj_rs["order_state"])) {
				if($obj_rs["order_state"]>0) {
					$obj_rs["order_state"] = array_search($obj_rs["order_state"] , $arr_state);
				} else {
					$obj_rs["order_state"] = "<font color='#ff0000'>" . array_search($obj_rs["order_state"] , $arr_state) . "</font>";
				}
			}
			$arr_return["list"][] = $obj_rs;
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
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
	/* 今日订单
	 */
	function get_today_list($where = '') {
		$obj_db = cls_obj::db();
		$arr_return = array("list" => array() , "area" => array() , "arrivetime" => array() , "newid" => 0);
		$arr_day = $arr_menu_ids = $arr_act_id = array();
		$str_where = " where 1=1";
		//非管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			$arr_shopinfo = $this->get_loginshop();
			$str_where .= " and order_shop_id='" . $this->shop_info["id"] . "'";
		}

		if( !empty($where) ) $str_where .= $where;
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
			$arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));
			if(isset($obj_rs["order_act_ids"])) $arr_act_id[] = $obj_rs["order_act_ids"];
			$arr_return["list"][] = $obj_rs;
		}
		$arr_menu_ids = array_unique($arr_menu_ids);
		$str_ids = implode("," , $arr_menu_ids);
		$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_day[] = $obj_rs;
			$arr_return["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
			if(!isset($arr_return["price"]["id_".$obj_rs["menu_id"]])) $arr_return["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
		}
		if(!isset($arr_return["list"])) $arr_return["list"] = array();
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
		//取店铺送餐时间
		$obj_shopinfo = $obj_db->get_one("select shop_extend from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $this->shop_info["id"] . "'");
		if(!empty($obj_shopinfo) && !empty($obj_shopinfo["shop_extend"])) {
			$arr = unserialize($obj_shopinfo["shop_extend"]);
			if(isset($arr["arr_arrivetime"])) {
				$arr_return["arrivetime"] = $arr["arr_arrivetime"];
			}
		}

		return $arr_return;
	}
	//获取新订单
	function get_newnum() {
		$arr_return = array("num" => 0 , "newid" => 0);
		$obj_db = cls_obj::db();
		$date = date("Y-m-d",TIME);
		$str_where = " where order_shop_id='" .$this->shop_info["id"] . "' and order_day='" . $date . "' and order_state=0";
		$id = (int)fun_get::get("id");
		if( !empty($id) ) $str_where .= " and order_id>" . $id;
		$obj_rs = $obj_db->get_one("select count(1) as num,max(order_id) as newid from " . cls_config::DB_PRE . "meal_order" . $str_where);
		if(!empty($obj_rs)) {
			$arr_return["num"] = $obj_rs["num"];
			$arr_return["newid"] = $obj_rs["newid"];
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
		$arr = tab_meal_order::on_state($id , 1 ,'',"order_shop_id='" . $this->shop_info["id"] . "'");
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
		$obj_db = cls_obj::db_w();
		$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state=-1,order_state_time='" . date("Y-m-d H:i:s" , TIME) . "',order_state_beta='" . $beta . "' where order_shop_id='" . $this->shop_info["id"] . "' and order_state=0 and order_id='" . $id . "'");
		if($arr["code"]!=0) {
			$arr_return["code"] = $arr['code'];
			$arr_return["msg"] = "处理失败";
			return $arr_return;
		}
		return $arr_return;
	}
}