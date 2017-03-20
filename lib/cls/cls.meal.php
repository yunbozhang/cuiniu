<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_meal {
	//取购物车列表
	static function cart_list($shop_id = 0) {
		$arr_return = array('list' => array() , 'total' => 0 , 'num' => 0 , 'ids' => array() , 'shop_ids' => array() );
		$cartids = cls_session::get_cookie("cart_ids");
		if(empty($cartids)) return $arr_return;
		$arr = explode("|" , $cartids);
		$arr_cartid = $arr_num = $arr_detailid = array();
		foreach($arr as $item) {
			$arrx = explode("," , $item);
			$id = (int)$arrx[0];
			if(empty($id)) continue;
			$arr_num['id_' . $arrx[0]] = (int)$arrx[1];
			$arr_cartid[] = $id;
		}
		if(empty($arr_cartid) && empty($arr_detailid) ) return $arr_return;
		if(!empty($arr_cartid)) {
			$ids = implode("," , $arr_cartid);
			$where = " where menu_id in(" . $ids . ")";
			if(!empty($shop_id)) $where .= " and menu_shop_id='" . $shop_id . "'";
			$obj_result = cls_obj::db()->select("select menu_id,menu_title,menu_price,menu_sold_today,menu_sold_time,menu_shop_id,menu_num from " . cls_config::DB_PRE . "meal_menu" . $where);
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				if($obj_rs["menu_sold_time"] < strtotime(date("Y-m-d"))) $obj_rs["menu_sold_today"] = 0;
				$arr = array();
				$arr['name'] = $obj_rs['menu_title'];
				$arr['price'] = $obj_rs['menu_price'];
				$arr['id'] = $obj_rs['menu_id'];
				$arr['num'] = $arr_num['id_' . $arr['id']];
				$arr['total'] = $arr['num'] * $arr['price'];
				$arr['store'] = $obj_rs['menu_num'];
				$arr['sold'] = $obj_rs['menu_sold_today'];
				$arr['shop_id'] = $obj_rs['menu_shop_id'];
				$arr_return['list'][] = $arr;
				$arr_return['total'] += $arr['num'] * $arr['price'];
				$arr_return['num'] += $arr['num'];
				$arr_return['ids'][] = $obj_rs['menu_id'];
				if(!in_array($obj_rs['menu_shop_id'] , $arr_return['shop_ids'])) $arr_return['shop_ids'][] = $obj_rs['menu_shop_id'];
			}
		}
		return $arr_return;
	}

	//清空购物车
	static function cart_clear() {
		cls_session::set_cookie("cart_ids" , "");
	}

}