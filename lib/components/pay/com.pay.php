<?php
/* 支付接口 */
class com_pay {
	function get_config($val) {
		static $config = array();
		if(empty($config)) {
			$config = array(
				'type' => array("在线付款" => 1 , "预付款充值" => 2 , "预付订金" => 3),
				'state' => array("充值成功" => 1 , "充值成功可退款" => 2 , "等待付款" => 0 , "充值失败" => -1 , "退款处理中" => -2 , "退款成功" => -3),
			);
		}
		 return (isset($config[$val]))? $config[$val] : '';
	}
	/* 支付操作
	 * arr_fields 包括：pay_method => 支付方式 ，pay_title => 支付标题，pay_about_id => 相关id ，pay_val => 价格，pay_beta => 备注 , pay_user_id => 用户id
	 * pay_type => 支付类型
	 */
	function on_pay($arr_fields = array()) {
		$arr_return=array("url"=>"","err"=>"");

		if(!isset($arr_fields["pay_method"])) return array('code' => 500 , 'msg' => '支付接口不能为空！');
		if(!isset($arr_fields['pay_type']) || empty($arr_fields['pay_type']) ) return array('code' => 500 , 'msg' => '支付类型不能为空');
		if(!isset($arr_fields['pay_banktype'])) $arr_fields['pay_banktype'] = '';
		$arr_paymethod = cls_config::get("" , "pay" , "" , "");
		if(empty($arr_fields["pay_method"]) && count($arr_paymethod) > 0) {
			//为空则取默认第一个支持方式
			$arr_keys = array_keys($arr_paymethod);
			$arr_fields["pay_method"] = $arr_keys[0];
		}
		if(!isset($arr_paymethod[$arr_fields["pay_method"]])) return array('code' => 500 , 'msg' => '支付接口不存在！');
		if(!isset($arr_fields['pay_val']) || empty($arr_fields['pay_val']) ) return array('code' => 500 , 'msg' => '支付金额不正确');
		if(!isset($arr_fields['pay_beta'])) $arr_fields['pay_beta'] = '';
		//保存支付记录
		$arr_payinfo = self::on_insert($arr_fields);
		if($arr_payinfo['code']!=0) return $arr_payinfo;

		//取支付接口需要的参数
		$arr_config = array(
			'pay_method' => $arr_fields['pay_method'],
			'subject' => $arr_fields['pay_title'],
			'detail' => $arr_fields['pay_beta'],
			'price' => $arr_fields['pay_val'],
			'orderid' => $arr_payinfo['number'],
			'banktype' => $arr_fields['pay_banktype'],
		);

		$file_path = dirname(__FILE__) . "/" . $arr_fields["pay_method"] . "/pay." .  $arr_fields["pay_method"] . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_" . $arr_fields["pay_method"];
		$obj_pay=new $class_name;
		$arr_return=$obj_pay->on_pay($arr_config);
		return $arr_return;
	}
	
	//保存支付记录
	function on_insert($arr_fields) {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		$arr_fields["pay_addtime"] = TIME;
		$arr_fields["pay_time"] = date("Y-m-d H:i:s");
		$arr_fields["pay_day"] = date("Y-m-d");
		$obj_db = cls_obj::db_w();
		$arr_fields["pay_number"] = $arr_fields["pay_about_id"] . date("ymdHis") . $arr_fields["pay_user_id"];//相关id+时间+用户id
		$arr = $obj_db->on_insert(cls_config::DB_PRE."other_pay",$arr_fields);
		if($arr['code'] == 0) {
			$arr_return['id'] = $obj_db->insert_id();
			//其它非mysql数据库不支持insert_id 时
			if(empty($arr_return['id'])) {
				$where  = "pay_user_id='" . $arr_fields['pay_user_id'] . " and pay_about_id='".$arr_fields['pay_about_id'] . "' and pay_addtime='".$arr_fields["pay_addtime"]."'";
				$obj_rs = $obj_db->get_one("select pay_id from ".cls_config::DB_PRE."other_pay where ".$where);
				if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['pay_id'];
			}
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
		}
		$arr_return["number"] = $arr_fields["pay_number"];
		return $arr_return;
	}

	//处理支付返回消息
	function on_return() {
		$pay_method = fun_get::get("paymethod");
		if(empty($pay_method)) return array('code'=>500 , 'msg' => '支付方式不正确');
		$file_path = dirname(__FILE__) . "/" . $pay_method . "/pay." .  $pay_method . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_" . $pay_method;
		$obj_pay=new $class_name;
		//调用接口，处理消息 , 返回：number => 订单流水号 , val => 充值金额
		$arr_info = $obj_pay->on_return();
		if($arr_info['code']!=0) return $arr_info;
		$arr_msg = self::_return_exe($arr_info);
		$arr_msg["msg_ok"] = $arr_info['msg_ok'];
		$arr_msg["msg_err"] = $arr_info['msg_err'];
		return $arr_msg;
	}
	//处理支付返回消息
	function on_notify() {
		$pay_method = fun_get::get("paymethod");
		if(empty($pay_method)) return array('code'=>500 , 'msg' => '支付方式不正确');

		$file_path = dirname(__FILE__) . "/" . $pay_method . "/pay." .  $pay_method . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_" . $pay_method;
		$obj_pay=new $class_name;
		//调用接口，处理消息 , 返回：number => 订单流水号 , val => 充值金额
		$arr_info = $obj_pay->on_notify();
		if($arr_info['code']!=0) return $arr_info;
		
		$arr_msg = self::_return_exe($arr_info);
		$arr_msg["msg_ok"] = $arr_info['msg_ok'];
		$arr_msg["msg_err"] = $arr_info['msg_err'];
		return $arr_msg;
	}

	//处理返回
	function _return_exe($arr_info) {

		//取支付信息
		$obj_db = cls_obj::db_w();
		$obj_pay = $obj_db->get_one("select pay_id , pay_number, pay_val , pay_method , pay_type , pay_about_id , pay_state ,pay_user_id from " . cls_config::DB_PRE . "other_pay where pay_number='" . $arr_info['number'] . "'");
		if(empty($obj_pay)) return array('code' => 500 , 'msg' => '支付信息错误，支付订单未找到');
		if($obj_pay['pay_state']>0) return array('code' => 0 , 'msg' => '支付成功' , "about_id" => $obj_pay['pay_about_id'] ,"pay_type" => $obj_pay['pay_type']);

		 //订餐支付
		if($obj_pay['pay_type']==1) {
			//将订单设为已支付状态
			$obj_rs = $obj_db->get_one("select order_id,order_ids,order_detail,order_name,order_mobile,order_area_allid,order_tel,order_ids,order_score_money,order_repayment,order_addprice,order_total,order_favorable,order_ticket,order_arrive,order_area,order_louhao1,order_louhao2,order_company,order_depart,order_pay_val,order_telext,order_beta,shop_name,shop_extend,shop_sms,shop_email,shop_weixin_id,shop_id,shop_sms_tel,order_reserve_id from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id where order_id='" . $obj_pay['pay_about_id'] . "'");
			if(!empty($obj_rs) && empty($obj_rs['order_reserve_id'])) {
				$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state='0' , order_pay_time='" . date("Y-m-d H:i:s") . "',order_pay_val='" . $arr_info['val'] . "',order_pay_id=" . $obj_pay['pay_id'] . " where order_id='" . $obj_pay['pay_about_id'] . "'");
				$arr = explode("|" , $obj_rs['order_ids']);
				$str_ids = implode(",",$arr);
				$arr_x = $arr_menu_price = $arr_menu = array();
				foreach($arr as $x) {
					$arr_x[] = explode("," , $x);
				}
				//取当时下单的定价
				if(!empty($obj_rs["order_detail"])) {
					$arr_detail = unserialize($obj_rs["order_detail"]);
					if(isset($arr_detail["menu_price"])) $arr_menu_price = $arr_detail["menu_price"];
				}

				$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
				while($obj_menu_rs = $obj_db->fetch_array($obj_result)) {
					if(isset($arr_menu_price["id_".$obj_menu_rs["menu_id"]])) $obj_menu_rs["menu_price"] = $arr_menu_price["id_".$obj_menu_rs["menu_id"]];
					$arr_menu["id_".$obj_menu_rs["menu_id"]] = $obj_menu_rs;
				}
				$cont = tab_meal_order::get_order_msg($arr_x , $arr_menu , $obj_rs);
				$id = $obj_rs["order_id"];
				if($id>=100000) $id = substr($id,-5);
				//是否分区域发短信
				$obj_tel = $obj_db->get_one("select dispatch_smstel from " . cls_config::DB_PRE . "meal_dispatch where dispatch_shop_id='" . $obj_rs["shop_id"] . " ' and dispatch_area_id in(" . $obj_rs['order_area_allid'] . ") and dispatch_smstel!=''");
				if(!empty($obj_tel) && !empty( $obj_tel['dispatch_smstel'])) {
					$arr_tel = explode("," , $obj_tel["dispatch_smstel"]);
				} else {
					$arr_tel = explode("," , $obj_rs["shop_sms_tel"]);
				}
				if($obj_rs["shop_sms"] == 2) {
					$smscont = $cont . "；确认码：" . $id;
					$tel = $arr_tel[rand(0,count($arr_tel)-1)];//随机一个电话
					$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => $smscont ,"id" => $obj_rs["order_id"] ,"confirm_id" => $id , "type"=>1) );
				} else if($obj_rs["shop_sms"] == 1) {
					$tel = $arr_tel[rand(0,count($arr_tel)-1)];//随机一个电话
					$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => cls_config::get("neworder_shop_tips","sms") ,"id" => $obj_rs["order_id"] , "type"=>1) );
				}
				if(!empty($obj_rs["shop_email"])) {
					$url = fun_get::html_url('index.php?app_module=shop&app=order&app_act=detail&id='.$id , 1);
					$cont .= '  <a href="'.$url.'">点击查看订单详情</a>';
					$arr = cls_obj::get("cls_com")->email('send' , array('to_mail' => $obj_rs["shop_email"] , 'title' => "#sitename#提醒您有新的订单" , 'content' => $cont ,'save' => 1));
				}
				$weixin_appid = cls_config::get('appid' , 'weixin' , '' , '');
				if(!empty($obj_rs["shop_weixin_id"]) && !empty($weixin_appid) ) {
					$smscont = $cont . "；确认码：" . $id;
					$arr_msg = cls_weixin::on_send(0 , $obj_rs["shop_weixin_id"] , 'text' , array('message_text'=>$smscont) );
				}
				$cfg_print = cls_config::get("" , "print","","");
				if(!empty($cfg_print['url']) && $obj_rs['shop_print_auto'] == 1) {
					file_get_contents(cls_config::get("url","base") . "/common.php?app_module=meal&app=call&app_act=print&isremote=1&order_id=" . $obj_rs["order_id"]);
				}
			} else if(!empty($obj_rs['order_reserve_id'])) {
				$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state=10 , order_pay_time='" . date("Y-m-d H:i:s") . "',order_pay_val='" . $arr_info['val'] . "',order_pay_id=" . $obj_pay['pay_id'] . " where order_id='" . $obj_pay['pay_about_id'] . "'");
				$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=10 where reserve_id='" . $obj_rs['order_reserve_id'] . "'");
			}

		}
		 //预付款支付
		if($obj_pay['pay_type']==2) {
			//新增预付款
			$arr_repayment_fields = array(
				"repayment_user_id" => $obj_pay['pay_user_id'],
				"repayment_val" => $arr_info['val'],
				"repayment_beta" => '充值',
				"repayment_type" => 2,
				"repayment_about_id" => $obj_pay['pay_id'],
			);

			$arr_msg = tab_sys_user_repayment::on_recharge($arr_repayment_fields);
		}
		if($obj_pay['pay_type']==3) {
			$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=1 , reserve_paytime='" . date("Y-m-d H:i:s") . "',reserve_payval='" . $arr_info['val'] . "',reserve_payid=" . $obj_pay['pay_id'] . " where reserve_id='" . $obj_pay['pay_about_id'] . "'");

		}
		$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "other_pay set pay_state=1 , pay_day='" . date("Y-m-d") . "',pay_time='" . date("Y-m-d H:i:s") . "',pay_return_val='" . $arr_info['val'] . "',pay_return_id='" . $arr_info['tradeid'] . "' where pay_id='" . $obj_pay['pay_id'] . "'");
		$arr_msg['about_id'] = $obj_pay['pay_about_id'];
		$arr_msg['pay_type'] = $obj_pay['pay_type'];
		return $arr_msg;
	}
	function on_refund($id , $beta = '') {
		$obj_db = cls_obj::db_w();
		$obj_rs = $obj_db->get_one("select pay_method,pay_val,pay_number,pay_state,pay_about_id,pay_id,pay_user_id,pay_type from " . cls_config::DB_PRE . "other_pay where pay_id='" . $id . "'");
		$obj_rs['pay_method'] = "weixin";
		$uid = 0;
		$tel = '';
		if(empty($obj_rs)) return array("code" => 500 , "msg" => "支付记录不存在");
		if($obj_rs['pay_state']==0) return array("code" => 500 , "msg" => "当前订单还未支付，无需退款");
		if($obj_rs['pay_state']<0) return array("code" => 500 , "msg" => "支付记录无效，无法退款");
		if($obj_rs["pay_type"] == 1) {
			$obj_order = $obj_db->get_one("select order_user_id,order_id,order_state,order_tel,order_mobile from " . cls_config::DB_PRE . "meal_order where order_id='" . $obj_rs['pay_about_id'] . "'");
			if(empty($obj_order)) return array("code" => 500 , "msg" => "支付订单不存在");
			if($obj_order["order_state"]>=0) return array("code" => 500 , "msg" => "当前订单状态不可以退款，请修改订单状态再来操作");
			$uid = $obj_order['order_user_id'];
			$tel = empty($obj_order['order_mobile']) ? $obj_order['order_tel'] : $obj_order['order_mobile'];
		} else {
			return array("code" => 500 , "msg" => "当前支付类型不允许退款");
		}
		$file_path = dirname(__FILE__) . "/" . $obj_rs['pay_method'] . "/pay." .  $obj_rs['pay_method'] . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_" . $obj_rs['pay_method'];
		$obj_pay=new $class_name;
		//调用接口，处理消息 , 返回：number => 订单流水号 , val => 充值金额
		$arr_info = $obj_pay->on_refund($obj_rs['pay_number'],$obj_rs['pay_val'],$obj_rs['pay_id']);
		if($arr_info['code']==0) {
			$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "other_pay set pay_state=-2 where pay_id='" . $id . "' and pay_state>0");
			if($arr['code'] != 0 || $obj_db->affected_rows()<1) return array("code" => 500 , "msg" => "退款订单不存在");
			$arr_fields = array(
				"refund_pay_id" => $id,
				"refund_state" => -1,
				"refund_apply_time" => date("Y-m-d"),
				"refund_admin_uid" => cls_obj::get("cls_user")->uid,
				"refund_return_id" => $arr_info["refund_id"],
				"refund_uid" => $uid,
				"refund_tips_tel" => $tel,
			);
			if(!empty($beta)) $arr_fields["refund_beta"] = $beta;
			$arr = tab_other_pay_refund::on_save($arr_fields);
			if(empty($tel)) $tel = $arr['tel'];
			$pay_return_apply_sms = cls_config::get("pay_return_apply_sms" , "tips");
			if(!empty($tel) && trim($pay_return_apply_sms) != '') {
				$msg_cmd_mode = cls_config::get("msg_cmd_mode","base");
				$pay_return_apply_sms = str_replace("{$total}" , $obj_rs['pay_val']  , $pay_return_apply_sms);
				if($msg_cmd_mode == 1) {
					tab_sys_cmd_notice::on_save(array(
							"notice_type" => 0,
							"notice_about_id" => $id,
							"notice_tel" => $tel,
							"notice_sms_cont" => $pay_return_apply_sms,
							"notice_from_uid" => $uid,
						));
				} else {
					$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => $pay_return_apply_sms , "type"=>0) );
				}
			}
			return $arr;
		} else {
			return $arr_info;
		}
	}
	function get_refund_state($id) {
		$obj_pay_refund = cls_obj::db()->get_one("select a.refund_pay_id,a.refund_state,a.refund_return_id,b.pay_method from " . cls_config::DB_PRE . "other_pay_refund a left join " . cls_config::DB_PRE . "other_pay b on a.refund_pay_id=b.pay_id where refund_pay_id='" . $id . "'");
		if(empty($obj_pay_refund)) return array("code" => 500 , "msg" => "退款记录不存在");
		if($obj_pay_refund['refund_state'] != -1) return array("code" => 500 , "msg" => "非退款处理中记录");
		$pay_method = $obj_pay_refund["pay_method"];
		$file_path = dirname(__FILE__) . "/" . $pay_method . "/pay." .  $pay_method . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_" . $pay_method;
		$obj_pay=new $class_name;
		$arr_info = $obj_pay->get_refund_state($obj_pay_refund["refund_return_id"]);
		$arr = array("code" => 0);
		if($arr_info['code'] == 0) {
			$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "other_pay_refund set refund_state=1,refund_return_time='" . date("Y-m-d H:i:s") . "',refund_retotal='" . $arr_info['retotal'] . "' where refund_pay_id='" . $id . "'");
		} else if($arr_info['code'] == 501){
			$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "other_pay_refund set refund_state=-3,refund_return_time='" . date("Y-m-d H:i:s") . "',refund_err='" . $arr_info['msg'] . "' where refund_pay_id='" . $id . "'");
		} else if($arr_info['code'] == 500) {
			$arr = $arr_info;
		}
		return $arr;
	}
}