<?php
class pay_weixin {
	function get_config() {
		$arr_paymethod = cls_config::get("alipay" , "pay" , "" , "");
		$arr_config = array();
		$arr_config["partner"] = $arr_paymethod["fields"]["parterid"];
		$arr_config["key"] = $arr_paymethod["fields"]["key"];
		$arr_config["email"] = $arr_paymethod["fields"]["email"];
		$arr_config["return_url"] = cls_config::get("url" , "base")."/common.php?app=pay&app_act=return&paymethod=alipay";
		$arr_config["notify_url"] = cls_config::get("url" , "base")."/common.php?app=pay&app_act=notify&paymethod=alipay";
		$arr_config["goods_count"]=1;
		$arr_config["input_charset"] = "utf-8"; //字符编码格式  目前支持 GBK 或 utf-8
		$arr_config["sign_type"] = "MD5"; //加密方式  系统默认(不要修改)
		$arr_config["transport"]= "http";//访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式(系统默认,不要修改)
		$arr_config["interfacetype"] = $arr_paymethod["fields"]["interfacetype"];
		$arr_config['logistics_fee'] = "0.00";
		$arr_config['logistics_type'] = "DIRECT";//必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）、DIRECT (不需要物流)
		$arr_config['logistics_payment'] = 'SELLER_PAY';//必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
		$arr_config["feetype"] = $arr_paymethod["fields"]["feetype"];
		$arr_config["feeval"] = (float)$arr_paymethod["fields"]["feeval"];
		return $arr_config;
	}
	function on_pay( $arr_fields = array() ) {
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header('Content-Type:text/html;charset=utf-8');//当非附件或非图片时输出
		if(!isset($arr_fields["subject"]) || empty($arr_fields["subject"]))  return array('code' => 500 , 'msg' => '支付标题不能为空！');

		if(!isset($arr_fields["orderid"]) || empty($arr_fields["orderid"]) )  return array('code' => 500 , 'msg' => '订单id不能为空！');

		if(!isset($arr_fields["price"])) return array('code' => 500 , 'msg' => '商品金额不能为空！');
		//取微信id
		$obj_rs = cls_obj::db()->get_one("select login_name from " . cls_config::DB_PRE . "userapi_login where login_user_id='" . cls_obj::get('cls_user')->uid . "' and login_name like 'weixin_%'");
		if(empty($obj_rs)) {
			return array("code" => 500 , "msg" => "微信用户标识丢失，返回微信，重新进入");
		}
		$openid = str_replace("weixin_" , "" , $obj_rs['login_name']);
		//取唯一id
		$obj_rs = cls_obj::db()->get_one("select user_openid from " . cls_config::DB_PRE . "weixin_user where user_unionid='" . $openid . "'");
		if(!empty($obj_rs)) $openid = $obj_rs['user_openid'];
		$nonce_str = TIME . rand(0,99999);
		$arr_config = array(
			"appid" => cls_weixin::get_perms("appid"),
			"mch_id" => cls_weixin::get_perms("mch_id"),
			"nonce_str" => $nonce_str,
			"body" => $arr_fields["subject"],
			"out_trade_no" => $arr_fields["orderid"],
			"total_fee" => $arr_fields["price"]*100,
			"spbill_create_ip" => fun_get::ip(),
			"notify_url" => cls_config::get("url" , "base")."/wxpay_notify.php",
			"trade_type" => "JSAPI",
			"openid" => $openid,
		);
		ksort($arr_config);
		$arr_config['sign'] = cls_weixin::sign_pay($arr_config);
		$xml = '<xml>';
		foreach($arr_config as $item=>$key) {
			$xml .= "<" . $item . ">" . $key . "</" . $item . ">";
		}
		$xml .= '</xml>';
		$header = array();
		$header[] = "Content-type: text/xml";//定义content-type为xml
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, "https://api.mch.weixin.qq.com/pay/unifiedorder");//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//POST数据
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);//接收返回信息
		if(curl_errno($ch)){//出错则显示错误信息
			 return array('code' => 500 , 'msg' => curl_error($ch));
		}
		curl_close($ch); //关闭curl链接
		$response = str_replace('<![CDATA[','',$response);
		$response = str_replace(']]>','',$response);
		$arr_xml = fun_format::xml_array($response);
		if(!isset($arr_xml['prepay_id'])) {
			return array('code' => 500 , 'msg' => '微信支付接口错误');
		} else {
			$arr = array(
				"appId" => cls_weixin::get_perms("appid"),
				"timeStamp" => TIME,
				"nonceStr" => TIME . rand(1000,99999),
				"package" => "prepay_id=" . $arr_xml['prepay_id'],
				"signType" => 'MD5',
			);
			$arr['paySign'] = cls_weixin::sign_pay($arr);
			$arr['code'] = 0;
			$t1 = serialize($arr);
			$html = fun_format::json($arr);
			return array('code' => 0 , 'html' => $html);
		}
	}
	function on_notify() {
		$response = file_get_contents("php://input");

		$response = str_replace('<![CDATA[','',$response);
		$response = str_replace(']]>','',$response);
		$arr_xml = fun_format::xml_array($response);
		if(isset($arr_xml['out_trade_no'])) {
			$sign = $arr_xml['sign'];
			unset($arr_xml['sign']);
			$mesign = cls_weixin::sign_pay($arr_xml);
			if($sign != $mesign) {
				$arr_xml = array();
			}
		}
		$xml_success = '<xml>';
		$xml_success .= '<return_code><![CDATA[SUCCESS]]></return_code>';
		$xml_success .= '<return_msg><![CDATA[OK]]></return_msg>';
		$xml_success .= '</xml>';
		$xml_fail = '<xml>';
		$xml_fail .= '<return_code><![CDATA[FAIL]]></return_code>';
		$xml_fail .= '<return_msg><![CDATA[FAIL]]></return_msg>';
		$xml_fail .= '</xml>';
		if(isset($arr_xml['out_trade_no'])) {
			$arr_return = array(
				'code' => 0,
				'number' => $arr_xml['out_trade_no'],	//获取订单号
				'tradeid' => $arr_xml['transaction_id'],		//获取支付宝交易号
				'val' => intval($arr_xml['total_fee'])/100,		//获取总价格
				'msg_ok' => $xml_success,
				'msg_err' => $xml_fail
			);
		} else {
			$arr_return = array(
				'code' => 500,
				'number' => '',	//获取订单号
				'tradeid' => '',		//获取支付宝交易号
				'val' => 0,		//获取总价格
				'msg_ok' => $xml_success,
				'msg_err' => $xml_fail
			);
		}
		return $arr_return;
	}
	function on_refund($oid,$total,$reid='',$retotal = 0) {
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header('Content-Type:text/html;charset=utf-8');//当非附件或非图片时输出
		$nonce_str = TIME . rand(0,99999);
		if(empty($retotal)) $retotal = $total;
		if(empty($reid)) $reid = $oid;
		$arr_config = array(
			"appid" => cls_weixin::get_perms("appid"),
			"mch_id" => cls_weixin::get_perms("mch_id"),
			"nonce_str" => $nonce_str,
			"out_trade_no" => $oid,
			"out_refund_no" => $reid,
			"total_fee" => $total*100,
			"refund_fee" => $retotal*100,
			"op_user_id" => cls_weixin::get_perms("mch_id"),
		);
		ksort($arr_config);
		$arr_config['sign'] = cls_weixin::sign_pay($arr_config);
		$xml = '<xml>';
		foreach($arr_config as $item=>$key) {
			$xml .= "<" . $item . ">" . $key . "</" . $item . ">";
		}
		$xml .= '</xml>';
		$header = array();
		$header[] = "Content-type: text/xml";//定义content-type为xml
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, "https://api.mch.weixin.qq.com/secapi/pay/refund");//设置链接
		curl_setopt($ch,CURLOPT_TIMEOUT,30);//超时时间
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).'/apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).'/apiclient_key.pem');
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//POST数据
		$response = curl_exec($ch);//接收返回信息
		if(curl_errno($ch)){//出错则显示错误信息
			 return array('code' => 500 , 'msg' => curl_error($ch));
		}
		curl_close($ch); //关闭curl链接
		$response = str_replace('<![CDATA[','',$response);
		$response = str_replace(']]>','',$response);
		$arr_xml = fun_format::xml_array($response);
		if(!isset($arr_xml['return_code'])) {
			return array('code' => 500 , 'msg' => '微信退款接口错误');
		} else if($arr_xml['return_code']=="SUCCESS") {
			if($arr_xml["result_code"]=="SUCCESS") {
				$arr = array(
					"code" => 0,
					"oid" => $arr_xml['out_trade_no'],
					"reid" => $arr_xml['out_refund_no'],
					"refund_id" => $arr_xml['refund_id'],
					"retotal" => $arr_xml['refund_fee']/100,
					"total" => $arr_xml['total_fee']/100,
				);
				return $arr;
			} else {
				return array("code" => 500 , "msg" => $arr_xml["err_code_des"] . "(code:" . $arr_xml["err_code"] . ")");
			}
		} else {
			return array("code" => 500 , "msg" => $arr_xml['return_msg']);
		}
	}
	function get_refund_state($return_id) {
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header('Content-Type:text/html;charset=utf-8');//当非附件或非图片时输出
		$nonce_str = TIME . rand(0,99999);
		if(empty($retotal)) $retotal = $total;
		if(empty($reid)) $reid = $oid;
		$arr_config = array(
			"appid" => cls_weixin::get_perms("appid"),
			"mch_id" => cls_weixin::get_perms("mch_id"),
			"nonce_str" => $nonce_str,
			"refund_id" => $return_id,
		);
		ksort($arr_config);
		$arr_config['sign'] = cls_weixin::sign_pay($arr_config);
		$xml = '<xml>';
		foreach($arr_config as $item=>$key) {
			$xml .= "<" . $item . ">" . $key . "</" . $item . ">";
		}
		$xml .= '</xml>';
		$header = array();
		$header[] = "Content-type: text/xml";//定义content-type为xml
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, "https://api.mch.weixin.qq.com/pay/refundquery");//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//POST数据
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);//接收返回信息
		if(curl_errno($ch)){//出错则显示错误信息
			 return array('code' => 500 , 'msg' => curl_error($ch));
		}
		curl_close($ch); //关闭curl链接
		$response = str_replace('<![CDATA[','',$response);
		$response = str_replace(']]>','',$response);
		$arr_xml = fun_format::xml_array($response);
		if(!isset($arr_xml['return_code'])) {
			return array('code' => 500 , 'msg' => '微信退款查询接口错误');
		} else if($arr_xml['return_code']=="SUCCESS") {
			if($arr_xml["result_code"]=="SUCCESS" && isset($arr_xml['refund_status_0'])) {
				$arr_err = array("FAIL"=>"退款失败","NOTSURE"=>"未确定，需要商户原退款单号重新发起","CHANGE"=>"转入代发，退款到银行发现用户的卡作废或者冻结了，导致原路退款银行卡失败，资金回流到商户的现金帐号，需要商户人工干预，通过线下或者财付通转账的方式进行退款。");
				if($arr_xml['refund_status_0']=='SUCCESS') {
					$arr = array(
						"code" => 0,
						"number" => $arr_xml['out_trade_no'],
						"tradeid" => $arr_xml['transaction_id'],
						"retotal" => $arr_xml['total_fee']/100,
					);
					return $arr;
				} else if(isset($arr_err[$arr_xml['refund_status_0']])){//PROCESSING  表示处理中
					return array("code" => 501 , "msg" => $arr_err[$arr_xml['refund_status_0']]);
				} else {
					return array("code" => 502 , "msg" => $arr_xml["err_code_des"] . "(code:" . $arr_xml["err_code"] . ")");
				}
			} else {
				return array("code" => 500 , "msg" => $arr_xml["err_code_des"] . "(code:" . $arr_xml["err_code"] . ")");
			}
		} else {
			return array("code" => 500 , "msg" => $arr_xml['return_msg']);
		}		
	}
	function sendredpack($arr_fields = array()) {
		$nonce_str = TIME . rand(0,99999);
		if(!isset($arr_fields["shopname"]) || empty($arr_fields['shopname'])) $arr_fields['shopname'] = cls_config::get("site_name" , "sys");
		$arr_config = array(
			"nonce_str" => $nonce_str,
			"mch_billno" => $arr_fields['number'],
			"mch_id" => cls_weixin::get_perms("mch_id"),
			"wxappid" => cls_weixin::get_perms("appid"),
			"send_name" => $arr_fields['shopname'],//商户名称
			"re_openid" => $arr_fields['openid'],
			"total_amount" => $arr_fields['money']*100,
			"total_num" => 1,
			"wishing" => $arr_fields['greet'],//红包祝福语
			"client_ip" => $_SERVER['SERVER_ADDR'],
			"act_name" => $arr_fields['title'],//活动名称
			"remark" => $arr_fields['beta'],
		);
		ksort($arr_config);
		$arr_config['sign'] = cls_weixin::sign_pay($arr_config);
		$xml = '<xml>';
		foreach($arr_config as $item=>$key) {
			$xml .= "<" . $item . ">" . $key . "</" . $item . ">";
		}
		$xml .= '</xml>';
		$header = array();
		$header[] = "Content-type: text/xml";//定义content-type为xml
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack");//设置链接
		curl_setopt($ch,CURLOPT_TIMEOUT,30);//超时时间
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).'/apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).'/apiclient_key.pem');
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//POST数据
		$response = curl_exec($ch);//接收返回信息
		if(curl_errno($ch)){//出错则显示错误信息
			 return array('code' => 500 , 'msg' => curl_error($ch));
		}
		curl_close($ch); //关闭curl链接
		$response = str_replace('<![CDATA[','',$response);
		$response = str_replace(']]>','',$response);
		$arr_xml = fun_format::xml_array($response);
		if(!is_array($arr_xml) || !isset($arr_xml['return_code'])) {
			cls_error::on_save("redpack" , $response , $arr_config);
			return array('code' => 500 , "msg" => "系统繁忙");
		}
		if($arr_xml['return_code'] != 'SUCCESS') {
			cls_error::on_save("redpack" , $response , $arr_config);
			return array('code' => 500 , "msg" => $arr_xml['return_msg']);
		}
		if(!isset($arr_xml['result_code'])) {
			cls_error::on_save("redpack" , $response , $arr_config);
			return array('code' => 500 , "msg" => "系统繁忙");
		}
		if($arr_xml['result_code']!='SUCCESS') {
			cls_error::on_save("redpack" , $response , $arr_config);
			return array('code' => 500 , "msg" => $arr_xml['err_code_des']);
		}
		return array('code' => 0 , "wx_oid" => $arr_xml['send_listid'] , "number" => $arr_xml['mch_billno']);
	}

}