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
	function get_shop_list($page = 1){
		$sortby = (int)fun_get::get("sortby");
		$sortval = (int)fun_get::get("sortval");
		$shoptype = fun_get::get("shoptype");
		$addprice = (int) fun_get::get("addprice");
		$iscollect = (int) fun_get::get("iscollect");
		$longitude = fun_get::get("longitude");
		$latitude = fun_get::get("latitude");
		$weekday = date("w");
		if(empty($longitude)) {
			if(empty($sortby)) $sortby = (int)cls_config::get("index_sortby" , "view");
			if(empty($sortval)) $sortval = (int)cls_config::get("index_sortval" , "view");
			$location = cls_obj::get('cls_session')->get("location");
			if(!empty($location) && isset($location['lng'])) {
				$longitude = $location['lng'];
				$latitude = $location['lat'];
			}
		} else {
			$location = array('lng' => $longitude, 'lat' => $latitude);
			cls_obj::get('cls_session')->set("location" , $location);
		}
		$cache_time = (int)cls_config::get("area_shoplist","cache");
		$env = cls_config::get_env();
		$s_key = fun_get::get("s_key");
		$cache_key = "area" . $env . "_" . $this->area_id . "_shoplist_" . $page."_".$sortby."_".$sortval . "_" . $addprice . "_" . $shoptype . "_" . cls_obj::get("cls_user")->shop_menu_type . "_" . $s_key;
		$obj_db = cls_obj::db();
		$arr_return = cls_cache::get($cache_key,"area_shop",$cache_time);
		//取收藏
		$arr_collect_id = array();
		if(cls_obj::get('cls_user')->is_login()) {
			$obj_result = $obj_db->select("select collect_for_id from " . cls_config::DB_PRE . "meal_collect where collect_user_id='" . cls_obj::get('cls_user')->uid . "' and collect_type=1");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_collect_id[] = $obj_rs['collect_for_id'];
			}
		}
		if($arr_return === null || $iscollect) {
			$lng_pagesize = (int)fun_get::get('pagesize');
			if(empty($lng_pagesize)) $lng_pagesize = (int)cls_config::get("index_pagesize","view");
			if(empty($lng_pagesize)) $lng_pagesize = 20;
			$arr_return = array("list" => array() , "shopid" => array() ,"sortby" => $sortby , "sortval" => $sortval , "isdistance" => 0);
			$arr_shop = $this->get_area_shop($this->area_id);
			if($iscollect) {
				$ids = empty($arr_collect_id) ? "0" : implode("," , $arr_collect_id);
			} else {
				$ids = (empty($arr_shop['id'])) ? "0" : implode("," , $arr_shop['id']);
			}
			$arr_where = array("(shop_state>0 or shop_state=-1) and shop_isdel=0 and shop_id in(" . $ids . ")");
			if(!empty($s_key)) {
				$arr_where[] = "(shop_name like '%" . $s_key . "%' or shop_id in(select menu_shop_id from " . cls_config::DB_PRE . "meal_menu where menu_title like '%" . $s_key . "%' and menu_about_id<1))";
				if(!isset($_GET['page'])) {
					$arr = tab_sys_cache_words::on_save("search" , $s_key);
					//历史搜索
					$arr_config = tab_sys_user_config::get_config("config_info");
					if(!isset($arr_config['search.words']) || empty($arr_config['search.words']) ) {
						$arr_config['search.words'] = array($s_key);
					} else {
						array_splice($arr_config['search.words'],0,0,array($s_key));
						$arr_config['search.words'] = array_unique($arr_config['search.words']);
						if(count($arr_config['search.words'])>10) {
							array_splice($arr_config['search.words'],10,5);
						}
					}
					$arr = tab_sys_user_config::on_save( array( "config_info" => serialize($arr_config) ));
				}
			}
			if(!empty($shoptype)) $arr_where[] = "shop_type='" . $shoptype . "'";
			if($addprice == 1) $arr_where[] = "shop_addprice=0";
			$where = " where " . implode(" and " , $arr_where);
			$sort = "";
			//取排序
			if($sortby == 2) {
				$sort = " order by shop_sold";
			} else if($sortby == 3) {
				$sort = " order by shop_hits";
			} else if($sortby == 1) {
				$sort = " order by shop_id";
			} else if(empty($longitude)) {
				$sort = " order by shop_tj desc,shop_id";
				$sortval = 1;
			} else {
				$sortby = 4;
				$arr_return["isdistance"] = 1;
			}
			$arr_list1 = $arr_list2 = array();
			if(!empty($sort)) $sort .= ($sortval==1) ? " asc" : " desc";
			$arr_shopid = $arr_tj = array();
			$todaytime = strtotime(date("Y-m-d"));
			$arr_shoplist = array();
			$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_shop" , $where , $page , $lng_pagesize);
			$limit = " limit 0,1000";
			$obj_result = $obj_db->select("select shop_name,shop_id,shop_state,shop_mode,shop_hits,shop_dispatch_price,shop_support_score,shop_pic_small,shop_tj,shop_extend,shop_addprice,shop_comment,shop_commentnum,shop_day_limit,shop_sold,shop_day_sold,shop_order_time,shop_position_lng,shop_position_lat from " . cls_config::DB_PRE . "meal_shop" . $where . $sort . $limit);
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_opentime=tab_meal_shop::get_opentime($obj_rs['shop_id'],$obj_rs['shop_extend']);
				$obj_rs['commentrate'] = $obj_rs['shop_comment']/5*100;
				$obj_rs["shop_dispatch_price"] = (float)$obj_rs['shop_dispatch_price'];
				$obj_rs["extend"] = (!empty($obj_rs['shop_extend'])) ? unserialize($obj_rs["shop_extend"]) : array();
				$obj_rs['extend']['weekday'] = empty($obj_rs['extend']['weekday']) ?  array() : explode("," , $obj_rs['extend']['weekday']);
				$obj_rs['shop_pic_small'] = fun_get::html_url($obj_rs['shop_pic_small']);
				$arr_return['shopid'][] = $obj_rs['shop_id'];
				if(isset($arr_shop['list']['id_' . $obj_rs['shop_id']])) {
					if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_price'])) $obj_rs['shop_dispatch_price'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_price'];
					if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_time'])) $obj_rs['extend']['arrivedelay'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_time'];
					if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'])) $obj_rs['shop_addprice'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'];
				}
				if($obj_rs['shop_order_time']<$todaytime) $obj_rs["shop_day_sold"] = 0;
				if($addprice == 1 && $obj_rs['shop_addprice']>0 ) continue;
				if(in_array($obj_rs['shop_id'] , $arr_collect_id)) {
					$obj_rs['iscollect'] = true;
				} else {
					$obj_rs['iscollect'] = false;
				}
				$obj_rs['len'] = 0;
				if(!empty($longitude)) {
					$len = cls_map::get_distance($longitude , $latitude, $obj_rs['shop_position_lng'] , $obj_rs['shop_position_lat'] , 2);
					$len = $len/1000;
					if($len>10000) $len = 10000;
					$obj_rs['len'] = fun_format::number($len);
				}
				if( $arr_return["isdistance"] ) {
					$len = intval($len*100);
					$arr_shoplist[$len][] = $obj_rs;
				} else {
					if(!isset($obj_rs['extend']["weekday"]) || !is_array($obj_rs['extend']["weekday"])) {
						print_r($obj_rs['extend']);exit;
					}
					if( ($obj_rs['shop_day_limit']>0 and $obj_rs['shop_day_sold']>=$obj_rs['shop_day_limit']) || $obj_rs['shop_state']==-1 || $arr_opentime['nowindex']<1 || (!empty($obj_rs['extend']['weekday']) && !in_array($weekday,$obj_rs['extend']['weekday']))) {
						$obj_rs['state'] = 0;
						if($obj_rs['shop_day_limit']>0 and $obj_rs['shop_day_sold']>=$obj_rs['shop_day_limit']) {
							$obj_rs['state_beta'] = "今日售完";
						} else if($obj_rs['shop_state']==-1) {
							$obj_rs['state_beta'] = "休息中";
						} else if($arr_opentime['havenext']==1) {
							$obj_rs['state_beta'] = "即将开始";
						} else {
							$obj_rs['state_beta'] = "明天继续";
						}
						$arr_list1[] = $obj_rs;
					} else {
						$obj_rs['state'] = 1;
						$arr_list2[] = $obj_rs;
					}
				}
			}

			if( $arr_return["isdistance"] ) {
				$arr_list = array();
				ksort($arr_shoplist);
				foreach($arr_shoplist as $key => $item) {
					foreach($item as $key1 => $obj_rs) {
						$arr_list[] = $obj_rs;
					}
				}
				$lngcount = count($arr_list);
				$arr_return['pageinfo']['pages'] = $lngcount/$lng_pagesize;
				if($arr_return['pageinfo']['pages'] != intval($arr_return['pageinfo']['pages'])) $arr_return['pageinfo']['pages'] = intval($arr_return['pageinfo']['pages']) + 1;
				if($page > $arr_return['pageinfo']['pages']) $page = $arr_return['pageinfo']['pages'];
				if($page<=0) $page = 1;
				$start = ($page - 1) * $lng_pagesize;
				$end = $start+$lng_pagesize;
				if($end>$lngcount) $end = $lngcount;
				for($i = $start ; $i < $end ; $i++) {
					$arr_return['list'][] = $arr_list[$i];
				}
				$arr_return['pageinfo']['page'] = $page;
				$arr_return['pageinfo']['total'] = $lngcount;
				$arr_return['pageinfo']['pagesize'] = $lng_pagesize;
			} else {
				$arr_list = array_merge($arr_list2,$arr_list1);
				$total = min($arr_return["pageinfo"]['offset']+$arr_return["pageinfo"]['pagesize'] , $arr_return['pageinfo']['total']);
				for($i = $arr_return["pageinfo"]['offset'] ; $i < $total ; $i++) {
					$arr_return['list'][] = $arr_list[$i];
				}
			}
			//if(!$iscollect) cls_cache::set($arr_return,$cache_key,"area_shop");
		}
		return $arr_return;
	}
	function get_nearby_shop() {
		$sortby = (int)fun_get::get("sortby");
		$sortval = (int)fun_get::get("sortval");
		$longitude = fun_get::get("longitude");
		$latitude = fun_get::get("latitude");
		if(empty($sortby)) $sortby = 4;
		if(empty($sortval)) $sortval = 1;
		$arr_return = array("list" => array() , "shopid" => array() , "pageinfo" => array( "pages" => 0 , "page" => 0 , "pagesize" => 20)  , "sortby" => $sortby , "sortval" => $sortval );
		$shoptype = fun_get::get("shoptype");
		$addprice = (int) fun_get::get("addprice");
		$sort = "";
		$arr_shop = $this->get_area_shop($this->area_id);
		if($iscollect) {
			$ids = empty($arr_collect_id) ? "0" : implode("," , $arr_collect_id);
		} else {
			$ids = (empty($arr_shop['id'])) ? "0" : implode("," , $arr_shop['id']);
		}
		//取排序
		if($sortby == 2) {
			$sort = " order by shop_sold";
		} else if($sortby == 3) {
			$sort = " order by shop_hits";
		} else if($sortby == 1) {
			$sort = " order by shop_id";
			$sortby = 1;
		} else {
			$sortby = 4;
		}
		if(!empty($sort)) {
			$sort .= ($sortval==1) ? " asc" : " desc";
		}
		$arr_where = array("(shop_state>0 or shop_state=-1) and shop_isdel=0");
		if($sortby == 4 && !empty($longitude) ) {
			$arr = cls_map::get_range($longitude , $latitude , 100);
			$arr_where[] = "shop_position_lat<=" . $arr['max_lat'] . " and shop_position_lat>=" . $arr['min_lat'] . " and shop_position_lng<=" . $arr['max_lng'] . " and shop_position_lng>=" . $arr['min_lng'];
		}
		if(!empty($shoptype)) $arr_where[] = "shop_type='" . $shoptype . "'";
		if($addprice == 1) $arr_where[] = "shop_addprice=0";
		$where = " where " . implode(" and " , $arr_where);
		//取收藏
		$arr_collect_id = array();
		if(cls_obj::get('cls_user')->is_login()) {
			$obj_result = cls_obj::db()->select("select collect_for_id from " . cls_config::DB_PRE . "meal_collect where collect_user_id='" . cls_obj::get('cls_user')->uid . "' and collect_type=1");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				$arr_collect_id[] = $obj_rs['collect_for_id'];
			}
		}
		$arr_shop = array();
		$pagesize = (int)fun_get::get('pagesize');
		if(empty($pagesize)) $pagesize = (int)cls_config::get("index_pagesize","view");
		if(empty($pagesize)) $pagesize = 20;
		$page = (int)fun_get::get("page");
		$arr_return["pageinfo"] = cls_obj::db()->get_pageinfo(cls_config::DB_PRE."meal_shop" , $where , $page , $pagesize);
		$limit = ($sortby == 4 && !empty($longitude)) ? " limit 0,1000" : $arr_return['pageinfo']['limit'];
		$obj_result = cls_obj::db()->select("select shop_name,shop_id,shop_state,shop_mode,shop_hits,shop_dispatch_price,shop_support_score,shop_pic_small,shop_tj,shop_extend,shop_addprice,shop_comment,shop_commentnum,shop_day_limit,shop_day_sold,shop_order_time,shop_position_lng,shop_position_lat from " . cls_config::DB_PRE . "meal_shop" . $where . $sort . $limit);
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$obj_rs['commentrate'] = $obj_rs['shop_comment']/5*100;
			$obj_rs["shop_dispatch_price"] = (float)$obj_rs['shop_dispatch_price'];
			$obj_rs["extend"] = (!empty($obj_rs['shop_extend'])) ? unserialize($obj_rs["shop_extend"]) : array();
			$obj_rs['extend']['weekday'] = empty($obj_rs['extend']['weekday']) ?  array() : explode("," , $obj_rs['extend']['weekday']);
			$obj_rs['shop_pic_small'] = fun_get::html_url($obj_rs['shop_pic_small']);
			$arr_return['shopid'][] = $obj_rs['shop_id'];
			if($addprice == 1 && $obj_rs['shop_addprice']>0 ) continue;
			$len = cls_map::get_distance($longitude , $latitude, $obj_rs['shop_position_lng'] , $obj_rs['shop_position_lat'] , 2);
			$obj_rs['len'] = number_format($len/1000,2);
			$len = intval($len*100);
			if(empty($obj_rs['shop_pic_small'])) $obj_rs['shop_pic_small'] = $obj_rs['shop_pic'];
			if(!in_array($obj_rs['shop_id'] , $arr_return['shopid'])) $arr_return['shopid'][] = $obj_rs['shop_id'];
			if(in_array($obj_rs['shop_id'] , $arr_collect_id)) {
				$obj_rs['iscollect'] = true;
			} else {
				$obj_rs['iscollect'] = false;
			}
			if($sortby == 4) {
				$arr_shop[$len][] = $obj_rs;
			} else {
				$arr_return["list"][] = $obj_rs;
			}
		}
		if($sortby == 4) {
			$arr_list = array();
			ksort($arr_shop);
			foreach($arr_shop as $key => $item) {
				foreach($item as $key1 => $obj_rs) {
					$arr_list[] = $obj_rs;
				}
			}
			$lngcount = count($arr_list);
			$arr_return['pageinfo']['pages'] = $lngcount/$pagesize;
			if($arr_return['pageinfo']['pages'] != intval($arr_return['pageinfo']['pages'])) $arr_return['pageinfo']['pages'] = intval($arr_return['pageinfo']['pages']) + 1;
			if($page > $arr_return['pageinfo']['pages']) $page = $arr_return['pageinfo']['pages'];
			if($page<=0) $page = 1;
			$start = ($page - 1) * $pagesize;
			$end = $start+$pagesize;
			if($end>$lngcount) $end = $lngcount;
			for($i = $start ; $i < $end ; $i++) {
				$arr_return['list'][] = $arr_list[$i];
			}
			$arr_return['pageinfo']['page'] = $page;
			$arr_return['pageinfo']['total'] = $lngcount;
			$arr_return['pageinfo']['pagesize'] = $pagesize;
		}
		return $arr_return;
	}
	//取收藏与推荐的店铺
	function get_shop_collect_tj() {
		//收藏的店铺
		$arr_return = array("collect" => array() , "tj" => array() , 'shopid' => array() );
		$obj_db = cls_obj::db();
		$arr_collect_id = array();
		$obj_result = $obj_db->select("select collect_for_id from " . cls_config::DB_PRE . "meal_collect where collect_user_id='" . cls_obj::get('cls_user')->uid . "' and collect_type=1");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_collect_id[] = $obj_rs['collect_for_id'];
		}
		$arr_shop = $this->get_area_shop($this->area_id);
		$ids = implode("," , $arr_collect_id);
		if(!empty($ids)) {
			$where = " where (shop_state>0 or shop_state=-1) and shop_isdel=0 and shop_id in(" . $ids . ")";
			$obj_result = $obj_db->select("select shop_name,shop_id,shop_state,shop_mode,shop_hits,shop_dispatch_price,shop_support_score,shop_pic_small,shop_tj,shop_extend,shop_addprice,shop_comment from " . cls_config::DB_PRE . "meal_shop" . $where);
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$obj_rs['commentrate'] = $obj_rs['shop_comment']/5*100;
				$obj_rs["shop_dispatch_price"] = (float)$obj_rs['shop_dispatch_price'];
				$obj_rs["extend"] = (!empty($obj_rs['shop_extend'])) ? unserialize($obj_rs["shop_extend"]) : array();
				$obj_rs['shop_pic_small'] = fun_get::html_url($obj_rs['shop_pic_small']);
				$obj_rs['iscollect'] = true;
				if(isset($arr_shop['list']['id_' . $obj_rs['shop_id']])) {
					if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_price'])) $obj_rs['shop_dispatch_price'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_price'];
					if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_time'])) $obj_rs['extend']['arrivedelay'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_time'];
					if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'])) $obj_rs['shop_addprice'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'];
				}
				$arr_return['collect'][] = $obj_rs;
				$arr_return['shopid'][] = $obj_rs['shop_id'];
			}
		}
		//推荐
		$arr_shopid = $arr_shop['id'];
		$ids = (empty($arr_shopid)) ? "0" : implode("," , $arr_shopid);
		$where = " where (shop_state>0 or shop_state=-1) and shop_isdel=0 and shop_id in(" . $ids . ")";
		$obj_result = $obj_db->select("select shop_name,shop_id,shop_state,shop_mode,shop_hits,shop_dispatch_price,shop_support_score,shop_pic_small,shop_tj,shop_extend,shop_addprice,shop_comment from " . cls_config::DB_PRE . "meal_shop" . $where . " and shop_tj>0 order by shop_tj");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['commentrate'] = $obj_rs['shop_comment']/5*100;
			$obj_rs["shop_dispatch_price"] = (float)$obj_rs['shop_dispatch_price'];
			$obj_rs["extend"] = (!empty($obj_rs['shop_extend'])) ? unserialize($obj_rs["shop_extend"]) : array();
			$obj_rs['shop_pic_small'] = fun_get::html_url($obj_rs['shop_pic_small']);
			if(in_array($obj_rs['shop_id'] , $arr_collect_id)) {
				$obj_rs['iscollect'] = true;
			} else {
				$obj_rs['iscollect'] = false;
			}
			if(isset($arr_shop['list']['id_' . $obj_rs['shop_id']])) {
				if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_price'])) $obj_rs['shop_dispatch_price'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_price'];
				if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_time'])) $obj_rs['extend']['arrivedelay'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_time'];
				if(!empty($arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'])) $obj_rs['shop_addprice'] = $arr_shop['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'];
			}
			$arr_return['shopid'][] = $obj_rs['shop_id'];
			$arr_return["tj"][] = $obj_rs;
		}
		return $arr_return;
	}
	//取店铺的活动信息
	function get_shop_act_info($arr_shopid , $is_index = false) {
		$arr_return = $arr_act = array();
		if(!empty($arr_shopid)) {
			$ids = (is_array($arr_shopid)) ? implode("," , $arr_shopid) : $arr_shopid;
			if(empty($ids)) return array();
			$obj_db = cls_obj::db();
			$arr_act = array();
			$obj_result = $obj_db->select("select act_name,act_shop_id,act_method from " . cls_config::DB_PRE . "meal_act where act_shop_id in(" . $ids . ") and act_state>0 and act_isdel=0 and act_starttime<='" . date("Y-m-d H:i") . "' and act_endtime>='" . date("Y-m-d H:i") . "' order by act_method");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_return['act_' . $obj_rs['act_shop_id']][] = $obj_rs;
				$type = $obj_rs['act_method'];
				if($type==4) {
					$type = 3;
				} else if($type==6) {
					$type = 5;
				}
				if(isset($arr_act['id_' . $obj_rs['act_shop_id']]['id_'.$type])) {
					$arr_act['id_' . $obj_rs['act_shop_id']]['id_'.$type]['title'] .= "&#10;" . $obj_rs['act_name'];
				} else {
					$arr_act['id_' . $obj_rs['act_shop_id']]['id_'.$type] = array('id'=>$type , 'title' => $obj_rs['act_name']);
				}
			}
		}
		if($is_index) {
			$arr_return['list'] = $arr_return;
			$arr_return['method'] = $arr_act;
		}
		return $arr_return;
	}
	//取指定店铺的活动信息
	function get_shop_cart_act($shop_id) {
		$arr_return = array();
		$date = date("Y-m-d H:i:s");
		$i = 1;
		$orderone_shop = $orderone = -1;
		$arr_where = $arr_num_where = $arr_time_num_where = array();
		$obj_result = cls_obj::db()->select("select act_id,act_name,act_where,act_method,act_where_val,act_method_val,act_shop_id from " . cls_config::DB_PRE . "meal_act where act_isdel=0 and act_state>0 and (act_shop_id='" . $shop_id . "' or act_shop_id=0) and act_starttime<='" . $date . "' and act_endtime>='" . $date . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$obj_rs["index"] = $i;
			$i++;
			if($obj_rs['act_where']==1) {//大于指定金额
				$obj_rs['where_val'] = (float)$obj_rs['act_where_val'];
				$arr_return[] = $obj_rs;
			} else if($obj_rs['act_where']==2) {//指定时间
				$arr = explode("," , $obj_rs['act_where_val']);
				$time1 = (int)$arr[0];
				$time2 = (count($arr)>1) ? (int)$arr[1] : 0;
				$time = (int)date("Hi");
				if($time>=$time1 && $time<$time2) {
					$x = (int)substr($time,0,-2);
					$x1 = (int)substr($time2,0,-2);
					if($x == $x1) {
						$x = (int)substr($time,-2);
						$x1 = (int)substr($time2,-2);
						$obj_rs['time'] = ($x1-$x)*60*1000;
					} else {
						$obj_rs['time'] = ($x1-$x)*60*60*1000;
					}
					$arr_return[] = $obj_rs;
				}
			} else if($obj_rs['act_where']==3) {//达到指定数量
				$obj_rs['where_val'] = (int)$obj_rs['act_where_val'];
				$arr_return[] = $obj_rs;
			} else if($obj_rs['act_where']==4) {//指定时间，达到指定数量
				$arr = explode("," , $obj_rs['act_where_val']);
				$time1 = (int)$arr[0];
				$time2 = (count($arr)>1) ? (int)$arr[1] : 0;
				$obj_rs['where_val'] = $lng_num = (count($arr)>2) ? (int)$arr[2] : 0;
				$time = (int)date("Hi");
				if($time>=$time1 && $time<$time2) {
					$x = (int)substr($time,0,-2);
					$x1 = (int)substr($time2,0,-2);
					if($x == $x1) {
						$x = (int)substr($time,-2);
						$x1 = (int)substr($time2,-2);
						$obj_rs['time'] = ($x1-$x)*60*1000;
					} else {
						$obj_rs['time'] = ($x1-$x)*60*60*1000;
					}
					$arr_return[] = $obj_rs;
				}
			} else if($obj_rs['act_where']==5 || $obj_rs['act_where']==7) {//任意条件
				$obj_rs['where_val'] = "";
				$arr_return[] = $obj_rs;
			} else if($obj_rs['act_where']==6) {//首次下单
				if(cls_obj::get("cls_user")->uid>0) {
					if($orderone == -1) {
						$obj_shopone = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_shop_id='" . $shop_id . "' and order_user_id='" . cls_obj::get("cls_user")->uid . "' and (order_state>=0 or order_state=-2)");
						if(empty($obj_shopone)) {
							$orderone_shop = 1;
							$obj_one = cls_obj::db()->get_one("select order_id from " . cls_config::DB_PRE . "meal_order where order_user_id='" . cls_obj::get("cls_user")->uid . "' and (order_state>=0 or order_state=-2)");
							$orderone = empty($obj_one) ? 1 : 0;
						} else {
							$orderone_shop = 0;
							$orderone = 0;
						}
					}
					if(($obj_rs['act_shop_id']==0 && $orderone==1) || ($obj_rs['act_shop_id']==$shop_id && $orderone_shop==1)) $arr_return[] = $obj_rs;
				}
			}
		}
		return $arr_return;
	}
	//取最近光顾过的店铺信息
	function get_shop_history($uid) {
		if(empty($uid)) return array('list'=>array() , 'shopid'=>array() );
		$obj_db = cls_obj::db();
		$arr_return = array('list'=>array() , 'shopid'=>array() );
		$arr_shopid = $arr_shop = $arr_collect_id = array();
		$obj_result = $obj_db->select("select collect_for_id from " . cls_config::DB_PRE . "meal_collect where collect_user_id='" . $uid . "' and collect_type=1");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_collect_id[] = $obj_rs['collect_for_id'];
		}
		$obj_result = $obj_db->select("select order_shop_id,max(order_addtime) as addtime from " . cls_config::DB_PRE . "meal_order where order_user_id='" . $uid . "' group by order_shop_id order by addtime");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_shopid[] = $obj_rs['order_shop_id'];
		}
		$ids = implode(',',$arr_shopid);
		$arr_shop_dispatch = $this->get_area_shop($this->area_id);
		if(empty($ids)) $ids = '0';
		$obj_result = $obj_db->select("select shop_name,shop_id,shop_state,shop_mode,shop_hits,shop_dispatch_price,shop_support_score,shop_pic_small,shop_tj,shop_extend,shop_addprice,shop_comment from " . cls_config::DB_PRE . "meal_shop where shop_id in(" . $ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['commentrate'] = $obj_rs['shop_comment']/5*100;
			$obj_rs["shop_dispatch_price"] = (float)$obj_rs['shop_dispatch_price'];
			$obj_rs["extend"] = (!empty($obj_rs['shop_extend'])) ? unserialize($obj_rs["shop_extend"]) : array();
			$obj_rs['shop_pic_small'] = fun_get::html_url($obj_rs['shop_pic_small']);
			if(in_array($obj_rs['shop_id'] , $arr_collect_id)) {
				$obj_rs['iscollect'] = true;
			} else {
				$obj_rs['iscollect'] = false;
			}
			if(!empty($arr_shop_dispatch['list']['id_' . $obj_rs['shop_id']]['dispatch_price'])) $obj_rs['shop_dispatch_price'] = $arr_shop_dispatch['list']['id_' . $obj_rs['shop_id']]['dispatch_price'];
			if(!empty($arr_shop_dispatch['list']['id_' . $obj_rs['shop_id']]['dispatch_time'])) $obj_rs['extend']['arrivedelay'] = $arr_shop_dispatch['list']['id_' . $obj_rs['shop_id']]['dispatch_time'];
			if(!empty($arr_shop_dispatch['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'])) $obj_rs['shop_addprice'] = $arr_shop_dispatch['list']['id_' . $obj_rs['shop_id']]['dispatch_addprice'];
			$arr_shop['id_' . $obj_rs['shop_id']] = $obj_rs;
			$arr_return['shopid'][] = $obj_rs['shop_id'];
		}
		foreach($arr_shopid as $item) {
			if(isset($arr_shop['id_' . $item])) $arr_return['list'][] = $arr_shop['id_' . $item];
		}
		return $arr_return;
	}
	/* 获取指店铺信息
	 *
	 */
	function get_shopinfo($shop_id, $arr_add_fields = array()) {
		$arr_shop = $this->get_area_shop($this->area_id);
		$arr = explode("," , 'shop_name,shop_id,shop_user_id,shop_extend,shop_linkname,shop_linktel,shop_tel,shop_day_sold,shop_address,shop_pic,shop_desc,shop_oneminleast,shop_dispatch_price,shop_mode,shop_support_score,shop_state,shop_addprice,shop_pic_small,shop_comment,shop_day_limit,shop_day_sold,shop_order_time,shop_close_tips,shop_tips,shop_service,shop_reserve_money,shop_reserve_time');
		$arr = array_merge($arr , $arr_add_fields);
		$arr = array_unique($arr);
		$fields = implode("," , $arr);
		$obj_shop = cls_obj::db()->get_one("select " . $fields . " from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $shop_id . "'");
		if(empty($obj_shop)) return array();
		$obj_shop['commentrate'] = $obj_shop['shop_comment']/5*100;
		if($obj_shop['shop_order_time']<strtotime(date("Y-m-d"))) $obj_shop["shop_day_sold"] = 0;
		if(!empty($obj_shop['shop_extend']))  {
			$obj_shop["extend"] = unserialize($obj_shop["shop_extend"]);
		} else {
			$obj_shop["extend"] = array("opentime" => "" , "arrivetime" => "" , "arrivedelay" => "" , "arr_opentime" => array() , "arr_arrivetime" => array() );
		}
		$obj_shop['shop_close_tips'] = fun_get::filter($obj_shop['shop_close_tips'],true);
		$obj_shop['extend']['weekday'] = empty($obj_shop['extend']['weekday']) ?  array() : explode("," , $obj_shop['extend']['weekday']);
		$obj_shop['extend']['weekdayshow'] = "";
		if(!empty($obj_shop['extend']['weekday'])) {
			$arr = array("天","一","二","三","四","五","六");
			$arrx = array();
			foreach($obj_shop['extend']['weekday'] as $item) {
				$arrx[] = $arr[$item];
			}
			$obj_shop['extend']['weekdayshow'] = implode("," , $arrx);
		}
		//评论数
		$obj_rs = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order_comment where comment_shop_id='" . $shop_id . "'");
		$obj_shop['commentnum'] = (empty($obj_rs)) ? 0 : $obj_rs['num'];
		//菜品数
		$obj_rs = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu where menu_shop_id='" . $shop_id . "' and menu_state>0 and menu_isdel=0 and menu_about_id<1");
		$obj_shop['menunum'] = (empty($obj_rs)) ? 0 : $obj_rs['num'];
		//是否收藏
		$obj_rs = cls_obj::db()->get_one("select collect_id from " . cls_config::DB_PRE . "meal_collect where collect_for_id='" . $shop_id . "' and collect_user_id='" . cls_obj::get("cls_user")->uid . "' and collect_type=1");
		$obj_shop['iscollect'] = (empty($obj_rs)) ? 0 : 1;
		$obj_shop['shop_pic_small'] = fun_get::html_url($obj_shop['shop_pic_small']);
		$obj_shop['shop_pic'] = fun_get::html_url($obj_shop['shop_pic']);
		if(!empty($arr_shop['list']['id_' . $obj_shop['shop_id']]['dispatch_price'])) $obj_shop['shop_dispatch_price'] = $arr_shop['list']['id_' . $obj_shop['shop_id']]['dispatch_price'];
		if(!empty($arr_shop['list']['id_' . $obj_shop['shop_id']]['dispatch_time'])) $obj_shop['extend']['arrivedelay'] = $arr_shop['list']['id_' . $obj_shop['shop_id']]['dispatch_time'];
		if(!empty($arr_shop['list']['id_' . $obj_shop['shop_id']]['dispatch_addprice'])) $obj_shop['shop_addprice'] = $arr_shop['list']['id_' . $obj_shop['shop_id']]['dispatch_addprice'];
		if(isset($obj_shop['shop_intro'])) $obj_shop['shop_intro'] = fun_get::filter($obj_shop['shop_intro'] , true);
		if($obj_shop['shop_order_time']<strtotime(date("Y-m-d"))) $obj_shop['shop_order_time'] = 0;
		$obj_rs = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_collect where collect_for_id='" . $shop_id . "' and collect_type=1");
		$obj_shop['collectnum'] = (empty($obj_rs)) ? 0 : $obj_rs['num'];
		return $obj_shop;
	}
	/* 获取店铺菜品列表，按分类分组
	 * 
	 */
	function get_menu_list($shop_id , $index_group = 'price' , $sort = '' ,$isdefault = 0,$shop_type = 0){
		if(empty($index_group)) $index_group = 'price';
		$arr_menu = $arr_cart = $arr_cart_id = $arr_menu_id = array();
		$arr_return = array("list"=>array(),"tj"=>array(),'price'=>array() , "group" => array() ,"count" => 0);
		$where = $this->get_menu_today_where($shop_id);
		$type = (int)fun_get::get("type");
		if(empty($type)) {
			if($shop_type == 0 || $shop_type == 3) {
				$where .= " and menu_type!=2";
				$arr_return['type'] = 1;
			} else {
				$arr_return['type'] = 2;
				$where .= " and menu_type!=1";
			}
		} else {
			if($type == 2) {
				$arr_return['type'] = $type;
				$where .= " and menu_type!=1";
			} else {
				$arr_return['type'] = 1;
				$where .= " and menu_type!=2";
			}
		}
		$obj_db = cls_obj::db();
		$arr = $this->get_cart_info();
		$shop_idx = ($arr_return['type']==1) ? $shop_id : $shop_id . "-".$arr_return['type'];
		if(isset($arr["shop_" . $shop_idx])) {
			$arr_cart = $arr["shop_" . $shop_idx]['cart'];
			$arr_cart_id = $arr["shop_" . $shop_idx]['ids'];
		}
		$arr_group_id = $arr_price = $arr_price_list = array();
		$arr_group_id = array();
		//当前星期值
		$weekday = date("w");
		$day = (int)date("d");
		if(!empty($sort)) {
			$sortby = $sort;
		} else {
			$sortby = "b.group_sort,a.menu_sort";
		}
		$arr_attribute = tab_meal_menu::get_perms("attribute");
		$obj_result = $obj_db->select("select menu_id,menu_type,menu_intro,menu_group_id,menu_title,menu_price,menu_comment_num,menu_sold,menu_tj,menu_pic,menu_pic_small,menu_num,menu_attribute,menu_mode,menu_holiday,menu_weekday,menu_mode,menu_weekday,group_id,group_name,group_mode,menu_date,menu_sold_time,menu_sold_today,menu_comment from " . cls_config::DB_PRE . "meal_menu a left join " . cls_config::DB_PRE . "meal_menu_group b on a.menu_group_id=b.group_id " . $where ." order by " . $sortby);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs["group_id"])) {
				$obj_rs["group_id"] = -1;
				$obj_rs["group_name"] = "默认";
			}
			$obj_rs['attribute'] = array_search($obj_rs['menu_attribute'] , $arr_attribute);
			//如果是按星期上，不在范围内就排除
			if($obj_rs["menu_mode"]==1 && !stristr("," . $obj_rs["menu_weekday"] . "," , ",".$weekday.",")) continue;
			//如果是按日期上，不在范围内就排除
			if($obj_rs["menu_mode"]==3 && !stristr("," . $obj_rs["menu_date"] . "," , ",".$day."," )) continue;
			$obj_rs['commentrate'] = $obj_rs['menu_comment']/5*100;
			if(empty($obj_rs["menu_pic_small"])) $obj_rs["menu_pic_small"] = $obj_rs["menu_pic"];
			if(empty($obj_rs["menu_pic"])) $obj_rs["menu_pic"] = $obj_rs["menu_pic_small"];
			$obj_rs["menu_pic_small"] = fun_get::html_url($obj_rs["menu_pic_small"]);
			$obj_rs["menu_pic"] = fun_get::html_url($obj_rs["menu_pic"]);
			$obj_rs["menu_price"] = (float)$obj_rs["menu_price"];
			$arr = explode("." , $obj_rs["menu_price"]);
			$obj_rs["price_int"] = $arr[0];
			$obj_rs["price_float"] = isset($arr[1]) ? $arr[1] : '00';
			$obj_rs["menu_price"] = (float)$obj_rs["menu_price"] . "";
			//当前数量
			if(empty($obj_rs['menu_num'])) {
				$obj_rs['num'] = 999;
			} else {
				$obj_rs['num'] = ($obj_rs['menu_sold_time']> strtotime(date("Y-m-d")) ) ? $obj_rs['menu_num']-$obj_rs['menu_sold_today'] : $obj_rs['menu_num'];
			}
			if(empty($sort) || $isdefault!=0) {
				if($index_group == 'group') {
					$arr_return["list"]["group_" . $obj_rs["group_id"]]["list"][] = $obj_rs;
				}
				$arr_price_list[$obj_rs["menu_price"]][] = $obj_rs;
				//初始购物车时用到
				if(in_array($obj_rs["menu_id"] , $arr_cart_id)) $arr_menu["id_". $obj_rs["menu_id"]] = $obj_rs;
				if(!in_array( $obj_rs["group_id"] , $arr_group_id) ) {
					if($index_group == 'group') {
						$arr_return["list"]["group_" . $obj_rs["group_id"]]["id"] = $obj_rs["group_id"];
						$arr_return["list"]["group_" . $obj_rs["group_id"]]["name"] = $obj_rs["group_name"];
						$arr_return["list"]["group_" . $obj_rs["group_id"]]["mode"] = $obj_rs["group_mode"];
					}
					$arr_group_id[] = $obj_rs["group_id"];
					if(!empty($obj_rs["group_name"])) $arr_return['group']['id_' . $obj_rs['group_id']] = array("id"=>$obj_rs['group_id'] , "name" => $obj_rs['group_name'] , "num" => 1 , 'mode' => $obj_rs['group_mode']);
				} else if(isset($arr_return['group']['id_' . $obj_rs['group_id']])) {
					$arr_return['group']['id_' . $obj_rs['group_id']]['num']++;
				}
				if(!empty($obj_rs['menu_tj'])) {
					$arr_return['tj'][] = $obj_rs;
				}
			} else {
				$arr_return["list"][] = $obj_rs;
			}
			$arr_menu_id[] = $obj_rs['menu_id'];
			$arr_return['count']++;
		}
		$arr_return['abount_menu'] = array();
		$arr_return['abount_cart'] = array();
		if(empty($sort) || $isdefault!=0 ) {
			if(stristr($sort , "price desc")) {
				krsort($arr_price_list);
			} else {
				ksort($arr_price_list);
			}
			foreach($arr_price_list as $item => $key) {
				if($index_group == 'price') {
					$arr_return["list"]["group_" . $item]["id"] = $item;
					$arr_return["list"]["group_" . $item]["name"] = cls_config::get("coinsign" , "sys") . $item;
					$arr_return["list"]["group_" . $item]["mode"] = $key[0]['group_mode'];
					$arr_return["list"]["group_" . $item]["list"] = $key;
				}
				if(!in_array( $item , $arr_price) ) {
					$arr_return['price'][] = array("id"=>$item,"name"=>cls_config::get("coinsign" , "sys") . $item);
					$arr_price[] = $item;
				}
			}
		}
		$arr_sold = array();
		if(!empty($arr_menu_id)) {
			$ids = implode("," , $arr_menu_id);
			$obj_result = $obj_db->select("select menu_id,menu_num,menu_price,menu_title,menu_about_name,menu_about_id,menu_sold from " . cls_config::DB_PRE . "meal_menu where menu_about_id in(" . $ids . ") and menu_isdel=0 and menu_state>0");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_return['abount_menu']['id_'. $obj_rs['menu_about_id']][] = $obj_rs;
				if(in_array($obj_rs['menu_id'] , $arr_cart_id)) $arr_return['abount_cart']['id_'.$obj_rs['menu_id']] = $obj_rs;
				if(!isset($arr_sold['id_'.$obj_rs['menu_about_id']])) $arr_sold['id_'.$obj_rs['menu_about_id']] = 0;
				$arr_sold['id_'.$obj_rs['menu_about_id']] += $obj_rs['menu_sold'];
			}
		}
		$arr_return['about_sold'] = $arr_sold;
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
		$arr = $arr_cart["menu_ids"];
		$menuids = array();
		foreach($arr as $item) {
			if(empty($item) || !is_numeric($item) ) continue;
			$menuids[] = $item;
		}
		$menuids = implode(",",$menuids);
		$shop_id = fun_get::get("shop_id");
		if(!empty($shop_id)) {
			(in_array($shop_id , $arr_cart['shop_ids']))? $shopids = $shop_id : $shopids = '0';
		}
		$shopids_x = $shopids;
		$arr_shopids_x = explode(",",$shopids_x);
		$arr = explode(",",$shopids);
		$arrx = array();
		foreach($arr as $item) {
			$arry = explode('-',$item);
			$arry[0] = (int)$arry[0];
			if(!empty($arry[0])) $arrx[] = $arry[0];
		}
		$shopids = implode("," , $arrx);
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
		$arr_weekday = array();
		//取店铺信息
		if(!empty($shopids)) {
			$ii = 1;
			$obj_result = $obj_db->select("select shop_id,shop_name,shop_dispatch_price,shop_oneminleast,shop_verifytel,shop_addprice,shop_support_score,shop_mode,shop_extend,shop_ticket from " . cls_config::DB_PRE . "meal_shop where shop_id in(".$shopids.")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(!empty($obj_rs["shop_extend"])) {
					$arr = unserialize($obj_rs["shop_extend"]);
					$obj_rs["shop_extend"] = $arr;
					if(isset($arr['weekday'])) $arr_weekday = $arr['weekday'];
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
				if(in_array($obj_rs["shop_id"],$arr_shopids_x)) {
					$obj_rs["index"] = $ii;
					$arr_shop["id_" . $obj_rs["shop_id"]]['info'] = $obj_rs;
					$arr_shop["id_" . $obj_rs["shop_id"]]['price'] = 0;
					foreach($arr_cart['shop_'.$obj_rs["shop_id"]]['cart'] as $item) {
						$arr = array();
						$arr['index'] = $jj;
						$arr['ids'] = $item;
						$arr['price'] = 0;
						foreach($item as $menu) {
							$arr['price'] += (isset($arr_menu["id_" . $menu])) ? $arr_menu["id_" . $menu]['menu_price'] : 0;
						}
						$num++;
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
						$arr_shop["id_" . $obj_rs["shop_id"]]['price'] += $arr['price'];
					}
					$arr_shop["id_" . $obj_rs["shop_id"]]['cart'] = $arr_1;
					$arr_shop["id_" . $obj_rs["shop_id"]]['num'] = $num;
					$ii++;
				}
				$arr_1 = array();
				$jj = 1;
				$num = 0;
				if(in_array($obj_rs["shop_id"].'-2',$arr_shopids_x)) {
					$obj_rs["index"] = $ii;
					$obj_rs['shop_id'] = $obj_rs['shop_id'] . '-2';
					$arr_shop["id_" . $obj_rs["shop_id"]]['info'] = $obj_rs;
					$arr_shop["id_" . $obj_rs["shop_id"]]['price'] = 0;
					foreach($arr_cart['shop_'.$obj_rs["shop_id"]]['cart'] as $item) {
						$arr = array();
						$arr['index'] = $jj;
						$arr['ids'] = $item;
						$arr['price'] = 0;
						foreach($item as $menu) {
							$arr['price'] += (isset($arr_menu["id_" . $menu])) ? $arr_menu["id_" . $menu]['menu_price'] : 0;
						}
						$num++;
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
						$arr_shop["id_" . $obj_rs["shop_id"]]['price'] += $arr['price'];
					}
					$arr_shop["id_" . $obj_rs["shop_id"]]['cart'] = $arr_1;
					$arr_shop["id_" . $obj_rs["shop_id"]]['num'] = $num;
					$ii++;
				}
			}
		}

		//发票
		$arr_ticket = array("0"=>"暂不提供");
		if($ticket) {
			$arr_ticket = cls_config::get("ticket_list","meal");
		}
		$arrivetime = $this->get_arrive_time($arr_arrive , $lng_delay);
		$arr_return =  array("cart"=>$arr_cart , "shop"=>$arr_shop , "menu"=>$arr_menu , "shopids"=>$shopids_x , "arrivetime" => $arrivetime , "ticket" => $arr_ticket , "arrive" => $arr_arrive);
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
		$date_i = (int)date('i');
		$lng_time = date("Hi")+$delay;
		if($date_i+$delay > 60) {
			$lng_time += 40;
		}
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
	function get_area($shopid) {
		$arr_return = array("default" => array() , "depth" => 1);
		$obj_db = cls_obj::db();
		$obj_result = $obj_db->select("select area_id as 'id',area_name as 'name',area_pid as 'pid',dispatch_area_id as 'a_id',area_depth as 'depth',area_pids as 'pids',area_val as 'val',dispatch_price as 'price',dispatch_addprice as 'addprice',dispatch_time as 'time' from " . cls_config::DB_PRE . "meal_dispatch a left join " . cls_config::DB_PRE . "sys_area b on " . $obj_db->concat("," , 'b.area_pids' , ',') . " like " . $obj_db->concat("%," , "a.dispatch_area_id" , ",%") . " where b.area_id>0 and a.dispatch_shop_id='" . $shopid . "' order by area_pid,area_jian");
		$depth = 0;
		$this_pids = $arr_area = $arr_list = array();
		$arr_str = array();
		$pid = 0;
		$depth_min = 100;
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs["name"])) $obj_rs["name"] = $obj_rs["val"];
			if(empty($obj_rs["val"])) $obj_rs["val"] = $obj_rs["name"];
			if($obj_rs['val'] == $obj_rs['name']) unset($obj_rs['val']);

			$arr_list["id_" . $obj_rs["pid"]][] = $obj_rs["id"];
			$arr_area["id_" . $obj_rs["id"]] = $obj_rs;
			if($obj_rs['depth'] > $depth) $depth = $obj_rs['depth'];
			if($obj_rs['depth'] < $depth_min) {
				$depth_min = $obj_rs['depth'];
				$pid = $obj_rs['pid'];
			}
			$arr = explode(',',$obj_rs['pids']);
			if($obj_rs['a_id'] == $obj_rs['id'] ) {
				if(!empty($arr)) unset($arr[count($arr)-1]);
				$ids = implode("," , $arr);
				if(!in_array($ids , $arr_str)) $arr_str[$ids] = $arr;
			}
			if($obj_rs['id'] == $this->area_id) {
				$this_pids = explode("," , $obj_rs['pids']);
			}
		}
		$arr_pids = array();
		$maxkey = 0;
		for($ii = 0; $ii<10 ; $ii++) {
			$arr = array();
			foreach($arr_str as $item => $key) {
				if(isset($key[$ii]) && !in_array($key[$ii] , $arr)) $arr[] = $key[$ii];
				if(count($key)>$maxkey) $maxkey = count($key);
			}
			if(count($arr)>1 || (isset($key[$ii]) && $pid == $key[$ii]) || $pid == 0) {
				foreach($arr_str as $item => $key) {
					for($i = $ii ; $i < count($key) ; $i++) {
						$arr_pids[] = $key[$i];
					}
				}
				$arr_pids = array_unique($arr_pids);
				break;
			} else if($maxkey == $ii+1 ) {
				$arr_pids = $arr;
				break;
			} else if(empty($arr)) {
				break;
			}
			if(isset($this_pids[$ii])) unset($this_pids[$ii]);
		}
		$ids = implode("," , $arr_pids);
		if(!empty($ids)) {
			$area_depth = 10;
			$obj_result = $obj_db->select("select area_id as 'id',area_name as 'name',area_pid as 'pid',area_depth as 'depth',area_pids as 'pids',area_val as 'val' from " . cls_config::DB_PRE . "sys_area where area_id in(" . $ids . ") order by area_pid,area_jian");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(empty($obj_rs["name"])) $obj_rs["name"] = $obj_rs["val"];
				if(empty($obj_rs["val"])) $obj_rs["val"] = $obj_rs["name"];
				if($obj_rs['val'] == $obj_rs['name']) unset($obj_rs['val']);
				$obj_rs['price'] = 0;
				$obj_rs['time'] = 0;
				$arr_list["id_" . $obj_rs["pid"]][] = $obj_rs["id"];
				$arr_area["id_" . $obj_rs["id"]] = $obj_rs;
				//if($obj_rs["area_pid"] == $pid) $arr_return["default"][] = $obj_rs;
				if($obj_rs['depth']<$area_depth) {
					$pid = $obj_rs['pid'];
					$area_depth = $obj_rs['depth'];
				}
			}
		}
		if(isset($arr_list['id_'.$pid])) {
			foreach($arr_list['id_' . $pid] as $item) {
				if(isset($arr_area["id_" . $item])) $arr_return["default"][] = $arr_area["id_" . $item];
			}
		}
		if(count($arr_return["default"])>0) {
			$arr_return["depth"] = $depth - $arr_return["default"][0]["depth"] + 1;
		}
		$arr_return["pids"] = implode(",", $this_pids);
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
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],5);
		return $arr_return;
	}

	function get_comment_shop_list($id) {
		$lng_pagesize = 10;
		$arr_return = array("list" => array() , "pagebtns" => "");
		$obj_db = cls_obj::db();
		$lng_page = (int)fun_get::get("page");
		$str_where = " where comment_shop_id='" . $id . "'";
		//取分页信息
		$arr_return["list"] = array();
		$arr_uid = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_order_comment" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT comment_addtime,comment_val,comment_user_id,comment_beta,user_experience,comment_pic,user_netname,comment_recont,comment_re_time FROM ".cls_config::DB_PRE."meal_order_comment a left join " . cls_config::DB_PRE . "sys_user b on a.comment_user_id=b.user_id" . $str_where . " order by comment_id desc" . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['val'] = $obj_rs['comment_val']/5*100;
			$obj_rs['addtime'] = date("Y-m-d H:i" , $obj_rs['comment_addtime']);
			$obj_rs['retime'] = empty($obj_rs['comment_re_time']) ? '' : date("Y-m-d H:i" , $obj_rs['comment_re_time']);
			$obj_rs['user_name'] = $obj_rs['user_netname'];
			$obj_rs['level'] = tab_sys_user::get_level($obj_rs['user_experience']);
			$obj_rs['pic'] = empty($obj_rs['comment_pic']) ? array() : explode("||" , $obj_rs['comment_pic']);
			$arr_uid[] = $obj_rs['comment_user_id'];
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				if(empty($arr_return["list"][$i]['user_name'])) $arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['comment_user_id'] , $user_info);
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],5);
		return $arr_return;
	}

	function get_slide_ads($id , $position = '') {
		$obj_db = cls_obj::db();
		$obj_rs = $obj_db->get_one("select ads_html from " . cls_config::DB_PRE . "other_ads where ads_shop_id='" . $id . "' and ads_position='" . $position . "' and ads_starttime<='" . TIME . "' and ads_endtime>='" . TIME . "' and ads_state>0");
		if(!empty($obj_rs)) {
			return fun_get::filter($obj_rs['ads_html'],true);
		} else {
			return '';
		}
	}

	function shop_info($shop_id = 0) {
		//当前文章信息
		if(empty($shop_id)) $shop_id = (int)fun_get::get("shop_id");
		$shop_info = $this->get_shopinfo($shop_id , array('shop_intro') );
		if(empty($shop_id)) {
			cls_error::on_exit("exit" , "访问店铺不存在，或已被删除");
		}
		$this->shopinfo = $shop_info;
		$this->cfg_opentime = tab_meal_shop::get_opentime($shop_id);
		//活动公告
		$this->arr_activitie = $this->get_activitie($shop_id);	
		$this->shop_id= $shop_id;
	}
	function get_menu_comment($id) {
		$obj_db = cls_obj::db();
		$obj_menu = $obj_db->get_one("select menu_comment,menu_comment_num from " . cls_config::DB_PRE . "meal_menu where menu_id='" . $id . "'");
		if(empty($obj_menu)) $obj_menu = array('menu_comment' => 0 , 'menu_comment_num' => 0);
		$this->arr_list = $this->get_comment_list($id);
		$arr_group = array('5'=>0,'4'=>0,'3'=>0,'2'=>0,'1'=>0);
		$total = 0;
		$obj_result = $obj_db->select("select comment_val,count(1) as 'num' from " . cls_config::DB_PRE . "meal_menu_comment where comment_menu_id='" . $id . "' group by comment_val order by num desc");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if($obj_rs['comment_val']>5 || $obj_rs['comment_val']<1) continue;
			$arr_group[$obj_rs['comment_val']] = $obj_rs['num'];
			$total += $obj_rs['num'];
		}
		$obj_menu['commentval'] = $arr_group;
		$obj_menu['commenttotalren'] = $total;
		return $obj_menu;
	}
	function get_reserve_day($weekday = '') {
		$arr_x = is_array($weekday) ? $weekday : explode(",",$weekday);
		foreach($arr_x as $item) {
			if(empty($iem)) continue;
			$arr_weekday[] = $item;
		}
		$day = cls_config::get("reserve_day","meal");
		$day = (int)$day;
		$arr_list = array();
		$max_wk = 0;

		for($i=1;$i<=$day;$i++) {
			$date = date("Y-m-d",strtotime($i." day"));
			$wd = date("w",strtotime($date));
			$wdname = fun_get::weekday($date);
			if($wd>$max_wk) {
				$max_wk = $wd;
			} else if($wd<$max_wk && $wd!=0) {
				$wdname = "下" . $wdname;
			}
			if(empty($arr_weekday) || in_array($wd , $arr_weekday)) {
				$arr_list[] = array('name'=>date("m-d",strtotime($date))."(".$wdname.")","val" => $date);
			} else {
				$day=$day+1;
			}
		}
		return $arr_list;
	}
	function get_table_list($id , $len = 0 , $isgroup = false , $datetime = '') {
		$arr_list = $arr_id = array();
		$limit = '';
		if($len > 0 ) $limit = " limit 0," . $len;
		$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "meal_table where table_state=1 and table_shop_id='" . $id . "'" . $limit);
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			if($isgroup) {
				$arr_list['id_'.$obj_rs['table_group_id']][] = $obj_rs;
			} else {
				$arr_list[] = $obj_rs;
			}
			$arr_id[] = $obj_rs['table_id'];
		}
		$ids = implode(",",$arr_id);
		if(!empty($ids)) {
			$arr_list2 = array();
			$arr_table_state = tab_meal_table::get_state($id , $ids , $datetime);
			foreach($arr_list as $item => $key) {
				if(is_numeric($item)) {
					if(isset($arr_table_state['id_'.$key['table_id']])) {
						$key['state'] = $arr_table_state['id_'.$key['table_id']]['state'];
						$key['oid'] = $arr_table_state['id_'.$key['table_id']]['oid'];
						$key['rid'] = $arr_table_state['id_'.$key['table_id']]['rid'];
					}
					$arr_list2[] = $key;
				} else {
					foreach($key as $item2) {
						if(isset($arr_table_state['id_'.$item2['table_id']])) {
							$item2['state'] = $arr_table_state['id_'.$item2['table_id']]['state'];
							$item2['oid'] = $arr_table_state['id_'.$item2['table_id']]['oid'];
							$item2['rid'] = $arr_table_state['id_'.$item2['table_id']]['rid'];
						}
						$arr_list2[$item][] = $item2;
					}
				}
			}
			$arr_list = $arr_list2;
		}
		return $arr_list;
	}
	function get_table_group_list($id) {
		$arr_list = array();
		$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "meal_table_group where group_shop_id='" . $id . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$arr_list[] = $obj_rs;
		}
		return $arr_list;
	}
	function get_table_reserve($id,$shop_time = 0) {
		if(!is_numeric($shop_time) && fun_is::isdate($shop_time)) $shop_time = strtotime($shop_time);
		$arr_list = array();
		$shop_time = $shop_time * 60;
		$time = TIME - $shop_time;
		$obj_result = cls_obj::db()->select("select reserve_datetime,reserve_id,reserve_table_id from " . cls_config::DB_PRE . "meal_table_reserve where reserve_shop_id='" . $id . "' and reserve_datetime>'" . date("Y-m-d H:i:s",$time) . "' and reserve_stae>0 order by reserve_datetime");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			if(strtotime($obj_rs['reserve_datetime'])<TIME) {
				$obj_rs['state'] = 2;
			} else {
				$obj_rs['state'] = 1;
			}
			if(!isset($arr_list['list']['id_'.$obj_rs['reserve_table_id']])) {
				$arr_list['id_'.$obj_rs['reserve_table_id']] = array(
					"state" => $obj_rs['state'],
					"list" => array($obj_rs['reserve_datetime'])
				);
			} else {
				$arr_list['id_'.$obj_rs['reserve_table_id']]['list'][] = $obj_rs['reserve_datetime'];
			}
		}
		return $arr_list;
	}
	function shop_table_save() {
		$id = fun_get::get("id");
		$name = fun_get::get("name");
		$tel = fun_get::get("tel");
		$beta = fun_get::get("beta");
		$num = fun_get::get("num");
		$date = fun_get::get("date");
		$time = fun_get::get("time");
		if(empty($time)) $time = date("H:i");
		if(empty($date)) $date = date("Y-m-d");
		$table_id = fun_get::get("table_id");
		if(empty($time)) return array("code" => 500 , "msg" => "预约时间不正确");
		$time = str_replace(":" , "",$time);
		if(strlen($time) < 4) $time = "0" . $time;
		$time = substr($time,0,2) . ":" . substr($time,2,2) . ":00";
		$obj_db = cls_obj::db();
		$obj_shop = $obj_db->get_one("select shop_reserve_money,shop_reserve_sms from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $id . "'");
		if(empty($obj_shop)) return array("code" => 500 , "msg" => "商家不存在");
		if(empty($date)) $date = date("Y-m-d");
		if(empty($time)) $time = date("H:i:ss");
		$arr_fields = array(
			"reserve_table_id" => fun_get::get("table_id"),
			"reserve_shop_id" => $id,
			"reserve_name" => fun_get::get("name"),
			"reserve_tel" => fun_get::get("tel"),
			"reserve_num" => fun_get::get("num"),
			"reserve_datetime" => $date . " " . $time,
			"reserve_user_id" => cls_obj::get('cls_user')->uid,
		);
		if(fun_get::get("qrcode") == '1') {
			$vtime = TIME-600;
			$obj_verify = cls_obj::db()->get_one("select 1 from " . cls_config::DB_PRE . "sys_verify where verify_val='" . $arr_fields['reserve_tel'] . "' and verify_time>'" . $vtime . "'");
			if($obj_shop['shop_reserve_sms'] == 3 && empty($obj_verify)) return array("code" => 500 , "msg" => "短信验证过期，请重新验证");
			if($obj_shop['shop_reserve_sms'] == 2) {
				$obj_one = cls_obj::db()->get_one("select 1 from " . cls_config::DB_PRE . "meal_table_reserve where reserve_user_id='" . cls_obj::get("cls_user")->uid . "' and reserve_state>0");
				if(empty($obj_one) && empty($obj_verify))  return array("code" => 500 , "msg" => "短信验证过期，请重新验证");
			}
			if($obj_shop['shop_reserve_sms'] == 1) {
				$obj_one = cls_obj::db()->get_one("select 1 from " . cls_config::DB_PRE . "meal_order where order_user_id='" . cls_obj::get("cls_user")->uid . "' and order_state>0");
				if(empty($obj_one) && empty($obj_verify))  return array("code" => 500 , "msg" => "短信验证过期，请重新验证");
			}
			$arr_fields["reserve_state"] = 1;
			$arr_fields["reserve_deposit"] = 0;
		} else {
			$arr_fields["reserve_deposit"] = $obj_shop['shop_reserve_money'];
			$arr_fields["reserve_beta"] = $obj_shop['beta'];
		}
		$obj_table = $obj_db->get_one("select table_name,table_num,table_state,table_shop_id from " . cls_config::DB_PRE . "meal_table where table_id='" . $arr_fields['reserve_table_id'] . "'");
		if(empty($obj_table)) return array("code" => 500 , "msg" => "预订桌位不存在");
		if($obj_table['table_shop_id']!=$id) return array("code" => 500 , "msg" => "商家桌位不存在");
		$arr_state = tab_meal_table::get_state($id , $arr_fields['reserve_table_id'] , $arr_fields['reserve_datetime']);
		if(empty($arr_state)) return array("code" => 500 , "msg" => "预约时间不正确");
		if(!isset($arr_state['id_'.$arr_fields['reserve_table_id']]) || $arr_state['id_'.$arr_fields['reserve_table_id']]!=0) return array("code" => 500 , "msg" => "选择桌位已经被预订了，请重新选择");

		$arr_fields['reserve_tablename'] = $obj_table['table_name'] . "(" . $obj_table['table_num'] . ")";
		$obj_db->begin("saveorder");
		$arr_msg = tab_meal_table_reserve::on_save($arr_fields);
		if($arr_msg['code'] == 0) {
			$arr_order=array(
				"order_user_id" => cls_obj::get("cls_user")->uid,
				"order_shop_id" => $id,
				"order_repayment" => $arr_fields['reserve_deposit'],
				"order_reserve_id" => $arr_msg['id'],
				"order_state"  => -2,
				"order_day"  => $date,
				"order_time"  => $date . " " . $time,
				"order_mobile"  => $arr_fields['reserve_tel'],
				"order_name"  => $arr_fields['reserve_name'],
			);
			$arr_order = tab_meal_order::on_save($arr_order);
			if($arr_order['code'] == 0) {
				$obj_db->commit("saveorder");
				$arr_msg['oid'] =$arr_order['id'];
			} else {
				$obj_db->rollback("saveorder");
				$arr_msg = $arr_order;
			}
		} else {
			$obj_db->rollback("saveorder");
		}
		return $arr_msg;
	}
}