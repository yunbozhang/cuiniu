<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_lottery_log extends inc_mod_admin {
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
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			's_uid' => (int)fun_get::get("s_uid"),
			's_wxid' => fun_get::get("s_wxid"),
			's_lotteryid' => (int)fun_get::get("s_lotteryid"),
			's_awardid' => (int)fun_get::get("s_awardid"),
			's_state' => (int)fun_get::get("s_state",-999),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "log_datetime >= '" . $arr_search_key['addtime1'] . "'";
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "log_datetime <= '" . date("Y-m-d H:i:s" , fun_get::endtime($arr_search_key['addtime2'])) . "'";
		if( !empty( $arr_search_key['s_uid'] ) ) $arr_where_s[] = "log_user_id= '" . $arr_search_key['s_uid'] . "'";
		if( !empty( $arr_search_key['s_wxid'] ) ) $arr_where_s[] = "log_wx_id='" . $arr_search_key['s_wxid'] . "'";
		if( !empty( $arr_search_key['s_lotteryid'] ) ) $arr_where_s[] = "log_lottery_id='" . $arr_search_key['s_lotteryid'] . "'";
		if( !empty( $arr_search_key['s_awardid'] ) ) $arr_where_s[] = "log_award_id='" . $arr_search_key['s_awardid'] . "'";
		if($arr_search_key['s_state'] != -999) $arr_where_s[] = "log_state='" . $arr_search_key['s_state'] . "'";
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("act.lottery.log" , $this->app_dir , "act");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("act.lottery.log"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_state = tab_act_lottery::get_perms("log_state");
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_lottery_log" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."act_lottery_log a left join " . cls_config::DB_PRE . "act_lottery b on a.log_lottery_id=b.lottery_id left join " . cls_config::DB_PRE . "act_lottery_award c on a.log_award_id=c.award_id left join " . cls_config::DB_PRE . "sys_user d on a.log_user_id=d.user_id" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['state'] = $obj_rs['log_state'];
			$obj_rs['log_state'] = array_search($obj_rs['log_state']  , $arr_state);
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	function on_state($id , $val) {
		$arr_state = tab_act_lottery::get_perms("log_state");
		$x = array_search($val , $arr_state);
		if(empty($x)) return array("code" => 500 , "msg" => "设置的状态不存在");
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "act_lottery_log set log_state=" . $val . " where log_id='" . $id . "' and log_state=0");
		return $arr;
	}
}