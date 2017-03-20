<?php
require_once(dirname(__FILE__) . "/alipay_service.php");
class pay_alipay_db
{
	function on_pay($arr_config)
	{
		$arr_return=array("html" => "" ,"code" => 0 , "msg" => "");

		$parameter = array(
				"service"			=> "create_partner_trade_by_buyer",	//接口名称，不需要修改
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
				"price"				=> $arr_config["price"],
				"quantity"			=> $arr_config["goods_count"],//商品数量
				
				"logistics_fee"		=> $arr_config["logistics_fee"],//物流费用
				"logistics_type"	=> $arr_config["logistics_type"],//物流类型,必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）、DIRECT (无需物流)
				"logistics_payment"	=> $arr_config["logistics_payment"],
				
				//扩展功能参数——买家收货信息
				"receive_name"		=> $arr_config["receive_name"],//收货人姓名
				"receive_address"	=> $arr_config["receive_address"],//收货人地址
				"receive_zip"		=> $arr_config["receive_zip"],//收货人邮编
				"receive_phone"		=> $arr_config["receive_phone"],//收货人电话
				"receive_mobile"	=> $arr_config["receive_mobile"],//收货人手机
		);

		//构造请求函数
		$alipay = new alipay_service($parameter , $arr_config['key'] , $arr_config['sign_type']);
		//若改成GET方式传递
		$url = $alipay->create_url();
		$arr_return["html"] = '<script>window.location ="' . $url . '";</script>'; 
		return $arr_return;
	}
	function on_return( $arr_config ) {
		require_once("alipay_notify.php");
		//构造通知函数信息
		$alipay = new alipay_notify($arr_config["partner"],$arr_config["key"],$arr_config["sign_type"],$arr_config["input_charset"],$arr_config["transport"]);
		//计算得出通知验证结果
		$verify_result = $alipay->return_verify();
		if($verify_result) {//验证成功
			$arr_return = array(
				'code' => 0,
			    'number' => fun_get::get('out_trade_no'),	//获取订单号
			    'tradeid' => fun_get::get('trade_no'),		//获取支付宝交易号
			    'val' => (float)fun_get::get('price'),		//获取总价格
				'msg_ok' => 'success',
				'msg_err' => 'fail'
			);
			return $arr_return;
		} else {
			return array('code' => 500 , 'msg' => '验证失败' , 'msg_ok' => 'success' , 'msg_err' => 'fail');
		}

	}
	function on_notify( $arr_config ) {
		require_once("alipay_notify.php");
		$alipay = new alipay_notify($arr_config["partner"],$arr_config["key"],$arr_config["sign_type"],$arr_config["input_charset"],$arr_config["transport"]);
		$verify_result = $alipay->notify_verify();  //计算得出通知验证结果
		if($verify_result) {//验证成功
			$arr_return = array(
				'code' => 0,
			    'number' => fun_get::post('out_trade_no'),	//获取订单号
			    'tradeid' => fun_get::post('trade_no'),		//获取支付宝交易号
			    'val' => (float)fun_get::post('price'),		//获取总价格
				'msg_ok' => 'success',
				'msg_err' => 'fail'
			);
			return $arr_return;
		} else {
			return array('code' => 500 , 'msg' => '验证失败' , 'msg_ok' => 'success' , 'msg_err' => 'fail');
		}

	}
}