<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_index extends inc_mod_default {

	/* 获取店铺列表 , 按经营模式返回
	 * 
	 */
	function get_shop_list(){
		$obj_db = cls_obj::db();
		$arr_return = array("list" => array() , "list_menu" => array() );
		$s_key = fun_get::get("s_key");
		$arr_shopid = $this->get_area_shopid($this->area_id);
		$ids = (empty($arr_shopid)) ? "0" : implode("," , $arr_shopid);
		$arr_where = array("(shop_state>0 or shop_state=-1) and shop_isdel=0 and shop_id in(" . $ids . ")");
		if(!empty($s_key)) $arr_where[] = $s_key;
		$where = " where " . implode(" and " , $arr_where);
		$arr_shopid = array();
		$obj_result = $obj_db->select("select shop_name,shop_id,shop_mode,shop_hits,shop_dispatch_price,shop_support_score from " . cls_config::DB_PRE . "meal_shop" . $where . " limit 0,100");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs["shop_dispatch_price"] = (float)$obj_rs['shop_dispatch_price'];
			$arr_shopid[] = $obj_rs['shop_id'];
			$arr_return['menu_' . $obj_rs['shop_id']] = array();
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_shopid)>0) {
			$str_ids = implode(",", $arr_shopid);
			//当前星期值
			$weekday = date("w");
			$day = (int)date("d");

			//取菜价
			$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_shop_id,menu_price,menu_mode,menu_weekday,menu_date from " . cls_config::DB_PRE . "meal_menu where menu_isdel=0 and menu_state>0 and menu_shop_id in(". $str_ids . ") and menu_tj=1");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				//如果是按星期上，不在范围内就排除
				if($obj_rs["menu_mode"]==1 && !stristr("," . $obj_rs["menu_weekday"] . "," , ",".$weekday.",")) continue;
				//如果是按日期上，不在范围内就排除
				if($obj_rs["menu_mode"]==3 && !stristr("," . $obj_rs["menu_date"] . "," , ",".$day."," )) continue;

				$obj_rs["menu_price"] = (float)$obj_rs['menu_price'];
				if(empty($obj_rs["menu_pic_small"])) $obj_rs["menu_pic_small"] = $obj_rs["menu_pic"];
				$obj_rs["menu_pic_small"] = fun_get::html_url($obj_rs["menu_pic_small"]);
				if(count($arr_return['menu_' . $obj_rs['menu_shop_id']])<9) $arr_return['menu_' . $obj_rs['menu_shop_id']][] = $obj_rs;
			}
			//取起送价
			$obj_result = $obj_db->select("select dispatch_price,dispatch_shop_id from " . cls_config::DB_PRE . "meal_dispatch where dispatch_area_id='" . $this->area_lou_id ."' and dispatch_shop_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$obj_rs['dispatch_price'] = (float)$obj_rs['dispatch_price'];
				if(!empty($obj_rs['dispatch_price'])) $arr_return['price_'.$obj_rs['dispatch_shop_id']] = $obj_rs['dispatch_price'];
			}
		}
		return $arr_return;
	}
	/* 获取指店铺信息
	 *
	 */
	function get_shopinfo($shop_id) {
		$obj_shop = cls_obj::db()->get_one("select shop_name,shop_id,shop_user_id,shop_extend,shop_linkname,shop_linktel,shop_tel,shop_address,shop_oneminleast,shop_dispatch_price,shop_mode,shop_support_score from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(!empty($obj_shop['shop_extend']))  $obj_shop["extend"] = unserialize($obj_shop["shop_extend"]);
		return $obj_shop;
	}
	/* 获取店铺菜品列表，按分类分组
	 * 
	 */
	function get_menu_list($shop_id){
		$obj_db = cls_obj::db();
		$arr_menu = $arr_cart = $arr_cart_id = array();
		$arr = $this->get_cart_info();
		if(isset($arr["shop_" . $shop_id])) {
			$arr_cart = $arr["shop_" . $shop_id]['cart'];
			$arr_cart_id = $arr["shop_" . $shop_id]['ids'];
		}
		$arr_return = array("list"=>array(),"tj"=>array());
		$arr_group_id = array();
		$where = $this->get_menu_today_where($shop_id);
		//当前星期值
		$weekday = date("w");
		$day = (int)date("d");
		$obj_result = $obj_db->select("select menu_id,menu_type,menu_intro,menu_group_id,menu_title,menu_price,menu_tj,menu_pic,menu_pic_small,menu_num,menu_attribute,menu_mode,menu_holiday,menu_weekday,menu_mode,menu_weekday,group_name,menu_date,menu_sold_time,menu_sold_today from " . cls_config::DB_PRE . "meal_menu a left join " . cls_config::DB_PRE . "meal_menu_group b on a.menu_group_id=b.group_id " . $where ." order by b.group_sort");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			//如果是按星期上，不在范围内就排除
			if($obj_rs["menu_mode"]==1 && !stristr("," . $obj_rs["menu_weekday"] . "," , ",".$weekday.",")) continue;
			//如果是按日期上，不在范围内就排除
			if($obj_rs["menu_mode"]==3 && !stristr("," . $obj_rs["menu_date"] . "," , ",".$day."," )) continue;
			if(empty($obj_rs["menu_pic_small"])) $obj_rs["menu_pic_small"] = $obj_rs["menu_pic"];
			if(empty($obj_rs["menu_pic"])) $obj_rs["menu_pic"] = $obj_rs["menu_pic_small"];
			$obj_rs["menu_pic_small"] = fun_get::html_url($obj_rs["menu_pic_small"]);
			$obj_rs["menu_pic"] = fun_get::html_url($obj_rs["menu_pic"]);
			$arr = explode("." , $obj_rs["menu_price"]);
			$obj_rs["price_int"] = $arr[0];
			$obj_rs["price_float"] = $arr[1];
			//当前数量
			if(empty($obj_rs['menu_num'])) {
				$obj_rs['num'] = 9999;
			} else {
				$obj_rs['num'] = ($obj_rs['menu_sold_time']> strtotime(date("Y-m-d")) ) ? $obj_rs['menu_num']-$obj_rs['menu_sold_today'] : $obj_rs['menu_num'];
			}
			$arr_return["list"]["group_" . $obj_rs["menu_group_id"]]["list"][] = $obj_rs;
			//初始购物车时用到
			if(in_array($obj_rs["menu_id"] , $arr_cart_id)) $arr_menu["id_". $obj_rs["menu_id"]] = $obj_rs;
			if(!in_array( $obj_rs["menu_group_id"] , $arr_group_id) ) {
				$arr_return["list"]["group_" . $obj_rs["menu_group_id"]]["id"] = $obj_rs["menu_group_id"];
				$arr_return["list"]["group_" . $obj_rs["menu_group_id"]]["name"] = $obj_rs["group_name"];
				$arr_group_id[] = $obj_rs["menu_group_id"];
			}
			if(!empty($obj_rs['menu_tj'])) {
				$arr_return['tj'][] = $obj_rs;
			}
		}
		$arr_return['cart_menu'] = $arr_menu;
		$arr_return['cart'] = $arr_cart;
		return $arr_return;
	}
	/* 获取购物车列表
	 *
	 */
	function get_cart_list() {
		$arr_cart = $this->get_cart_info();
		$shopids = implode(",", $arr_cart['shop_ids']);
		$menuids = implode("," , $arr_cart["menu_ids"]);
		$shop_id = fun_get::get("shop_id");
		if(!empty($shop_id)) {
			(in_array($shop_id , $arr_cart['shop_ids']))? $shopids = $shop_id : $shopids = '0';
		}
		$arr_shop = $arr_menu = array();
		$obj_db = cls_obj::db();
		//取菜品信息
		if(!empty($menuids)) {
			$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "meal_menu where menu_id in(".$menuids.")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$obj_rs["menu_price"] = (float)$obj_rs["menu_price"];
				$arr_menu["id_" . $obj_rs["menu_id"]] = $obj_rs;
			}
		}
		$arr_dispatch = $arr_arrive = array();
		$lng_delay = 0;
		$dispatch_isnull = 0;//支持的送货地区是否为空，只要有一家店为空，则为空
		$ticket = 1;
		//取店铺信息
		if(!empty($shopids)) {
			$ii = 1;
			$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "meal_shop where shop_id in(".$shopids.")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$obj_rs["index"] = $ii;
				$arr_shop["id_" . $obj_rs["shop_id"]]['info'] = $obj_rs;
				if(!empty($obj_rs["shop_extend"])) {
					$arr = unserialize($obj_rs["shop_extend"]);
					if(isset($arr["arr_arrivetime"])) {
						(count($arr_arrive)<1) ? $arr_arrive = $arr["arr_arrivetime"] : $arr_arrive = array_intersect( $arr_arrive , $arr["arr_arrivetime"]);
					}
					if(isset($arr["arrivedelay"]) && $arr["arrivedelay"] > $lng_delay ) $lng_delay = $arr["arrivedelay"];
				}
				//如果存在多店，只要一家不提供发票，则不提供发票
				if(empty($obj_rs["shop_ticket"])) $ticket = 0;
				$arr_1 = array();
				$jj = 1;
				$num = 0;
				$arr_shop["id_" . $obj_rs["shop_id"]]['price'] = 0;
				foreach($arr_cart['shop_'.$obj_rs["shop_id"]]['cart'] as $item) {
					$arr['index'] = $jj;
					$arr['ids'] = $item;
					$arr['price'] = 0;
					foreach($item as $menu) {
						$arr['price'] += $arr_menu["id_" . $menu]['menu_price'];
					}
					$num++;
					if($obj_rs['shop_mode'] == 2 || $obj_rs['shop_mode'] == 3) {
						$arr_1[] = $arr;
						$jj++;
					} else {
						$x = implode("_" , $item);
						if(isset($arr_1[$x])) {
							$arr_1[$x]['price'] = $arr_1[$x]['price']/$arr_1[$x]['num'];
							$arr_1[$x]['num']++;
							$arr_1[$x]['price'] = $arr_1[$x]['price']*$arr_1[$x]['num'];
						} else {
							$arr['num'] = 1;
							$arr_1[$x] = $arr;
							$jj++;
						}
					}
					$arr_shop["id_" . $obj_rs["shop_id"]]['price'] += $arr['price'];
				}
				$arr_shop["id_" . $obj_rs["shop_id"]]['cart'] = $arr_1;
				$arr_shop["id_" . $obj_rs["shop_id"]]['num'] = $num;
				$ii++;
			}
		}
		//发票
		$arr_ticket = array("0"=>"暂不提供");
		if($ticket) {
			$arr_ticket = cls_config::get("ticket_list","meal");
		}
		$arr_arrive = $this->get_arrive_time($arr_arrive , $lng_delay);
		$arr_return =  array("cart"=>$arr_cart , "shop"=>$arr_shop , "menu"=>$arr_menu , "shopids"=>$shopids , "arrivetime" => $arr_arrive , "ticket" => $arr_ticket);
		return $arr_return;
	}
	/* 购物车信息
	 *
	 */
	function get_cart_info() {
		//取当购物车信息
		$cart_ids = fun_get::get("cart_ids");
		if(empty($cart_ids)) $cart_ids = cls_obj::get("cls_session")->get_cookie("cart_ids");
		$arr_return = $this->format_cart($cart_ids);
		return $arr_return;
	}

	//取送餐时间
	function get_arrive_time($arr , $delay) {
		$lng_time = date("Hi")+40+$delay;
		$lng_time_now = date("Hi");
		$arr_new = array();
		foreach($arr as $item=>$key) {
			if($item < $lng_time) continue;
			$arr_new[$item] = $key;
		}
		return $arr_new;
	}
	//根据指定id 获取文章内容
	function get_article($id , $fid = 0) {
		$obj_db = cls_obj::db();
		if(!empty($id)) {
			$where = " where article_id='" . $id . "'";
		} else {
			$where = " where article_folder_id='" . $fid . "' order by article_id desc";
		}
		$obj_rs = $obj_db->get_one("select * from " . cls_config::DB_PRE ."article" . $where);
		if(!empty($obj_rs)) {
			$obj_rs["article_content"] = fun_get::filter($obj_rs["article_content"] , true);
		}
		return $obj_rs;
	}
	//获取地区html列表
	function get_area($shopid , $dispatch_mode , $pid = -1) {
		if($pid < 0 ) {
			$pid = (int)cls_config::get("area_city_id", "meal");
		}
		$arr_return = array("list" => "" , "default" => array() ,"depth" => 0 , "area" => "");
		$obj_db = cls_obj::db();
		$arr_area = $arr_default = $arr_list = array();
		if(empty($dispatch_mode)) {
			$obj_result = $obj_db->query("select area_id,area_name,area_pid,area_depth,area_val from " . cls_config::DB_PRE . "sys_area where " . cls_db::concat("," , "area_pids" , ",") ." like '%," . $pid . ",%' order by area_depth,area_sort,area_name");
		} else {
			$obj_result = $obj_db->query("select area_id,area_name,area_pid,area_depth,area_val,dispatch_price,dispatch_time from " . cls_config::DB_PRE . "meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on a.dispatch_area_id=b.area_id where a.dispatch_shop_id='" . $shopid . "' order by area_depth,area_sort,area_name");
		}
		$depth = 0;
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs["area_name"])) $obj_rs["area_name"] = $obj_rs["area_val"];
			if(empty($obj_rs["area_val"])) $obj_rs["area_val"] = $obj_rs["area_name"];
			if($obj_rs['area_val'] == $obj_rs['area_name']) unset($obj_rs['area_val']);
			$arr_list["id_" . $obj_rs["area_pid"]][] = $obj_rs["area_id"];
			$arr_area["id_" . $obj_rs["area_id"]] = $obj_rs;
			if($obj_rs['area_depth'] > $depth) $depth = $obj_rs['area_depth'];
			if($obj_rs["area_pid"] == $pid) $arr_return["default"][] = $obj_rs;
		}
		if(count($arr_return["default"])>0) {
			$arr_return["depth"] = $depth - $arr_return["default"][0]["area_depth"] + 1;
		}
		$arr_return["area"] = $arr_area;
		$arr_return["list"] = $arr_list;
		return $arr_return;
	}
	//取首页活动信息
	function get_activitie($shop_id) {
		$arr = array();
		$obj_db = cls_obj::db();
		$obj_result = $obj_db->select("select channel_name,channel_id,article_title,article_id from " . cls_config::DB_PRE . "article_channel a left join " . cls_config::DB_PRE . "article b on a.channel_id=b.article_channel_id where channel_key='activitie' and article_state>0 and article_about_id='" . $shop_id . "' and article_isdel=0 order by b.article_id limit 0,10");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr[] = $obj_rs;
		}
		return $arr;
	}
	function get_article_list($channel_id , $about_id = 0) {
		$arr_return = array("list" => array() , "pagebtns" => "");
		$obj_db = cls_obj::db();
		$lng_page = (int)fun_get::get("page");
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("com.msg"  , $this->app_dir);
		$lng_pagesize = $arr_config_info["pagesize"];
		$str_where = " where article_isdel=0 and article_state>0 and article_channel_id='" . $channel_id . "'";
		$arr = cls_config::get_data("article_channel" , "id_" . $channel_id );
		if(!empty($about_id) || !empty($arr) && !empty($arr['channel_user_type'])) $str_where .= " and article_about_id='" . $about_id . "'";
		//取分页信息
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."article" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT * FROM ".cls_config::DB_PRE."article" . $str_where . " order by article_id desc" . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
}