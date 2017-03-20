<?php
class pay_alipaywap {
	function get_config() {
		$arr_paymethod = cls_config::get("alipaywap" , "pay" , "" , "");
		$arr_config = array(
			"return_url" => cls_config::get("url" , "base")."/common.php?" . urlencode("app=pay&app_act=return&paymethod=alipaywap"),
			"notify_url" => cls_config::get("url" , "base")."/common.php?" . urlencode("app=pay&app_act=notify&paymethod=alipaywap"),
			"count" => $arr_paymethod["fields"]["email"],
			"partner" => $arr_paymethod["fields"]["parterid"],
			"key" => $arr_paymethod["fields"]["key"],
			"private_key_path" => "key/rsa_private_key.pem",
			"ali_public_key_path" => "key/alipay_public_key.pem",
			"sign_type" => "MD5",
			"input_charset" => "utf-8",
			"cacert" => getcwd().'\\cacert.pem',
			"transport" => "http",
			"format" => "xml",
			"version" => "2.0",
		);
		return $arr_config;
	}
	function on_pay( $arr_fields = array() ) {
			require_once(dirname(__FILE__) . "/lib/alipay_submit.class.php");
			$arr_return=array("html" => "" ,"code" => 0 , "msg" => "");
			$arr_config = self::get_config();
			$req_data = '<direct_trade_create_req><notify_url>' . $arr_config['notify_url'] . '</notify_url><call_back_url>' . $arr_config['return_url'] . '</call_back_url><seller_account_name>' . $arr_config['count'] . '</seller_account_name><out_trade_no>' . $arr_fields["orderid"] . '</out_trade_no><subject>' . $arr_fields["subject"] . '</subject><total_fee>' . $arr_fields["price"] . '</total_fee><merchant_url>' . $arr_config['return_url'] . '</merchant_url></direct_trade_create_req>';

			$para_token = array(
					"service" => "alipay.wap.trade.create.direct",
					"partner" => trim($arr_config['partner']),
					"sec_id" => $arr_config['sign_type'],
					"format"	=> $arr_config['format'],
					"v"	=> $arr_config['version'],
					"req_id"	=> date('Ymdhis') . rand(1000,9999),
					"req_data"	=> $req_data,
					"_input_charset"	=> trim(strtolower($arr_config['input_charset']))
			);
			//建立请求
			$alipaySubmit = new AlipaySubmit($arr_config);
			$html_text = $alipaySubmit->buildRequestHttp($para_token);

			//URLDECODE返回的信息
			$html_text = urldecode($html_text);

			//解析远程模拟提交后返回的信息
			$para_html_text = $alipaySubmit->parseResponse($html_text);
			$error = (isset($para_html_text['res_error']) && !empty($para_html_text['res_error']) ) ? $para_html_text['res_error'] : "参数不对";
			if(!isset($para_html_text['request_token']) || empty($para_html_text['request_token'])) cls_error::on_exit("exit" , $error);
			//获取request_token
			$request_token = $para_html_text['request_token'];


			/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

			//业务详细
			$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
			//必填

			//构造要请求的参数数组，无需改动
			$parameter = array(
					"service" => "alipay.wap.auth.authAndExecute",
					"partner" => trim($arr_config['partner']),
					"sec_id" => trim($arr_config['sign_type']),
					"format"	=> $arr_config['format'],
					"v"	=> $arr_config['version'],
					"req_id"	=> $para_token['req_id'],
					"req_data"	=> $req_data,
					"_input_charset"	=> trim(strtolower($arr_config['input_charset']))
			);
	
			//建立请求
			$alipaySubmit = new AlipaySubmit($arr_config);
			$arr_return['html'] = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
			return $arr_return;
	}
	function on_return() {
		require_once(dirname(__FILE__) . "/lib/alipay_notify.class.php");
		$arr_config = self::get_config();
		$alipayNotify = new AlipayNotify($arr_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {//验证成功
			$number = fun_get::get("out_trade_no");
			//商户订单号
			$number = fun_get::get('out_trade_no');
			//交易状态
			$result = fun_get::get('result');
			$obj_pay = cls_obj::db()->get_one("select pay_val from " . cls_config::DB_PRE . "other_pay where pay_number='" . $number . "'");
			if(empty($obj_pay)) return array('code' => 500 , 'msg' => '验证失败');
			$totalpay = $obj_pay['pay_val'];
			$arr_return = array(
				'code' => 0,
			    'number' => $number,	//获取订单号
			    'tradeid' => fun_get::get('trade_no'),		//获取支付宝交易号
			    'val' => $totalpay,		//获取总价格
				'msg_ok' => 'success',
				'msg_err' => 'fail'
			);
			return $arr_return;
		} else {
			return array('code' => 500 , 'msg' => '验证失败');
		}

	}
	function on_notify() {
		require_once(dirname(__FILE__) . "/lib/alipay_notify.class.php");
		$arr_config = self::get_config();
		$alipayNotify = new AlipayNotify($arr_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {//验证成功
			$notify_data = $_POST['notify_data'];
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			//解析notify_data
			//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
			$doc = new DOMDocument();
			$doc->loadXML($notify_data);
			if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
				//商户订单号
				$number = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
				//支付宝交易号
				$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
				//交易状态
				$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
				$obj_pay = cls_obj::db()->get_one("select pay_val from " . cls_config::DB_PRE . "other_pay where pay_number='" . $number . "'");
				if(empty($obj_pay)) return array('code' => 500 , 'msg' => '验证失败');
				$totalpay = $obj_pay['pay_val'];
				$arr_return = array(
					'code' => 0,
					'number' => $number,	//获取订单号
					'tradeid' => $trade_no,		//获取支付宝交易号
					'val' => $totalpay,		//获取总价格
					'msg_ok' => 'success',
					'msg_err' => 'fail'
				);
				return $arr_return;
			} else {
				return array('code' => 500 , 'msg' => '验证失败' , 'msg_ok' => 'success' , 'msg_err' => 'fail');
			}
		} else {
			return array('code' => 500 , 'msg' => '验证失败' , 'msg_ok' => 'success' , 'msg_err' => 'fail');
		}

	}
}