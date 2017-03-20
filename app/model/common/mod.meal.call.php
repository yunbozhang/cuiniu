<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_call extends inc_mod_common {
	function __construct($arr_v = array() ) {
		//是否登录
		if(!cls_obj::get("cls_user")->is_login() && fun_get::get("app_act")!="print") {
			cls_error::on_error("no_login");
		}
		//是否为管理员
		if(!cls_obj::get("cls_user")->is_admin() && cls_obj::get("cls_user")->type!='shop' && fun_get::get('app_act') != 'print') {
			cls_error::on_error("no_limit" , "没有查看权限");
		}
		parent::__construct($arr_v);
	}
	function get_new_reserve($id = 0 , $hide = 0 , $shop_id = 0)  {
		$obj_db = cls_obj::db();
		$arr_id = $arr_menu_ids = array();
		$arr_order = array("list" => array() , 'ordernum' =>0 , 'ordertotal' => 0 , 'menunum' => 0 , 'act_ids' => array());
		$arr_order['endid'] = 0;
		$time = (int)cls_config::get("order_overtime" , "meal") * 60;
		$arr_order['starttime'] = (fun_get::get("starttime")!='') ? fun_get::get("starttime") : date("Y-m-d");
		$arr_order['endtime'] = (fun_get::get("endtime")!='') ? fun_get::get("endtime") : date("Y-m-d 23:59:59");
		$where = " where reserve_datetime>='" . $arr_order['starttime'] . "' and reserve_datetime<'" . $arr_order['endtime'] . "' and reserve_state>0";
		if($hide==1) $where .= " and reserve_state!=10";
		if(!empty($id)) $where .= " and order_id>" . $id;
		if(!empty($shop_id)) $where .= " and reserve_shop_id='" . $shop_id . "'";
		$arr_config_info = tab_sys_user_config::get_info("reserve.call"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_order["sort"] = $arr_config_info["sort"];
		$arr_award = tab_meal_order::get_perms("award");
		$arr_state = tab_meal_table_reserve::get_perms("state");
		$obj_result = $obj_db->select("select a.*,b.*,c.shop_name,c.shop_linkname,c.shop_linktel,c.shop_tel from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_order b on a.reserve_id=b.order_reserve_id left join " . cls_config::DB_PRE . "meal_shop c on a.reserve_shop_id=c.shop_id" . $where . " order by order_id desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr = explode("|" , $obj_rs["order_ids"]);
			if(isset($obj_rs["order_act_ids"])) $arr_order['act_ids'][] = $obj_rs["order_act_ids"];
			$arr_x = array();
			$obj_rs["menu"] = array();
			foreach($arr as $item) {
				if(empty($item)) continue;
				if(!in_array($item , $arr_x)) {
					$obj_rs["menu"][$item] = array( 'id'=> explode("," , $item) , 'num' => 1);
					$arr_x[] = $item;
				} else {
					$obj_rs["menu"][$item]['num']++;
				}
				$arr_order['menunum']+=1;
			}
			if(isset($obj_rs["order_isaward"])) {
				$obj_rs["order_isaward"] = array_search($obj_rs["order_isaward"] , $arr_award);
			}
			//看是否有活动优惠
			if(!empty($obj_rs["order_act"])) {
				$obj_rs["order_act"] = unserialize($obj_rs["order_act"]);
			} else {
				$obj_rs["order_act"] = array();
			}
			//取当时下单的定价
			if(!empty($obj_rs["order_detail"])) {
				$arr_detail = unserialize($obj_rs["order_detail"]);
				if(isset($arr_detail["menu_price"])) $arr_order["price"] = $arr_detail["menu_price"];
			}
			if(!empty($obj_rs["order_ids"])) $arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));

			if($obj_rs['order_id']>$arr_order['endid']) $arr_order['endid'] = $obj_rs['order_id'];
			$obj_rs['state'] = array_search($obj_rs['reserve_state'] , $arr_state);
			$obj_rs['order_total'] = (float)$obj_rs['order_total'];
			$obj_rs['order_addprice'] = (float)$obj_rs['order_addprice'];
			$obj_rs['order_score_money'] = (float)$obj_rs['order_score_money'];
			$obj_rs['order_favorable'] = (float)$obj_rs['order_favorable'];
			$obj_rs['order_repayment'] = (float)$obj_rs['order_repayment'];
			$obj_rs['order_pay_val'] = (float)$obj_rs['order_pay_val'];
			$obj_rs['reserve_deposit'] = (float)$obj_rs['reserve_deposit'];
			$obj_rs['order_total_pay'] = (float)$obj_rs['order_total_pay'];
			$obj_rs['reserve_datetime'] = date("Y-m-d H:i" , strtotime($obj_rs['reserve_datetime']));
			$arr_order['list'][] = $obj_rs;
			$arr_order['ordernum']++;
			$arr_order['ordertotal']+=$obj_rs['order_total'];
		}
		if(count($arr_menu_ids)>0) {
			$arr_menu_ids = array_unique($arr_menu_ids);
			$str_ids = implode("," , $arr_menu_ids);
			$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_order["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
				if(!isset($arr_order["price"]["id_".$obj_rs["menu_id"]])) $arr_order["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
			}
		}
		$arr_order['ids'] = implode("," , $arr_id);
		return $arr_order;
	}
	//取当天未处理订单
	function get_new_order($id = 0 , $hide = 0 , $area_id = array() , $shop_id = 0) {
		$obj_db = cls_obj::db();
		$arr_areaid = $arr_id = $arr_menu_ids = array();
		$arr_order = array("list" => array() , 'ordernum' =>0 , 'ordertotal' => 0 , 'menunum' => 0 , 'act_ids' => array());
		$arr_order['endid'] = 0;
		$time = (int)cls_config::get("order_overtime" , "meal") * 60;
		$arr_order['starttime'] = (fun_get::get("starttime")!='') ? fun_get::get("starttime") : date("Y-m-d");
		$arr_order['endtime'] = (fun_get::get("endtime")!='') ? fun_get::get("endtime") : date("Y-m-d 23:59:59");
		$where = " where order_day>='" . $arr_order['starttime'] . "' and order_day<'" . $arr_order['endtime'] . "' and order_reserve_id=0";
		if($hide==1) $where .= " and order_state=0";
		if(!empty($id)) $where .= " and order_id>" . $id;
		if(!empty($shop_id)) $where .= " and order_shop_id='" . $shop_id . "'";
		if(!empty($area_id)) {
			if(!is_array($area_id)) $area_id = array($area_id);
			$arr_where2 = array();
			foreach($area_id as $item) {
				$arr_where2[] = $obj_db->concat("," , "order_area_allid" , ",") . " like '%," . $item . ",%'";
			}
			$where .= " and (" . implode(" or " , $arr_where2) . ")";
		}
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.call"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_order["sort"] = $arr_config_info["sort"];
		$arr_paymethod = cls_config::get("" , "pay" , "" , "");
		$arr_award = tab_meal_order::get_perms("award");
		$arr_state = tab_meal_order::get_perms("state");
		$obj_result = $obj_db->select("select a.*,b.shop_name,b.shop_linkname,b.shop_linktel,b.shop_tel,c.pay_method,d.user_name from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id left join " . cls_config::DB_PRE . "other_pay c on a.order_pay_id=c.pay_id left join " . cls_config::DB_PRE . "sys_user d on a.order_user_id=d.user_id" . $where . " order by order_id desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if($obj_rs['order_pay_method'] == 'onlinepayment' && $obj_rs['order_pay_val']<$obj_rs['order_total_pay']) continue;
			if(isset($arr_paymethod[$obj_rs['pay_method']])) $obj_rs['pay_method'] = $arr_paymethod[$obj_rs['pay_method']]['name'];
			$arr = explode("|" , $obj_rs["order_ids"]);
			if(isset($obj_rs["order_act_ids"])) $arr_order['act_ids'][] = $obj_rs["order_act_ids"];
			$arr_x = array();
			foreach($arr as $item) {
				if(!in_array($item , $arr_x)) {
					$obj_rs["menu"][$item] = array( 'id'=> explode("," , $item) , 'num' => 1);
					$arr_x[] = $item;
				} else {
					$obj_rs["menu"][$item]['num']++;
				}
				$arr_order['menunum']+=1;
			}
			if(isset($obj_rs["order_isaward"])) {
				$obj_rs["order_isaward"] = array_search($obj_rs["order_isaward"] , $arr_award);
			}
			//取当时下单的定价
			if(!empty($obj_rs["order_act"])) {
				$obj_rs["order_act"] = unserialize($obj_rs["order_act"]);
			} else {
				$obj_rs["order_act"] = array();
			}
			//看是否有活动优惠
			if(!empty($obj_rs["order_detail"])) {
				$arr_detail = unserialize($obj_rs["order_detail"]);
				if(isset($arr_detail["menu_price"])) $arr_order["price"] = $arr_detail["menu_price"];
			}
			$arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));

			if($obj_rs['order_id']>$arr_order['endid']) $arr_order['endid'] = $obj_rs['order_id'];
			if(empty($obj_rs["order_state"]) && $obj_rs['order_addtime']+$time<TIME) {
				$obj_rs['state'] = 1;
			} else {
				$obj_rs['state'] = array_search($obj_rs['order_state'] , $arr_state);
			}
			if(!empty($obj_rs["order_telext"])) $obj_rs["order_tel"] .= "转" . $obj_rs["order_telext"];
			if( empty($obj_rs["order_state"]) ) $arr_id[] = $obj_rs["order_id"];
			$obj_rs['order_total'] = (float)$obj_rs['order_total'];
			$obj_rs['order_addprice'] = (float)$obj_rs['order_addprice'];
			$obj_rs['order_score_money'] = (float)$obj_rs['order_score_money'];
			$obj_rs['order_favorable'] = (float)$obj_rs['order_favorable'];
			$obj_rs['order_repayment'] = (float)$obj_rs['order_repayment'];
			$obj_rs['order_pay_val'] = (float)$obj_rs['order_pay_val'];
			$obj_rs['order_total_pay'] = (float)$obj_rs['order_total_pay'];
			$obj_rs['order_addtime'] = date("Y-m-d H:i" , $obj_rs['order_addtime']);
			$arr_order['list'][] = $obj_rs;
			$arr_order['ordernum']++;
			$arr_order['ordertotal']+=$obj_rs['order_total'];
		}
		if(count($arr_menu_ids)>0) {
			$arr_menu_ids = array_unique($arr_menu_ids);
			$str_ids = implode("," , $arr_menu_ids);
			$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_order["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
				if(!isset($arr_order["price"]["id_".$obj_rs["menu_id"]])) $arr_order["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
			}
		}
		$arr_order['ids'] = implode("," , $arr_id);
		return $arr_order;
	}
	//取指定ids 订单状态
	function get_order_state() {
		$obj_db = cls_obj::db();
		$str_ids = fun_get::post("ids");
		if(empty($str_ids)) return array();
		$time = (int)cls_config::get("order_overtime" , "meal") * 60;
		$arr = array();
		$arr_ids = explode("," , $str_ids);
		foreach($arr_ids as $item) {
			if(empty($item)) continue;
			$arr[] = $item;
		}
		$str_ids = implode("," , $arr);
		if(empty($str_ids)) $str_ids = '0';
		$arr = array();
		$where = " where order_id in(" . $str_ids . ")";
		//非管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			$arr_shopinfo = $this->get_loginshop();
			$where .= " and order_shop_id='" . $arr_shopinfo["shop_id"] . "'";
		}
		$arr_state = tab_meal_order::get_perms("state");
		$obj_result = $obj_db->select("select order_id,order_addtime,order_state from " . cls_config::DB_PRE . "meal_order" . $where . " order by order_id");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs["order_state"]) && $obj_rs['order_addtime']+$time<TIME) {
				$arr['id_'.$obj_rs['order_id']] = array("order_state" => $obj_rs['order_state'] , "state" => 1);
			} else {
				$arr['id_'.$obj_rs['order_id']] = array('order_state'=>$obj_rs['order_state'] , 'state' => array_search($obj_rs['order_state'] , $arr_state));
			}
		}
		return $arr;
	}
	//接受预订
	function on_accept() {
		$id = (int)fun_get::get("id");
		$arr_return = array("code"=>0 , "msg"=> "接受成功" , "id" => $id);
		$where = "";
		//非管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			$arr_shopinfo = $this->get_loginshop();
			$where = "order_shop_id='" . $arr_shopinfo["shop_id"] . "'";
		}
		$arr = tab_meal_order::on_state($id , 1 ,'',$where);
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr['code'];
			$arr_return["msg"] = "处理失败";
			return $arr_return;
		}
		return $arr_return;
	}
	//作废订单
	function on_invalid() {
		$id = (int)fun_get::get("id");
		$arr_return = array("code"=>0 , "msg"=> "作废成功" , "id" => $id);
		$where = "";
		//非管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			$arr_shopinfo = $this->get_loginshop();
			$where = "order_shop_id='" . $arr_shopinfo["shop_id"] . "'";
		}
		$arr = tab_meal_order::on_state($id , -6 ,'',$where);
		if($arr["code"] != 0) {
			$arr_return["code"] = $arr['code'];
			$arr_return["msg"] = "作废处理失败";
			return $arr_return;
		}
		return $arr_return;
	}
	function get_loginshop() {
			$obj_shop = cls_obj::db()->get_one("select shop_id,shop_name,shop_state,shop_progress from " . cls_config::DB_PRE . "meal_shop where shop_user_id='" . cls_obj::get("cls_user")->uid . "'");
			if(empty($obj_shop)) return array("shop_id" => 0 , "shop_name" => "" , "shop_state" => 0 , "shop_progress" => '');
			return $obj_shop;
	}
	//取消预订
	function on_cancel() {
		$id = (int)fun_get::get("id");
		$closeshop = (int)fun_get::get("closeshop");
		$issms = (int)fun_get::get("issms");
		$beta = fun_get::get("beta");
		$arr_return = array("code"=>0 , "msg"=> "成功取消订单" , "id" => $id);
		$where = "order_state=0";
		//非管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			$arr_shopinfo = $this->get_loginshop();
			$where .= " and order_shop_id='" . $arr_shopinfo["shop_id"] . "'";
		}
		$obj_db = cls_obj::db_w();
		$obj_shop = cls_obj::db()->get_one("select order_shop_id,order_mobile from " . cls_config::DB_PRE . "meal_order where order_id='" . $id . "' and " . $where);
		$arr = tab_meal_order::on_state($id , -4 , $beta , $where);
		if($arr["code"]!=0) {
			$arr_return["code"] = $arr['code'];
			$arr_return["msg"] = "处理失败";
			return $arr_return;
		} else {
			//关闭店铺运营状态
			if($closeshop == 1) {
				if(!empty($obj_shop)) {
					$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_state=-1 where shop_id='" . $obj_shop['order_shop_id'] . "'");
				}
			}
			//发送手机短信
			if( $issms == 1 && !empty($obj_shop['order_mobile']) ) {
				//$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$obj_shop['order_mobile'] , "cont" => $beta ,"id" => $id , "type"=>2) );
			}
		}
		return $arr_return;
	}
	function get_shop_info($shop_id = 0) {
		if(empty($shop_id)) {
			$where = "shop_user_id='" . cls_obj::get("cls_user")->uid . "'";
		} else {
			$where = "shop_id='" . $shop_id . "'";
		}
		$obj_info = cls_obj::db()->edit(cls_config::DB_PRE ."meal_shop" , $where);
		return $obj_info;
	}
	function on_sms_return() {
			$arr = cls_obj::get('cls_com')->sms("get_recont");
			if($arr['code']!=0) return $arr;
			if(empty($arr['list'])) return array("code"=>0);
			foreach($arr['list'] as $row) {
				if(!isset($arr_shoptel['tel_' . $row['tel']])) {
					$obj_rs = cls_obj::db()->get_one("select shop_sms_tel,shop_id,shop_name from " . cls_config::DB_PRE . "meal_shop where shop_sms_tel like '%" . $row['tel'] . "%'");
					if(empty($obj_rs)) continue;
					$arr_tel = explode("," , $obj_rs['shop_sms_tel']);
					foreach($arr_tel as $tel) {
						$arr_shoptel['tel_' . trim($tel)] = $obj_rs;
					}
				}
				//将短信状态设置为已回复
				cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "other_sms set sms_recont='" . $row['cont'] . "',sms_retime='" . $row['time'] . "' where sms_confirm_id='" . $row['confirm_id'] . "' and sms_day='" . date("Y-m-d") . "' and sms_tel='" . $row['tel'] . "'");
				//插入回复记录
				tab_other_sms_re::on_save(array('re_tel'=>$row['tel'] , "re_cont" => $row['cont'] , "re_time" => $row['time'] , "re_day" => date("Y-m-d" , strtotime($row['time']))));
				//更改订单状态
				(empty($row['cont']))? $state = 1 : $state = -1;

				$where = " order_shop_id='" . $arr_shoptel['tel_' . $row['tel']]['shop_id'] . "' and right(order_id," . strlen($row['confirm_id']) . ")='" . $row['confirm_id'] . "'";
				tab_meal_order::on_state('' , $state , $row['cont'] , $where);
			}
	}
	function on_excel() {
		//非管理员
		$shop_id = 0;
		$area_id = 0;
		if(!cls_obj::get("cls_user")->is_admin()) {
			$this->shopinfo = $this->get_loginshop();
			$shop_id = $this->shopinfo['shop_id'];
		} else {
			$area_info = $this->get_area_info();
			$area_id = $area_info['id'];
		}

		$hide_detail = tab_sys_user_config::get_var("call.hide.detail"  , $this->app_dir);
		$hide_handle = tab_sys_user_config::get_var("call.hide.handle"  , $this->app_dir);
		$arr_return = $this->get_new_order(0 , $hide_handle , $area_id , $shop_id);
		$arr_act_id = $arr_return['act_ids'];
		if(count($arr_act_id)>0) {
			$str_ids = implode(",", $arr_act_id);
			$arr_act = $arr_act_id = array();
			$arr = explode(",", $str_ids);
			foreach($arr as $item) {
				if(is_numeric($item)) $arr_act_id[] = $item;
			}
			$str_ids = implode(",", $arr_act_id);
			if(empty($str_ids)) $str_ids = '0';
			$obj_result = cls_obj::db()->select("select act_name,act_id from " . cls_config::DB_PRE . "meal_act where act_id in(" . $str_ids . ")");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
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
					$item['order_act_ids'] = implode("；" , $arr_actname);
				}
				$arr[] = $item;
			}
			$arr_return["list"] = $arr;
		}
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.order" , 'admin' , "meal");
		$arr_cfg_fields["sel"] = substr(str_replace(",user_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",shop_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
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
				if($field["key"] == 'order_ticket' && empty($val)) $val = '';
				if($field["key"] == 'order_act_ids') $val = str_replace('<br>' , '；' , $val);
				$arr[] = $val;
			}
			$item['order_detail'] = unserialize($item['order_detail']);
			$arr_excel[] = $arr;
			if(!$hide_detail) {
				$arr = array();
				foreach($item['menu'] as $menu=>$key) {
					if(isset($arr_return['menu']['id_'.$menu])) {
						$price = isset($item['order_detail']['menu_price']['id_'.$menu]) ? (float)$item['order_detail']['menu_price']['id_'.$menu] : $arr_return['menu']['id_'.$menu]['menu_price'];
						$arr[] = $arr_return['menu']['id_'.$menu]['menu_title'] . "：" . cls_config::get("coinsign","sys") . $price . "×" . $key['num'] . "=" . cls_config::get("coinsign","sys") . ($key['num']*$price);
					}
				}
				$arr = array('' , implode("；" , $arr));
				for($i = 2 ; $i < count($arr_return['tabtit']) ; $i++) {
					$arr[] = '';
				}
				$arr_excel[] = $arr;
			}
		}
		$get_excel_name = fun_get::get("excel_name" , date("Y-m-d"));
		cls_excel::save_excel($get_excel_name.".xls",$arr_excel);
	}
	function on_reserve_cancel($id) {
		$obj_order = cls_obj::db()->get_one("select order_reserve_id from " . cls_config::DB_PRE . "meal_order where order_id='" . $id . "'");
		if(empty($obj_order)) return array('code' => 500 , 'msg' => "订单信息不存在");
		$obj_reserve = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $obj_order['order_reserve_id'] . "'");
		if(empty($obj_reserve)) return array('code' => 500 , 'msg' => '预订信息不存在');
		if($obj_reserve['reserve_state']==10)  return array('code' => 500 , 'msg' => '已完成的订单不能取消');
		$arr = tab_meal_order::on_state($id , -4 , '后台取消');
		if($arr['code'] == 0) {
			$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=-1 where reserve_id='" . $obj_order['order_reserve_id'] . "'");
		}
		$arr['id'] = $id;
		return $arr;
	}
	function on_upmenu() {
		$id = fun_get::get("id");
		$obj_order = cls_obj::db()->get_one("select order_reserve_id from " . cls_config::DB_PRE . "meal_order where order_id='" . $id . "'");
		if(empty($obj_order)) return array('code' => 500 , 'msg' => "订单信息不存在");
		$obj_reserve = cls_obj::db()->get_one("select reserve_state,reserve_id from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $obj_order['order_reserve_id'] . "'");
		if(empty($obj_reserve)) return array("code" => 500 , "msg" => "预订信息不存在");
		if($obj_reserve['reserve_state']<1)  return array("code" => 500 , "msg" => "预订信息无效");
		if($obj_reserve['reserve_state']<2)  return array("code" => 500 , "msg" => "请先点菜");
		if($obj_reserve['reserve_state']>=3)  return array("code" => 0 , "msg" => "");
		$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_menutime='" . TIME . "',reserve_state=3 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		return $arr;
	}
	function on_reserve_pay() {
		$id = fun_get::get("id");
		$obj_order = cls_obj::db()->get_one("select order_reserve_id from " . cls_config::DB_PRE . "meal_order where order_id='" . $id . "'");
		if(empty($obj_order)) return array('code' => 500 , 'msg' => "订单信息不存在");
		$obj_reserve = cls_obj::db()->get_one("select reserve_state,reserve_id from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $obj_order['order_reserve_id'] . "'");
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