<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_lottery extends inc_mod_default {
	function get_lottery($id) {
		$obj_db = cls_obj::db();
		$arr_award = $arr_award_lottery = array();
		$obj_lottery = $obj_db->get_one("select * from " . cls_config::DB_PRE . "act_lottery where lottery_id='" . $id . "'");
		if(empty($obj_lottery)) cls_error::on_exit('exit','活动不存在');
		$obj_lottery['lottery_desc'] = fun_get::filter($obj_lottery['lottery_desc'] , true);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "act_lottery_award where award_lottery_id='" . $id . "'");
		$total_rate = 0;
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$total_rate+=$obj_rs['award_rate'];
			$obj_lottery['award_list']['id_' . $obj_rs['award_id']] = $obj_rs;
		}
		if($obj_lottery['lottery_type']==0) {
			$obj_lottery['award'] = tab_act_lottery::get_award($obj_lottery,$obj_lottery['award_list']);
		} else {
			$arr = array();
			foreach($obj_lottery['award_list'] as $item) {
				$rate = number_format($item['award_rate']/$total_rate*100,2);
				$arr[] = array(
					"name" => $item['award_name'],
					"rate" => $rate,
					"id" => $item['award_id'],
					'pic' => fun_get::url($item['award_pic'])
				);
			}
			$obj_lottery['award'] = fun_format::json($arr);
		}
		return $obj_lottery;
	}
	//取获奖记录
	function get_award_log($id) {
		$uid = cls_obj::get("cls_user")->uid;
		if(empty($uid)) $uid = -1;
		$wx_id = cls_obj::get("cls_session")->get("wx_id");
		if(empty($wx_id)) $wx_id = -1;
		$arr_state = tab_act_lottery::get_perms("log_state");
		$arr_return = array();
		$obj_result = cls_obj::db()->select("select a.log_id,a.log_state,b.award_name,a.log_datetime from " . cls_config::DB_PRE . "act_lottery_log a left join " . cls_config::DB_PRE . "act_lottery_award b on a.log_award_id=b.award_id where (log_user_id='" . $uid . "' or log_wx_id='" . $wx_id . "') and log_lottery_id='" . $id . "'");
		while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
			$obj_rs['state'] = array_search($obj_rs['log_state'] , $arr_state);
			$arr_return[] = $obj_rs;
		}
		return $arr_return;
	}
	//抽奖
	function get_award() {
		$obj_db = cls_obj::db();
		$id = (int)fun_get::get("id");
		$obj_lottery = $obj_db->get_one("select * from " . cls_config::DB_PRE . "act_lottery where lottery_id='" . $id . "'");
		if(empty($obj_lottery)) return array("code" => 500 , "msg" => "活动不存在");
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "act_lottery_award where award_lottery_id='" . $id . "'");
		$total_rate = 0;
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$total_rate+=$obj_rs['award_rate'];
			$obj_lottery['award_list']['id_' . $obj_rs['award_id']] = $obj_rs;
		}
		if($obj_lottery['lottery_type']==1) $obj_lottery['lottery_rate'] = $total_rate;
		$arr_return = tab_act_lottery::get_award($obj_lottery,$obj_lottery['award_list']);
		return $arr_return;
	}
}