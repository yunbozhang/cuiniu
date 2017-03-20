<?php
/* 快捷订餐系统之多店版
 * 版本号：3.7
 * 官网：http://www.kjcms.com
 * 2014-06-25
 */
class mod_meal_comment extends inc_mod_meal {

	function get_pagelist() {
		//取查询参数
		$arr_where_s = $arr_where = array();
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_time1"),
			'addtime2' => fun_get::get("s_time2"),
			'key' => fun_get::get("s_key"),
		);
		$obj_db = cls_obj::db();
		$arr_return = array("list" => array() , "menu" => array() , "shop" => array() , "issearch" => 0);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "comment_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "comment_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if(!empty($arr_search_key['key'])) {
			if(cls_config::USER_CENTER=='user.klkkdj') {
				$arr_uid = array();
				$obj_result = $obj_db->select("select user_id from " . cls_config::DB_PRE . "user where user_name like '%" . $arr_search_key['key'] . "%'");
				while($obj_rs = $obj_db->fetch_array($obj_result)) {
					$arr_uid[] = $obj_rs['user_id'];
				}
				if(!empty($arr_uid)) {
					$ids = implode("," , $arr_uid);
					$arr_where_s[] = "(comment_beta like '%" . $arr_search_key['key'] . "%' or comment_user_id in(" . $ids . "))";
				} else {
					$arr_where_s[] = "comment_beta like '%" . $arr_search_key['key'] . "%'";
				}
			} else {
				$arr_x = cls_obj::get("cls_user")->get_user($arr_search_key['key']);
				if(isset($arr_x[$arr_search_key['key']])) {
					$arr_where_s[] = "(comment_beta like '%" . $arr_search_key['key'] . "%' or comment_user_id='" . $arr_x[$arr_search_key['key']] . "')";
				} else {
					$arr_where_s[] = "comment_beta like '%" . $arr_search_key['key'] . "%'";
				}
			}
		}
		//管理权限
		$limit_area = $this->this_limit->get_perms('limit_area');
		if(!empty($limit_area) && $this->admin_shop['id']<1) {
			$arr = explode("," , $limit_area);
			$arr_x = array();
			foreach($arr as $areaid) {
				$arr_x[] = cls_obj::db_w()->concat(',','shop_area_allid',',') . " like '%," . $areaid . ",%'";
			}
			$arr_shopid = array();
			$obj_result = cls_obj::db()->select("select shop_id from " . cls_config::DB_PRE . "meal_shop where (" . implode(" or " , $arr_x) . ")");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				$arr_shopid[] = $obj_rs['shop_id'];
			}
			$ids = empty($arr_shopid) ? '0' : implode(",",$arr_shopid);
			$arr_where[] = "comment_shop_id in(" . $ids . ")";
		}
		if( count($arr_where_s) > 0 ) $arr_return['issearch'] = 1;
		if($this->admin_shop['id'] != -999) $arr_where_s[] = "comment_shop_id='" . $this->admin_shop['id'] . "'";
		$arr_where = array_merge($arr_where , $arr_where_s);
		$where = '';
		if(count($arr_where)>0) $where = " where " . implode(" and " , $arr_where);
		$arr_order_id = $arr_shopid = $arr_menu_id = $arr_user_id = array();
		$arr_config_info = tab_sys_user_config::get_info("meal.order.comment"  , $this->app_dir);
		$lng_pagesize = $arr_config_info["pagesize"];
		$lng_page = (int)fun_get::get("page");
		$sort = " order by comment_id desc";
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."meal_order_comment" , $where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT * FROM ".cls_config::DB_PRE."meal_order_comment" . $where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['val'] = $obj_rs['comment_val']/5*100;
			$arr_shopid[] = $obj_rs['comment_shop_id'];
			$obj_rs['pic'] = empty($obj_rs['comment_pic']) ? array() : explode("||" , $obj_rs['comment_pic']);
			$arr_order_id[] = $obj_rs['comment_order_id'];
			$arr_user_id[] = $obj_rs['comment_user_id'];
			$obj_rs["addtime"] = date("Y-m-d H:i:s" , $obj_rs["comment_addtime"]);
			$obj_rs['menu_comments'] = array();
			$obj_rs['user_name'] = '';
			$arr_return['list']['id_' . $obj_rs['comment_order_id']] = $obj_rs;
		}
		if(!empty($arr_order_id)) {
			$ids = implode("," , $arr_order_id);
			$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "meal_menu_comment where comment_order_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_menu_id[] = $obj_rs['comment_menu_id'];
				$arr_return['list']['id_' . $obj_rs['comment_order_id']]['menu_comments'][] = $obj_rs;
			}
			$ids = implode("," , $arr_shopid);
			$obj_result = $obj_db->select("select shop_id,shop_name from " . cls_config::DB_PRE . "meal_shop where shop_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_return['shop']['id_' . $obj_rs['shop_id']] = $obj_rs['shop_name'];
			}
			$ids = implode("," , $arr_menu_id);
			$obj_result = $obj_db->select("select menu_id,menu_title from " . cls_config::DB_PRE . "meal_menu where menu_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_return['menu']['id_' . $obj_rs['menu_id']] = $obj_rs['menu_title'];
			}
			$user_info = cls_obj::get("cls_user")->get_user($arr_user_id);
			foreach($arr_return["list"] as $key=>$item) {
				$arr_return["list"]['id_' . $item['comment_order_id']]['user_name'] = array_search($arr_return["list"]['id_' . $item['comment_order_id']]['comment_user_id'] , $user_info);
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 删除指定  ads_id 数据
	 */
	function on_delete() {
		$arr_return = array("code"=>0 , "msg"=> cls_language::get("delete_ok"));
		$str_id = (int)fun_get::get("id");
		$arr_id = fun_get::get("selid",array());
		if(!empty($str_id)) $arr_id = array($str_id);
		if(empty($arr_id)) return array("code" => 500 , "msg" => "请选择要删除的评论");
		$obj_db = cls_obj::db_w();
		foreach($arr_id as $id) {
			//统计店铺评论
			$obj_oldcomment = $obj_db->get_one("select comment_list,comment_shop_id,comment_val from " . cls_config::DB_PRE . "meal_order_comment where comment_order_id='" . $id . "'");
			if(empty($obj_oldcomment)) return array("code" => 500 , "msg" => "评论不存在或已删除");
			$obj_shopcomment = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "meal_order_comment where comment_shop_id='" . $obj_oldcomment['comment_shop_id'] . "'");
			$shop_commentnum = empty($obj_shopcomment) ? 0 : $obj_shopcomment['num'];
			$arrx = explode("||" , $obj_oldcomment['comment_list']);
			$arr_oldcommentval = array();
			$valallintold = 0;
			foreach($arrx as $item) {
				$arry = explode( "=>" , $item);
				$arr_oldcommentval[$arry[0]] = (float)$arry[1];
				$valallintold+=$arr_oldcommentval[$arry[0]];
			}
			$valallintold = (int)$obj_oldcomment['comment_val'];//intval($valallintold/count($arr_oldcommentval));
			$arr_menu_id = array();
			//还原店铺评分
			$obj_result = $obj_db->select("select comment_menu_id from " . cls_config::DB_PRE . "meal_menu_comment where comment_order_id in(" . $id . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_menu_id[] = $obj_rs['comment_menu_id'];
			}
			$obj_db->on_exe("delete from " . cls_config::DB_PRE . "meal_order_comment where comment_order_id in(" . $id . ")");
			$arr_msg = $obj_db->on_exe("delete from " . cls_config::DB_PRE . "meal_menu_comment where comment_order_id in(" . $id . ")");
			if(!empty($arr_menu_id) && $arr_msg['code']==0 ) {
				$ids = implode("," , $arr_menu_id);
				$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "meal_menu set menu_comment_num=menu_comment_num-1 where menu_id in(" . $ids . ")");
			}

			//同步店铺评论
			$obj_shop = $obj_db->get_one("select shop_comment_list,shop_comment_listall,shop_comment_val from " . cls_config::DB_PRE . "meal_shop where shop_id='" . $obj_oldcomment['comment_shop_id'] . "'");
			$arrv = array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0);
			$arrlist = $arrlistall = array();
			$valall = 0;
			$commentlist = $commentval = '';
			if(!empty($obj_shop['shop_comment_list'])) {
				$arrxall = explode("||" , $obj_shop['shop_comment_listall']);
				$arrlistx = $arrlistxall = array();
				$total = $num = 0;
				foreach($arrxall as $i=>$item) {
					$arry = explode("=>" , $item);
					$arry[1] = (float)$arry[1];
					$y = isset($arr_oldcommentval[$arry[0]]) ? $arry[1]-$arr_oldcommentval[$arry[0]] : $arry[1];
					$arrlistxall[$arry[0]] = $y;
				}
				$arrx = explode("||" , $obj_shop['shop_comment_val']);
				foreach($arrx as $item) {
					$arry = explode("=>" , $item);
					$arrv[$arry[0]] = (int)$arry[1];
					$num+=(int)$arry[1];
				}
				
				if(isset($arrv[$valallintold]) && $arrv[$valallintold]>0)  {
					$arrv[$valallintold]--;
					if($num>0) $num--;
				}
				$arry = array();
				foreach($arrv as $item=>$key) {
					$arry[] = $item . "=>" . $key;
				}
				$arrx = explode("||" , $obj_shop['shop_comment_list']);
				$commentval = implode("||" , $arry);
				foreach($arrx as $i=>$item) {
					$arry = explode("=>" , $item);
					$arry[1] = (float)$arry[1];
					$y = (float)$arrlistxall[$arry[0]];
					$x = (isset($arrlistxall[$arry[0]]) && $num>0) ? $y/$num : 0;
					$arrlist[] = $arry[0] . "=>" . $x;
				}
				$arrlistxall2 = array();
				foreach($arrlistxall as $item=>$key) {
					$arrlistxall2[] = $item . "=>" . $key;
				}
				$valall = ($num>0) ? $total/$num : 0;
				$commentlist = implode("||" , $arrlist);
				$commentlistall = implode("||" , $arrlistxall2);
			}
			$arr = $obj_db->on_update(cls_config::DB_PRE . "meal_shop" , array("shop_comment" => $valall , "shop_comment_list" => $commentlist , "shop_comment_listall" => $commentlistall , "shop_comment_val" => $commentval) , "shop_id='" . $obj_oldcomment['comment_shop_id'] . "'");
		}
		return $arr_return;
	}
	function on_recont() {
		$id = (int)fun_get::get("id");
		$cont = fun_get::get("cont");
		$arr = cls_obj::db_w()->on_update(cls_config::DB_PRE . "meal_order_comment" , array("comment_recont" => $cont , "comment_re_time" => TIME ) , "comment_order_id=" . $id);
		$arr['id'] = $id;
		return $arr;
	}

}