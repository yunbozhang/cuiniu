<?php
require_once(dirname(__FILE__) . "/alipay_service.class.php");
class pay_alipay_js
{
	function on_pay($arr_config)
	{
		$arr_return=array("html" => "" ,"code" => 0 , "msg" => "");
		//构造要请求的参数数组
		$parameter = array(
				"service"			=> "create_direct_pay_by_user",	//接口名称，不需要修改
				"payment_type"		=> "1",               				//交易类型，不需要修改

				//获取配置文件(alipay_config.php)中的值
				"partner"			=> trim($arr_config["partner"]),
				"seller_email"		=> trim($arr_config["email"]),
				"return_url"		=> trim($arr_config["return_url"]),
				"notify_url"		=> trim($arr_config["notify_url"]),
				"_input_charset"	=> trim($arr_config["input_charset"]),
				"show_url"			=> trim($arr_config["show_url"]),

				//从订单数据中动态获取到的必填参数
				"out_trade_no"		=> $arr_config["orderid"],
				"subject"			=> $arr_config["subject"],
				"body"				=> $arr_config["body"],
				"total_fee"				=> $arr_config["price"],
				//扩展功能参数——网银提前
				"paymethod"	      => "directPay",
				"defaultbank"	  => "",

				//扩展功能参数——防钓鱼
				"anti_phishing_key"=> "",
				"exter_invoke_ip"  => fun_get::ip(),
				//扩展功能参数——自定义参数
				"buyer_email"	   => "",
				"extra_common_param" => "" ,
				"royalty_type"		=> "",
				"royalty_parameters"=> "",
			
		);

		$alipay = new AlipayService($arr_config);
		$arr_return["html"] = $alipay->create_direct_pay_by_user($parameter);
		return $arr_return;
	}
	function on_return( $arr_config ) {
		require_once("alipay_notify.class.php");
		$alipayNotify = new AlipayNotify($arr_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {//验证成功
			$arr_return = array(
				'code' => 0,
			    'number' => fun_get::get('out_trade_no'),	//获取订单号
			    'tradeid' => fun_get::get('trade_no'),		//获取支付宝交易号
			    'val' => (float)fun_get::get('total_fee'),		//获取总价格
				'msg_ok' => 'success',
				'msg_err' => 'fail'
			);
			return $arr_return;
		} else {
			return array('code' => 500 , 'msg' => '验证失败');
		}

	}
	function on_notify( $arr_config ) {
		require_once("alipay_notify.class.php");
		$alipayNotify = new AlipayNotify($arr_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {//验证成功
			$arr_return = array(
				'code' => 0,
			    'number' => fun_get::post('out_trade_no'),	//获取订单号
			    'tradeid' => fun_get::post('trade_no'),		//获取支付宝交易号
			    'val' => (float)fun_get::post('total_fee'),		//获取总价格
				'msg_ok' => 'success',
				'msg_err' => 'fail'
			);
			return $arr_return;
		} else {
			return array('code' => 500 , 'msg' => '验证失败' , 'msg_ok' => 'success' , 'msg_err' => 'fail');
		}

	}
}