<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_member extends mod_member{
	//我的订单
	function act_default() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		//订单列表
		$this->order_list = $this->get_order_list();
		return $this->get_view(); //显示页面
	}
	//我的订单
	function act_main() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		return $this->get_view(); //显示页面
	}

	function act_pwd(){
		//初始公共信息
		$this->memberinfo = $this->init_info();
		$this->editinfo = $this->get_userinfo();
		return $this->get_view();
	}
	//配送地址页
	function act_info() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		//地址列表
		$this->this_info = $this->get_infolist();
		//地区
		$this->arr_area = $this->get_area_default();
		return $this->get_view(); //显示页面
	}
	//会员等级
	function act_myvip() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		//积分明细记录
		$this->action_list = $this->get_action_list();
		//取进度条
		$this->progress = $this->myvip_progress();
		return $this->get_view(); //显示页面
	}
	//订单支付跳转页
	function act_order_pay() {
		$id = fun_get::get("id");
		$pay_method = fun_get::get("pay_method");
		$obj_order = cls_obj::db()->get_one("select order_pay_method , order_total_pay , order_state , order_id , order_reserve_id from " . cls_config::DB_PRE . "meal_order where order_id='" . $id . "'");
		if(empty($obj_order)) {
			cls_error::on_error("exit" , "订单不存在");
		}
		if($obj_order['order_total_pay']<=0 && !empty($obj_order['order_reserve_id'])) {
			$arr_msg = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=10 , reserve_paytime='" . date("Y-m-d H:i:s") . "',reserve_payval='" . $obj_order['order_total_pay'] . "' where reserve_id='" . $obj_order['order_reserve_id'] . "'");
			$arr_msg = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state=1 where order_id='" . $obj_order['order_id'] . "'");
			cls_error::on_display(0 , "订单已经支付" , array(array( 'time' => 10 , 'target' => '_self' , 'title' => '跳转' , 'url' => '/wap.php?app_act=cart.ok&id=' . $id)) );//显示消息页面
		}
		if($obj_order['order_state'] != -2) {
			if($obj_order['order_state'] >= 0 && !in_array($obj_order['order_pay_method'] , array('paymented' , 'afterpayment') )) {
				cls_error::on_display(0 , "订单已经支付" , array(array( 'time' => 10 , 'target' => '_self' , 'title' => '跳转' , 'url' => '/wap.php?app_act=cart.ok&id=' . $id)) );//显示消息页面
			} else {
				cls_error::on_error("exit" , "该订单不能在线支付");
			}
		}
		$bank_type = fun_get::get("bank_type");
		$arr = cls_obj::get('cls_com')->pay("on_pay" , array("pay_method"=> $pay_method , "pay_title" => "在线订餐" , "pay_about_id" => $obj_order['order_id'] , "pay_val" => $obj_order['order_total_pay'] , 'pay_type' => 1 ,'pay_user_id' => cls_obj::get("cls_user")->uid , "pay_beta" => '外卖支付' , "pay_banktype" => $bank_type) );
		if($arr['code'] == 0) {
			echo $arr['html'];exit;
		} else {
			cls_error::on_error("exit" , $arr['msg']);
		}
	}
	//预订支付跳转页
	function act_reserve_pay() {
		$id = fun_get::get("id");
		$pay_method = fun_get::get("pay_method");
		$obj_order = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $id . "'");
		if(empty($obj_order)) {
			cls_error::on_error("exit" , "订单不存在");
		}
		if($obj_order['reserve_state'] != 0) {
			if($obj_order['order_state'] > 0) {
				cls_error::on_display(0 , "订单已经支付" , array(array( 'time' => 10 , 'target' => '_self' , 'title' => '跳转' , 'url' => '/wap.php?app=member&app_act=table.reserve&id=' . $id)) );//显示消息页面
			} else {
				cls_error::on_error("exit" , "预订信息无效");
			}
		}
		$bank_type = fun_get::get("bank_type");
		$arr = cls_obj::get('cls_com')->pay("on_pay" , array("pay_method"=> $pay_method , "pay_title" => "在线预订" , "pay_about_id" => $obj_order['reserve_id'] , "pay_val" => $obj_order['reserve_deposit'] , 'pay_type' => 3 ,'pay_user_id' => cls_obj::get("cls_user")->uid , "pay_beta" => '在线订桌' , "pay_banktype" => $bank_type) );
		if($arr['code'] == 0) {
			echo $arr['html'];exit;
		} else {
			cls_error::on_error("exit" , $arr['msg']);
		}
	}
	//订单支付跳转页
	function act_repayment_pay() {
		$val = (float)fun_get::get("val");
		$pay_method = fun_get::get("pay_method");
		$arr = cls_obj::get('cls_com')->pay("on_pay" , array("pay_method"=> $pay_method , "pay_title" => "预付款充值" , "pay_about_id" => 0 , "pay_val" => $val , 'pay_type' => 2 ,'pay_user_id' => cls_obj::get("cls_user")->uid) );
		if($arr['code'] == 0) {
			echo $arr['html'];exit;
		} else {
			cls_error::on_error("exit" , $arr['msg']);
		}
	}
	//我的积分 
	function act_myintegral() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		$this->score_money = intval(cls_obj::get("cls_user")->get_score() * cls_config::get("score_money_scale","meal"));
		//订单列表
		$this->action_list = $this->get_action_list(1);
		return $this->get_view(); //显示页面
	}
	//我的优惠券 
	function act_voucher() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		//订单列表
		$this->voucher_list = $this->get_voucher_list();
		return $this->get_view(); //显示页面
	}
	//我的预付款 
	function act_repayment() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		//订单列表
		$this->arr_list = $this->get_repayment_list(1);
		$this->arr_pay = cls_config::get("" , "pay" , array() , "");
		return $this->get_view(); //显示页面
	}
	//获取指定id收货信息
	function act_getinfo() {
		$arr_info = $this->get_info();
		return fun_format::json($arr_info);
	}
	//我的留言
	function act_msg() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		$this->arr_list = $this->get_msglist();
		return $this->get_view();
	}
	//评论订单
	function act_comment() {
		$id = fun_get::get("order_id");
		$this->arr_list = $this->get_comment($id);
		$arr = cls_config::get("comment_menu" , "meal");
		$this->arr_comment_menu = array_values($arr);
		$arr = cls_config::get("comment_shop" , "meal" , array());
		$arr_comment_shop = array();
		foreach($arr as $item => $key) {
			$arr_comment_shop[$item] = explode("||" , $key);
		}
		$this->arr_comment_shop = $arr_comment_shop;
		return $this->get_view();
	}
	//保存评论
	function act_comment_save() {
		$arr = $this->save_comment();
		return fun_format::json($arr);
	}
	//订单明细
	function act_order_detail() {
		$this->memberinfo = $this->init_info();
		//订单列表
		$id = (int)fun_get::get("id");
		$order_cancel = (int)cls_config::get("order_cancel" , "meal");
		$this->order_cancel = $order_cancel;
		if($order_cancel>0) $order_cancel = TIME-($order_cancel * 60);
		$this->order_cancel_time = $order_cancel;
		$this->order_list = $this->get_order_detail($id);
		return $this->get_view(); //显示页面
	}
	//取消订单
	function act_order_cancel() {
		//订单列表
		$id = (int)fun_get::get("id");
		$arr = $this->on_order_cancel($id);
		return fun_format::json($arr);
	}
	//取消预订
	function act_reserve_cancel() {
		//订单列表
		$arr = $this->on_reserve_cancel();
		return fun_format::json($arr);
	}
	function act_address() {
		$this->memberinfo = $this->init_info();
		$this->arr_list = fun_kj::get_address();
		$this->areainfo = $this->get_area_info();
		return $this->get_view(); //显示页面
	}

	//修改密码
	function act_anquan_editpwd() {
		$arr = $this->on_editpwd();
		return fun_format::json($arr);
	}
	//绑定手机
	function act_anquan_verifytel() {
		$arr = $this->on_bind_verifytel();
		return fun_format::json($arr);
	}
	//保存安全密保
	function act_anquan_safeask() {
		$arr = $this->on_save_safeask();
		return fun_format::json($arr);
	}
	//发送验证码
	function act_anquan_send_sms() {
		$obj_user = cls_obj::db()->get_one("select user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . cls_obj::get("cls_user")->uid . "'");
		$mobile = '';
		if(isset($obj_user['user_mobile'])) $mobile = $obj_user['user_mobile'];
		fun_get::get("tel",$mobile);
		fun_get::get("type",4);
		$str = cls_app::on_display('common' , '' , "sys" , "send.sms");
		return $str;
	}
	//验证验证码
	function act_anquan_verify_sms() {
		cls_obj::get("cls_session")->set('anquan.verify' , 1);//设置已验证标识
		$obj_user = cls_obj::db()->get_one("select user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . cls_obj::get("cls_user")->uid . "'");
		$mobile = '';
		if(isset($obj_user['user_mobile'])) $mobile = $obj_user['user_mobile'];
		fun_get::get("tel",$mobile);
		fun_get::get("type",4);
		$str = cls_app::on_display('common' , '' , "sys" , "verify.sms");
		if( stristr($str , '"code":0,' ) ) {
			cls_obj::get("cls_session")->set('anquan.verify' , 1);//设置已验证标识
		}
		return $str;
	}
	//验证密保
	function act_anquan_verify_ask() {
		$arr_question = array();
		$obj_result = cls_obj::db()->select("select safeask_question,safeask_number,safeask_answer from " . cls_config::DB_PRE . "sys_safeask where safeask_user_id='" . cls_obj::get("cls_user")->uid . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_question[] = $obj_rs;
		}
		foreach($arr_question as $item) {
			$answer = fun_get::get('answer_' . $item['safeask_number']);
			if($item['safeask_answer'] != md5($answer)) return fun_format::json( array( "code" => 500 , "msg" => "验证密保答案错误") );
		}
		cls_obj::get("cls_session")->set('anquan.verify' , 1);//设置已验证标识
		return fun_format::json(array('code' => 0 , 'msg' => '验证成功'));
	}
	//
	function act_anquan() {
		$this->memberinfo = $this->init_info();
		$obj_user = cls_obj::db()->get_one("select user_mobile,user_verify_tel from " . cls_config::DB_PRE . "sys_user where user_id='" . cls_obj::get("cls_user")->uid . "'");
		$mobile = '';
		if(isset($obj_user['user_mobile']) && $obj_user['user_verify_tel']) {
			if(strlen($obj_user['user_mobile'])>5) $obj_user['user_mobile'] = str_pad(substr($obj_user['user_mobile'],0,3),strlen($obj_user['user_mobile'])-4,"*") . substr($obj_user['user_mobile'],-4);
			$mobile = $obj_user['user_mobile'];
		}
		$this->mobile = $mobile;
		$arr_question = array();
		$obj_result = cls_obj::db()->select("select safeask_question,safeask_number from " . cls_config::DB_PRE . "sys_safeask where safeask_user_id='" . cls_obj::get("cls_user")->uid . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_question[] = $obj_rs;
		}
		$this->arr_question = $arr_question;
		$this->ask1 = tab_sys_safeask::get_perms('ask1');
		$this->ask2 = tab_sys_safeask::get_perms('ask2');
		return $this->get_view();
	}
}