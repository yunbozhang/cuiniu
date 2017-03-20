<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_meal_order {
	static $perms;

	//获取表配置参数
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"state" => array("已接受" => 1 , "等待处理" => 0 , "已拒绝" => -1 , "待付款" => -2 ,"过期未付款" => -3 ,"已取消" => -4 ,"用户取消" => -5,"已作废" => -6),
				"award" => array("已奖励" => 1 , "不奖励" => 0 , "未奖励" => -1),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}

	/* 保存操作
	 * arr_fields : 为字段数据，默认如果包函 id，则为修改，否则为插入
	 * where : 默认为空，用于有时候条件修改
	 */
	static function on_save($arr_fields , $where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['order_id'])) {
			$arr_fields['id'] = $arr_fields['order_id'];
			unset($arr_fields['order_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " order_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and order_id='" . $arr_return['id'] . "'";
				}
			}
		}
		//序列化明细
		if(isset($arr_fields["order_detail"]) && is_array($arr_fields["order_detail"]) ) {
			$arr_fields["order_detail"] = serialize($arr_fields["order_detail"]);
		}
		//序列化明细
		if(isset($arr_fields["order_act"]) && is_array($arr_fields["order_act"]) ) {
			$arr_fields["order_act"] = serialize($arr_fields["order_act"]);
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {
			//必填项检查
			if((!isset($arr_fields['order_reserve_id']) || empty($arr_fields['order_reserve_id'])) && (!isset($arr_fields['order_ids']) || empty($arr_fields['order_ids']))) {
				$arr_return['code'] = 113;
				$arr_return['msg']  = cls_language::get("order_ids_is_null" , "meal");//区域id不能为空
				return $arr_return;
			}
			$arr_fields["order_addtime"] = TIME;
			if(!isset($arr_fields["order_day"]) || empty($arr_fields["order_day"])) $arr_fields["order_day"] = date("Y-m-d",TIME);
			if(!isset($arr_fields["order_time"]) || empty($arr_fields["order_time"])) $arr_fields["order_time"] = date("Y-m-d H:i:s",TIME);
			$arr_fields["order_number"] = $arr_fields["order_shop_id"] . date("ymdHis") . $arr_fields["order_user_id"];//店铺id+时间+用户id
			//插入到用户表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."meal_order",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$where  = "order_ids='" . $arr_fields['order_ids'] . " and order_user_id='".$arr_fields['order_user_id'] . "' and order_addtime='".$arr_fields["order_addtime"]."'";
					$obj_rs = $obj_db->get_one("select order_id from ".cls_config::DB_PRE."meal_order where ".$where);
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['order_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select order_id from ".cls_config::DB_PRE."meal_order where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['order_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "order_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."meal_order" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
	//刷新用户数据
	static function on_refresh_user($uid) {
		$arr_return = array("code"=>0,"msg"=>"");
		$obj_db = cls_obj::db_w();
		$obj_rs = $obj_db->get_one("SELECT order_user_id,sum(order_total) as total,count(1) as num FROM " . cls_config::DB_PRE . "meal_order where order_user_id='".$uid."' and order_state>0 group by order_user_id");
		if( !empty($obj_rs) ) {
			$arr_return = $obj_db->on_exe("update ".cls_config::DB_PRE."sys_user set user_order_num='".$obj_rs["num"]."',user_totalpay='".$obj_rs["total"]."' where user_id='" .$obj_rs["order_user_id"]."'");
		}
		return $arr_return;
	}
	/* 删除函数
	 * arr_id : 要删除的 id数组
	 * where : 删除附加条件
	 */
	static function on_delete($arr_id , $where = '') {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if( empty($str_id) && empty($where) ){
			$arr_return["code"] = 22;
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "order_id='".$str_id."'" : $arr_where[] = "order_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		//取用户id，刷新用户数据
		$arr_uid = array();
		$obj_result = $obj_db->select("select order_user_id from ".cls_config::DB_PRE."meal_order where order_state=1 and " . $where);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_uid[] = $obj_rs["order_user_id"];
		}
		$arr_where[] = "order_state!=1 and order_state!=-6";
		$where = implode(" and " , $arr_where);
		$arr_return = $obj_db->on_delete(cls_config::DB_PRE."meal_order" , $where);
		if($arr_return["code"]==0) {
			foreach($arr_uid as $item) {
				self::on_refresh_user($item);
			}
		}
		return $arr_return;
	}


	/* 确认函数，送积分与经验
	 * arr_id : 要确认的 id数组
	 * where : 附加条件
	 */
	static function on_award($arr_id , $where = '') {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if( empty($str_id) && empty($where) ){
			$arr_return["code"] = 22;
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$arr_where = array();
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "order_id='".$str_id."'" : $arr_where[] = "order_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$arr_where[] = " order_state>0 and order_isaward=-1";
		$where = implode(" and " , $arr_where);
		$obj_db->begin("orderok");
		$obj_result = $obj_db->select("select order_id,order_user_id,order_total_pay,order_addtime,order_detail,shop_user_id,shop_name from ".cls_config::DB_PRE."meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id where " . $where);
		while($obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_score = array("score"=>$obj_rs['order_total_pay'] , "experience" => $obj_rs['order_total_pay']);
			if(!empty($obj_rs['order_detail'])) {
				$arr_detail = unserialize($obj_rs['order_detail']);
				if(isset($arr_detail['score'])) {
					/*
					$arr_re = tab_sys_user_action::on_action( $obj_rs['shop_user_id'] , "admin" ,  array("score" => $arr_detail['score'] , 'beta'=>'订单【' . $obj_rs['order_id'] . '】消耗' , 'basescore'=> -1) );
					if($arr_re['code'] != 0) {
						$obj_db->rollback("orderok");
						$arr_re['msg'] = $arr_re['msg'] . "【" . $obj_rs['shop_name'] . "】";
						return $arr_re;
					}
					*/
					$arr_re = tab_sys_user_action::on_action( $obj_rs["order_user_id"] , "meal_submit_order_ok" ,  array('score' => $arr_detail['score'] , 'basescore'=> 1 , 'addscore'=> 0 , 'experience' => $obj_rs['order_total_pay']));
					if($arr_re["code"] != 0 ) {
						$obj_db->rollback("orderok");
						$arr_return["code"] = $arr_re["code"];
						$arr_return["msg"] = $arr_re["msg"];
						return $arr_return;
					}
				}
			}
		}
		//设置已奖励状态为：2
		$arr_return = $obj_db->on_update(cls_config::DB_PRE."meal_order" , array("order_isaward"=>1) ,  $where);
		if($arr_return["code"] != 0 ) {
			$obj_db->rollback("orderok");
			return $arr_return;
		}
		$obj_db->commit("orderok");
		return $arr_return;
	}

	/*
	 * 设置订单状态
	 */
	static function on_state($arr_id , $state , $beta = '' , $msg_where = '') {
		$arr_state = self::get_perms("state");
		if(!in_array($state , $arr_state)) return array("code" => 500 , "msg" => "处理状态不存在");
		$str_id = fun_format::arr_id($arr_id);
		if( empty($str_id) && empty($msg_where) ) return array("code" => 500 , "msg" => "未指定要处理的订单");
		
		$arr_where = array();
		if(!empty($msg_where)) $arr_where[] = $msg_where;
		if(!empty($str_id)) {
			(is_numeric($str_id)) ? $arr_where[] = "order_id='".$str_id."'" : $arr_where[] = "order_id in(".$str_id.")";
		}
		$where = " where " . implode(" and " , $arr_where);

		$arr_order = array();
		$obj_result = cls_obj::db_w()->select("select order_id,order_user_id,order_shop_id,order_state,order_repayment,order_pay_id,order_pay_val,order_score_money,order_mobile,order_tel,shop_name,shop_print_cancel,shop_print_pages,shop_print_id from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id" . $where);
		while($obj_rs = cls_obj::db_w()->fetch_array($obj_result)) {
			$arr_order[] = $obj_rs;
		}
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state=" . $state . ",order_state_time='" . date("Y-m-d H:i:s" , TIME) . "',order_state_beta='" . $beta . "'" . $where);
		if($arr['code'] == 0 && in_array($state , array(1,-1,-4,-5)) ) {
			foreach($arr_order as $item) {
				//取用户手机号
				$tel = '';
				if(fun_is::tel($item['order_mobile'])) $tel = trim($item['order_mobile']);
				if(empty($tel) && fun_is::tel($item['order_tel']))  $tel = trim($item['order_tel']);
				//如果为接受处理，则同步用户订单量及金额统计信息
				if($state == 1) {
					self::on_refresh_user($item['order_user_id']);
					//同步店铺销售量
					cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_shop set shop_sold=shop_sold+1 where shop_id='" . $item['order_shop_id'] . "'");
					//接授处理，是否消息用户
					$accept_user_beta = cls_config::get("accept_user_beta" , "sms");
					if(!empty($accept_user_beta) && !empty($tel)) {
						$accept_user_beta = str_replace("#shopname#" , $item['shop_name'] , $accept_user_beta);
						$arr_sms = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => $accept_user_beta ,"id" => $item["order_id"] ,"confirm_id" => 0 , "type"=>2) );
					}
				}
				if($item['order_state'] == 0 && in_array($state , array(-1,-4,-5)) ) {
					$score_money_scale = cls_config::get("score_money_scale" , "meal");
					//退积分
					if( $item['order_score_money'] > 0 ) {
						$score_val = $item['order_score_money'] / $score_money_scale;
						$arr_msg = tab_sys_user_action::on_action( $item['order_user_id'] , "admin" ,  array("addscore" => $score_val , 'beta'=>"订单退回抵扣积分" , 'addexperience'=>0) );
					}
					//退预付款
					if( $item['order_repayment'] > 0 ) {
						$arr_msg = tab_sys_user_repayment::on_admin_recharge( array('repayment_val'=>$item['order_repayment'] , 'repayment_user_id' => $item['order_user_id'] , 'repayment_about_id' => $item['order_id'] , "repayment_beta" => "订单【" . $item['order_id'] . "】退款") );
						if($arr_msg['code']!=0) {
							cls_error::on_save('pay_refund',"订单【" . $item['order_id'] . "】预付款" . cls_config::get('coinsign','sys') . $item['order_repayment'] . "退款失败" , $arr_msg);
						}
					}
					//退在线支付
					if( $item['order_pay_val'] > 0 && !empty($item['order_pay_id'])) {
						cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "other_pay set pay_state=2 where pay_id='" . $item['order_pay_id']  . "'");
						if(cls_config::get("payconfirm" , "base") != 1) {//自动即时退款
							$arr = cls_obj::get('cls_com')->pay("on_refund",$item['order_pay_id']);
						}
					}
					//如果是取消，是否打印
					if($item['shop_print_cancel']== 1 && ($state == -5 || $state == -4) ) {
						$print_temp = "订单：" . $item["order_id"] . "已被";
						$print_temp .= ($state == -5) ? "用户" : "管理员或店主";
						$print_temp .= "取消";
						$print_temp .= (empty($beta)) ? '' : chr(10) . "备注：" . $beta;
						$print_temp = strip_tags($print_temp)  . chr(10) . chr(10) . chr(10) . chr(10) . chr(10);
						$print_temp = '<1B4500><1D2110>注意<1D2100>' . chr(10) . $print_temp;
						cls_obj::get('cls_com')->print("on_print" , array("oid"=>$item['order_id'] , "cont" => $print_temp , "shop_dayinjisn" => $item['shop_print_id'] , "shop_print_pages" => $item['shop_print_pages']) );
					}
					//如果已拒绝或已拒绝，是否通知用户
					$call_user = cls_config::get("cancel_call_user" , "sms");
					if($call_user && ($state==-1 || $state==-4)) {
						if(!empty($tel)) {
							$cancel_user_beta = cls_config::get("cancel_user_beta" , "sms");
							(stristr($cancel_user_beta , "#cont#"))? $cancel_user_beta = str_replace("#cont#" , $beta , $cancel_user_beta) : $cancel_user_beta.=$beta;
							$cancel_user_beta = str_replace("#shopname#" , $item['shop_name'] , $cancel_user_beta);
							cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => $cancel_user_beta ,"id" => $item["order_id"] ,"confirm_id" => 0 , "type"=>2) );
						}
					}
				}
			}
		}
		return $arr;
	}
	static function get_order_msg($arr , $arr_menu , $arr_order) {
		$ii = 1;
		$arr_i = array();
		foreach($arr as $item) {
			$arr_x = array();
			$price = 0;
			foreach($item as $next) {
				$arr_x[] = $arr_menu["id_" . $next]["menu_title"];
				$price+=$arr_menu["id_".$next]["menu_price"];
			}
			$str = implode(" + " , $arr_x);
			if(!isset($arr_i[$str])) {
				$arr_i[$str] = array('name' => $str , "num" => 1 , "price" => $price);
			} else {
				$arr_i[$str]['num']++;
			}
			$ii++;
		}
		$arr_j = array();
		foreach($arr_i as $key =>$item) {
			$str = $item['name'];
			if($item['num']>1) $str .= " " . $item['num'] . "份";
			$str .=  " " . cls_config::get("coinsign","sys") . $item['price']*$item['num'];
			$arr_j[] = $str;
		}
		$score_money = (isset($arr_order["order_score_money"])) ? $arr_order["order_score_money"] : 0;
		$score_money += $arr_order['order_repayment'];
		$cont = "(" . $arr_order['shop_name'] . ")" . implode("；" , $arr_j) . " 合计：" . $arr_order["order_total"] . "；";
		if(!empty($arr_order["order_addprice"])) $cont .= "配送费：" . $arr_order["order_addprice"]."；";
		if(!empty($score_money)) $cont .= "抵扣：" . $score_money . "；";
		if(!empty($arr_order["order_favorable"])) $cont .= "优惠：" . $arr_order["order_favorable"] . "；";
		$cont .= "应收：" . $arr_order["order_total_pay"];
		if(isset($arr_order['order_pay_val']) && !empty($arr_order['order_pay_val'])) $cont .= "(已付:" . $arr_order['order_pay_val'] . ")";
		if($arr_order["order_ticket"]>0) $cont .= "；发票：" . $arr_order["order_ticket"];
		//抵达时间
		$arr = unserialize($arr_order["shop_extend"]);
		if( isset($arr["arr_arrivetime"]) && isset($arr["arr_arrivetime"][$arr_order['order_arrive']] )) {
			$cont .= "；" . $arr["arr_arrivetime"][$arr_order['order_arrive']] . "前送到";
		} else {
			$cont .= "；" . $arr_order['order_arrive'];
		}
		$cont .= "；" . $arr_order["address_name"] . "；";
		//取地区
		if(!empty($arr_order["order_area"])) {
			$cont .= $arr_order["order_area"];
		}
		if(!empty($arr_order["order_louhao1"])) {
			$cont .= "；" . $arr_order["order_louhao1"];
			if(!empty($arr_order["order_louhao2"])) {
				$cont .= $arr_order["order_louhao2"];
			}
		}
		if(!empty($arr_order["order_company"])) {
			$cont .= "；" . $arr_order["order_company"];
			if(!empty($arr_order["order_depart"])) {
				$cont .= "/" . $arr_order["order_depart"];
			}
		}

		if(!empty($arr_order["order_tel"])) {
			$cont .= "；电话:" . $arr_order["order_tel"];
			if(!empty($arr_order["order_telext"])) {
				$cont .= "转" . $arr_order["order_telext"];
			}
		} 
		if(!empty($arr_order["order_mobile"])) {
			$cont .= "；电话:" . $arr_order["order_mobile"];
		}
		if(!empty($arr_order['order_beta'])) $cont .= "；备注：" . $arr_order['order_beta'];
		return $cont;
	}
}