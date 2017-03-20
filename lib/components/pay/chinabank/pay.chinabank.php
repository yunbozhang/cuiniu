<?php
class pay_chinabank
{
	function get_config() {
		$arr_paymethod = cls_config::get("chinabank" , "pay" , "" , "");
		$arr_config = array();
		$arr_config["mid"] = $arr_paymethod["fields"]["mid"];
		$arr_config["key"] = $arr_paymethod["fields"]["key"];
		$arr_config["return_url"] = cls_config::get("url" , "base")."/common.php?app=pay&app_act=return&paymethod=chinabank";
		$arr_config["notify_url"] = cls_config::get("url" , "base")."/common.php?app=pay&app_act=notify&paymethod=chinabank";
		$arr_config["currency"] = $arr_paymethod["currency"];
		return $arr_config;
	}
	function on_pay($arr_fields=array())
	{
		$arr_return=array("html" => "" ,"code" => 0 , "msg" => "");
		$arr_config = self::get_config();
		if(!isset($arr_fields["orderid"]) || empty($arr_fields["orderid"]) )  return array('code' => 500 , 'msg' => '定单id不能为空！');
		if(!isset($arr_fields["price"])) return array('code' => 500 , 'msg' => '商品金额不能为空！');
		$arr_config["orderid"] = $arr_fields["orderid"];
		$arr_config["price"] = $arr_fields["price"];

		$text = $arr_config['price'] . $arr_config['currency']  . $arr_config['orderid'] . $arr_config['mid'] . $arr_config['return_url'] . $arr_config['key'];        //md5加密拼凑串,注意顺序不能变
		$v_md5info = strtoupper(md5($text));                             //md5函数加密并转化成大写字母

		$html = '<html><header></header><body>';
		$html .= '<form method="post" name="E_FORM" action="https://pay3.chinabank.com.cn/PayGate">';
		$html .= '<input type="hidden" name="v_mid" value="' . $arr_config['mid'] . '">';
		$html .= '<input type="hidden" name="v_oid" value="' . $arr_config['orderid'] . '">';
		$html .= '<input type="hidden" name="v_amount" value="' . $arr_config['price'] . '">';
		$html .= '<input type="hidden" name="v_moneytype" value="' . $arr_config['currency'] . '">';
		$html .= '<input type="hidden" name="v_url" value="' . $arr_config['return_url'] . '">';
		$html .= '<input type="hidden" name="v_md5info" value="' . $v_md5info . '">';
		$html .= '</form>';
		$html .= '<script>document.E_FORM.submit();</script></body></html>';
		$arr_return["html"] = $html;
		return $arr_return;
	}
	function on_return() {
		$arr_config = self::get_config();

		$v_oid     = isset($_POST['v_oid']) ? trim($_POST['v_oid']) : "";       // 商户发送的v_oid定单编号   
		$v_pmode   = isset($_POST['v_pmode']) ? trim($_POST['v_pmode']) : "";    // 支付方式（字符串）   
		$v_pstatus = isset($_POST['v_pstatus']) ? trim($_POST['v_pstatus']) : "";   //  支付状态 ：20（支付成功）；30（支付失败）
		$v_pstring = isset($_POST['v_pstring']) ? trim($_POST['v_pstring']) : "";   // 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）； 
		$v_amount  = isset($_POST['v_amount']) ? trim($_POST['v_amount']) : "";     // 订单实际支付金额
		$v_moneytype  = isset($_POST['v_moneytype']) ? trim($_POST['v_moneytype']) : ""; //订单实际支付币种    
		$remark1   = isset($_POST['remark1']) ? trim($_POST['remark1' ]) : "";      //备注字段1
		$remark2   = isset($_POST['remark2']) ? trim($_POST['remark2' ]) : "";     //备注字段2
		$v_md5str  = isset($_POST['v_md5str']) ? trim($_POST['v_md5str' ]) : "";   //拼凑后的MD5校验值  

		$md5string=strtoupper(md5($v_oid . $v_pstatus . $v_amount . $v_moneytype . $arr_config['key']));
		if ($v_md5str == $md5string) {//验证成功
			if($v_pstatus=="20") {
				$arr_return = array(
					'code' => 0,
					'number' => fun_get::get('v_oid'),	//获取订单号
					'tradeid' => '',		//获取交易号
					'val' => (float)fun_get::get('v_amount')		//获取总价格
				);
				return $arr_return;
			} else {
				return array('code' => 500 , 'msg' => '支付失败');
			}
		} else {
			return array('code' => 500 , 'msg' => '验证失败');
		}

	}
	function on_notify() {
		$arr = self::on_return();
		$arr['msg_ok'] = 'ok';
		$arr['msg_err'] = 'error';
		return $arr;
	}

}