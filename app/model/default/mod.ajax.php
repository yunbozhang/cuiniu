<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_ajax extends inc_mod_default {
	function shop_table_reserve_save() {
		$number = fun_get::get("reserve_id");
		$shop_id = (int)fun_get::get("id");
		$obj_reserve = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_number='" . $number . "'");
		if(empty($obj_reserve)) return array("code" => "500" , "msg" => "预订桌位不存在");
		if($obj_reserve['reserve_state']<1) return array("code" => "500" , "msg" => "预订桌位无效，不能点餐");
		if($obj_reserve['reserve_state']==10) return array("code" => "500" , "msg" => "该桌位已用使用结束，不能再点餐了");
		$obj_order = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_order where order_reserve_id='" . $obj_reserve['reserve_id'] . "'");
		if(empty($obj_order)) return array("code" => "500" , "msg" => "预订桌位不存在");
		$arr_order = array(
			"order_id" => $obj_order['order_id'],
			"order_ids" => $obj_order['order_ids'],
			"order_total" => $obj_order['order_total'],
			"order_total_pay" => $obj_order['order_total_pay'],
			"order_pay_val" => $obj_order['order_pay_val'],
			"order_repayment" => $obj_order['order_repayment'],
		);
		$arr = $this->save_order($arr_order);
		if($arr['code'] == 0 && $obj_reserve['reserve_state']==1) {
			cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=2 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		}
		return $arr;
	}
	function on_reserve_delmenu() {
		$number = fun_get::get("number");
		$shop_id = (int)fun_get::get("id");
		$menuid = (int)fun_get::get("menuid");
		$obj_reserve = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_number='" . $number . "'");
		if(empty($obj_reserve)) return array("code" => "500" , "msg" => "预订桌位不存在");
		if($obj_reserve['reserve_state']<1) return array("code" => "500" , "msg" => "预订桌位无效，不能操作菜品");
		if($obj_reserve['reserve_state']==10) return array("code" => "500" , "msg" => "该桌位已用使用结束，不能操作菜品");
		$obj_order = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_order where order_reserve_id='" . $obj_reserve['reserve_id'] . "'");
		if(empty($obj_order)) return array("code" => "500" , "msg" => "预订桌位不存在");
		$arr_ids = explode("|",$obj_order['order_ids']);
		$num = substr_count(str_replace("|","||","|".$obj_order['order_ids']."|"),"|".$menuid."|");
		$arr = fun_base::array_remove($arr_ids , $menuid);
		if(empty($arr)) {
			$arr_order = array(
				"order_id" => $obj_order['order_id'],
				"order_ids" => '',
				"order_detail" => '',
				"order_favorable" => 0,
				"order_act_ids" => '',
				"order_act" => '',
				"order_total_pay" => 0,
				"order_total" => 0,
				"order_repayment" => $obj_reserve['reserve_deposit'],
				"order_addprice" => 0,
				"order_state" => -2,
			);
			cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=1 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		} else {
			$obj_order['order_ids'] = implode("|" , $arr);
			$arr_detail = unserialize($obj_order['order_detail']);
			$price = 0;
			if(isset($arr_detail['menu_price']['id_'.$menuid])) {
				$price = (float)$arr_detail['menu_price']['id_'.$menuid];
				unset($arr_detail['menu_price']['id_'.$menuid]);
			}
			$total_price = $num * $price;
			$obj_order['order_total'] = $obj_order['order_total'] - $total_price;
			$obj_order['order_detail'] = $arr_detail;
			//去除优惠与抵扣后的应付
			$lng_total = $obj_order['order_total'];
			$lng_total += $obj_order['order_addprice'];//配送费
			//算积分
			$arr_action = cls_config::get("meal_submit_order_ok" , 'user.action' , '' , '');
			$basescore = $lng_total * (float)$arr_action['basescore'];
			if(!empty($arr_action['addscore'])) $basescore += intval($arr_action['addscore']);
			//是否满足活动
			$actscore = 0;
			$num = count(explode("|" , $obj_order['order_ids']));
			$shop_act_id= $shop_act = array();
			$obj_order["order_favorable"] = 0;
			$arr_act_where = $this->get_shop_act($obj_order['order_shop_id'] , $obj_order['order_total'] , $num , $obj_order['order_pay_method']);
			foreach($arr_act_where as $item) {
				if($item['act_method'] == '2') {//达到指定金额
					$obj_order["order_favorable"] += $obj_order["order_total"] - (float)($obj_order["order_total"]*(float)$item['act_method_val']/10);
				} else if($item['act_method'] == '5') {//按立减
					$obj_order["order_favorable"] += (float)$item['act_method_val'];
				} else if($item['act_method'] == '6') {//每份减
					$item['act_method_val'] = (float)$item['act_method_val'];
					$obj_order["order_favorable"] = $obj_order["order_favorable"] + $item['act_method_val'] * $num;
				} else if($item['act_method'] == '3') {//赠送固定积分
					$obj_order["order_detail"]['score_add'] = (int)$item['act_method_val'];
					$actscore += (int)$item['act_method_val'];
				} else if($item['act_method'] == '4') {//积分翻倍
					$obj_order += $basescore * (float)$item['act_method_val'];
				} else if($item['act_method'] == '7') {//免配送费
					$lng_total -= $arr_order['order_addprice'];
					$obj_order['order_addprice'] = 0;
				}
				$shop_act_id[] = $item['act_id'];
				$shop_act[] = $item['act_name'];
			}
			$lng_total = $lng_total - $obj_order["order_favorable"];//减优惠
			$obj_order['order_act_ids'] = implode(",",$shop_act_id);
			$obj_order['order_act'] = $shop_act;
			$basescore += $actscore;
			$obj_order["order_detail"]['score'] = (int)$basescore;
			$lng_total -= $obj_order['order_repayment'];//减预付款
			$obj_order["order_total_pay"] = $lng_total;
			$arr_order = array(
				"order_id" => $obj_order['order_id'],
				"order_ids" => $obj_order['order_ids'],
				"order_detail" => serialize($obj_order['order_detail']),
				"order_favorable" => $obj_order['order_favorable'],
				"order_act_ids" => $obj_order['order_act_ids'],
				"order_act" => $obj_order['order_act'],
				"order_total_pay" => $obj_order['order_total_pay'],
				"order_total" => $obj_order['order_total'],
				"order_addprice" => $obj_order['order_addprice'],
			);
		}
		$arr = tab_meal_order::on_save($arr_order);
		return $arr;
	}
	//保存定单
	function save_order($obj_order = array()){
		$arr_return=array("code"=>0,"msg"=>"下单成功");
		//是否登录
		if(!cls_obj::get("cls_user")->is_login() && cls_config::get("nologin","base") != 1) {
			return array("code" => 500 , "msg" => "请先登录再来下单");
		}
		$cfg_print = cls_config::get("" , "print","","");
		$obj_db = cls_obj::db_w();
		//取定单信息
		$cart_ids = fun_get::get("cart_ids");
		$arr_cart = $this->format_cart($cart_ids);
		if(count($arr_cart['shop_ids'])<1) {
			$arr_return["code"]=1;
			$arr_return["msg"]="请选择您要点的商品";
			return $arr_return;
		}
		$arr = explode(":" , $cart_ids);
		$menu_ids = str_replace("|" , "," , $arr[1]);
		$str_ids = $arr[1];
		$shop_id_base=$shop_id = $arr_cart['shop_ids'][0];
		if(!is_numeric($shop_id)) {
			$arr = explode("-" , $shop_id);
			$shop_id = intval($arr[0]);
		}
		//查看店铺运营信息
		$obj_shop = $obj_db->get_one("select shop_name,shop_state,shop_user_id,shop_sms,shop_sms_tel,shop_extend,shop_addprice,shop_email,shop_weixin_id,shop_print_auto,shop_day_limit,shop_order_time,shop_day_sold from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(empty($obj_shop)) {
			return array("code"=>500 , "msg"=>"您选择的商家不存在");
		}
		if($obj_shop["shop_state"]<1) {
			return array("code"=>500 , "msg"=>"您选择的商家暂时未开通网上预订服务，请选择其它商家");
		}
		$obj_shop["extend"] = (!empty($obj_shop['shop_extend'])) ? unserialize($obj_shop["shop_extend"]) : array();
		$obj_shop['extend']['weekday'] = empty($obj_shop['extend']['weekday']) ?  array() : explode("," , $obj_shop['extend']['weekday']);
		$arr_opentime = tab_meal_shop::get_opentime($shop_id,$obj_shop['shop_extend']);
		$reserve_day = fun_get::get("reserve_day");
		$weekday = empty($reserve_day) ? date("w") : date("w",strtotime($reserve_day));
		if($arr_opentime['nowindex']<1 ||  (!empty($obj_shop['extend']['weekday']) && !in_array($weekday,$obj_shop['extend']['weekday']))) return array("code" => 500 , "msg" => "当前非商家营业时间，请重新选择");

		if($obj_shop['shop_order_time']<strtotime(date("Y-m-d"))) $obj_shop['shop_day_sold'] = 0;
		$address_id = (int)fun_get::get("address_id");
		$obj_info = array();
		if(!empty($address_id) || empty($obj_order) ) {
			$obj_info = $obj_db->get_one("select * from " . cls_config::DB_PRE . "sys_user_address where address_id='" . $address_id . "'");
			if(empty($obj_info)) return array("code"=>500 , "msg"=>"请选择配送地址");
		}
		$arr_order=array(
			"order_user_id" => cls_obj::get("cls_user")->uid,
			"order_shop_id" => $shop_id,
			"order_arrive" => fun_get::post("arrive"),
			"order_ticket" => (int)fun_get::post("ticket"),
			"order_ids" => $str_ids,
			"order_beta" => fun_get::post("beta"),
			"order_pay_method" => fun_get::post("paymethod"),
			"order_addprice" => (float)fun_get::post("addprice"),
			"order_repayment" => (float)fun_get::post("repayment"),
		);
		if(!empty($obj_order)) $arr_order['order_repayment'] = $arr_order['order_repayment']+$obj_order["order_repayment"];
		if(!empty($obj_info)) {
			$arr_order["order_name"] = $obj_info["address_name"];
			$arr_order["order_area_id"] = $obj_info["address_area_id"];
			$arr_order["order_area_allid"] = $obj_info["address_area_allid"];
			$arr_order["order_area"] = $obj_info["address_area"];
			$arr_order["order_louhao1"] = $obj_info["address_address"];
			$arr_order["order_mobile"] = $obj_info["address_tel"];
		}
		if(isset($obj_order['order_id'])) $arr_order['order_id'] = $obj_order['order_id'];
		if($arr_order['order_user_id']>0) {
			$obj_newuser = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_user_id='" . $arr_order['order_user_id'] . "' and (order_state>=0 or order_state=-2)");
		} else {
			$obj_newuser = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_mobile='" . $arr_order['order_mobile'] . "' and (order_state>=0 or order_state=-2)");
		}
		if(empty($obj_newuser)) $arr_order['order_newuser'] = 1;
		$score_money_scale = (float)cls_config::get("score_money_scale" , "meal");
		if($score_money_scale > 0 )  $arr_order['order_isaward'] = -1;
		//计价
		//取商品信息
		$arr_today_menu = $arr_sold = array();
		$totalnum = 0;
		$datetime = date("Y-m-d H:i:s");
		$weekday = empty($reserve_day) ? date("w") : date("w",strtotime($reserve_day));
		$mode_day = empty($reserve_day) ? date("d") : date("d",strtotime($reserve_day));
		$today_date = empty($reserve_day) ? strtotime(date('Y-m-d' , TIME)) : strtotime(date('Y-m-d' , strtotime($reserve_day)));
		$str_ids = implode("," , $arr_cart["menu_ids"]);
		$obj_result = $obj_db->select("select menu_id,menu_state,menu_shop_id,menu_mode,menu_weekday,menu_date,menu_sold_time,menu_num,menu_sold_today,menu_title,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if($obj_rs["menu_state"]<1 || $obj_rs["menu_shop_id"]!=$shop_id) {
				$arr_return['code']=500;
				$arr_return['msg']="选择商品【" . $obj_rs["menu_title"] . "】不存在,请重新选择后再提交";
				return $arr_return;
			}
			//按星期
			if($obj_rs['menu_mode'] == 1 && !in_array($weekday,explode(",",$obj_rs["menu_weekday"]))) {
				$arr_return['code']=500;
				$arr_return['msg']="选择商品【" . $obj_rs["menu_title"] . "】今天暂不提供,请重新选择后再提交";
				return $arr_return;
			}
			//按日期
			if($obj_rs['menu_mode'] == 3 && !in_array($mode_day,explode(",",$obj_rs["menu_date"]))) {
				$arr_return['code']=500;
				$arr_return['msg']="选择商品【" . $obj_rs["menu_title"] . "】今天暂不提供,请重新选择后再提交";
				return $arr_return;
			}
			//计算当前剩余量
			$num1 = 0;
			$num1 = substr_count("," . str_replace("," , ",,", $menu_ids) . "," , ','.$obj_rs["menu_id"].',');
			if(empty($reserve_day)) {
				if($obj_rs["menu_sold_time"] < strtotime(date("Y-m-d"))) $obj_rs["menu_sold_today"] = 0;
				($obj_rs["menu_num"]>0) ? $obj_rs["num"] = $obj_rs["menu_num"] - $obj_rs["menu_sold_today"] : $obj_rs["num"] = 0;
				//如果是自定义的，则按自定义数量
				if($obj_rs["menu_mode"] == 2) {
					$arr_today_menu[] = $obj_rs["menu_id"];
				} else {
					if($obj_rs["menu_num"]>0 && $obj_rs["num"] < $num1) {
						$arr_return["code"]=1;
						if($obj_rs["num"]>0) {
							$arr_return["msg"]="【".$obj_rs["menu_title"]."】当前只剩" . $obj_rs["num"] . "份，请重新选择";
						} else {
							$arr_return["msg"]="【".$obj_rs["menu_title"]."】当天已售完，请选择其它商品";
						}
						return $arr_return;
					}
				}
			}
			$arr_menu["id_".$obj_rs["menu_id"]] = $obj_rs;
			$arr_sold[] = array("sold_datetime" => $datetime , "sold_menu_id" => $obj_rs['menu_id'] , "sold_num" => $num1 , "sold_price" => $obj_rs['menu_price'] , "sold_total" => $obj_rs["menu_price"] * $num1 , "sold_shop_id" => $obj_rs['menu_shop_id'] , "sold_user_id" => cls_obj::get("cls_user")->uid);
			$totalnum += $num1;
		}
		if(empty($reserve_day) && $obj_shop['shop_day_limit']>0 && ($obj_shop['shop_day_sold']+$totalnum)>$obj_shop['shop_day_limit']) {
			$x = $obj_shop['shop_day_limit'] - $obj_shop['shop_day_sold'];
			$msg = ($x==0) ? "【" . $obj_shop["shop_name"] . "】当天已售完，请选择别的商家" :  "【数量不足】当前仅有" . $x . "份，请修改数量重新下单";
			return array("code" => 500 , "msg" => $msg);
		}
		$obj_shop['shop_day_sold']+=$totalnum;
		//如果有当天商品
		if(empty($reserve_day) && count($arr_today_menu)>0) {
			//取每天自定义商品
			$arr_opentime = tab_meal_shop::get_opentime($shop_id);
			$date_period = $arr_opentime['nowindex'];
			if($date_period>0) {
				$where_today = " and (today_date_period='".$date_period."' or today_date_period=0)";
			} else {
				$where_today = " and today_date_period='".$date_period."'";
			}

			$str_ids = implode("," , $arr_today_menu);
			$obj_result = $obj_db->select("select today_menu_id,today_num,today_sold from " . cls_config::DB_PRE . "meal_menu_today where today_shop_id='" . $shop_id . "' and today_menu_id in(" . $str_ids . ") and today_date='" . $today_date . "'" . $where_today);
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$num = 0;
				if($obj_rs["today_num"]>0) $num = $obj_rs["today_num"]-$obj_rs["today_sold"];
				$num1 = substr_count("," . str_replace("," , ",,", $menu_ids) . "," , $obj_rs["today_menu_id"]);
				if($obj_rs["today_num"] > 0 && $num < $num1) {
					$arr_return["code"]=1;
					if($num>0) {
						$arr_return["msg"]="【".$arr_menu["id_" . $obj_rs["today_menu_id"]]["menu_title"]."】当前只剩" . $num . "份，请重新选择";
					} else {
						$arr_return["msg"]="【".$arr_menu["id_" . $obj_rs["today_menu_id"]]["menu_title"]."】当天已售完，请选择其它商品";
					}
					return $arr_return;
				}
				$arr_menu["id_" . $obj_rs["today_menu_id"]]["num"] = $num;
			}
		}
		$lng_total=0;
		//保存当前菜品价格
		$menu_price = array();
		$arr_a = explode("," , $menu_ids);
		foreach($arr_a as $item){
			if(!isset($arr_menu["id_".$item])) {
				$arr_return["code"] = 500;
				$arr_return["msg"] = "选择商品没找到，清空购物车重新选择";
				return $arr_return;
			}
			$lng_total+=$arr_menu["id_".$item]["menu_price"];
			$menu_price['id_'.$item] = $arr_menu["id_".$item]['menu_price'];
		}
		$arr_order["order_detail"] = array("menu_price" => $menu_price);
		if(isset($obj_order['order_total'])) $lng_total += $obj_order["order_total"];//表示修改
		if(isset($obj_order['order_ids']) && !empty($obj_order['order_ids']) ) $arr_order['order_ids'] = $arr_order['order_ids'] . "|" . $obj_order["order_ids"];//表示修改
		if(isset($obj_order['order_pay_val'])) $arr_order['order_pay_val'] = $obj_order["order_pay_val"];//表示修改
		//总价
		$arr_order["order_total"] = $lng_total;
		$arr_order["order_favorable"] = 0;
		//去除优惠与抵扣后的应付
		$lng_total += $arr_order['order_addprice'];//配送费
		//算积分
		$arr_action = cls_config::get("meal_submit_order_ok" , 'user.action' , '' , '');
		$basescore = $lng_total * (float)$arr_action['basescore'];
		if(!empty($arr_action['addscore'])) $basescore += intval($arr_action['addscore']);
		//是否满足活动
		$shop_act_id = fun_get::post("shop_act_id");
		if(!empty($shop_act_id) && is_array($shop_act_id)) {
			$actscore = 0;
			$num = count(explode("|" , $arr_order['order_ids']));
			$shop_act_id= $shop_act = array();
			$arr_act_where = $this->get_shop_act($arr_order['order_shop_id'] , $arr_order['order_total'] , $num , $arr_order['order_pay_method']);
			foreach($arr_act_where as $item) {
				if($item['act_method'] == '2') {//达到指定金额
					$arr_order["order_favorable"] += $arr_order["order_total"] - (float)($arr_order["order_total"]*(float)$item['act_method_val']/10);
				} else if($item['act_method'] == '5') {//按立减
					$arr_order["order_favorable"] += (float)$item['act_method_val'];
				} else if($item['act_method'] == '6') {//每份减
					$item['act_method_val'] = (float)$item['act_method_val'];
					$arr_order["order_favorable"] = $arr_order["order_favorable"] + $item['act_method_val'] * $num;
				} else if($item['act_method'] == '3') {//赠送固定积分
					$arr_order["order_detail"]['score_add'] = (int)$item['act_method_val'];
					$actscore += (int)$item['act_method_val'];
				} else if($item['act_method'] == '4') {//积分翻倍
					$actscore += $basescore * (float)$item['act_method_val'];
				} else if($item['act_method'] == '7') {//免配送费
					$lng_total -= $arr_order['order_addprice'];
					$arr_order['order_addprice'] = 0;
				}
				$shop_act_id[] = $item['act_id'];
				$shop_act[] = $item['act_name'];
			}
			$lng_total = $lng_total - $arr_order["order_favorable"];//减优惠
			$arr_order['order_act_ids'] = implode(",",$shop_act_id);
			$arr_order['order_act'] = $shop_act;
			$basescore += $actscore;
		}
		$arr_order["order_detail"]['score'] = (int)$basescore;
		$lng_total -= $arr_order['order_repayment'];//减预付款
		$obj_db->begin("saveorder");
		$arr_order["order_total_pay"] = $lng_total;
		if(empty($obj_order)) {
			$arr_order['order_state'] = 0;
			if($lng_total == 0) {
				$arr_order['order_pay_method'] = 'paymented';
			} else {
				if(!in_array($arr_order['order_pay_method'] , array('afterpayment' , 'paymented'))) $arr_order['order_state'] = -2;//等待支付
			}
		}
		if(!empty($reserve_day)) {
			$arr_order["order_arrive"] = fun_get::get("arrive2");
			$strx = str_replace(":","",$arr_order["order_arrive"]);
			$time = '';
			if(strlen($strx)==4) $time = substr($strx,0,2) . ":" . substr($strx,2) . ":00";
			$arr_order["order_day"] = date("Y-m-d",strtotime($reserve_day));
			$arr_order["order_time"] = date("Y-m-d",strtotime($reserve_day)) . $time;
		} else {
			$arr_order["order_arrive"] = fun_get::get("arrive");
			$strx = str_replace(":","",$arr_order["order_arrive"]);
			$time = '';
			if(strlen($strx)==4) $time = substr($strx,0,2) . ":" . substr($strx,2) . ":00";
			if(!empty($time)) $arr_order["order_time"] = date("Y-m-d") . $time;
		}
		if(!empty($obj_order)) unset($arr_order['order_user_id']);
		$arr = tab_meal_order::on_save($arr_order);
		if($arr["code"] != 0){
			$obj_db->rollback("saveorder");
			$arr_return['code']=101;
			$arr_return['msg']=$arr['msg'];
			return $arr_return;
		}
		//如果有预付款抵扣
		if(!empty($obj_order)) $arr_order['order_repayment'] = $arr_order['order_repayment']-$obj_order["order_repayment"];
		if($arr_order['order_repayment'] > 0 && cls_obj::get("cls_user")->is_login()) {
			$repayment = cls_obj::get("cls_user")->get_repayment();
			if( $arr_order['order_repayment'] > $repayment ) {
				$obj_db->rollback("saveorder");
				return array("code"=>500 , "msg"=>"您当前预付款金额不足");
			}
			$arr_msg = tab_sys_user_repayment::on_order_pay($arr_order['order_user_id'] , $arr_order['order_repayment'] , $arr["id"] , "下单抵扣");
			if($arr_msg['code']!=0) {
				$obj_db->rollback("saveorder");
				return $arr_msg;
			}
		}
		$arr_return["id"] = $arr["id"];
		for($i = 0 ; $i < count($arr_sold) ; $i++) {
			$arr_sold[$i]["sold_order_id"] = $arr_return["id"];

		}
		$arr = $obj_db->on_insert_all(cls_config::DB_PRE . "meal_menu_sold" , $arr_sold);
		$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_order_time='" . TIME . "',shop_day_sold='" . $obj_shop['shop_day_sold'] . "' where shop_id='" . $shop_id . "'");
		//确认事务
		$obj_db->commit("saveorder");
		//修改已定数量
		$arr= $arr_cart["menu_ids"];
		foreach($arr as $item){
			//if($arr_menu['id_'.$item]['menu_num']<1) continue;
			if(in_array($item , $arr_today_menu)) {
				//自定义商品
				$num = substr_count("," . str_replace("," , ",,", $menu_ids) . "," ,"," . $item . ",");
				$arr_x = $obj_db->on_exe("update ".cls_config::DB_PRE."meal_menu_today set today_sold=today_sold+'".$num."' where today_menu_id='".$item."' and today_date='".$today_date."' and today_date_period='".$today_date_period."'" . $where_today);
				$arr_x = $obj_db->on_exe("update ".cls_config::DB_PRE."meal_menu set menu_sold=menu_sold+" . $num . " where menu_id='".$item."'");
			}else {
				$num1 = substr_count("," . str_replace("," , ",,", $menu_ids) . "," ,"," . $item . ",");
				$num = $num1 + $arr_menu['id_'.$item]['menu_sold_today'];
				$obj_db->on_exe("update ".cls_config::DB_PRE."meal_menu set menu_sold_today='".$num."',menu_sold=menu_sold+" . $num1 . ",menu_sold_time='".TIME."' where menu_id='".$item."'");
			}
		}
		$arr_tel = array();
		//取要发短信的号码
		if($obj_shop["shop_sms"] > 0) {
			//是否分区域发短信
			$obj_tel = $obj_db->get_one("select dispatch_smstel from " . cls_config::DB_PRE . "meal_dispatch where dispatch_shop_id='" . $shop_id . " ' and dispatch_area_id in(" . $obj_info['address_area_allid'] . ") and dispatch_smstel!=''");
			if(!empty($obj_tel) && !empty( $obj_tel['dispatch_smstel'])) {
				$arr_tel = explode("," , $obj_tel["dispatch_smstel"]);
			} else {
				$arr_tel = explode("," , $obj_shop["shop_sms_tel"]);
			}
		}
		$site_title = cls_config::get("site_name" , "sys");
		if(empty($site_title)) $site_title = cls_config::get("site_title" , "sys");
		if(!empty($site_title)) $site_title = '【' . $site_title . '】';
		//发送短信提醒
		$msg_cmd_mode = cls_config::get("msg_cmd_mode","base");
		if(empty($obj_order) && $obj_shop["shop_sms"] == 1 && !empty($arr_tel) && $arr_order['order_state'] == 0) {//订单提醒短信
			$tel = implode("," , $arr_tel);//$arr_tel[rand(0,count($arr_tel)-1)];//随机一个电话
			if($msg_cmd_mode == 1) {
				tab_sys_cmd_notice::on_save(array(
					"notice_type" => 1,
					"notice_about_id" => $arr_return['id'],
					"notice_tel" => $tel,
					"notice_sms_cont" => cls_config::get("neworder_shop_tips","sms"),
					"notice_from_uid" => cls_obj::get("cls_user")->uid,
				));
			} else {
				$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => cls_config::get("neworder_shop_tips","sms") ,"id" => $arr_return["id"] , "type"=>1) );
			}
		} else if(empty($obj_order) && ($obj_shop["shop_sms"] == 2 || !empty($obj_shop["shop_email"]) || !empty($obj_shop["shop_weixin_id"]) ) && $arr_order['order_state'] == 0) {//订单详细短信
			$arr = $arr_cart["shop_" . $shop_id_base]["cart"];
			$arr_order['address_name'] = $obj_info['address_name'];
			$arr_order['order_id'] = $arr_return['id'];
			$arr_order['shop_name'] = $obj_shop['shop_name'];
			$arr_order['shop_extend'] = $obj_shop['shop_extend'];
			//取订单后五位为确认码，每天最大只能处理99999条订单
			$id = $arr_order["order_id"];
			if($id>=100000) $id = substr($id,-5);

			$cont = tab_meal_order::get_order_msg($arr , $arr_menu , $arr_order);
			$arr_notice =array("notice_type" => 1,"notice_about_id" => $arr_return['id'],"notice_from_uid" => cls_obj::get("cls_user")->uid);
			if($obj_shop["shop_sms"] == 2) {
				$smscont = $cont . "；确认码：" . $id;
				$tel = implode("," , $arr_tel);//随机一个电话
				if($msg_cmd_mode == 1) {
					$arr_notice['notice_tel'] = $tel;
					$arr_notice['notice_sms_cont'] = $smscont;
				} else {
					$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => $smscont ,"id" => $arr_return["id"] ,"confirm_id" => $id , "type"=>1) );
				}
			}
			if(!empty($obj_shop["shop_email"])) {
				$url = fun_get::html_url('index.php?app_module=shop&app=order&app_act=detail&id='.$id , 1);
				$cont .= '  <a href="'.$url.'">点击查看订单详情</a>';
				if($msg_cmd_mode == 1) {
					$arr_notice['notice_email'] = $obj_shop["shop_email"];
					$arr_notice['notice_email_cont'] = $cont;
					$arr_notice['notice_title'] = $site_title . "提醒您有新的订单";
				} else {
					$arr = cls_obj::get("cls_com")->email('send' , array('to_mail' => $obj_shop["shop_email"] , 'title' => $site_title . "提醒您有新的订单" , 'content' => $cont ,'save' => 1));
				}
			}
			$weixin_appid = cls_config::get('appid' , 'weixin' , '' , '');
			if(!empty($obj_shop["shop_weixin_id"]) && !empty($weixin_appid) ) {
				$smscont = $cont . "；确认码：" . $id;
				if($msg_cmd_mode == 1) {
					$arr_notice['notice_wx'] = $obj_shop["shop_weixin_id"];
					$arr_notice['notice_wx_cont'] = $smscont;
				} else {
					cls_weixin::on_send(0 , $obj_shop["shop_weixin_id"] , 'text' , array('message_text'=>$smscont) );
				}
			}
			if($msg_cmd_mode == 1) {
				tab_sys_cmd_notice::on_save($arr_notice);
			}
		}
		//新订单通知用户短信
		$neworder_user_tips = cls_config::get("neworder_user_tips" , "sms");
		if(!empty($neworder_user_tips) && fun_is::tel($arr_order['order_mobile'])  && $arr_order['order_state'] == 0) {
			$neworder_user_tips = str_replace('#sitename#',$site_title,$neworder_user_tips);
			$neworder_user_tips = str_replace('#shopname#',$obj_shop['shop_name'],$neworder_user_tips);
			$arr_notice =array("notice_type" => 0,"notice_about_id" => $arr_return['id'],"notice_from_uid" => cls_obj::get("cls_user")->uid,"notice_tel" => $arr_order['order_mobile'] , "notice_sms_cont" => $neworder_user_tips);
			$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$arr_order['order_mobile'] , "cont" => $neworder_user_tips , "type"=>0) );
		}
		//更新地址时间
		if(!empty($obj_info)) $arr_msg = tab_sys_user_address::on_save(array("address_id" => $obj_info['address_id'] , 'address_order_time' => TIME ) );
		$arr_return['state'] = (empty($arr_order['order_state']) && !empty($cfg_print['url']) && $obj_shop['shop_print_auto'] == 1) ? 0 : -1;
		return $arr_return;
	}


	//保存收货信息
	function on_address_save() {
		$arr_fields = array(
			"address_id" => (int)fun_get::get("id"),
			"address_name" => fun_get::get("name"),
			"address_address" => fun_get::get("address"),
			"address_area" => fun_get::get("area"),
			"address_tel" => fun_get::get("tel"),
			"address_area_id" => fun_get::get("area_id"),
			"address_area_allid" => fun_get::get("area_allid"),
		);
		$arr_msg = tab_sys_user_address::on_save($arr_fields);
		return $arr_msg;
	}

	//删除收货信息
	function on_address_del() {
		$id = (int)fun_get::get("id");
		if(empty($id)) return array("code" => 500 , "msg" => "没有指定要删除的收货信息");
		$arr_msg = tab_sys_user_address::on_delete($id , "address_user_id='" . cls_obj::get("cls_user")->uid . "'");
		$arr_msg['id'] = $id;
		return $arr_msg;
	}
	//用户中心修改密码
	function on_useredit(){
		$lng_user_id = cls_obj::get('cls_user')->uid;
		$id=(int)fun_get::get("id");
		if(cls_obj::get("cls_user")->is_admin() && $id>0){
			$lng_user_id=$id;
		}
		$arr_return=array("code"=>0,"msg"=>"保存成功");
		$obj_db = cls_obj::db_w();
		$arr_pwd=array(
			"oldpwd" => fun_get::post("oldpwd"),
			"pwd1" => fun_get::post('pwd1'),
			"pwd2" =>fun_get::post("pwd2")
		);
		if($arr_pwd["oldpwd"]!='' || $arr_pwd["pwd1"]!='' || $arr_pwd["pwd2"]!='') {
			if($arr_pwd["pwd1"]!=$arr_pwd["pwd2"]){
				$arr_return['code'] = 500;
				$arr_return['msg'] = "两次输入密码不一至！";
				return $arr_return;
			}
			$arr = cls_obj::get("cls_user")->on_update_pwd($arr_pwd["oldpwd"] , $arr_pwd["pwd1"]);
			if($arr["code"] != 0){
				$arr_return['code']=500;
				$arr_return['msg']=$arr['msg'];
				return $arr_return;
			}
		}
		$arr_fields = array(
			"user_id" => $lng_user_id,
			"user_email" => fun_get::post("email")
		);
		$arr_return = tab_sys_user::on_save($arr_fields);
		return $arr_return;
	}

	function on_reg($user_type = ''){
		$arr_return=array("code"=>0,"msg"=>"注册成功");
		$str_pwd1 = fun_get::post('pwd1');
		$str_pwd2 = fun_get::post('pwd2');
		if($str_pwd1 != $str_pwd2){
			$arr_return["code"]=1;
			$arr_return["msg"]="两次输入密码不一致";
			return $arr_return;
		}
		$reg_switch = (int)cls_config::get("reg_switch" , "user");
		$reg_invite_code = cls_config::get("reg_invite_code" , "user");
		$reg_switch_info = cls_config::get("reg_switch_info" , "user");
		if($reg_switch == 1) {
			$arr_return["code"]=500;
			if(empty($reg_switch_info)) $reg_switch_info = "网站关闭了新用户注册功能";
			$arr_return["msg"]=$reg_switch_info;
			return $arr_return;
		} else if($reg_switch == 2) {
			$invite_code = fun_get::post('invite_code');
			if($invite_code != $reg_invite_code) {
				$arr_return["code"]=500;
				$arr_return["msg"] = "邀请码输入不正确";
				return $arr_return;
			}
		}
		$verifycode = fun_get::post("verifycode");
		$isverify = (cls_obj::get("cls_session")->get('verify_reg') > 0) ? false : true;
		$arr_user=array(
			"user_name" => fun_get::post("uname"),
			"user_pwd" => $str_pwd1,
		);
		if(cls_config::get('rule_uname','user')=='email') {
			$arr = tab_sys_verify::on_verify($arr_user['user_name'] , $verifycode ,3 ,86400 , $isverify);
			if($arr['code'] != 0) return $arr;
			cls_obj::get("cls_session")->set('verify_reg' , 1);//设置已验证标识
		} else if(cls_config::get('rule_uname','user')=='mobile') {
			$arr = tab_sys_verify::on_verify($arr_user['user_name'] , $verifycode , 4 ,600, $isverify);
			if($arr['code'] != 0) return array('code' => 500 , 'msg' => '短信验证码有误');
			cls_obj::get("cls_session")->set('verify_reg' , 1);//设置已验证标识
		} else if(cls_config::get('reg_verify' , 'user')){
		//是否需要验证码
			if(cls_verifycode::on_verify($verifycode) == false) {
				$arr_return["code"] = 11;
				$arr_return["msg"]  = cls_language::get("verify_code_err");
				return $arr_return;
			}
		}
		if(fun_is::set("email")) $arr_user['user_email'] = fun_get::post("email");
		if(!empty($user_type)) $arr_user['user_type'] = $user_type;
		//注册用户
		$arr = cls_obj::get("cls_user")->on_reg($arr_user);
		if($arr["code"] != 0){
			return $arr;
		} else {
			$arr_return['id'] = $arr['id'];
			$arr_login=array( "user_name"=>$arr_user["user_name"],"user_pwd"=>$arr_user["user_pwd"]);
			$arr=cls_obj::get("cls_user")->on_login($arr_login);
			if($arr["code"]!=0){
				return $arr;
			}
		}
		return $arr_return;
	}
	function on_shop_reg(){
		$arr_return=array("code"=>0,"msg"=>"注册成功");
		$arr_shop=array(
			"shop_name" => fun_get::post("shop_name"),
			"shop_linkname" => fun_get::post("shop_linkname"),
			"shop_linktel" => fun_get::post("shop_linktel"),
			"shop_area_id"  => fun_get::post("shop_area_id"),
			"shop_area_allid"  => fun_get::post("shop_area_allid"),
			"shop_area"  => fun_get::post("shop_area"),
			"shop_address"  => fun_get::post("shop_address"),
		);
		//取店铺注册状态
		$arr_shop["shop_state"] = (int)cls_config::get("shop_state" , "meal");

		if(empty($arr_shop["shop_name"])) {
			$arr_return['code']=500;
			$arr_return['msg']="店铺名称不能为空";
			return $arr_return;
		}
		if(empty($arr_shop["shop_linkname"])) {
			$arr_return['code']=500;
			$arr_return['msg']="店铺联系人不能为空";
			return $arr_return;
		}
		if(empty($arr_shop["shop_linktel"])) {
			$arr_return['code']=500;
			$arr_return['msg']="店铺联系电话不能为空";
			return $arr_return;
		}
		if(empty($arr_shop["shop_area_id"])) {
			$arr_return['code']=500;
			$arr_return['msg']="请选择店铺所在地区";
			return $arr_return;
		}
		$obj_shop = cls_obj::db()->get_one("select 1 from " . cls_config::DB_PRE . "meal_shop where shop_name='" . $arr_shop["shop_name"] . "' and shop_area_id='" . $arr_shop["shop_area_id"] ."'");
		if(!empty($obj_shop)) {
			$arr_return['code']=500;
			$arr_return['msg']="选择地区【" . $arr_shop["shop_name"] . "】店铺已存在";
			return $arr_return;
		}
		$arr = $this->on_reg('shop');
		if($arr['code']!=0) return $arr;
		//注册企业
		$arr_shop["shop_user_id"] = $arr["id"];
		$shop_msg = tab_meal_shop::on_save($arr_shop);
		//当店铺状态默认为未审核时
		if( $arr_return['code'] == 0 && empty($arr_shop["shop_state"]) ) {
			$msg_cont = "系统提醒：新店【" . $arr_shop['shop_name'] . "】申请开通，请审核";
			$msg_newshop = (int)cls_config::get("msg_newshop" , "sys");
			if($msg_newshop == 1) {
				$linkmobile = cls_config::get("linkmobile" , "sys");
				if(!empty($linkmobile)) cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$linkmobile , "cont" => $msg_cont ) );
			} else if($msg_newshop == 2) {
				$linkemail = cls_config::get("linkemail" , "sys");
				$url = cls_config::get("url" , "base") . "/" . KJ_ADMIN_FILENAME;
				if(!empty($linkemail)) cls_obj::get("cls_com")->email('send' , array('to_mail' => $linkemail , 'title' => $msg_cont , 'content' =>  $msg_cont . "，" . $url , 'save' => 1));
			} else if($msg_newshop == 2) {
				$weixin_id = cls_config::get("weixin_id" , "sys");
				if(!empty($weixin_id)) cls_weixin::on_send($weixin_id , 'text' , array('message_text'=>$msg_cont) );
			}
		}
		return $arr_return;
	}

	function on_findpwd_step1() {
		$verifycode = fun_get::get("verifycode");
		if(cls_verifycode::on_verify($verifycode) == false) {
			return array('code' => '11' , 'msg' => '验证码输入错误');
		}
		$uname = fun_get::get("uname");
		$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
		if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '输入账号不存在');
		$obj_rs = cls_obj::db()->get_one("select user_email,user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . $arr_user[$uname] . "'");
		if(empty($obj_rs)) {
			return array('code' => '500' , 'msg' => '注册账号没有邦定个人信息');
		}
		$arr_return = array('code' => '0' , 'email' => '');
		$arr_return['is_sms'] = (fun_is::com('sms'))? "1" : "0";
		$arr_return['is_email'] = (fun_is::com('email'))? "1" : "0";
		$arr_return['is_msg'] = (fun_is::com('msg'))? "1" : "0";
		$arr = explode("@" , $obj_rs['user_email']);
		if(strlen($arr[0])>3) {
			$arr_return['email'] = str_pad(substr($arr[0],0,3),strlen($arr[0]),"*") . "@" . $arr[1];
		} else if(count($arr)>1) {
			$arr_return['email'] = str_pad(substr($arr[0],0,1),strlen($arr[0]),"*") . "@" . $arr[1];
		}
		$arr_return['mobile'] = str_pad(substr($obj_rs['user_mobile'],0,3),strlen($obj_rs['user_mobile'])-4,"*") . substr($obj_rs['user_mobile'],-4);
		return $arr_return;
	}
	function on_findpwd_step2() {
		$method = (int)fun_get::get("method");
		$uname = fun_get::get("uname");
		$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
		if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '输入账号不存在');

		$obj_user = cls_obj::db()->get_one("select user_id,user_email,user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . $arr_user[$uname] . "'");
		if(empty($obj_user)) {
			return array('code' => '500' , 'msg' => '输入账号不存在');
		}
		if($method == 1) {
			$arr_key = tab_sys_verify::get_key($obj_user['user_id'],1,$obj_user['user_email']);
			if($arr_key['code']!=0) return $arr_key;
			$url = cls_config::get("url" , 'base') . "/index.php?app_act=findpwd.email&val=" . urlencode($obj_user['user_email']) . "&key=" . $arr_key['key'];
			//取邮件内容
			$obj_cont = cls_obj::db()->get_one("select article_title,article_content from " . cls_config::DB_PRE . "article where article_key='findpwdwords'");
			if(empty($obj_cont)) {
				$obj_cont['article_title'] = cls_config::get("site_title" , "sys") . "找回密码";
				$obj_cont['article_content'] = "<a href='".$url."'>请点击链接重置登录密码</a>，如果未操作，系统将保留原密码<br>如果无法点击请复制以下代码，粘贴到浏览器地址栏访问<br>" . $url;
			} else {
				$obj_cont['article_content'] = fun_get::filter($obj_cont['article_content'] , true);
				$obj_cont['article_content'] = str_replace('{$url}' , $url , $obj_cont['article_content']);
			}
			$arr = cls_obj::get("cls_com")->email('send' , array('to_mail' => $obj_user['user_email'] , 'title' => $obj_cont['article_title'] , 'content' => $obj_cont['article_content'] ,'save' => 1));
			cls_obj::get("cls_session")->set('verify_val' , $obj_user['user_email']);
			return $arr;
		} else if($method == 2) {
			$arr_key = tab_sys_verify::get_key($obj_user['user_id'],2,$obj_user['user_mobile']);
			if($arr_key['code']!=0) return $arr_key;
			$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$obj_user['user_mobile'] , "cont" => "您的验证码：" . $arr_key['key'] . ",请在网页上输入此号码，如非本人操作请忽略" ) );
			cls_obj::get("cls_session")->set('verify_val' , $obj_user['user_mobile']);
			return $arr;
		} else if($method == 3) {
			$arr_fields = array(
				"msg_name" => fun_get::get('name'),
				"msg_tel" => fun_get::get('tel'),
				"msg_cont" => fun_get::get('cont'),
				"msg_type" => 1,
				"msg_user_id" => $obj_user['user_id']
			);
			$arr = cls_obj::get("cls_com")->msg('on_save',$arr_fields);
			return $arr;
		} else {
			return array("code" => 500 , "msg" =>"传递参数有误");
		}
	}

	function on_findpwd_step3() {
		$isverify = cls_obj::get("cls_session")->get('sms_verify');
		$key = fun_get::get("key");
		$uname = fun_get::get("uname");
		$uid = fun_get::get("uid");
		$pwd = fun_get::get("pwd");
		if($uid < 1 ) {
			$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
			if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '验证账号不存在');
			$uid = $arr_user[$uname];
		} else {
			$arr_user = cls_obj::get("cls_user")->get_user($uid);
			if(empty($arr_user) || !in_array($uid , $arr_user)) return array('code' => '500' , 'msg' => '验证账号不存在');
			$uname = array_search($uid , $arr_user);
		}
		if($isverify != $uid) return array("code"=>500 , "msg" => "验证已过期，请重新验证");
		
		$arr = cls_obj::get("cls_user")->on_update_pwd('' , $pwd , $uid , false);
		if($arr["code"] != 0){
			$arr_return['code']=500;
			$arr_return['msg']=$arr['msg'];
			return $arr_return;
		}
		//注销标识
		cls_obj::get("cls_session")->destroy('sms_verify');
		return array("code"=>0,"msg"=>'');
	}

	function on_verify_mobile() {
		$mobile = cls_obj::get("cls_session")->get('verify_val');
		$key = fun_get::get("key");
		$uname = fun_get::get("uname");
		$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
		if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '验证账号不存在');
		$arr = tab_sys_verify::on_verify($mobile , $key , 2);
		if($arr['code'] == 0) {
			$isverify = cls_obj::get("cls_session")->set('sms_verify' , $arr_user[$uname]);//设置已验证标识
		}
		return $arr;
	}
	function on_msg_save() {
		$options = cls_config::get("msg_options","sys");
		if(in_array('login',$options) && cls_obj::get("cls_user")->is_login()==false) {
			return array("code" => 500 , "msg" => "需要登录才能留言");
		}
		$arr_fields = array(
			"msg_email" => fun_get::get('email'),
			"msg_name" => fun_get::get('name'),
			"msg_tel" => fun_get::get('tel'),
			"msg_cont" => fun_get::get('cont'),
			"msg_user_id" => cls_obj::get("cls_user")->uid
		);
		if(in_array('email',$options) && empty($arr_fields['msg_email'])) {
			return array("code" => 500 , "msg" => "邮箱不能为空");
		}
		if(in_array('tel',$options) && empty($arr_fields['msg_tel'])) {
			return array("code" => 500 , "msg" => "电话不能为空");
		}
		if(in_array('name',$options) && empty($arr_fields['msg_name'])) {
			return array("code" => 500 , "msg" => "名称不能为空");
		}
		$arr = cls_obj::get("cls_com")->msg('on_save',$arr_fields);

		if( $arr['code'] == 0 ) {
			$msg_cont = "系统提醒：您管理的网站有新的留言，请查收";
			$msg_newshop = (int)cls_config::get("msg_newmsg" , "sys");
			if($msg_newshop == 1) {
				$linkmobile = cls_config::get("linkmobile" , "sys");
				if(!empty($linkmobile)) cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$linkmobile , "cont" => $msg_cont ) );
			} else if($msg_newshop == 2) {
				$linkemail = cls_config::get("linkemail" , "sys");
				$url = cls_config::get("url" , "base") . "/" . KJ_ADMIN_FILENAME;
				if(!empty($linkemail)) cls_obj::get("cls_com")->email('send' , array('to_mail' => $linkemail , 'title' => $msg_cont , 'content' =>  $arr_fields['msg_cont'] . "，" . $url , 'save' => 1));
			} else if($msg_newshop == 2) {
				$weixin_id = cls_config::get("weixin_id" , "sys");
				if(!empty($weixin_id)) cls_weixin::on_send($weixin_id , 'text' , array('message_text'=>$msg_cont) );
			}
		}

		return $arr;
	}

	function on_collect_shop($shop_id) {
		if(empty($shop_id)) return array("code" => 500 , "msg" => "请选择要收藏的店铺");
		$obj_db = cls_obj::db();
		$arr_fields = array(
			"collect_user_id" => cls_obj::get('cls_user')->uid,
			"collect_for_id" => $shop_id,
			"collect_type" => 1
		);
		$arr = tab_meal_collect::on_save($arr_fields);
		return $arr;
	}

	function get_sold_list($id) {
		$lng_pagesize = 10;
		$arr_return = array("list" => array() , "pagebtns" => "");
		$obj_db = cls_obj::db();
		$lng_page = (int)fun_get::get("page");
		$str_where = " where concat('|' , order_ids , '|') like '%|" . $id . "|%'";
		//取分页信息
		$arr_return["list"] = array();
		$arr_uid = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_order" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT order_addtime,order_ids,order_user_id FROM ".cls_config::DB_PRE."meal_order" . $str_where . " order by order_id desc" . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['addtime'] = date("Y-m-d H:i" , $obj_rs['order_addtime']);
			$arr_uid[] = $obj_rs['order_user_id'];
			$arr = explode("|" , $obj_rs['order_ids']);
			$num = 0;
			foreach($arr as $item) {
				if($item == $id) $num++;
			}
			$obj_rs['user_name'] = '';
			$obj_rs['num'] = $num;
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				if(empty($arr_return["list"][$i]['user_name'])) $arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['order_user_id'] , $user_info);
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'] , 5);
		return $arr_return;
	}
	function on_upmenu() {
		$number = fun_get::get("number");
		$obj_reserve = cls_obj::db()->get_one("select reserve_state,reserve_id from " . cls_config::DB_PRE . "meal_table_reserve where reserve_number='" . $number . "'");
		if(empty($obj_reserve)) return array("code" => 500 , "msg" => "预订信息不存在");
		if($obj_reserve['reserve_state']<1)  return array("code" => 500 , "msg" => "预订信息无效");
		if($obj_reserve['reserve_state']<2)  return array("code" => 500 , "msg" => "请先点菜");
		if($obj_reserve['reserve_state']>=3)  return array("code" => 0 , "msg" => "");
		$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_menutime='" . TIME . "',reserve_state=3 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		return $arr;
	}
}