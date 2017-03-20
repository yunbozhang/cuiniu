<?php
/* 短信接口 */
class com_print {
	static function on_print($arr_msg) {
		$oid = (isset($arr_msg['oid'])) ? $arr_msg['oid'] : '';
		$cont = (isset($arr_msg['cont'])) ? $arr_msg['cont'] : '';
		$shop_dayinjisn = (isset($arr_msg['shop_dayinjisn'])) ? $arr_msg['shop_dayinjisn'] : '';
		$reply_url = cls_config::get("url" , "base") . "/print.php";
		$cfg_print = cls_config::get('','print','','');
		if(!isset($cfg_print['url'])) return array('code' => 500 , 'msg' => '没有配置无线打印服务器' , 'id' => $oid);
		if($cfg_print['state'] != 1) return array('code' => 500 , 'msg' => '没有开启打印机功能' , 'id' => $oid);
		$dayinjisn = $cfg_print["dayinjisn"];
		if(!empty($shop_dayinjisn)) $dayinjisn = $shop_dayinjisn;
		if($shop_dayinjisn == '-1') return fun_format::json(array('code' => 500 , 'msg' => '店铺没有配置打印机id' , 'id' => $oid));
		if(!isset($dayinjisn)) return fun_format::json(array('code' => 500 , 'msg' => '没有无线打印机Id' , 'id' => $oid));
		$pages = (isset($arr_msg['shop_print_pages']) && !empty($arr_msg['shop_print_pages']) ) ? $arr_msg['shop_print_pages'] : $cfg_print["pages"];
		$arr_val = array(      
			'dingdanID'=>'dingdanID='.$oid, //订单号
			'dayinjisn'=>'dayinjisn='.$dayinjisn, //打印机ID号
			'dingdan'=>'dingdan='.$cont, //订单内容
			'pages'=>'pages='.$pages, //联数 
			'replyURL'=>'replyURL='.$reply_url); //回复确认URL    
		$post_data = implode('&',$arr_val);
		$arr = fun_base::post($cfg_print['url'] , $post_data);
		$arr['id'] = $oid;
		return $arr;
	}
}