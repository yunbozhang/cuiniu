<?php
class pay_alipay_jsdb
{
	function get_config($arr_config) {
		$alipay_config = array();
		//合作身份者id，以2088开头的16位纯数字
		$alipay_config['partner']		= $arr_config['partner'];
		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['key']			= $arr_config['key'];
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//签名方式 不需修改
		$alipay_config['sign_type']    = $arr_config['sign_type'];
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= $arr_config['input_charset'];
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().'\\cacert.pem';
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = $arr_config['transport'];
		return $alipay_config;
	}
	function on_pay($arr_config) {
		require_once(dirname(__FILE__) . "/alipay_submit.class.php");
		$arr_return=array("html" => "" ,"code" => 0 , "msg" => "");
		//构造要请求的参数数组
		$parameter = array(
				"service" => "trade_create_by_buyer",
				"partner" => trim($arr_config["partner"]),
				"payment_type"	=> 1,//支付类型
				"notify_url"	=> trim($arr_config["notify_url"]),//服务器异步通知页面路径
				"return_url"	=> trim($arr_config["return_url"]),//页面跳转同步通知页面路径
				"seller_email"	=> trim($arr_config["email"]),//卖家支付宝帐户
				"out_trade_no"	=> $arr_config["orderid"],//商户订单号
				"subject"	=> $arr_config["subject"],//订单名称
				"price"	=> $arr_config["price"],//付款金额
				"quantity"	=> $arr_config["goods_count"],//商品数量
				"logistics_fee"	=> $arr_config["logistics_fee"],//物流费用
				"logistics_type"	=> $arr_config["logistics_type"],//物流类型,必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
				"logistics_payment"	=> $arr_config["logistics_payment"],
				"body"	=> $arr_config["body"],//订单描述
				"show_url"	=> $arr_config["show_url"],//商品展示地址
				"receive_name"	=> $arr_config["receive_name"],//收货人姓名
				"receive_address"	=> $arr_config["receive_address"],//收货人地址
				"receive_zip"	=> $arr_config["receive_zip"],//收货人邮编
				"receive_phone"	=> $arr_config["receive_phone"],//收货人电话
				"receive_mobile"	=> $arr_config["receive_mobile"],//收货人手机
				"_input_charset"	=> $arr_config["input_charset"],
		);
		$alipay_config = self::get_config($arr_config);
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$html .= '<html>';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		$html .= '<title>支付宝标准双接口接口</title>';
		$html .= '</head><body>';
		$html .= $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		$html .= '</body></html>';
		$arr_return["html"] = $html;
		return $arr_return;
	}
	function on_return( $arr_config ) {
		require_once("alipay_notify.class.php");
		$alipay_config = self::get_config($arr_config);
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
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
		require_once("alipay_notify.class.php");
		$alipay_config = self::get_config($arr_config);
		$alipayNotify = new AlipayNotify($alipay_config);
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