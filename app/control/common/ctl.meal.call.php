<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_meal_call extends mod_meal_call {

	//来单显示页
	function act_default() {
		$this->agree_print = tab_sys_user_config::get_var("call.agree.print"  , $this->app_dir);
		$this->hide_handle = tab_sys_user_config::get_var("call.hide.handle"  , $this->app_dir);
		$this->hide_detail = tab_sys_user_config::get_var("call.hide.detail"  , $this->app_dir);
		$this->hide_isremote = tab_sys_user_config::get_var("call.hide.isremote"  , $this->app_dir);
		$url = cls_config::get("url" , "print" , "" , "");
		$this->isremote = (empty($url)) ? 0 : 1;
		//非管理员
		$shop_id = 0;
		$area_id = 0;
		if(!cls_obj::get("cls_user")->is_admin() ) {
			$this->shopinfo = $this->get_loginshop();
			$shop_id = $this->shopinfo['shop_id'];
			$this->area_val = '0';
			$this->area_list = array('list' => '{}','area'=>'{}','depth' => 0,'default' => array());
		} else {
			$area_info = $this->get_area_info();
			$this->area_val = $area_info['info'];
			$this->area_list = $area_info['list'];
			$area_id = $area_info['id'];
		}
		$this->arr_list = $this->get_new_order(0 , $this->hide_handle , $area_id , $shop_id);
		return $this->get_view();
	}
	//来单显示页
	function act_reserve() {
		$this->agree_print = tab_sys_user_config::get_var("call.agree.print"  , $this->app_dir);
		$this->hide_handle = tab_sys_user_config::get_var("call.hide.handle"  , $this->app_dir);
		$this->hide_detail = tab_sys_user_config::get_var("call.hide.detail"  , $this->app_dir);
		$this->hide_isremote = tab_sys_user_config::get_var("call.hide.isremote"  , $this->app_dir);
		$url = cls_config::get("url" , "print" , "" , "");
		$this->isremote = (empty($url)) ? 0 : 1;
		$shop_id = (int)fun_get::get("shop_id");
		$this->arr_list = $this->get_new_reserve(0 , $this->hide_handle , $shop_id);
		return $this->get_view();
	}
	//自动刷新来单显示页
	function act_reserve_refresh() {
		//取新单列表
		$id = (int)fun_get::get("endid");
		//非管理员
		$shop_id = 0;

		$hide_handle = tab_sys_user_config::get_var("call.hide.handle"  , $this->app_dir);
		$arr_return = $this->get_new_order($id , $hide_handle , $shop_id);
		$arr_return['orderstate'] = $this->get_order_state();
		return fun_format::json($arr_return);
	}
	function act_reserve_cancel() {
		//订单列表
		$id = (int)fun_get::get("id");
		$arr = $this->on_reserve_cancel($id);
		return fun_format::json($arr);
	}
	function act_upmenu() {
		$arr = $this->on_upmenu();
		return fun_format::json($arr);
	}
	function act_reserve_pay() {
		$arr = $this->on_reserve_pay();
		return fun_format::json($arr);
	}

	function get_area_info() {
			$arr_limit_area = array();
			$group_id = cls_obj::get("cls_user")->group_id;
			$arr_group = cls_obj::db()->get_one("select group_limit,group_limit_article,group_limit_area from " . cls_config::DB_PRE . "sys_user_group where group_id='".$group_id . "'");
			if(!empty($arr_group) && !empty($arr_group["group_limit"])) {
				$arr = explode("," , $arr_group["group_limit_area"]);
				foreach($arr as $item) {
					if(!empty($item)) $arr_limit_area[] = $item;
				}
			}
			$arr_return['info'] = tab_sys_user_config::get_var("call.area"  , $this->app_dir);
			$arr_return['list'] = fun_kj::get_area($arr_limit_area);

			$arr_list = fun_format::toarray($arr_return['list']['list']);
			$arr = explode(",",$arr_return['info']);
			$area_id = (int)$arr[count($arr)-1];
			$is_in = false;
			foreach($arr as $item) {
				if(in_array($item , $arr_limit_area)) {
					$is_in = true;break;
				}
			}

			if(!empty($arr_limit_area) && $is_in == false) {
				$area_id = $this->get_area_selid($arr_list , $area_id , $arr_limit_area);
			} else {
				$area_id = (empty($area_id)) ? array() : array($area_id);
			}
			$arr_return['id'] = $area_id;
			return $arr_return;
	}
	function get_area_selid($arr_list , $area_id , $arr_limit_area) {
		if(!isset($arr_list['id_' . $area_id])) return array();
		$arr_childs = $arr_list['id_' . $area_id];
		$arr_selid = array();
		foreach($arr_childs as $item) {
			$arrx = array();;
			if(!in_array($item , $arr_limit_area)) {
				$arrx = $this->get_area_selid($arr_list , $item , $arr_limit_area);
				if(!empty($arrx)) $arr_selid = array_merge($arr_selid , $arrx);
			} else {
				$arr_selid[] = $item;
			}
		}
		return $arr_selid;
	}
	function act_excel() {
		$this->on_excel();
	}
	//自动刷新来单显示页
	function act_refresh() {
		//取新单列表
		$id = (int)fun_get::get("endid");
		//非管理员
		$shop_id = 0;
		$area_id = 0;
		if(!cls_obj::get("cls_user")->is_admin()) {
			$this->shopinfo = $this->get_loginshop();
			$shop_id = $this->shopinfo['shop_id'];
		} else {
			//处理短信回复信息
			$this->on_sms_return();
			$area_info = $this->get_area_info();
			$area_id = $area_info['id'];
		}

		$hide_handle = tab_sys_user_config::get_var("call.hide.handle"  , $this->app_dir);
		$arr_return = $this->get_new_order($id , $hide_handle , $area_id , $shop_id);
		$arr_return['orderstate'] = $this->get_order_state();
		return fun_format::json($arr_return);
	}
	//接受预定
	function act_accept() {
		$arr_return = $this->on_accept();
		return fun_format::json($arr_return);
	}
	//接受预定
	function act_invalid() {
		$arr_return = $this->on_invalid();
		return fun_format::json($arr_return);
	}
	//取消预定
	function act_cancel() {
		$arr_return = $this->on_cancel();
		return fun_format::json($arr_return);
	}
	//测试打印页
	function act_print_test() {
		$print_info = fun_get::filter(cls_config::get("printinfo" , "print"),true);
		$print_temp = fun_get::get("shop_printinfo");
		$shop_id = fun_get::get("shop_id");
		$shop_info = $this->get_shop_info($shop_id);
		if(empty($print_temp)) $print_temp = $shop_info["shop_printinfo"];
		$print_temp = fun_get::filter($print_temp,true);
		if(empty($print_temp)) $print_temp = $print_info;
		$print_source = array(
			"{餐厅名称}"=> $shop_info["shop_name"],
			"{订餐电话}" => $shop_info["shop_tel"],
			"{餐厅地址}" =>  $shop_info["shop_address"],
			"{订单id}"=> "000001" ,
			"{所在区域}" => "福田区车公庙" ,
			"{具体地址}"=>"某某街首多少号" ,
			"{公司}"=>"x公司" ,
			"{部门}" => "x部",
			"{客户称呼}"=>"张/先生" ,
			"{送餐地址}"=>"x大厦x楼x层" ,
			"{客户电话}"=>"固话：1111111转222 手机：18600000000",
			"{固话}"=>"1111111转222" ,
			"{手机}"=>"18600000000",
			"{指定时间信息}"=>"12:00之前",
			"{下单时间}"=> date("Y-m-d H:i:s"),
			"{打印时间}"=> date("Y-m-d H:i:s"),
			"{应收金额}"=>"38",
			"{积分抵扣}"=>"5",
			"{优惠活动}"=>"满30每份减1元",
			"{发票信息}"=>"100元发票两张",
			"{备注}"=>"备注信息",
			"{商品列表}"=>"<table class='orderlist'><tr><td>名称</td><td>数量</td><td>单价</td></tr><tr><td>套餐一</td><td>1</td><td>10</td></tr><tr><td>套餐二</td><td>1</td><td>13</td></tr><tr><td>套餐三</td><td>1</td><td>15</td></tr></table>",
			chr(10)=>"<br>",
			" "=>"&nbsp;"
		);
		foreach($print_source as $item=>$key) {
			$print_temp = str_replace($item,$key,$print_temp);
		}
		$this->print_cont = $print_temp;
		return $this->get_view("print");
	}
	//测试打印页
	function act_print() {
		$arr_paymethod = array("afterpayment"=>"货到付款","repayment"=>"预付款支付","onlinepayment"=>"在线付款");
		$print_info = fun_get::filter(cls_config::get("printinfo" , "print"),true);
		$this->width = cls_config::get("width" , "print" , 200);
		$coinsign = cls_config::get("coinsign","sys");
		$order_id = (int)fun_get::get("order_id");
		$pay_method = '';
		$obj_order = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_order where order_id='" . $order_id . "'");
		if($obj_order['order_pay_method'] == 'onlinepayment') {
			$obj_one = cls_obj::db()->get("select pay_method from " . cls_config::DB_PRE . "other_pay where pay_number='" . $obj_order["order_number"] . "' and pay_state>0");
			$arr_cfgpay = cls_config::get("weixinapp" , "pay" , "" , "");
			if(!empty($obj_one) && isset($arr_cfgpay[$obj_one['pay_method']])) {
				$pay_method = $arr_cfgpay[$obj_one['pay_method']]['name'];
			}
		}
		if(empty($pay_method)) {
			$pay_method = isset($arr_paymethod[$obj_order['order_pay_method']]) ? $arr_paymethod[$obj_order['order_pay_method']] : $obj_order['order_pay_method'];
		}
		$shop_id = $obj_order['order_shop_id'];
		$shop_info = $this->get_shop_info($shop_id);
		$print_temp = trim(fun_get::filter($shop_info["shop_printinfo"],true));
		if(empty($print_temp)) $print_temp = $print_info;
		$tel_mobile = $obj_order['order_mobile'];
		$tel = $obj_order['order_tel'];
		$lou_hao = $obj_order['order_louhao1'];// . "层";
		//if(!empty($obj_order['order_louhao2'])) $lou_hao .= $obj_order['order_louhao2'] . "室";
		//取列表
		$arr = explode("|" , $obj_order["order_ids"]);
		$arr_x = array();
		foreach($arr as $item) {
			if(!in_array($item , $arr_x)) {
				$arr_menu_id[$item] = array( 'id'=> explode("," , $item) , 'num' => 1);
				$arr_x[] = $item;
			} else {
				$arr_menu_id[$item]['num']++;
			}
		}
		$arr_menu = $arr_price = array();
		//取当时下单的定价
		if(!empty($obj_order["order_detail"])) {
			$arr_detail = unserialize($obj_order["order_detail"]);
			if(isset($arr_detail["menu_price"])) $arr_price = $arr_detail["menu_price"];
		}
		$arr_menu_ids = array_unique(explode("," , str_replace("|" , "," , $obj_order["order_ids"])));
		$str_ids = implode("," , $arr_menu_ids);
		$obj_result = cls_obj::db()->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_day[] = $obj_rs;
			$arr_menu["id_".$obj_rs["menu_id"]] = $obj_rs;
			if(!isset($arr_price["id_".$obj_rs["menu_id"]])) $arr_price["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
		}

		$arr_tr = $arr_tr = array();
		foreach($arr_menu_id as $item => $key) {
			$arr_name = array();
			$price = 0;
			foreach($key['id'] as $id) {
				$arr_name[] = $arr_menu["id_".$id]['menu_title'];
				$price += $arr_price["id_".$id];
			}
			$price = $key['num'] * $price;
			$arr_tr[] = '<tr><td>' . implode("+" , $arr_name) . '</td><td>' . $key['num'] . '</td><td>￥' . $price . '</td></tr>';
			$arr_tr2[] = $this->addkong(implode("+" , $arr_name),16) . $key['num'] . "份 " . $coinsign . $price;
		}
		$list = implode("" , $arr_tr);
		$list2 = implode(chr(10) , $arr_tr2);
		//积分抵扣信息
		$order_score_money = $order_ticket = '';
		if(!empty($obj_order['order_score_money'])) {
			$order_score_money = '积分抵扣' . $coinsign . $obj_order['order_score_money'];
		}
		if(!empty($obj_order['order_ticket'])) {
			$order_ticket = '(需提供面值' . $coinsign . $obj_order['order_ticket']."发票)";
		}
		$order_act = '';
		if(!empty($obj_order['order_act'])) {
			$order_act = implode("<br>" , unserialize($obj_order['order_act']));
		}
		$total_pay = cls_config::get("coinsign" , "sys") . (float)$obj_order['order_total_pay'];
		if($obj_order['order_pay_val']>0) $total_pay .= "(已付:". $coinsign . (float)$obj_order['order_pay_val'] . ")";
		$arr_price_detail = array();
		if($obj_order['order_favorable']>0) $arr_price_detail[] = "优惠金额：" . $coinsign . (float)$obj_order['order_favorable'];
		if($obj_order['order_addprice']>0) $arr_price_detail[] = "配送费：" . $coinsign . (float)$obj_order['order_addprice'];
		if($obj_order['order_repayment']>0) $arr_price_detail[] = "预付款：" . $coinsign . (float)$obj_order['order_repayment'];
		$price_detail = implode("<br>" , $arr_price_detail);
		$print_source = array(
			"{订餐电话}" => $shop_info["shop_tel"],
			"{餐厅地址}" =>  $shop_info["shop_address"],
			"{订单id}"=>  $obj_order["order_id"] ,
			"{订单号}"=> $obj_order['order_number'] ,
			"{所在区域}" => $obj_order["order_area"] ,
			"{具体地址}"=>  $lou_hao,
			"{公司}"=>$obj_order['order_company'] ,
			"{部门}" =>$obj_order['order_depart'] ,
			"{客户称呼}"=>$obj_order['order_name'],
			"{送餐地址}"=> $obj_order["order_area"] . " " . $lou_hao ,
			"{客户电话}"=>$tel_mobile,
			"{固话}"=>$tel ,
			"{手机}"=>$obj_order['order_mobile'],
			"{下单时间}"=> date("Y-m-d H:i" , $obj_order['order_addtime']),
			"{打印时间}"=> date("Y-m-d H:i"),
			"{应收金额}"=>$total_pay,
			"{积分抵扣}"=>$order_score_money,
			"{优惠活动}"=>$order_act,
			"{发票信息}"=>$order_ticket,
			"{备注}"=>$obj_order['order_beta'],
			"{价格明细}"=>$price_detail,
			"{支付方式}"=>$pay_method,
			chr(10)=>"<br>"
		);
		if(is_numeric($obj_order['order_arrive'])) {
			$print_source["{指定时间信息}"] = "指定时间：" . $obj_order['order_arrive'] . "之前";
		} else {
			$print_source["{指定时间信息}"] = $obj_order['order_arrive'];
		}
		foreach($print_source as $item=>$key) {
			$print_temp = str_replace($item,$key,$print_temp);
		}
		//更新订单打印状态
		if(empty($obj_order['order_isprint'])){
			cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_order set order_isprint=1 where order_id='" . $obj_order['order_id'] . "'");
		}
		if(isset($_GET['isremote'])) { //无线打印
			$print_temp = trim($print_temp);
			if(empty($print_temp)) return fun_format::json(array('code' => 500 , 'msg' => '没有为店铺配置打印模板' , 'id' => $order_id));
			//只有店主或管理员才能重复打印
			if(!empty($obj_order['order_isprint'])) {
				if(!cls_obj::get('cls_user')->is_admin() && cls_obj::get("cls_user")->shop_id != $obj_order['shop_id']) return fun_format::json(array('code' => 500 , 'msg' => '非管理员或店主不能重复打印' , 'id' => $order_id));
			}
			$print_temp = str_replace('{菜品列表}',$list2,$print_temp);
			$print_temp = str_replace('<br>' , chr(10) , $print_temp);
			$print_temp = strip_tags($print_temp)  . chr(10) . chr(10) . chr(10) . chr(10) . chr(10);
			$print_temp = str_replace('{餐厅名称}','<1B6101>' . $shop_info["shop_name"] . '<1B6100><1D2110>',$print_temp);

			$print_temp=$print_temp.'<0D0A><0D0A><0D0A><0D0A>';
			cls_error::on_save('temp',$print_temp);
			$arr = cls_obj::get('cls_com')->print("on_print" , array("oid"=>$obj_order['order_id'] , "cont" => $print_temp , "shop_dayinjisn" => $shop_info['shop_print_id'] , "shop_print_pages" => $shop_info["shop_print_pages"]) );
			return fun_format::json($arr);

		} else {
			$print_temp = str_replace('{餐厅名称}',$shop_info["shop_name"],$print_temp);
			$print_temp = str_replace('{菜品列表}',"<table class='orderlist'><tr><td>名称</td><td>数量</td><td>小计</td></tr>" . $list . "</table>",$print_temp);
			$this->print_cont = $print_temp;
			$this->print_cont = $print_temp;
			return $this->get_view("print");
		}
	}
	function addkong($val , $len) {
		$str_utf8_u="u";
		$str_chinacode="\x{4e00}-\x{9fa5}";
		$val2=preg_replace("/([".$str_chinacode."]+)/".$str_utf8_u."is", "", $val);
		$len2 = strlen(trim($val));
		$len3 = strlen(trim($val2));
		$len4 = ($len2-$len3)/3*2+$len3;
		$i = $len - $len4;
		if($i<1) return fun_format::len($val,$len);
		for($j=1;$j<$i;$j++) {
			$val .= " ";
		}
		return $val;
	}
}