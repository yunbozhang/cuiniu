<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_meal_order extends inc_mod_meal {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist($isexcel = false) {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'user' => fun_get::get("s_user"),
			'state' => (int)fun_get::get("s_state" , -999),
			'key' => fun_get::get("s_key"),
			's_id' => (int)fun_get::get("s_id"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "order_time >= '" . $arr_search_key['addtime1'] . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "order_time <= '" . date("Y-m-d H:i:s",fun_get::endtime($arr_search_key['addtime2'])) . "'"; 
		if(!fun_is::isdate( $arr_search_key['addtime1'] ) && !fun_is::isdate( $arr_search_key['addtime2'] ) && fun_get::get('url_channel')=='') {
			$arr_where[] = "order_time >='" . date("Y-m-d") . "' and order_time<='" . date("Y-m-d H:i:s",fun_get::endtime(date("Y-m-d"))) ."'"; 
		}
		if(empty($arr_search_key['addtime1']) && fun_get::get('url_channel')=='reserve') {
			$arr_where[] = "order_time>='" . date("Y-m-d",strtotime("1 day")) . "'"; 
		}
		if(!empty($arr_search_key['s_id'])) $arr_where[] = "order_id='" . $arr_search_key['s_id'] . "'";
		if( $arr_search_key['state'] != -999 ) $arr_where_s[] = "order_state = '" . $arr_search_key['state'] . "'"; 
		if( !empty($arr_search_key['key']) ) $arr_where_s[] = "(order_name like '%" . $arr_search_key['key'] . "%' or order_tel like '%" . $arr_search_key['key'] . "%' or order_mobile like '%" . $arr_search_key['key'] . "%')";
		if( !empty($arr_search_key['user']) ) {
			if(cls_config::USER_CENTER=='user.klkkdj') {
				$arr_ids = array();
				$obj_result = cls_obj::db()->select("select user_id from " .cls_config::DB_PRE . "user where user_name like '" . $arr_search_key['user'] . "%' or user_id='" . (int)$arr_search_key['user'] . "'");
				while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
					$arr_ids[] = $obj_rs['user_id'];
				}
				$ids = implode("," , $arr_ids);
				if(!empty($ids)) {
					$arr_where_s[] = "order_user_id in(" . $ids . ")";
				} else {
					$arr_where_s[] = "order_id=0";
				}
			} else {
				$arr_x = cls_obj::get("cls_user")->get_user($arr_search_key['user'],false);
				$arr_x = array_values($arr_x);
				$ids = implode(",",$arr_x);
				if(!empty($ids)) {
					$arr_where_s[] = "order_user_id in (" . $ids . ")";
				} else {
					$arr_where_s[] = "order_user_id='" . (int)$arr_search_key['user'] . "'";
				}
			}
		}
		//管理权限
		$limit_area = $this->this_limit->get_perms('limit_area');
		if(!empty($limit_area)) {
			$arr = explode("," , $limit_area);
			$arr_x = array();
			foreach($arr as $areaid) {
				$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
			}
			$arr_where[] = "order_shop_id in(select shop_id from " . cls_config::DB_PRE . "meal_shop where " . implode(" or " , $arr_x) . ")";
		}

		//合并查询数组
		if($this->admin_shop["id"] != -999) $arr_where[] = "order_shop_id='" . $this->admin_shop["id"] . "'";
		if(fun_get::get("ischeckout")==1) {
			$str_where = " where order_state>0 and order_checkout_id=0 and order_time>='" . fun_get::get("checkout_date1") . "' and order_time<='" . fun_get::get("checkout_date2") . "' and order_shop_id='" . (int)fun_get::get('shop_id') . "'";
		} else {
			$arr_where = array_merge($arr_where , $arr_where_s);
			if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		}
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page') , $isexcel);

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;
		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1 , $isexcel = false) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("meal.order" , $this->app_dir , "meal");
		$arr_cfg_fields["sel"] = substr(str_replace(",user_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_cfg_fields["sel"] = substr(str_replace(",shop_name," , "," , "," . $arr_cfg_fields["sel"] . ","),1,-1);
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("meal.order"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_state = tab_meal_order::get_perms("state");
		$arr_award = tab_meal_order::get_perms("award");
		//取分页信息
		$arr_uid = array();
		$arr_return["list"] = $arr_areaid = $arr_act_id = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_order" , $str_where , $lng_page , $lng_pagesize,array("sum(order_total) as 'totalall',sum(order_repayment) as 'totalrepayment',sum(order_total_pay) as 'total_pay',sum(order_addprice) as 'totaladdprice',sum(order_score_money) as 'totalmoney',sum(order_pay_val) as 'payed',sum(order_favorable) as 'favorable'"));
		$sql = "SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."meal_order" . $str_where . $sort;
		if(!$isexcel) $sql .= $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select($sql);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs["order_addtime"])) $obj_rs["order_addtime"] = date("Y-m-d H:i:s" , $obj_rs["order_addtime"]);
			if(isset($obj_rs["order_act_ids"])) $arr_act_id[] = $obj_rs["order_act_ids"];
			if(isset($obj_rs["order_state"])) {
				$obj_rs['state'] = $obj_rs['order_state'];
				if($obj_rs["order_state"]>0 || $isexcel) {
					$obj_rs["order_state"] = array_search($obj_rs["order_state"] , $arr_state);
				} else {
					$obj_rs["order_state"] = "<font color='#ff0000'>" . array_search($obj_rs["order_state"] , $arr_state) . "</font>";
				}
			}
			if(isset($obj_rs["order_isaward"])) {
				if($obj_rs["order_isaward"]>0 || $isexcel) {
					$obj_rs["order_isaward"] = array_search($obj_rs["order_isaward"] , $arr_award);
				} else if($obj_rs["order_isaward"]<0) {
					$obj_rs["order_isaward"] = "<font color='#ff0000'>" . array_search($obj_rs["order_isaward"] , $arr_award) . "</font>";
				} else {
					$obj_rs["order_isaward"] = "<font color='#888888'>" . array_search($obj_rs["order_isaward"] , $arr_award) . "</font>";
				}
			}
			$arr_uid[] = $obj_rs['order_user_id'];
			$arr_shop_id[] = $obj_rs['order_shop_id'];
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$user_info = cls_obj::get("cls_user")->get_user($arr_uid);
			$count = count($arr_return["list"]);
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['user_name'] = array_search($arr_return["list"][$i]['order_user_id'] , $user_info);
			}
			$str_ids = implode(",", $arr_shop_id);
			$obj_result = $obj_db->select("select shop_id,shop_name from " . cls_config::DB_PRE . "meal_shop where shop_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_shop_name["id_".$obj_rs["shop_id"]] = $obj_rs["shop_name"];
			}
			for($i = 0 ; $i < $count ; $i++) {
				$arr_return["list"][$i]['shop_name'] = isset($arr_shop_name['id_' . $arr_return["list"][$i]['order_shop_id']]) ? $arr_shop_name['id_' . $arr_return["list"][$i]['order_shop_id']] : '';
			}
		}

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
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 删除指定  order_id 数据
	 */
	function on_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = cls_language::get("delete_no_id");
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_order::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	/* 确认定单并奖励
	 */
	function on_award() {
		$arr_return = array("code"=>0 , "msg"=> "奖励成功");
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if( empty($arr_id) && empty($str_id) ) {
			$arr_return['code'] = 22;//见参数说明表
			$arr_return['msg']  = "未指定要处理的订单";
			return $arr_return;
		}
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$arr = tab_meal_order::on_award($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
	/* 设置订单状态
	 */
	function on_state() {
		$arr_return = array("code"=>0 , "msg"=> "处理成功");
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		$beta = fun_get::get("state_beta");
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$state = (int)fun_get::get("state_val");
		$where = 'order_state=0';
		if($state == -6) $where = "order_state>=0";
		$arr = tab_meal_order::on_state($str_id , $state , $beta , $where);
		if($arr['code'] != 0) return $arr;
		return $arr_return;
	}
	function get_detail($id) {
		$obj_db = cls_obj::db();
		$arr_return = array("list" => array() , "arrivetime" => array() , "newid" => 0);
		$arr_day = $arr_menu_ids = $arr_act_id = array();
		$obj_rs = $obj_db->get_one("select a.*,b.shop_name,b.shop_tel,b.shop_pic from " . cls_config::DB_PRE . "meal_order a left join " . cls_config::DB_PRE . "meal_shop b on a.order_shop_id=b.shop_id where order_id='" . $id . "' limit 0,1");
		$arr = explode("|" , $obj_rs["order_ids"]);
		$arr_x = array();
		foreach($arr as $item) {
			if(!in_array($item , $arr_x)) {
				$obj_rs["menu"][$item] = array( 'id'=> explode("," , $item) , 'num' => 1);
				$arr_x[] = $item;
			} else {
				$obj_rs["menu"][$item]['num']++;
			}
		}
		//取店铺信息
		//取店铺送餐时间
		$arr_return["arrivetime"] = array();
		$obj_shop = $obj_db->get_one("select shop_extend from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $obj_rs['order_shop_id'] . "'");
		if(!empty($obj_shop) && !empty($obj_shop['shop_extend'])) {
			$arr = unserialize($obj_shop["shop_extend"]);
			if(isset($arr["arr_arrivetime"])) $arr_return["arrivetime"] = $arr["arr_arrivetime"];
		}
		//取当时下单的定价
		if(!empty($obj_rs["order_detail"])) {
			$arr_detail = unserialize($obj_rs["order_detail"]);
			if(isset($arr_detail["menu_price"])) $arr_return["price"] = $arr_detail["menu_price"];
		}
		$arr_return["detail"] = $obj_rs;
		$arr_menu_ids = explode("," , str_replace("|" , "," , $obj_rs["order_ids"]));
		$str_ids = implode("," , $arr_menu_ids);
		$obj_result = $obj_db->select("select menu_id,menu_title,menu_pic_small,menu_pic,menu_price from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $str_ids . ")");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_day[] = $obj_rs;
			$arr_return["menu"]["id_".$obj_rs["menu_id"]] = $obj_rs;
			if(!isset($arr_return["price"]["id_".$obj_rs["menu_id"]])) $arr_return["price"]["id_".$obj_rs["menu_id"]] = $obj_rs["menu_price"];
		}
		if(!isset($arr_return["detail"])) $arr_return["detail"] = array();
		if( !empty($arr_return["detail"]["order_act_ids"]) ) {
			$arr_act = $arr_act_id = array();
			$arr = explode(",", $arr_return["detail"]["order_act_ids"]);
			foreach($arr as $item) {
				if(is_numeric($item)) $arr_act_id[] = $item;
			}
			$str_ids = implode(",", $arr_act_id);
			if(empty($str_ids)) $str_ids = '0';
			$obj_result = $obj_db->select("select act_name,act_id from " . cls_config::DB_PRE . "meal_act where act_id in(" . $str_ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_act["id_".$obj_rs["act_id"]] = $obj_rs["act_name"];
			}
			$arr_actname = array();
			foreach($arr_act_id as $actid) {
				if(isset($arr_act['id_' . $actid])) $arr_actname[] = $arr_act['id_' . $actid];
			}
			$arr_return["detail"]['order_act_ids'] = implode("<br>" , $arr_actname);
		}
		return $arr_return;
	}

	function on_excel() {
		$arr_return = $this->get_pagelist(true);
		$arr_excel=array();
		$arr = array();
		foreach($arr_return['tabtit'] as $item) {
			$arr[] = $item["name"];
		}
		$arr_excel[] = $arr;

		foreach($arr_return['list'] as $item) {
			$arr = array() ;
			foreach($arr_return['tabtit'] as $field) {
				$val = $item[$field["key"]];
				if($field["key"] == 'order_ticket' && empty($val)) $val = '';
				if($field["key"] == 'order_act_ids') $val = str_replace('<br>' , '；' , $val);
				$arr[] = $val;
			}
			$arr_excel[] = $arr;
		}
		$get_excel_name = fun_get::get("excel_name" , date("Y-m-d"));
		cls_excel::save_excel($get_excel_name.".xls",$arr_excel);
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
		$obj_comment = cls_obj::db()->edit(cls_config::DB_PRE."meal_order_comment" , "comment_order_id='".$order_id."'");
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
		$order_id = fun_get::get("id");
		$obj_db = cls_obj::db_w();
		$obj_rs = $obj_db->get_one("select order_id,order_shop_id,order_ids,order_state,order_user_id,order_comment from " . cls_config::DB_PRE . "meal_order where order_id='" . $order_id . "'");
		if(empty($obj_rs)) return array("code" => 500 , "msg" => "订单不存在");
		if($obj_rs['order_state']<1) return array("code" => 500 , "msg" => "只有成交的订单才能评论");
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
				$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_menu_comment set comment_val='" . $val . "' where comment_id='" . $x . "' and comment_order_id='" . $order_id . "'");
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
			$obj_oldcomment = $obj_db->get_one("select comment_list from " . cls_config::DB_PRE . "meal_order_comment where comment_id='" . $id . "'");
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
				$obj_db->on_exe("update " . cls_config::DB_PRE . "meal_order set order_comment=1 where order_id='" . $order_id . "'");
				//评论订单
				//tab_sys_user_action::on_action( $uid , 'meal_order_comment');
			}
		} else {
			$arr = $obj_db->on_update(cls_config::DB_PRE . "meal_order_comment" , $arr_val , "comment_id='" . $id . "' and comment_order_id='" . $order_id . "'");
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
		if($obj_rs['order_comment'] != 1) $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_order set order_comment=1 where order_id='" . $obj_rs['order_id'] . "'");
		return $arr;
	}
}