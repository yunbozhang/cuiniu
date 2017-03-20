<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_lottery extends inc_mod_admin {
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
			'key' => fun_get::get("s_key"),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "lottery_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "lottery_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "lottery_title like '%" . $arr_search_key['key'] . "%'";

		//合并查询数组
		$arr_where[] = "lottery_isdel=0";
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
		$arr_cfg_fields = tab_sys_user_config::get_fields("act.lottery" , $this->app_dir , "act");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("act.lottery"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_type = tab_act_lottery::get_perms("type");
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_lottery" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."act_lottery" . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs['lottery_type'] = array_search( $obj_rs['lottery_type'] , $arr_type);
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo($msg_id) {
		$obj_rs = cls_obj::db()->edit(cls_config::DB_PRE."act_lottery" , "lottery_id='".$msg_id."'");
		if(empty($obj_rs['lottery_id'])) {
			$obj_rs['award'] = array();
		} else {
			$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "act_lottery_award where award_lottery_id='" . $obj_rs['lottery_id'] . "'");
			while($obj_award = cls_obj::db()->fetch_array($obj_result)) {
				$obj_rs['award'][] = $obj_award;
			}
		}
		return $obj_rs;
	}
	/* 保存数据
	 * 
	 */
	function on_save() {
		$arr_return = array("code" => 0 , "id"=>0 , "msg" => cls_language::get("save_ok"));
		$obj_db = cls_obj::db_w();
		$obj_db->begin("save");
		$arr_fields = array(
			"id"     => (int)fun_get::post("id"),
			"lottery_type" => fun_get::post("lottery_type"),
			"lottery_title" => fun_get::post("lottery_title"),
			"lottery_starttime" => fun_get::post("lottery_starttime"),
			"lottery_endtime"  => fun_get::post("lottery_endtime"),
			"lottery_desc"  => fun_get::post("lottery_desc"),
			"lottery_limit_user"  => (int)fun_get::post("lottery_limit_user"),
			"lottery_rate"  => (int)fun_get::post("lottery_rate"),
			"lottery_score"  => (int)fun_get::post("lottery_score"),
			"lottery_experience"  => (int)fun_get::post("lottery_experience"),
			"lottery_interval"  => (int)fun_get::post("lottery_interval"),
			"lottery_interval_unit"  => fun_get::post("lottery_interval_unit"),
			"lottery_interval_num"  => (int)fun_get::post("lottery_interval_num"),
			"lottery_pic"  => fun_get::post("lottery_pic"),
		);
		$arr = tab_act_lottery::on_save($arr_fields);
		if($arr['code']==0) {
			if(isset($arr['id'])) $arr_return['id'] = $arr['id'];
			//保存奖项
			$award_id = fun_get::get("award_id" , array());
			$arr_id = array();
			foreach($award_id as $item) {
				if(!empty($item)) $arr_id[] = $item;
			}
			$where = "award_lottery_id='" . $arr_return['id'] . "'";
			if(!empty($arr_id)) $where .= " and not award_id in(" . implode("," , $arr_id) . ")";
			tab_act_lottery_award::on_delete(array() , $where);
 
			$award_name = fun_get::get("award_name" , array());
			$award_num_total = fun_get::get("award_num_total" , array());
			$award_rate = fun_get::get("award_rate" , array());
			$award_limit_user = fun_get::get("award_limit_user" , array());
			$award_pic = fun_get::get("award_pic" , array());
			$award_num_exchange = fun_get::get("award_num_exchange" , array());
			for($i = 0 ; $i < count($award_id) ; $i++) {
				$arr_award = array(
					'id' => $award_id[$i],
					'award_name' => $award_name[$i],
					'award_num_total' => (int)$award_num_total[$i],
					'award_rate' => (int)$award_rate[$i],
					'award_limit_user' => (int)$award_limit_user[$i],
					'award_pic' => $award_pic[$i],
					'award_lottery_id' => $arr_return['id'],
				);
				$num_exchange = (int)$award_num_exchange[$i];
				if($arr_award['award_num_total']<$num_exchange) {
					$obj_db->rollback("save");
					return array('code'=>500,'msg'=>'奖品【' . $arr_award['award_name'] . '】总数量不能小于已抽出数量');
				}
				$arr_msg = tab_act_lottery_award::on_save($arr_award);
				if($arr_msg['code']!=0) {
					$obj_db->rollback("save");
					return $arr_msg;
				}
			}
		} else {
			$obj_db->rollback("save");
			return $arr;
		}
		$obj_db->commit("save");
		return $arr_return;
	}

	/* 删除指定  lottery_id 数据
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
		$arr = tab_act_lottery::on_delete($str_id);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = $arr['msg'];
		}
		return $arr_return;
	}
}