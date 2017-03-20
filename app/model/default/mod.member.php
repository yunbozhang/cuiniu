<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_member extends inc_mod_default {
	function __construct($arr_v = array() ) {
		//是否登录
		if(!cls_obj::get("cls_user")->is_login()) {
			if(isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"]  , $_SERVER['REQUEST_URI'])) {
				fun_base::url_jump(cls_config::get('url' , 'base'));
			}
			$agent = fun_get::agent();
			$act = '';
			if(fun_is::wap()) {
				$jump_url = fun_get::get("jump_url");    //获取跳转地址
				echo cls_app::on_display(KJ_WAP_DIR , '' , "index" , "login" , array( "jump_fromurl"=>$jump_url ) );exit;
			} else {
				cls_error::on_error("no_login");
			}
		}
		parent::__construct($arr_v);
	}
	//初始化公共信息
	function init_info() {
		//$this->carts_num = $this->get_carts_num();
		$arr['experience'] = cls_obj::get("cls_user")->get_experience();
		$arr['experience_next'] = tab_sys_user::get_level_next( $arr['experience'] );
		$arr['experience_poor'] = $arr['experience_next'] - $arr['experience'];
		$arr['level'] = cls_obj::get("cls_user")->get_level();
		$arr['level_next'] = $arr['level'] + 1;
		$arr['process'] = $arr['experience'] / $arr['experience_next']*100;
		$arr['paymethod'] = cls_config::get("paymethod" , "meal");
		return $arr;
	}

	//获取当前登录用户信息
	function get_userinfo() {
		$obj_rs = cls_obj::db()->get_one("select user_email from " . cls_config::DB_PRE . "sys_user where user_id='" . cls_obj::get("cls_user")->uid . "'");
		return $obj_rs;
	}
	/*获取当前用户行为列表
	 * type: 0表示经验，1表示积分
	 */
	function get_action_list($type = 0) {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page",1);
		//取排序字段
		$str_where = " where action_user_id='" . cls_obj::get("cls_user")->uid . "'";
		if($type==0) {
			$str_key = ".member.myvip";
			$str_where .= " and action_experience!=0";
		} else {
			$str_key = ".member.myintegral";
			$str_where .= " and action_score!=0";
		}
		$arr_config_info = tab_sys_user_config::get_info($str_key  , $this->app_dir);
		$pagesize = $arr_config_info["pagesize"];

		$action_id = 0;
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."sys_user_action" , $str_where , $page , $pagesize);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "sys_user_action" . $str_where . " order by action_id desc" . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($action_id)) $action_id = $obj_rs["action_id"];
			if(empty($obj_rs["action_beta"])) {
				$arr = cls_config::get($obj_rs["action_key"] , 'user.action' , '' , '');
				$obj_rs["beta"] = (isset($arr["title"]))? $arr["title"] : "";
			} else {
				$obj_rs["beta"] = $obj_rs["action_beta"];
			}
			$obj_rs["action_addtime"] = date("Y-m-d H:i:s" , $obj_rs["action_addtime"]);
			$arr_return["list"][]= $obj_rs;
		}
		$obj_rs = $obj_db->get_one("select sum(action_score) as score,sum(action_experience) as experience from " . cls_config::DB_PRE . "sys_user_action where action_user_id='" . cls_obj::get("cls_user")->uid . "' and action_id<='" . $action_id . "'");
		if(!empty($obj_rs)) {
			$arr_return["score"] = $obj_rs["score"];
			$arr_return["experience"] = $obj_rs["experience"];
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'] , 5);

		return $arr_return;
	}
	/*获取当前用户预付款记录
	 * 
	 */
	function get_repayment_list() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page",1);
		//取排序字段
		$str_where = " where repayment_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$arr_config_info = tab_sys_user_config::get_info(".member.repayment"  , $this->app_dir);
		$pagesize = $arr_config_info["pagesize"];

		$repayment_id = 0;
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."sys_user_repayment" , $str_where , $page , $pagesize);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "sys_user_repayment" . $str_where . " order by repayment_id desc" . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($repayment_id)) $repayment_id = $obj_rs["repayment_id"];
			if(empty($obj_rs["repayment_beta"])) {
				$arr = tab_sys_user_repayment::$this_type;
				$obj_rs["beta"] = array_search($obj_rs["repayment_type"] , $arr);
			} else {
				$obj_rs["beta"] = $obj_rs["repayment_beta"];
			}
			$obj_rs["repayment_addtime"] = date("Y-m-d H:i:s" , $obj_rs["repayment_addtime"]);
			$arr_return["list"][]= $obj_rs;
		}
		$arr_return["repayment"] = cls_obj::get("cls_user")->get_repayment();
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'] , 5);

		return $arr_return;
	}
	/*获取当前用户预付款记录
	 * 
	 */
	function get_voucher_list() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page",1);
		//取排序字段
		$str_where = " where voucher_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$arr_config_info = tab_sys_user_config::get_info(".member.voucher"  , $this->app_dir);
		$pagesize = $arr_config_info["pagesize"];

		$repayment_id = 0;
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_voucher" , $str_where , $page , $pagesize);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "act_voucher" . $str_where . " order by voucher_usetime desc" . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs["voucher_usetime"] = date("Y-m-d H:i:s" , $obj_rs["voucher_usetime"]);
			$arr_return["list"][]= $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'] , 5);

		return $arr_return;
	}
	//获取当前用户订单列表
	function get_order_list() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page",1);
		$arr_day = $arr_menu_ids = array();
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info(".member.orderlist"  , $this->app_dir);
		$pagesize = $arr_config_info["pagesize"];
		$reserve = array("reserve" => array());
		$str_where = " where order_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$arr_return["pageinfo"] = $obj_db->get_pageinfo( "(select 1 from " . cls_config::DB_PRE."meal_order " . $str_where . " group by order_day) tab1" , '' , $page , $pagesize);
		$obj_result = $obj_db->select("select order_day from " . cls_config::DB_PRE . "meal_order" . $str_where . " group by order_day order by order_day desc" . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_day[] = $obj_rs;
		}
		$arr_shop_id = $arr_timeoutid = array();
		//计算如果是在线支付，多久没有支付将取消订单
		$lng_i = (int)cls_config::get("pay_timeout" , "meal");
		if(empty($lng_i)) $lng_i = 10;
		$arr_reserve_id = array();
		$lng_i = $lng_i * 60;
		if(count($arr_day) > 0 ) {
			$end_day = $arr_day[0]["order_day"];
			$start_day = $arr_day[count($arr_day)-1]["order_day"];
			$arr_menu_ids = array();
			$arr_state = tab_meal_order::get_perms('state');
			$arr_x = array();
			$obj_result = $obj_db->select("select a.*,b.shop_name,b.shop_tel,b.shop_pic from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id where order_user_id='" . cls_obj::get("cls_user")->uid . "' and order_day>='" . $start_day . "' and order_day<='" . $end_day . "' order by order_id desc");

			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if($obj_rs['order_state'] == -2) {
					$time = $lng_i + strtotime($obj_rs['order_time']);
					//是否过期
					if(TIME > $time) {
						 $arr_timeoutid[] = $obj_rs['order_id'];
						 $obj_rs['order_state'] = -3;
					}
				}
				$obj_rs["addtime"] = date("H:i",strtotime($obj_rs["order_time"]));
				$obj_rs["order_act"] = !empty($obj_rs["order_act"]) ? unserialize($obj_rs["order_act"]) : array();
				$arr = explode("|" , $obj_rs["order_ids"]);
				$obj_rs['menunum'] = array_count_values($arr);
				$arr = array_unique($arr);
				foreach($arr as $item) {
					$obj_rs["menu"][] = explode("," , $item);
				}
				//取当时下单的定价
				if(!empty($obj_rs["order_detail"])) {
					$arr_detail = unserialize($obj_rs["order_detail"]);
					if(isset($arr_detail["menu_price"])) $arr_return["price"] = $arr_detail["menu_price"];
				}
				$obj_rs['state'] = array_search($obj_rs['order_state'] , $arr_state);
				if(!empty($obj_rs["order_ids"])) $arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));
				$obj_rs['order_total'] = (float)$obj_rs['order_total'];
				$obj_rs['order_total_pay'] = (float)$obj_rs['order_total_pay'];
				$obj_rs['order_favorable'] = (float)$obj_rs['order_favorable'];
				$arr_return["list"][$obj_rs["order_day"]][] = $obj_rs;
				$arr_shop_id[] = $obj_rs["order_shop_id"];
				$arr_return['shop']['id_' . $obj_rs['order_shop_id']] = '';
				if(!empty($obj_rs['order_reserve_id'])) $arr_reserve_id[] = $obj_rs['order_reserve_id'];
			}
			$arr_menu_ids = array_unique($arr_menu_ids);
			$str_ids = implode("," , $arr_menu_ids);
			$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_day[] = $obj_rs;
				$arr_return["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
				if(!isset($arr_return["price"]["id_".$obj_rs["menu_id"]])) $arr_return["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
			}
			if(count($arr_shop_id)>0) {
				$str_ids = implode("," , $arr_shop_id);
				$obj_result = $obj_db->select("select shop_id,shop_name,shop_tel from " . cls_config::DB_PRE . "meal_shop where shop_id in (". $str_ids . ")");
				while($obj_rs = $obj_db->fetch_array($obj_result)) {
					$arr_return['shop']['id_' . $obj_rs['shop_id']] = $obj_rs;
				}
			}
			$arr_timeoutid1 = $arr_timeoutid2 = array();
			if(!empty($arr_reserve_id)) {
				$str_ids = implode("," , $arr_reserve_id);
				$obj_result = $obj_db->select("select a.*,b.shop_reserve_time from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_shop b on a.reserve_shop_id=b.shop_id where reserve_id in (". $str_ids . ")");
				while($obj_rs = $obj_db->fetch_array($obj_result)) {
					if($obj_rs['reserve_state']==0) {
						$time = $lng_i + $obj_rs['reserve_addtime'];
						//是否过期
						if(TIME > $time) {
							 $arr_timeoutid1[] = $obj_rs['reserve_id'];
							 $obj_rs['reserve_state'] = -1;
						}
					} else if($obj_rs['reserve_state']<10) {
						$time = strtotime($obj_rs['reserve_datetime'])+$obj_rs['shop_reserve_time']*60*2;
						if(TIME > $time) {
							 $arr_timeoutid2[] = $obj_rs['reserve_id'];
							 $obj_rs['reserve_state'] = 11;
						}
					}
					$arr_return['reserve']['id_' . $obj_rs['reserve_id']] = $obj_rs;
				}
			}
			if(!empty($arr_timeoutid1)) {
				$ids = implode(",",$arr_timeoutid1);
				$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=-1 where reserve_id in(" . $ids . ")");
				tab_meal_order::on_state('' , -4 , '后台取消',"order_reserve_id in(" . $ids . ")");
			}
			if(!empty($arr_timeoutid2)) {
				$ids = implode(",",$arr_timeoutid2);
				$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=11 where reserve_id in(" . $ids . ")");
				tab_meal_order::on_state('' , 10 , '用餐结束,未在线结款',"order_reserve_id in(" . $ids . ")");
			}
		}
		if(!isset($arr_return["list"])) $arr_return["list"] = array();
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],5);
		return $arr_return;
	}

	//获取指定id收货信息
	function get_info() {
		$id = (int)fun_get::get("id");
		$obj_rs = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE  . "meal_info where info_user_id='" . cls_obj::get("cls_user")->uid . "' and info_id='" . $id . "'");
		return $obj_rs;
	}
	//
	function myvip_progress() {
		$experience = cls_obj::get("cls_user")->get_experience();
		$progress = 0;
		if($experience >= 5000) {
			$lng_val = $experience - 5000;
			$lng_x = 5000;
			$lng_y = 542;
			$val = $lng_y + intval($lng_val/$lng_x * 136);
		} else if($experience >= 2000) {
			$lng_val = $experience - 2000;
			$lng_x = 3000;
			$lng_y = 423;
			$val = $lng_y + intval($lng_val/$lng_x * 122);
		} else if($experience >= 800) {
			$lng_val = $experience - 800;
			$lng_x = 1200;
			$lng_y = 300;
			$val = $lng_y + intval($lng_val/$lng_x * 120);
		} else if($experience >= 300) {
			$lng_val = $experience - 300;
			$lng_x = 500;
			$lng_y = 200;
			$val = $lng_y + intval($lng_val/$lng_x * 100);
		} else if($experience >= 100) {
			$lng_val = $experience - 100;
			$lng_x = 200;
			$lng_y = 100;
			$val = $lng_y + intval($lng_val/$lng_x * 100);
		} else {
			$lng_val = $experience;
			$lng_x = 100;
			$lng_y = 0;
			$val = $lng_y + intval($lng_val/$lng_x * 100);
		}

		return $val;
	}
	function get_msglist() {
		$arr_return = array("list" => array() , "pagebtns" => "");
		if(fun_is::com('msg') == false) return $arr_return;
		$obj_db = cls_obj::db();
		$lng_page = (int)fun_get::get("page",1);
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("com.msg"  , $this->app_dir);
		$lng_pagesize = $arr_config_info["pagesize"];
		$str_where = " where msg_user_id='" . cls_obj::get("cls_user")->uid . "'";
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."other_msg" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT * FROM ".cls_config::DB_PRE."other_msg" . $str_where . " order by msg_id desc" . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],5);
		return $arr_return;
	}
	function get_order_detail($order_id) {
		$obj_db = cls_obj::db();
		$roid = cls_obj::get("cls_session")->get("reserve_oid");
		$arr_return = array("list" => array() , "area" => array() , "arrivetime" => array() , "newid" => 0 ,"shopinfo" => array() );
		$arr_day = $arr_menu_ids = $arr_act_id = array();
		$str_where = " where order_id='" . $order_id . "'";
		if(empty($roid) || $order_id!=$roid)  $str_where .= " and order_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$obj_rs = $obj_db->get_one("select a.*,b.shop_name,b.shop_tel,b.shop_pic from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id" . $str_where);
		$shopid = 0;
		$arr_state = tab_meal_order::get_perms("state");
		if(!empty($obj_rs)) {
			if(empty($arr_return['newid'])) $arr_return['newid'] = $obj_rs['order_id'];
			$arr = explode("|" , $obj_rs["order_ids"]);
			$arr_x = array();
			$obj_rs["menu"] = array();
			foreach($arr as $item) {
				if(empty($item)) continue;
				if(!in_array($item , $arr_x)) {
					$obj_rs["menu"][$item] = array( 'id'=> explode("," , $item) , 'num' => 1);
					$arr_x[] = $item;
				} else {
					$obj_rs["menu"][$item]['num']++;
				}
			}
			if($obj_rs["order_state"]>0) {
				$obj_rs["state"] = array_search($obj_rs["order_state"] , $arr_state);
			} else {
				$obj_rs["state"] = "<font color='#ff0000'>" . array_search($obj_rs["order_state"] , $arr_state) . "</font>";
			}
			//取当时下单的定价
			if(!empty($obj_rs["order_detail"])) {
				$arr_detail = unserialize($obj_rs["order_detail"]);
				if(isset($arr_detail["menu_price"])) $arr_return["price"] = $arr_detail["menu_price"];
			}
			$arr_menu_ids = array_merge($arr_menu_ids , explode("," , str_replace("|" , "," , $obj_rs["order_ids"])));
			if(isset($obj_rs["order_act_ids"])) $arr_act_id[] = $obj_rs["order_act_ids"];
			$shopid = $obj_rs['order_shop_id'];
			$obj_rs["order_act"] = !empty($obj_rs["order_act"]) ? unserialize($obj_rs["order_act"]) : array();
			$arr_return["list"][] = $obj_rs;
		}
		$arr_reserve = array();
		if(!empty($obj_rs['order_reserve_id'])) {
			$arr_reserve = $obj_db->get_one("select a.*,b.table_number from " . cls_config::DB_PRE . "meal_table_reserve a left join " . cls_config::DB_PRE . "meal_table b on a.reserve_table_id=b.table_id where reserve_id='" . $obj_rs['order_reserve_id'] . "'");
		}
		$arr_menu_ids = array_unique($arr_menu_ids);
		$str_ids = implode("," , $arr_menu_ids);
		$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_day[] = $obj_rs;
			$arr_return["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
			if(!isset($arr_return["price"]["id_".$obj_rs["menu_id"]])) $arr_return["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
		}
		if(!isset($arr_return["list"])) $arr_return["list"] = array();
		if(count($arr_act_id)>0) {
			$str_ids = implode(",", $arr_act_id);
			$arr_act = $arr_act_id = array();
			$arr = explode(",", $str_ids);
			foreach($arr as $item) {
				if(is_numeric($item)) $arr_act_id[] = $item;
			}
			$str_ids = implode(",", $arr_act_id);
			if(empty($str_ids)) $str_ids = '0';
			$obj_result = $obj_db->select("select act_name,act_id from " . cls_config::DB_PRE . "meal_act where act_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_act["id_".$obj_rs["act_id"]] = $obj_rs["act_name"];
			}
			$arr = array();
			foreach($arr_return["list"] as $item) {
				if(!empty($item['order_act_ids'])){
					$str_ids = explode(",", $item['order_act_ids']);
					$arr_actname = array();
					foreach($str_ids as $actid) {
						if(isset($arr_act['id_' . $actid])) $arr_actname[] = $arr_act['id_' . $actid];
					}
					$item['order_act_ids'] = implode("<br>" , $arr_actname);
				}
				$arr[] = $item;
			}
			$arr_return["list"] = $arr;

		}
		//取店铺送餐时间
		$obj_shopinfo = $obj_db->get_one("select shop_id,shop_extend,shop_addprice,shop_tel,shop_name,shop_pic from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shopid . "'");
		if(!empty($obj_shopinfo) && !empty($obj_shopinfo["shop_extend"])) {
			$arr = unserialize($obj_shopinfo["shop_extend"]);
			if(isset($arr["arr_arrivetime"])) {
				$arr_return["arrivetime"] = $arr["arr_arrivetime"];
			}
		}
		$arr_return['shopinfo'] = $obj_shopinfo;
		$arr_return['reserve'] = $arr_reserve;
		return $arr_return;
	}
	function get_comment($order_id) {
		$arr_return = array("list" => array() , "score" => 0 , "comment" => array("pic" => array() , "beta" => "" , "list" => array() , "val" => -1 , "id" => 0) );
		$obj_db = cls_obj::db();
		$obj_rs = $obj_db->get_one("select order_ids,order_detail from " . cls_config::DB_PRE . "meal_order where order_id='" . $order_id . "'");
		if(empty($obj_rs)) return $arr_return;
		$arr_detail = unserialize($obj_rs['order_detail']);
		if(isset($arr_detail['score'])) $arr_return['score'] = $arr_detail['score'];

		$ids = str_replace("|" , "," , $obj_rs['order_ids']);
		if(empty($ids)) return $arr_return;
		$arr_val = array();
		$obj_result = $obj_db->select('select comment_val,comment_menu_id,comment_id from ' . cls_config::DB_PRE . "meal_menu_comment where comment_menu_id in(" . $ids . ") and comment_order_id='" . $order_id . "'");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_val["id_" . $obj_rs['comment_menu_id']] = $obj_rs;
		}
		$obj_result = $obj_db->select('select menu_title,menu_id from ' . cls_config::DB_PRE . "meal_menu where menu_id in(" . $ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['comment'] = (isset($arr_val['id_' . $obj_rs['menu_id']])) ? $arr_val['id_' . $obj_rs['menu_id']] : array('comment_val'=>-1,'comment_id'=>0);
			$arr_return["list"][] = $obj_rs;
		}
		$obj_comment = cls_obj::db()->edit(cls_config::DB_PRE."meal_order_comment" , "comment_order_id='".$order_id."' and comment_user_id='" . cls_obj::get("cls_user")->uid . "'");
		if(!empty($obj_comment)) {
			$arr = explode("||" , $obj_comment["comment_list"]);
			$arry = array();
			$val = 0;
			foreach($arr as $item) {
				$arrx = explode("=>" , $item);
				if(!isset($arrx[1])) $arrx[1] = 0;
				$arrx[1] = (int)$arrx[1];
				$arry[$arrx[0]] = $arrx[1];
				$val += $arrx[1];
			}
			$val = empty($arr) ? 0 : intval($val/count($arr));
			$pic = explode("||" , $obj_comment['comment_pic']);
			$arrpic = array();
			foreach($pic as $item) {
				if(!empty($item)) $arrpic[] = $item;
			}
			$arr_return['comment'] = array("pic"=>$arrpic , "beta" => $obj_comment['comment_beta'] , "list" => $arry , "val" => $val , "id" => $obj_comment['comment_id']);
		}
		return $arr_return;
	}
	function save_comment() {
		$order_id = fun_get::get("order_id");
		$obj_db = cls_obj::db_w();
		$uid = cls_obj::get("cls_user")->uid;
		$obj_rs = $obj_db->get_one("select order_id,order_shop_id,order_ids,order_state,order_user_id,order_comment,order_total_pay from " . cls_config::DB_PRE . "meal_order where order_id='" . $order_id . "' and order_user_id='" . $uid . "'");
		if(empty($obj_rs)) return array("code" => 500 , "msg" => "订单不存在");
		if($obj_rs['order_state']<1) return array("code" => 500 , "msg" => "只有成交的订单才能评论");
		//if($obj_rs['order_comment']==1) return array("code" => 500 , "msg" => "您已经评论过了");
		$order_total_pay = $obj_rs['order_total_pay'];
		$uid = $obj_rs['order_user_id'];
		$ids = str_replace("|" , "," , $obj_rs['order_ids']);
		$arr_id = array_unique(explode("," , $ids));
		$arr_insert = array();
		$arr_menuid = array();
		foreach($arr_id as $item) {
			if(!fun_is::set("commentval". $item)) continue;
			$x = (int)fun_get::get("commentid" . $item);
			$val = (int)fun_get::get("commentval". $item);
			$val += 1;
			if(empty($x)) {
				$arr_insert[] = array("comment_val" => $val , "comment_menu_id" => $item , "comment_user_id" => $obj_rs['order_user_id'] , "comment_shop_id" => $obj_rs['order_shop_id'] , "comment_order_id" => $obj_rs["order_id"] , "comment_addtime" => TIME);
				$arr_menuid[] = $item;
			} else {
				$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_menu_comment set comment_val='" . $val . "' where comment_id='" . $x . "' and comment_order_id='" . $order_id . "' and comment_user_id='" . $uid . "'");
			}
		}
		if(!empty($arr_insert)) {
			$obj_db->on_insert_all(cls_config::DB_PRE . "meal_menu_comment" , $arr_insert);
		}
		foreach($arr_id as $item) {
			tab_meal_menu::on_tongbu_comment($item);
		}
		$id = (int)fun_get::get("commentid");
		$arr_oldcommentval = array();
		$valallintold = 0;
		if(!empty($id)) {//修改评论的话，得还原原评分
			$obj_oldcomment = $obj_db->get_one("select comment_list from " . cls_config::DB_PRE . "meal_order_comment where comment_id='" . $id . "' and comment_user_id='" . $uid . "'");
			$arrx = explode("||" , $obj_oldcomment['comment_list']);
			foreach($arrx as $item) {
				$arry = explode( "=>" , $item);
				$arr_oldcommentval[$arry[0]] = (float)$arry[1];
				$valallintold+=$arr_oldcommentval[$arry[0]];
			}
			$valallintold = intval($valallintold/count($arr_oldcommentval));
		}
		$commentval = fun_get::get("commentval" , array());
		$commentname = fun_get::get("commentname" , array());
		$valall = 0;
		$arrlist = array();
		for($i = 0 ; $i < count($commentval) ; $i++) {
			$commentval[$i] = (int)$commentval[$i]+1;
			$arrlist[] = $commentname[$i] . "=>" . $commentval[$i];
			$valall += $commentval[$i];
		}
		$commentlist = $commentlistall = implode("||" , $arrlist);
		$valall = $valall/count($commentval);
		$arr_pic = fun_get::get("pic" , array());
		$commentpic = implode("||" , $arr_pic);
		$arr_val = array(
			"comment_val" => $valall,
			"comment_beta" => fun_get::get("comment_beta"),
			"comment_list" => $commentlist,
			"comment_pic" => $commentpic,
		);
		if(empty($id)) {
			$arr_val["comment_user_id"] = $uid;
			$arr_val["comment_shop_id"] = $obj_rs['order_shop_id'];
			$arr_val["comment_order_id"] = $obj_rs['order_id'];
			$arr_val["comment_addtime"] = TIME;
			$arr = $obj_db->on_insert(cls_config::DB_PRE . "meal_order_comment" , $arr_val);
			if($arr["code"] == 0) {
				//奖励订单所获得积分
				tab_meal_order::on_award($obj_rs['order_id']);
				$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_order set order_comment=1 where order_id='" . $order_id . "' and order_user_id='" . $uid . "'");
				//评论订单
				tab_sys_user_action::on_action( $uid , 'meal_order_comment' , array("basescore"=>$order_total_pay));
			}
		} else {
			$arr = $obj_db->on_update(cls_config::DB_PRE . "meal_order_comment" , $arr_val , "comment_id='" . $id . "' and comment_order_id='" . $order_id . "' and comment_user_id='" . $uid . "'");
		}
		//统计店铺评论
		$obj_shopcomment = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order_comment where comment_shop_id='" . $obj_rs['order_shop_id'] . "'");
		$shop_commentnum = empty($obj_shopcomment) ? 0 : $obj_shopcomment['num'];
		//同步店铺评论
		$obj_shop = $obj_db->get_one("select shop_comment_list,shop_comment_listall,shop_comment_val from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $obj_rs['order_shop_id'] . "'");
		$arrv = array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0);
		if(!empty($obj_shop['shop_comment_list'])) {
			$arrx = explode("||" , $obj_shop['shop_comment_list']);
			$arrxall = explode("||" , $obj_shop['shop_comment_listall']);
			$arrlistx = $arrlistxall = array();
			if($shop_commentnum != 1 || empty($arr_oldcommentval)) {
				foreach($arrx as $i=>$item) {
					$arry = explode("=>" , $item);
					$arry[1] = (float)$arry[1];
					$arrlistx[$arry[0]] = isset($arr_oldcommentval[$arry[0]]) ? $arry[1]*2-$arr_oldcommentval[$arry[0]] : $arry[1];
				}
				foreach($arrxall as $i=>$item) {
					$arry = explode("=>" , $item);
					$arry[1] = (float)$arry[1];
					$arrlistxall[$arry[0]] = isset($arr_oldcommentval[$arry[0]]) ? $arry[1]-$arr_oldcommentval[$arry[0]] : $arry[1];
				}
			}
			$arrlist = $arrlistall = array();
			$valall = 0;
			for($i = 0 ; $i < count($commentval) ; $i++) {
				$val = isset($arrlistxall[$commentname[$i]]) ? ($arrlistxall[$commentname[$i]]+(float)$commentval[$i])/$shop_commentnum : (float)$commentval[$i];
				$val = (float)number_format($val , 1);
				$arrlist[] = $commentname[$i] . "=>" . $val;
				$valall += (float)$commentval[$i];
				$val = isset($arrlistxall[$commentname[$i]]) ? $arrlistxall[$commentname[$i]]+(float)$commentval[$i] : (float)$commentval[$i];
				$val = (float)number_format($val , 1);
				$arrlistall[] = $commentname[$i] . "=>" . $val;
			}
			$valall = $valall/count($commentval);
			$commentlist = implode("||" , $arrlist);
			$commentlistall = implode("||" , $arrlistall);
			$arrx = explode("||" , $obj_shop['shop_comment_val']);
			foreach($arrx as $item) {
				if(empty($item)) continue;
				$arry = explode("=>" , $item);
				$arrv[$arry[0]] = (int)$arry[1];
			}
		}
		$valallint = (int)$valall;
		if(!empty($arr_oldcommentval) && isset($arrv[$valallintold]) && $arrv[$valallintold]>0)  $arrv[$valallintold]--;
		(isset($arrv[$valallint])) ? $arrv[$valallint]++ : $arrv[$valallint] = 1;
		$arry = array();
		$total = $num = 0;
		foreach($arrv as $item=>$key) {
			$key = (int)$key;
			$item = (int)$item;
			$total+=$key*$item;
			$num+=$key;
			$arry[] = $item . "=>" . $key;
		}
		$valall = $total/$num;
		$commentval = implode("||" , $arry);
		$arr = $obj_db->on_update(cls_config::DB_PRE . "meal_shop" , array("shop_comment" => $valall , "shop_comment_list" => $commentlist , "shop_comment_listall" => $commentlistall , "shop_comment_val" => $commentval) , "shop_id='" . $obj_rs['order_shop_id'] . "'");
		return $arr;
	}

	function on_order_cancel($id) {
		$order_cancel = (int)cls_config::get("order_cancel" , "meal");
		if($order_cancel < 0) return array("code" => 500 , "msg" => "商家不允许取消订单" , "id" => $id);
		$obj_db = cls_obj::db_w();
		$where = " where order_id='" . $id . "' and order_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$obj_order = $obj_db->get_one("select order_shop_id,order_state,order_addtime from " . cls_config::DB_PRE	. "meal_order" . $where);
		if(empty($obj_order)) return array("code" => 500 , "msg" => "订单不存在");
		if($obj_order['order_state']>0) {
			return array("code"=>500 , "msg" => "当前订单已经在配送过程中，无法取消");
		} else if(!in_array($obj_order['order_state'] , array(0,-2,-3) ) ) {
			return array("code"=>500 , "msg" => "当前订单已经处理完成，无法取消");
		}
		if($order_cancel>0) {
			$time = (int)((TIME - $obj_order['order_addtime'])/60);
			if($time > $order_cancel) return array("code" => 500 , "msg" => "订单只能在" . $order_cancel . "分钟之内才能取消" , "id" => $id);
		}
		$arr = tab_meal_order::on_state($id , -5 , '用户取消' , "order_user_id='" . cls_obj::get("cls_user")->uid . "'");
		if($arr["code"]!=0) {
			$arr_return["code"] = $arr['code'];
			$arr_return["msg"] = "处理失败";
			return $arr_return;
		} else {
			//看是否发过商家短信，取手机号，再发一次取消通知
			$obj_sms = $obj_db->get_one("select sms_tel,sms_confirm_id from " . cls_config::DB_PRE . "other_sms where sms_type=1 and sms_about_id='" . $id . "'");
			if(!empty($obj_sms)){
				$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$obj_sms['sms_tel'] , "cont" => "确认码为：" . $obj_sms['sms_confirm_id'] . " 的订单用户已经取消" ,"id" => $id , "type"=>2) );
			}
		}
		return array("code" => 0 , "msg" => "成功取消" , "id" => $id);
	}
	function on_reserve_cancel() {
		$number = fun_get::get("number");
		$obj_reserve = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "meal_table_reserve where reserve_number='" . $number . "'");
		if(empty($obj_reserve)) return array('code' => 500 , 'msg' => '预订信息不存在');
		if($obj_reserve['reserve_state']==10)  return array('code' => 500 , 'msg' => '已完成的订单不能取消');
		$obj_order = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_reserve_id='" . $obj_reserve['reserve_id'] . "'");
		if(empty($obj_order)) return array("code" => 500 , "msg" => "订单不存在");
		$arr = tab_meal_order::on_state($obj_order['order_id'] , -4 , '用户取消');
		if($arr['code'] == 0) {
			$arr = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "meal_table_reserve set reserve_state=-1 where reserve_id='" . $obj_reserve['reserve_id'] . "'");
		}
		return $arr;
	}
	function get_area_info() {
		$arr_return = array();
		$area_depth = 0;
		$obj_db = cls_obj::db();
		$pid = 0;
		$obj_result = $obj_db->select("select area_id as 'id',area_name as 'name',area_pid as 'pid',area_depth as 'depth',area_pids as 'pids',area_val as 'val' from " . cls_config::DB_PRE . "sys_area order by area_pid,area_jian");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs["name"])) $obj_rs["name"] = $obj_rs["val"];
			if(empty($obj_rs["val"])) $obj_rs["val"] = $obj_rs["name"];
			if($obj_rs['val'] == $obj_rs['name']) unset($obj_rs['val']);
			$obj_rs['price'] = 0;
			$obj_rs['time'] = 0;
			$arr_list["id_" . $obj_rs["pid"]][] = $obj_rs["id"];
			$arr_area["id_" . $obj_rs["id"]] = $obj_rs;
			//if($obj_rs["area_pid"] == $pid) $arr_return["default"][] = $obj_rs;
			if($obj_rs['depth']>$area_depth) {
				$area_depth = $obj_rs['depth'];
			}
		}
		if(isset($arr_list['id_'.$pid])) {
			foreach($arr_list['id_' . $pid] as $item) {
				if(isset($arr_area["id_" . $item])) $arr_return["default"][] = $arr_area["id_" . $item];
			}
		}
		$arr_return["depth"] = $area_depth;
		$arr_return["area"] = $arr_area;
		$arr_return["list"] = $arr_list;
		return $arr_return;
	}
	//绑定手机号
	function on_bind_verifytel() {
		$tel = fun_get::post("mobile");
		$key = fun_get::post("verifycode");
		$arr_return = tab_sys_verify::on_verify($tel , $key , 4);

		//查看用户是否已绑定过电话
		$obj_user = cls_obj::db()->get_one("select user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . cls_obj::get("cls_user")->uid . "' and user_verify_tel=1");
		$mobile = '';
		if(isset($obj_user['user_mobile'])) $mobile = $obj_user['user_mobile'];
		$anquan_verify = cls_obj::get("cls_session")->get('anquan.verify');
		if(!empty($mobile) && empty($anquan_verify)) return array("code" => 500 , "msg" => "验证已过期，请刷新重新验证通过后再来绑定新手机");
		if($arr_return['code'] != 0) return $arr_return;
		$arr_return = tab_sys_user::on_save(array(
			'user_id' => cls_obj::get('cls_user')->uid,
			'user_mobile' => $tel,
			'user_verify_tel' => 1,
		));
		cls_obj::get("cls_session")->destroy('anquan.verify');
		return $arr_return;
	}
	//保存验证码
	function on_save_safeask() {
		$uid = cls_obj::get("cls_user")->uid;
		$arr_ask = cls_obj::db_w()->get_one("select 1 from " . cls_config::DB_PRE . "sys_safeask where safeask_user_id='" . $uid . "'");
		$anquan_verify = cls_obj::get("cls_session")->get('anquan.verify');
		if(!empty($arr_ask) && empty($anquan_verify)) return array("code" => 500 , "msg" => "验证已过期请重新验证后再来修改密保");
		$arr = array(
			array('safeask_question' => fun_get::post('ask1') , 'safeask_answer' => fun_get::post("answer1") , 'safeask_user_id' => $uid),
			array('safeask_question' => fun_get::post('ask2') , 'safeask_answer' => fun_get::post("answer2") , 'safeask_user_id' => $uid),
			array('safeask_question' => fun_get::post('ask3') , 'safeask_answer' => fun_get::post("answer3") , 'safeask_user_id' => $uid)
		);
		$ii = 1;
		foreach($arr as $item) {
			$item['safeask_number'] = $ii;
			$ii++;
			$arr_msg = tab_sys_safeask::on_save($item);
			if($arr_msg['code'] != 0) {
				cls_obj::db_w()->on_exe("delete from " . cls_config::DB_PRE . "sys_safeask where safeask_user_id='" . cls_obj::get("cls_user")->uid . "' and safeask_addtime='" . TIME . "'");
				return $arr_msg;
			}
		}
		$arr_msg = cls_obj::db_w()->on_exe("delete from " . cls_config::DB_PRE . "sys_safeask where safeask_user_id='" . cls_obj::get("cls_user")->uid . "' and safeask_addtime<'" . TIME . "'");
		if($arr_msg['code'] != 0) {
			cls_obj::db_w()->on_exe("delete from " . cls_config::DB_PRE . "sys_safeask where safeask_user_id='" . cls_obj::get("cls_user")->uid . "' and safeask_addtime='" . TIME . "'");
			return $arr_msg;
		}
		cls_obj::get("cls_session")->destroy('anquan.verify');
		return array("code" => 0 , "msg" => "设置成功");
	}
	//用户中心修改密码
	function on_editpwd(){
		$lng_user_id = cls_obj::get('cls_user')->uid;
		if(empty($lng_user_id)) return array("code" => 500 , "msg" => "您还没有登录，无法修改密码");
		$arr_return = array( "code" => 0 , "msg" => "修改成功" );
		$arr_pwd=array(
			"oldpwd" => fun_get::post("oldpwd"),
			"pwd1" => fun_get::post('pwd1'),
			"pwd2" =>fun_get::post("pwd2")
		);
		if( $arr_pwd["pwd1"] != $arr_pwd["pwd2"] )  return array("code" => 1 , "msg" => "两次输入密码不一致");
		$arr = cls_obj::get("cls_user")->on_update_pwd($arr_pwd["oldpwd"] , $arr_pwd["pwd1"]);
		return $arr;
	}

}