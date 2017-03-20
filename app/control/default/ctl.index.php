<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_index extends mod_index{
	function act_default(){
		if($this->site['customview']==1) {//店铺自定义域名进来的
			fun_get::get("id" , $this->site['shop_id']);
			return $this->act_shop();
		} else {
			if( $this->area_id > 0 ) {
				$this->shop_list = $this->get_shop_list();
				if(!cls_obj::get("cls_user")->is_login()) {
					$this->verfifycode = cls_obj::get("cls_user")->is_verifycode();
				} else if(cls_obj::get("cls_user")->type=='shop') {
					//今日总订单
					$obj_rs = cls_obj::db()->get_one("select count(1) as 'num',sum(order_total_pay) as 'total' from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . cls_obj::get("cls_user")->shop_id . "' and order_addtime>'" . strtotime(date("Y-m-d")) . "'");
					$this->today_ordernum = $obj_rs["num"];
					$this->today_ordermoney = (empty($obj_rs["total"])) ? 0 : $obj_rs["total"];
				} else {
					$this->loginuser_level = cls_obj::get("cls_user")->get_level();
					$this->loginuser_experience = cls_obj::get("cls_user")->get_experience();
				}
				$this->arr_type = cls_config::get("shop_type" , "meal");
				$this->collect_tj = $this->get_shop_collect_tj();
				$shopid = array_merge($this->collect_tj['shopid'] , $this->shop_list['shopid']);
				$this->act_info = $this->get_shop_act_info($shopid , true);
				return $this->get_view();
			} else {
				fun_base::url_jump("?app_act=area");
			}
		}
	}
	function act_shoplist() {
		if( $this->area_id > 0 ) {
			$this->shop_list = $this->get_shop_list();
			$this->arr_type = cls_config::get("shop_type" , "meal");
			return $this->get_view();
		} else {
			return $this->act_area();
		}
	}
	function act_shoplist_detail() {
		if( $this->area_id > 0 ) {
			$arr_list = $this->get_shop_list();
			for($i = 0 ; $i < count($arr_list['list']) ; $i++) {
				$arr_menu  = array();
				$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic,menu_pic_small,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_shop_id='" . $arr_list['list'][$i]['shop_id'] . "'");
				while($obj_rs = $obj_db->fetch_array($obj_result)) {
					if(empty($obj_rs['menu_pic_small'])) $obj_rs['menu_pic_small'] = $obj_rs['menu_pic'];
					if(empty($obj_rs['menu_pic'])) $obj_rs['menu_pic'] = $obj_rs['menu_pic_small'];
					$arr_menu[] = $obj_rs;
				}
				$arr_list['list'][$i]['menulist'] = $arr_menu;
			}
			$this->shop_list = $arr_list;
			$this->arr_type = cls_config::get("shop_type" , "meal");
			return $this->get_view();
		} else {
			return $this->act_area();
		}
	}
	function act_collect() {
		fun_get::get("iscollect",1);
		$this->shop_list = $this->get_shop_list();
		$this->arr_type = cls_config::get("shop_type" , "meal");
		//$this->collect_tj = $this->get_shop_collect_tj();
		$shopid = $this->shop_list['shopid'];//array_merge($this->collect_tj['shopid'] , $this->shop_list['shopid']);
		$this->act_info = $this->get_shop_act_info($shopid , true);
		return $this->get_view();
	}
	//附近餐饮
	function act_nearby_shop() {
		$this->shop_list = $this->get_nearby_shop();
		$shopid = isset($this->shop_list['shopid']) ? $this->shop_list['shopid'] : '';
		$this->act_info = $this->get_shop_act_info($shopid , true);
		$this->arr_type = cls_config::get("shop_type" , "meal");
		return $this->get_view('nearby.shop');
	}
	//最近光顾过的店铺
	function act_shophistory() {
		$this->shop_list = $this->get_shop_history(cls_obj::get("cls_user")->uid);
		$this->act_info = $this->get_shop_act_info($this->shop_list['shopid']);
		return $this->get_view(); //显示页面
	}
	//更多店铺列表
	function act_shopmore() {
		$page = (int)fun_get::get("page");
		$this->shop_list = $this->get_shop_list($page);
		$this->act_info = $this->get_shop_act_info($this->shop_list['shopid'] , true);
		return $this->get_view();
	}
	function act_shopbeta() {
		$id = (int)fun_get::get("id");
		$shopinfo = $this->get_shopinfo($id);
		$shopinfo['shop_dispatch_price'] = (float)$shopinfo['shop_dispatch_price'];
		$this->shopinfo = $shopinfo;
		return $this->get_view(); //显示页面
	}
	//地区列表样式一
	function act_area() {
		$default_id = $this->area_id;
		$this->arr_list = $this->get_area_1();
		$this->area_path = $this->get_path($default_id);
		return $this->get_view('area'); //显示页面
	}
	//子地区列表
	function act_area_next() {
		$pid = (int)fun_get::get("pid");
		if(empty($pid)) $pid = (int)cls_config::get("area_city_id","meal");
		$arr = $this->arr_list = $this->get_area_1($pid);
		return fun_format::json($arr);
	}
	//搜索
	function act_area_search() {
		$pid = (int)fun_get::get("pid");
		$skey = fun_get::get("skey");
		if(empty($pid)) $pid = (int)cls_config::get("area_city_id","meal");
		$arr = $this->arr_list = $this->get_area_2($pid , $skey);
		return fun_format::json($arr);
	}
	//店铺页
	function act_shop(){
		$id = (int)fun_get::get("id");
		$this->shop_id = $id;
		//首页默认分组
		$index_group = cls_config::get("index_group" , "view");
		$this->index_group = (empty($index_group)) ? "price" : $index_group;
		$sort = fun_get::get("sort");
		$this->shopinfo = $this->get_shopinfo($id);
		$this->arr_menu = $this->get_menu_list($id , $this->index_group , $sort , 0 , $this->shopinfo['shop_service']);
		$reserve_id = fun_get::get("reserve_id");
		if($this->arr_menu['type'] == 2 && empty($reserve_id)) {
			return $this->act_shop_table();
		} else if(!empty($reserve_id)) {
			$obj_table_reserve = cls_obj::db()->get_one("select a.*,b.order_id from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_order b on a.reserve_id=b.order_reserve_id where reserve_number='" . $reserve_id . "'");
			$obj_table_reserve['datetime'] = date("m-d H:i",strtotime($obj_table_reserve['reserve_datetime']));
			$this->table_reserve = $obj_table_reserve;
		}
		$this->cfg_opentime = tab_meal_shop::get_opentime($id);
		//活动公告
		$this->arr_activitie = $this->get_activitie($id);	
		//店铺活动
		$this->act_list = $this->get_shop_act_info($id);
		if(fun_get::get("tableid")!='') {
			$act = "shop.reserve";
		} else {
			$act = 'shop.default';
		}
		return $this->get_view($act);
	}
	//店铺页
	function act_shop_table(){
		$id = (int)fun_get::get("id");
		$this->shop_id = $id;
		$this->shopinfo = $this->get_shopinfo($id);
		$this->cfg_opentime = tab_meal_shop::get_opentime($id);
		//活动公告
		$this->arr_activitie = $this->get_activitie($id);	
		//店铺活动
		$this->act_list = $this->get_shop_act_info($id);
		$this->arr_group = $this->get_table_group_list($id);
		$this->arr_reserve_day = $this->get_reserve_day($this->shopinfo['extend']['weekday']);
		$time = $this->shopinfo['shop_reserve_time']*60;
		$datetime = date("Y-m-d H:i:s",TIME-$time);
		$mytable = cls_obj::db()->get_one("select a.*,b.order_id from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_order b on a.reserve_id=b.order_reserve_id where reserve_user_id='" . cls_obj::get("cls_user")->uid . "' and reserve_state>=0 order by reserve_id desc");
		$this->mytable = $mytable;
		$arr_arrive = array();
		if(isset($this->shopinfo['extend']["arr_arrivetime"])) {
			$arr_arrive = $this->shopinfo['extend']["arr_arrivetime"];
		}
		$arrivetime = $this->get_arrive_time($arr_arrive , $this->shopinfo['extend']['arrivedelay']);
		$date = date("Y-m-d");
		$time = date("H:i:s");
		foreach($arrivetime as $item=>$key) {
			$item = str_replace(":","",$item);
			if(strlen($item)>=4) {
				$date = date("Y-m-d");
				$time = substr($item,0,2) . ":" . substr($item,2,2) . ":00";
			} else if(strlen($item)==3) {
				$date = date("Y-m-d");
				$time = "0" . substr($item,0,1) . ":" . substr($item,1,2) . ":00";
			}
			break;
		}
		$this->reserve_date = $date;
		$this->reserve_time = $time;
		$this->arr_table_reserve = $this->get_table_reserve($id,$date . " " . $time);
		$this->arr_table = $this->get_table_list($id,0,true,$date . " " . $time);
		$this->arrivetime = $arrivetime;
		$this->arr_arrive = $arr_arrive;
		return $this->get_view('shop.table');
	}
	function act_shop_tablelist(){
		$id = (int)fun_get::get("id");
		$date = fun_get::get("date");
		$time = fun_get::get("time");
		if(empty($date) || fun_is::isdate($date)==false) $date = date('Y-m-d');
		$time = str_replace(":","",$time);
		if(strlen($time)!=4) $time = "0" . $time;
		if(strlen($time)!=4) $time = date("H:i");
		$time = substr($time,0,2) . ":" . substr($time , 2) . ":00";
		$datetime = $date . " " . $time;
		$arr_group = $this->get_table_group_list($id);
		$arr_table = $this->get_table_list($id,0,true,$datetime);
		$arr_return = array(
			"group" => $arr_group,
			"table" => $arr_table,
			"date" => $date,
			"time" => $time,
		);
		return fun_format::json($arr_return);
	}
	function act_shop_table_save(){
		$arr_return = $this->shop_table_save();
		return fun_format::json($arr_return);
	}
	//扫码
	function act_shop_table_qrcode(){
		$certify = cls_config::get("certify" , 'weixin' , '' ,'');
		if($certify == 1 && cls_obj::get("cls_user")->is_login() == false) {//未登录则自动跳转微信登录
			cls_obj::get("cls_session")->set("weixin",1);
			$url = cls_config::get("url");
			$jump_url = fun_get::url(array('app_weixin'=>''));
			$jump_url = urlencode(cls_config::get("domain") . $jump_url);
			fun_base::url_jump( $url . '/common.php?app=api.login&plat=weixin&jump_url=' . $jump_url);
			exit;
		}

		$id = (int)fun_get::get("id");
		$number = fun_get::get("table_number");
		$obj_table = cls_obj::db()->get_one("select table_id from " . cls_config::DB_PRE . "meal_table where table_number='" . $number . "'");
		if(empty($obj_table)) return $this->act_shop_table();
		$this->shop_id = $id;
		$this->shopinfo = $this->get_shopinfo($id,array('shop_reserve_sms'));
		$this->arr_group = $this->get_table_group_list($id);
		$time = $this->shopinfo['shop_reserve_time']*60;
		$datetime = date("Y-m-d H:i:s",TIME-$time);
		$mytable = cls_obj::db()->get_one("select a.*,b.order_id from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_order b on a.reserve_id=b.order_reserve_id where reserve_user_id='" . cls_obj::get("cls_user")->uid . "' and reserve_state>=0 and reserve_datetime>='" . $datetime . "' order by reserve_id desc");
		$this->mytable = $mytable;
		$date = date("Y-m-d");
		$time = date("H:i:s");
		$this->reserve_date = $date;
		$this->reserve_time = $time;
		$this->arr_table = $this->get_table_list($id,0,true,$date . " " . $time);
		$userinfo = cls_obj::db()->get_one("select reserve_name,reserve_tel from " . cls_config::DB_PRE . "meal_table_reserve where reserve_user_id='" . cls_obj::get("cls_user")->uid . "' and reserve_state>0 order by reserve_id desc");
		$orderinfo = cls_obj::db()->get_one("select order_name,order_mobile from " . cls_config::DB_PRE . "meal_order where order_user_id='" . cls_obj::get("cls_user")->uid . "' and order_state>0 order by order_id desc"); 
		if($this->shopinfo['shop_reserve_sms'] == 1 && empty($orderinfo) && empty($userinfo)) {
			$needsms = 1;
		} else if($this->shopinfo['shop_reserve_sms'] == 2 && empty($userinfo)) {
			$needsms = 1;
		} else if($this->shopinfo['shop_reserve_sms'] == 3) {
			$needsms = 1;
		} else {
			$needsms = 0;
		}
		$this->needsms = $needsms;
		return $this->get_view();
	}
	//验证加入桌号
	function act_shop_table_qrcode_pwd(){
		$id = fun_get::get("id");
		$pwd = fun_get::get("pwd");
		$obj_one = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_id='" . $id . "'");
		if(empty($obj_one)) return array("code" => 500 , "msg" => "预订不存在");
		if($obj_one['reserve_pwd'] != $pwd) return fun_format::json(array("code" => 500 , "msg" => "密码错误,验证失败"));
		$obj_order = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_reserve_id='" . $id . "'");
		if(empty($obj_order)) return fun_format::json(array("code" => 500 , "msg" => "预订不存在"));
		cls_obj::get("cls_session")->set("reserve_oid" , $obj_order['order_id']);
		return fun_format::json(array("code" => 0, "msg" => "" , "oid" => $obj_order['order_id'] , 'rid' => $id));
	}
	function act_shop_list() {
		$sortby = fun_get::get("sortby");
		if(empty($sortby)) $sortby = 'asc';
		$sort = fun_get::get("sort");
		if(!empty($sort)) $sort = "menu_" . $sort . " " . $sortby;
		$id = (int)fun_get::get("id");
		$index_group = cls_config::get("index_group" , "view");
		$this->index_group = (empty($index_group)) ? "price" : $index_group;
		$this->arr_menu = $this->get_menu_list($id , $this->index_group , $sort , 1);
		$this->shopinfo = $this->get_shopinfo($id);
		$act = 'shop.list';
		$this->cfg_opentime = tab_meal_shop::get_opentime($id);
		//if($this->shopinfo['shop_mode'] == 2 || $this->shopinfo['shop_mode'] == 3) $act = 'shop.optional.list';
		return $this->get_view($act);
	}
	//分组显示
	function act_grouplist() {
		$sortby = fun_get::get("sortby");
		if(empty($sortby)) $sortby = 'asc';
		$sort = fun_get::get("sort");
		if(!empty($sort)) $sort = "menu_" . $sort . " " . $sortby;
		$index_group = fun_get::get("index_group");
		if(empty($index_group)) $index_group = 'price';
		$shop_id = (int)fun_get::get('id');
		$this->index_group = $index_group;
		$this->arr_menu = $this->get_menu_list( $shop_id , $index_group , $sort , 1);
		$this->cfg_opentime = tab_meal_shop::get_opentime($shop_id);
		$this->shopinfo = $this->get_shopinfo($shop_id);
		$act = 'default';
		if($this->shopinfo['shop_mode'] == 2 || $this->shopinfo['shop_mode'] == 3) {
			$act = 'optional';
		}
		return $this->get_view("grouplist." . $act);
	}
	//排序显示
	function act_sortlist() {
		$sortby = fun_get::get("sort");
		$sortval = fun_get::get("sortval");
		if(empty($sortby)) $sortby = 'price';
		if(empty($sortval)) {
			$sortval = 'asc';
			if($sortby != 'price') $sortval = 'desc';
		}
		$sort = "menu_" . $sortby;
		$this->sortby = $sortby;
		$this->sortval = $sortval;
		$shop_id = (int)fun_get::get('id');
		$this->arr_menu = $this->get_menu_list($shop_id , '' , $sort . " " . $sortval);
		$this->cfg_opentime = tab_meal_shop::get_opentime($shop_id);
		$act = 'default';
		$this->shopinfo = $this->get_shopinfo($shop_id);
		if($this->shopinfo['shop_mode'] == 2 || $this->shopinfo['shop_mode'] == 3) {
			$act = 'optional';
		}
		return $this->get_view("sortlist." . $act);
	}
	/* 购物车页
	 * 分单店与多店，当多店时，则先列出所有店及相关所点的菜品，然后选某店以单店的形式结算
	 */
	function act_cart(){
		$this->cart_list = $this->get_cart_list();
		$this->score_total = cls_obj::get("cls_user")->get_score();
		$this->verfifycode = cls_obj::get("cls_user")->is_verifycode();
		//积分选项
		$score_money_scale = cls_config::get("score_money_scale" , "meal");
		$arr_action = cls_config::get("meal_submit_order_ok" , 'user.action' , '' , '');
		$this->action_score = fun_format::json(array('base'=>$arr_action['basescore'] , 'add' => $arr_action['addscore']));
		$this->score_money = intval($this->score_total * $score_money_scale);
		$this->score_money_scale = $score_money_scale;
		$score_mode = cls_config::get("score_mode" , "meal");
		$act = 'cart.default';
		$arr = explode("," , $this->cart_list["shopids"]);
		if(count($this->cart_list['menu'])>0 && !empty($arr[0])) {
			if(count($arr)== 1) {
				$this->dispatch_min_price = $this->cart_list['shop']['id_'.$arr[0]]['info']['shop_dispatch_price'];
				$this->dispatch_min_addprice = $this->cart_list['shop']['id_'.$arr[0]]['info']['shop_addprice'];//配送费
				$this->this_info = fun_kj::get_address($arr[0]);
				$this->areainfo = $this->get_area($arr[0]);
				if(empty($score_mode) || ($score_mode==2 && empty($this->cart_list['shop']['id_'.$arr[0]]['info']['shop_support_score']))) $this->score_money_scale = 0;
				$x = tab_sys_user_var::get("last.area.id" , cls_obj::get("cls_user")->uid);
				if(empty($x) && count($this->this_info)>0) $x = $this->this_info[0]['address_id'];
				$this->last_area_id = $x;
				$this->shop_submit = 1;//表示单店，可以结算
				//取付款方式
				$this->paymethod = cls_config::get("paymethod" , "meal");
				$this->arr_pay = cls_config::get("" , "pay" , array() , "");
				//取用户当前预付款
				$this->user_repayment = cls_obj::get("cls_user")->get_repayment();
				//取店铺营销活动
				$this->shop_act = $this->get_shop_cart_act($arr[0]);
				$this->cart_beta = cls_config::get("cart_beta","view");
			} else {
				$this->shop_submit = 0;//表示多店，需要选择某店结算
				$this->shop_act = array();
				$act = "cart.shop";
			}
			$this->ticket_list =  cls_config::get("ticket_list","meal");
			$this->arr_reserve_day = $this->get_reserve_day($this->cart_list['shop']['id_'.$arr[0]]['info']['shop_extend']['weekday']);
			return $this->get_view($act);
		} else {
			return $this->get_view("cart.null");
		}
	}

	//获取指定id收货信息
	function act_getinfo() {
		$id = (int)fun_get::get("id");
		$arr_info = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE  . "meal_info where info_user_id='" . cls_obj::get("cls_user")->uid . "' and info_id='" . $id . "'");
		return fun_format::json($arr_info);
	}
	//登录页
	function act_login() {
		$jump_url = fun_get::get("jump_url");    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$this->jump_fromurl = $jump_url;
		$this->verfifycode = cls_obj::get("cls_user")->is_verifycode();
		return $this->get_view(); //显示页面
	}
	//登录绑定页
	function act_login_bind() {
		$this->verfifycode = cls_obj::get("cls_user")->is_verifycode();
		return $this->get_view(); //显示页面
	}
	//找回密码
	function act_findpwd() {
		return $this->get_view(); //显示页面
	}
	//邮件回调找回密码
	function act_findpwd_email() {
		$key = fun_get::get("key");
		$val = fun_get::get("val");
		//是否为邮件认证
		$arr = array("code"=>500,'msg' => '传递参数有误' ,'uid' =>0);
		if(!empty($key)) {
			$arr = tab_sys_verify::on_verify($val , $key , 1);
			if($arr['code'] == 0) {
				$isverify = cls_obj::get("cls_session")->set('sms_verify' , $arr['uid']);//设置已验证标识
			} else {
				$arr['uid'] = 0;
			}
		}
		$this->info = $arr;
		return $this->get_view(); //显示页面
	}
	//注册页
	function act_reg() {
		if(cls_obj::get("cls_user")->is_login()) fun_base::url_jump('./');
		$jump_url = fun_get::get("jump_url");    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		if(stristr($jump_url,"app_act=reg") || stristr($jump_url,"app_act=login")) {
			$jump_url = '/';
		}
		$this->jump_fromurl = $jump_url;
		$this->reg_switch = cls_config::get("reg_switch" , "user");
		$this->reg_switch_info = cls_config::get("reg_switch_info" , "user");
		//取注册协议
		$this->reg_content = tab_article::get_bykey("regargreement");
		return $this->get_view(); //显示页面
	}


	//登录页
	function act_reg_shop() {
		$jump_url = fun_get::get("jump_url");    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$this->jump_fromurl = $jump_url;
		$this->arr_help = $this->get_folder_article('default');
		$this->reg_switch = cls_config::get("reg_switch" , "user");
		$this->reg_switch_info = cls_config::get("reg_switch_info" , "user");
		//取注册协议
		$this->reg_content = tab_article::get_bykey("shopregargreement");
		$this->arr_area = fun_kj::get_area();
		return $this->get_view(); //显示页面
	}
	//帮助
	function act_help() {
		$this->arr_help = $this->get_folder_article('default');
		//当前文章信息
		$id = (int)fun_get::get("id");
		if(empty($id)) $id = $this->arr_help[0]['article_id'];
		$this->id = $id;
		$thisinfo = $this->get_article($id);
		if($thisinfo['article_key']=="about.us" && $this->site['customview']==1) {
			$obj_rs = cls_obj::db()->get_one("select shop_intro from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $this->site['shop_id'] . "'");
			if(!empty($obj_rs)) $thisinfo['article_content'] = fun_get::filter($obj_rs['shop_intro'] , true);
		}
		$this->thisinfo = $thisinfo;
		return $this->get_view(); //显示页面
	}
	//留言
	function act_msg() {
		$this->arr_help = $this->get_folder_article('default');
		$this->verfifycode = cls_obj::get("cls_user")->is_verifycode();
		$this->options = cls_config::get("msg_options","sys" , array());
		return $this->get_view(); //显示页面
	}

	//文章列表
	function act_shop_news() {
		$channel_id = (int)fun_get::get("channel_id");
		$channel_key = fun_get::get("channel_key");
		$shop_id = (int)fun_get::get("shop_id");
		$this->shopinfo = $this->get_shopinfo($shop_id);
		$this->cfg_opentime = tab_meal_shop::get_opentime($shop_id);
		$act = 'shop.default';
		$channel_name = '';
		$where = (empty($channel_key))? " where channel_id='" . $channel_id . "'" : " where channel_key='" . $channel_key . "'";
		$obj_rs = cls_obj::db()->get_one("select channel_name,channel_id from " . cls_config::DB_PRE . "article_channel" . $where);
		if(!empty($obj_rs)) {
			$channel_name = $obj_rs['channel_name'];
			$channel_id = $obj_rs['channel_id'];
		}
		//活动公告
		$this->arr_activitie = $this->get_activitie($shop_id);	
		$this->arr_help = $this->get_folder_article('default');
		$this->arr_list = $this->get_article_list($channel_id , $shop_id);
		$this->channel_name = $channel_name;
		//店铺活动
		$this->act_list = $this->get_shop_act_info($shop_id);
		return $this->get_view(); //显示页面
	}
	//文章列表
	function act_shop_view() {
		//当前文章信息
		$info = $this->get_article(fun_get::get("id"));
		$shop_id = (!empty($info))? $info["article_about_id"] : 0;
		if(empty($shop_id)) {
			cls_error::on_exit("exit" , "访问文章不存在，或已被删除");
		}
		$this->shopinfo = $this->get_shopinfo($shop_id);
		$this->cfg_opentime = tab_meal_shop::get_opentime($shop_id);
		//活动公告
		$this->arr_activitie = $this->get_activitie($shop_id);	
		$this->thisinfo = $info;
		$this->shop_id= $shop_id;
		$this->act_list = $this->get_shop_act_info($shop_id);
		return $this->get_view(); //显示页面
	}
	//店铺信息
	function act_shop_info() {
		$shop_id = (int)fun_get::get("shop_id");
		$this->shop_info();
		//店铺活动
		$this->act_list = $this->get_shop_act_info($shop_id);
		return $this->get_view(); //显示页面
	}
	//文章列表
	function act_shop_detail() {
		$shop_id = (int)fun_get::get("shop_id");
		$this->shop_info();
		//店铺活动
		$this->act_list = $this->get_shop_act_info($shop_id);
		return $this->get_view(); //显示页面
	}
	function act_comment() {
		$id = (int)fun_get::get("menu_id");
		$obj_menu = cls_obj::db()->get_one("select menu_id,menu_title,menu_shop_id from " . cls_config::DB_PRE . "meal_menu where menu_id='" . $id . "'");
		if(empty($obj_menu)) cls_error::on_exit("exit" , "商品不存在");
		$this->menuinfo = $obj_menu;
		$this->menucomment = $this->get_menu_comment($id);
		return $this->get_view();
	}
	function act_comment_shop() {
		//评论总计
		$obj_db = cls_obj::db();
		$id = (int)fun_get::get("shop_id");
		$obj_shop = $obj_db->get_one("select shop_comment_list,shop_comment_val from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $id . "'");
		if(empty($obj_shop)) cls_error::on_exit("exit" , "店铺不存在");
		$arr = explode("||" , $obj_shop['shop_comment_list']);
		$total = 0;
		foreach($arr as $item) {
			$arrx = explode("=>" , $item);
			$val = isset($arrx[1]) ? $arrx[1] : 0;
			$obj_shop['commentlist'][$arrx[0]] = $val;
			$total += $val;
		}
		$obj_shop['commenttotal'] = (float)number_format($total/count($arr),1);
		$arr = explode("||" , $obj_shop['shop_comment_val']);
		$obj_shop['commentval'] = array();
		$total = 0;
		foreach($arr as $item) {
			$arrx = explode("=>" , $item);
			$val = isset($arrx[1]) ? $arrx[1] : 0;
			$obj_shop['commentval'][$arrx[0]] = $val;
			$total += $val;
		}
		$obj_shop['commenttotalren'] = $total;
		krsort($obj_shop['commentval']);
		$this->shopcomment = $obj_shop;
		$this->arr_list = $this->get_comment_shop_list($id);
		$this->shopinfo = $this->get_shopinfo($id);
		$this->shopid = $id;
		//活动公告
		$this->arr_activitie = $this->get_activitie($id);	
		//店铺活动
		$this->act_list = $this->get_shop_act_info($id);
		return $this->get_view();
	}
	function act_get_area() {
		$arr = array("selid" => $this->area_id);
		$arr['list'] = $this->get_area_3();
		return fun_format::json($arr);
	}
	function act_common_message() {
		return $this->get_view();
	}
	function act_menu() {
		//活动公告
		$this->arr_help = $this->get_folder_article('default');
		$menu_id = (int)fun_get::get("id");
		$obj_menu = cls_obj::db()->get_one("select menu_id,menu_type,menu_intro,menu_cont,menu_group_id,menu_title,menu_price,menu_comment_num,menu_sold,menu_tj,menu_pic,menu_pic_small,menu_num,menu_attribute,menu_mode,menu_holiday,menu_weekday,menu_mode,menu_weekday,menu_date,menu_sold_time,menu_sold_today,menu_shop_id,menu_pics from " . cls_config::DB_PRE . "meal_menu where menu_id='" . $menu_id . "'");
		if(!empty($obj_menu)) {
			if(empty($obj_menu['menu_pic'])) $obj_menu['menu_pic']=$obj_menu['menu_pic_small'];
			$obj_menu['menu_pic'] = fun_get::html_url($obj_menu['menu_pic']);
			$obj_menu['menu_cont'] = fun_get::filter($obj_menu['menu_cont'] , true);
			$obj_menu['pics'] = array();
			if(!empty($obj_menu["menu_pics"])) {
				$arr = explode("|" , $obj_menu['menu_pics']);
				foreach($arr as $pic) {
					$obj_menu['pics'][] = fun_get::html_url($pic);
				}
			}
			//当前数量
			if(empty($obj_menu['menu_num'])) {
				$obj_menu['num'] = 9999;
			} else {
				$obj_menu['num'] = ($obj_menu['menu_sold_time']> strtotime(date("Y-m-d")) ) ? $obj_menu['menu_num']-$obj_menu['menu_sold_today'] : $obj_menu['menu_num'];
			}

			$this->menu_info = $obj_menu;
		} else {
			cls_error::on_exit('exit','商品不存在');exit;
		}
		$this->shop_info($obj_menu['menu_shop_id']);
		$this->arr_menu = $this->get_menu_list($this->shop_id);
		$act = 'menu.default';
		//评论总计
		$obj_db = cls_obj::db();
		$id = (int)fun_get::get("menu_id");
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu_comment where comment_menu_id='" . $menu_id . "' and comment_val=1");
		$this->goodnum = (!empty($obj_rs)) ? $obj_rs['num'] : 0;
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu_comment where comment_menu_id='" . $menu_id . "' and comment_val=0");
		$this->generalnum = (!empty($obj_rs)) ? $obj_rs['num'] : 0;
		$obj_rs = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu_comment where comment_menu_id='" . $menu_id . "' and comment_val=-1");
		$this->failnum = (!empty($obj_rs)) ? $obj_rs['num'] : 0;
		$this->menucomment = $this->get_menu_comment($menu_id);
		$this->act_list = $this->get_shop_act_info($obj_menu['menu_shop_id']);
		$this->cfg_opentime = tab_meal_shop::get_opentime($obj_menu['menu_shop_id']);
		return $this->get_view($act);
	}
	//订单提交成功页
	function act_cart_ok() {
		//初始公共信息
		$this->memberinfo = $this->init_info();
		//积分明细记录
		$this->id = fun_get::get("id");
		$obj_order = cls_obj::db()->get_one("select order_id,order_pay_method,order_total_pay,order_total,order_addprice,order_favorable,order_state,order_number,order_addtime,order_state,order_shop_id,order_pay_val from " . cls_config::DB_PRE . "meal_order where order_id='" . $this->id . "'");
		
		if(empty($obj_order)) {
			cls_error::on_exit("exit" , '订单不存在');
		}
		$obj_order['order_total_pay'] = $obj_order['order_total'] + $obj_order['order_addprice'] - $obj_order['order_favorable'];
		$obj_order['paymethod'] = '';
		//计算如果是在线支付，多久没有支付将取消订单
		$lng_i = (int)cls_config::get("pay_timeout" , "meal");
		if(empty($lng_i)) $lng_i = 10;
		$lng_i = $lng_i * 60 + $obj_order['order_addtime'];
		$this->delay_time = date("H:i" , $lng_i);
		//是否过期
		$this->timeout = 0;
		if(TIME > $lng_i) {
			if($obj_order['order_state'] == -2) cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "meal_order set order_state=-3 where order_id='" . $this->id . "'");
			if($obj_order['order_state'] == -3 || $obj_order['order_state'] == -2) $this->timeout = 1;
		}
		//当前在线支付方式
		$this->arr_pay = cls_config::get("" , "pay" , array() , "");
		if(isset($this->arr_pay[$obj_order['order_pay_method']])) {
			$obj_order['paymethod'] = $this->arr_pay[$obj_order['order_pay_method']]['fields']['title'];
		} else if($obj_order['order_pay_method'] == 'afterpayment'){
			$obj_order['paymethod'] = "货到付款";
		} else if($obj_order['order_pay_method'] == 'paymented') {
			$obj_order['paymethod'] = "抵扣(已支付)";
		}
		if(!empty($obj_order)) {
			$obj_order['order_total_pay'] = (float)$obj_order['order_total_pay'];
		}
		$arr_state = tab_meal_order::get_perms("state");
		if($obj_order["order_state"]>0) {
			$obj_order["state"] = array_search($obj_order["order_state"] , $arr_state);
		} else {
			$obj_order["state"] = "<font color='#ff0000'>" . array_search($obj_order["order_state"] , $arr_state) . "</font>";
		}

		$this->obj_order = $obj_order;
		$this->order_cancel = (int)cls_config::get("order_cancel" , "meal");
		return $this->get_view(); //显示页面
	}
	function act_cart_pay() {
		$id = fun_get::get("id");
		$obj_order = cls_obj::db()->get_one("select order_id,order_pay_method,order_total_pay,order_total,order_addprice,order_favorable,order_state,order_number,order_addtime,order_state,order_shop_id,order_pay_val from " . cls_config::DB_PRE . "meal_order where order_id='" . $id . "'");
		if(empty($obj_order)) {
			cls_error::on_exit("exit" , '订单不存在');
		}
		$obj_order['order_total_pay'] = $obj_order['order_total'] + $obj_order['order_addprice'] - $obj_order['order_favorable'];
		$this->obj_order = $obj_order;
		return $this->get_view(); //显示页面
	}
	function act_wx_share() {
		$url = urldecode($_GET['url']);
		$arr = cls_weixin::sign_js($url);
		return fun_format::json($arr);
	}
	//取当前位置
	function act_get_location() {
		$longitude = fun_get::get("longitude");
		$latitude = fun_get::get("latitude");
		$location = array('longitude' => $longitude , 'latitude' => $latitude , 'area_name' => '' , 'area_id' => 0) ;
		$arr_range = cls_map::get_range($location['longitude'] , $location['latitude'] , 100);
		$obj_result = cls_obj::db()->select("select area_id,area_name from " . cls_config::DB_PRE . "sys_area where area_position_lat<=" . $arr_range['max_lat'] . " and area_position_lat>=" . $arr_range['min_lat'] . " and area_position_lng<=" . $arr_range['max_lng'] . " and area_position_lng>=" . $arr_range['min_lng']);
		$length = 0;
		$obj_area = array();
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$len = cls_map::get_distance($location['longitude'] , $location['latitude'], $obj_rs['area_position_lng'] , $obj_rs['area_position_lat'] , 2);
			$len = (float)$len;
			if($length == 0 || $len < $length) {
				$obj_area = $obj_rs;
				$length = $len;
			}
		}
		if(!empty($obj_area)) {
			$location['area_name'] = $obj_area['area_name'];
			$location['area_id'] = $obj_area['area_id'];
		}
		//cls_obj::get("cls_session")->set("location",$location);
		return fun_format::json($location);
	}
	function act_search() {
		$this->cache_words = fun_kj::get_cache_words('search','','',10);
		$arr_config = tab_sys_user_config::get_config("config_info");
		if(!isset($arr_config['search.words']) || !is_array($arr_config['search.words'])) $arr_config['search.words'] = array();
		$this->history_words = $arr_config['search.words'];
		return $this->get_view();
	}

}