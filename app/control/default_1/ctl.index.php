<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_index extends mod_index{
	function act_default(){
		if( $this->area_lou_id > 0 ) {
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
			return $this->get_view();
		} else {
			return self::act_area();
		}
	}
	//地区列表样式一
	function act_area() {
		$default_id = $this->area_id;
		$id = (int)fun_get::get("id");
		if(empty($id)) $id = $default_id;
		$this->arr_list = $this->get_area_1($id);
		$this->area_path = $this->get_path($id);
		return $this->get_view('area'); //显示页面
	}

	//店铺页
	function act_shop(){
		$this->shop_list = $this->get_shop_list();
		$id = (int)fun_get::get("id");
		$this->shop_id = $id;
		$this->arr_menu = $this->get_menu_list($id);
		$this->shopinfo = $this->get_shopinfo($id);
		$this->cfg_opentime = tab_meal_shop::get_opentime($id);
		$act = 'shop.default';
		//活动公告
		$this->arr_activitie = $this->get_activitie($id);	
		if($this->shopinfo['shop_mode'] == 2 || $this->shopinfo['shop_mode'] == 3) $act = 'shop.optional';
		return $this->get_view($act);
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
		$this->money_score = $arr_action["basescore"];
		$this->score_money = intval($this->score_total * $score_money_scale);
		$this->score_money_scale = cls_config::get("score_money_scale" , "meal");
		$score_mode = cls_config::get("score_mode" , "meal");
		$act = 'cart.default';
		$arr = explode("," , $this->cart_list["shopids"]);
		if(count($this->cart_list['menu'])>0 && !empty($arr[0])) {
			if(count($arr)== 1) {
				$this->dispatch_min_price = $this->cart_list['shop']['id_'.$arr[0]]['info']['shop_dispatch_price'];
				$this->this_info = $this->get_infolist();
				$this->areainfo = $this->get_area($arr[0] , $this->cart_list['shop']['id_'.$arr[0]]['info']['shop_dispatch_mode'] , $this->area_id);
				if(empty($score_mode) || ($score_mode==2 && empty($this->cart_list['shop']['id_'.$arr[0]]['info']['shop_support_score']))) $this->score_money_scale = 0;
				$x = tab_sys_user_var::get("last.area.id" , cls_obj::get("cls_user")->uid);
				if(empty($x) && count($this->this_info['list'])>0) $x = $this->this_info['list'][0]['info_id'];
				$this->last_area_id = $x;
				$this->shop_submit = 1;//表示单店，可以结算
				//取付款方式
				$this->paymethod = cls_config::get("paymethod" , "meal");
				$this->arr_pay = cls_config::get("" , "pay" , array() , "");
				//取用户当前预付款
				$this->user_repayment = cls_obj::get("cls_user")->get_repayment();
				//取店铺营销活动
				$this->shop_act = $this->get_shop_act($arr[0] , $this->cart_list['shop']['id_'.$arr[0]]['price'] , $this->cart_list['shop']['id_'.$arr[0]]['num']);
				if($this->cart_list['shop']['id_'.$arr[0]]['info']['shop_mode'] == 2 || $this->cart_list['shop']['id_'.$arr[0]]['info']['shop_mode'] == 3) $act = 'cart.optional';
			} else {
				$this->shop_submit = 0;//表示多店，需要选择某店结算
				$this->shop_act = array();
			}
			$this->ticket_list =  cls_config::get("ticket_list","meal");
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
			}
		}
		$this->info = $arr;
		return $this->get_view(); //显示页面
	}
	//注册页
	function act_reg() {
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
		$this->list_area = fun_kj::get_area();
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
		$this->thisinfo = $this->get_article($id);
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
		return $this->get_view(); //显示页面
	}
	//文章列表
	function act_shop_view() {
		//当前文章信息
		$info = $this->get_article(fun_get::get("id"));
		$shop_id = (!empty($info))? $info["article_about_id"] : 0;
		$this->shopinfo = $this->get_shopinfo($shop_id);
		$this->cfg_opentime = tab_meal_shop::get_opentime($shop_id);
		$act = 'shop.default';
		//活动公告
		$this->arr_activitie = $this->get_activitie($shop_id);	
		$this->thisinfo = $info;
		$this->shop_id= $shop_id;
		return $this->get_view(); //显示页面
	}
}