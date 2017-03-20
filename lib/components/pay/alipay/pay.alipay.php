<?php
class pay_alipay {
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
		$arr_config = self::get_config();
		$arr_config["subject"] = $arr_fields["subject"];
		$arr_config["orderid"] = $arr_fields["orderid"];
		$arr_config["price"] = $arr_fields["price"];
		//计算手续费
		if($arr_config["feeval"] > 0) {
			if($arr_config["feetype"] == 1) {
				$arr_config["price"] += $arr_config["feeval"];
			} else {
				$arr_config["price"] += $arr_config["feeval"] * $arr_config["price"]/100;
			}
		}

		if(!isset($arr_config["partner"]) || empty($arr_config["partner"]) ) return array('code' => 500 , 'msg' => '合作伙伴ID不能为空！');

		if(!isset($arr_config["key"]) || empty($arr_config["key"]) ) return array('code' => 500 , 'msg' => '安全检验码不能为空！');

		if(!isset($arr_config["email"]) || empty($arr_config["email"]) )  return array('code' => 500 , 'msg' => '支付宝帐户不能为空！');

		if(!isset($arr_config["subject"]) || empty($arr_config["subject"]))  return array('code' => 500 , 'msg' => '支付标题不能为空！');

		if(!isset($arr_config["orderid"]) || empty($arr_config["orderid"]) )  return array('code' => 500 , 'msg' => '订单id不能为空！');

		if(!isset($arr_config["price"])) return array('code' => 500 , 'msg' => '商品金额不能为空！');

		$arr_config["body"] = (isset($arr_fields["detail"])) ? $arr_fields['detail'] : "";//订单描述
		$arr_config["show_url"] = (isset($arr_fields["show_url"])) ? $arr_fields['show_url'] : "";//商品展示地址
		$arr_config["receive_name"] = (isset($arr_fields["receive_name"])) ? $arr_fields['receive_name'] : "";//收货人姓名
		$arr_config["receive_address"] = (isset($arr_fields["receive_address"])) ? $arr_fields['receive_address'] : "";//收货人地址
		$arr_config["receive_zip"] = (isset($arr_fields["receive_zip"])) ? $arr_fields['receive_zip'] : "";//收货人邮编
		$arr_config["receive_phone"] = (isset($arr_fields["receive_phone"])) ? $arr_fields['receive_phone'] : "";//收货人电话
		$arr_config["receive_mobile"] = (isset($arr_fields["receive_mobile"])) ? $arr_fields['receive_phone'] : "";//收货人手机

		$file_path = dirname(__FILE__) . "/" . $arr_config["interfacetype"] . "/pay.alipay." .  $arr_config["interfacetype"] . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_alipay_" . $arr_config["interfacetype"];
		$obj_pay = new $class_name;
		$arr_return = $obj_pay->on_pay($arr_config);
		return $arr_return;
	}
	function on_return() {
		$arr_config = self::get_config();
		$file_path = dirname(__FILE__) . "/" . $arr_config["interfacetype"] . "/pay.alipay." .  $arr_config["interfacetype"] . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_alipay_" . $arr_config["interfacetype"];
		$obj_pay = new $class_name;
		$arr_return = $obj_pay->on_return($arr_config);
		return $arr_return;
	}
	function on_notify() {
		$arr_config = self::get_config();
		$file_path = dirname(__FILE__) . "/" . $arr_config["interfacetype"] . "/pay.alipay." .  $arr_config["interfacetype"] . ".php";
		if(!file_exists($file_path))  return array('code' => 500 , 'msg' => '支付接口不存在！');
		require_once( $file_path );
		$class_name = "pay_alipay_" . $arr_config["interfacetype"];
		$obj_pay = new $class_name;
		$arr_return = $obj_pay->on_notify($arr_config);
		return $arr_return;
	}
}