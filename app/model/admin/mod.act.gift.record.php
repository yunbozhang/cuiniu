<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_gift_record extends inc_mod_admin {
	/* 按模块查询菜单信息并返回数组列表
	 * module : 指定查询模块
	 */
	function get_pagelist() {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			's_key' => fun_get::get("s_key"),
			'time1' => fun_get::get("s_time1"),
			'time2' => fun_get::get("s_time2"),
			'send_time1' => fun_get::get("s_send_time1"),
			'send_time2' => fun_get::get("s_send_time2"),
			's_send_time' => fun_get::get("s_send_time"),
		);
		if( !empty($arr_search_key['s_key']) ) {
			$arr_where_1 = array("record_linkname like '%" . $arr_search_key['s_key'] . "%' or record_tel='" . $arr_search_key['s_key'] . "' or record_address like '%" . $arr_search_key['s_key']  . "%'");
			if(cls_config::USER_CENTER=='user.klkkdj') {
				$arr_uid = array();
				$obj_result = cls_obj::db()->select("select user_id from " . cls_config::DB_PRE . "user where user_name like '%" . $arr_search_key['s_key'] . "%'");
				while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
					$arr_uid[] = $obj_rs['user_id'];
				}
				$ids = implode("," , $arr_uid);
				if(!empty($ids)) $arr_where_1[] = "record_user_id in(" . $ids . ")";
			}
			$arr = array();
			$obj_result = cls_obj::db()->select("select gift_id from " . cls_config::DB_PRE . "act_gift where gift_name like '%" . $arr_search_key['s_key'] . "%'");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				$arr[] = $obj_rs['gift_id'];
			}
			$ids = implode("," , $arr);
			if(!empty($ids)) $arr_where_1[] = "record_gift_id in(" . $ids . ")";
			$arr_where_s[] = "(" . implode(" or " , $arr_where_1) . ")"; 
		}
		if( fun_is::isdate( $arr_search_key['time1'] ) ) $arr_where_s[] = "record_time >= '" . $arr_search_key['time1'] . "'"; 
		if( fun_is::isdate( $arr_search_key['time2'] ) ) $arr_where_s[] = "record_time <= '" . date("Y-m-d H:i:s" , fun_get::endtime( $arr_search_key['time2'] )) . "'"; 
		if( $arr_search_key['s_send_time'] ) {
			$arr_where_s[] = "record_send_time=0";
		} else {
			if( fun_is::isdate( $arr_search_key['send_time1'] ) ) $arr_where_s[] = "record_send_time >= '" . strtotime($arr_search_key['send_time1']) . "'"; 
			if( fun_is::isdate( $arr_search_key['send_time2'] ) ) $arr_where_s[] = "record_send_time <= '" . fun_get::endtime( $arr_search_key['send_time2'] ) . "'"; 
		}
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页 , lng_pagesize : 分页大小
	 */
	function sql_list($str_where = "" , $lng_page = 1 , $lng_pagesize = 10) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("act.gift.record" , $this->app_dir , "act");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("act.gift.record"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//过滤user_name,gift_name
		$arr = explode(",",$arr_cfg_fields['sel']);
		$arr = fun_base::array_remove($arr,"user_name");
		$arr = fun_base::array_remove($arr,"gift_name");
		if(!in_array('record_send_time',$arr)) $arr[] = 'record_send_time';
		$fields = implode("," , $arr);
		//取分页信息
		$arr_return["list"] = $arr_uid = $arr_gift_id = $arr_giftname = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_gift_record" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $fields . " FROM ".cls_config::DB_PRE."act_gift_record" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$arr_uid[] = $obj_rs['record_user_id'];
			$arr_gift_id[] = $obj_rs['record_gift_id'];
			$obj_rs['send_time'] = $obj_rs['record_send_time'];
			$obj_rs['record_send_time'] = (empty($obj_rs['record_send_time'])) ? "<font style='color:#ff0000'>未发货</font>" : date("Y-m-d H:i:s",$obj_rs['record_send_time']);
			if(isset($obj_rs['record_receive_time'])) {
				$obj_rs['record_receive_time'] = (empty($obj_rs['record_receive_time'])) ? "<font style='color:#ff0000'>未领取</font>" : date("Y-m-d H:i:s",$obj_rs['record_receive_time']);
			}
			$arr_return["list"][] = $obj_rs;
		}
		if(count($arr_uid)>0) {
			$arr_uname = cls_obj::get("cls_user")->get_user($arr_uid);
			$ids = implode("," , $arr_gift_id);
			$obj_result = $obj_db->select("select gift_name,gift_id from " . cls_config::DB_PRE . "act_gift where gift_id in("  . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_giftname['id_' . $obj_rs['gift_id']] = $obj_rs['gift_name'];
			}
			$count = count($arr_return['list']);
			for($i = 0 ; $i < $count ; $i++ ) {
				if(isset($arr_giftname['id_' . $arr_return['list'][$i]['record_gift_id']])) $arr_return['list'][$i]['gift_name'] = $arr_giftname['id_' . $arr_return['list'][$i]['record_gift_id']];
				$arr_return['list'][$i]['user_name'] = array_search( $arr_return['list'][$i]['record_user_id'] , $arr_uname );
			}
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}
	function on_send() {
		$arr_return = array("code"=>0 , "msg"=> "成功设置为发货状态");
		$str_id = fun_get::get("id");
		$arr_id = fun_get::get("selid");
		if(!empty($arr_id)) $str_id = $arr_id; //优先考虑 arr_id
		$ids = fun_format::arr_id($str_id);
		if(empty($ids)) return array("code"=>500 , "msg"=>"请选择要发货的记录");
		$arr_msg = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "act_gift_record set record_send_time='" . TIME . "' where record_id in(" . $ids . ")");
		if($arr_msg['code']!=0) return $arr_msg;
		return $arr_return;
	}
}