<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_act_lottery {
	static $perms;
	static $value;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"type" => array("刮刮奖" => 0 , "幸运大转盘" => 1),
				"log_state" => array("未领取"=>0,"已领取"=>1,"已作废"=>-1),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['lottery_id'])) {
			$arr_fields['id'] = $arr_fields['lottery_id'];
			unset($arr_fields['lottery_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " lottery_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and lottery_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['lottery_title']) || $arr_fields['lottery_title'] == '' ) return array("code" => 500 , "msg" => "活动标题不能为空");
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."act_lottery",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$obj_rs = $obj_db->get_one("select lottery_id from ".cls_config::DB_PRE."act_lottery where lottery_title='" . $arr_fields["lottery_title"] . " and lottery_type='" . $arr_fields["lottery_type"] . "'");
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['lottery_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select lottery_id from ".cls_config::DB_PRE."act_lottery where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['lottery_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "lottery_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."act_lottery" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}

	/* 删除函数
	 * arr_id : 要删除的 id数组
	 * where : 删除附加条件
	 */
	static function on_delete($arr_id , $where = '') {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if( empty($str_id) && empty($where) ){
			$arr_return["code"] = 22;
			$arr_return["msg"]=cls_language::get("not_where");
			return $arr_return;
		}
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "lottery_id='".$str_id."'" : $arr_where[] = "lottery_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_update(cls_config::DB_PRE."act_lottery" , array('lottery_isdel'=>1) , $where);
		return $arr_return;
	}
	static function get_rand($rate , $list = array() ) {
		$rnd = mt_rand(0,$rate);
		$val = 0;
		foreach($list as $item) {
			$val+=$item['award_rate'];
			if($rnd<=$val) return $item['award_id'];
		}
		return 0;
	}
	static function get_award($lottery , $arr_award) {
		$datetime = date("Y-m-d H:i:s" , TIME);
		if($lottery['lottery_starttime']>$datetime) return array("code" => 500 , "msg" => "活动将于" . $lottery['lottery_starttime'] . "准时开始，敬请关注.");
		if($lottery['lottery_endtime']<$datetime) return array("code" => 500 , "msg" => "活动将已于" . $lottery['lottery_endtime'] . "结束，谢谢参与."); 
		$wx_id = cls_obj::get("cls_session")->get("wx_id");
		$uid = cls_obj::get("cls_user")->uid;
		if(empty($wx_id)) $wx_id = -1;
		if(empty($uid)) $uid = -1;
		//需要登录
		if($wx_id == -1 && $uid == -1) return array("code" => 500 , "msg" => '本活动需要登录后才可以参与');
		$obj_db = cls_obj::db_w();
		//是否需要积分
		if(!empty($lottery['lottery_score'])) {
			if( empty($uid) ) return array("code" => 500 , "msg" => '本活动需要登录后才可以参与');
			if( cls_obj::get("cls_user")->get_score() < $lottery['lottery_score'] ) return array("code" => 500 , "msg" => "您积分不够参与本次抽奖");
		}
		if(!empty($lottery['lottery_experience'])) {
			if( empty($uid) ) return array("code" => 500 , "msg" => '本活动需要登录后才可以参与');
			if( cls_obj::get("cls_user")->get_experience() < $lottery['lottery_experience'] ) return array("code" => 500 , "msg" => '本活动只限经验值' . $lottery['lottery_experience'] . '及以上的用户才能参与');
		}
		//是否限制抽取次数
		if($lottery['lottery_limit_user']>0) {
			$obj_log = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "act_lottery_record where (record_wx_id='" . $wx_id . "' or record_user_id='" . $uid . "') and record_lottery_id='" . $lottery['lottery_id'] . "'");
			if(!empty($obj_log) && $obj_log['num']>=$lottery['lottery_limit_user']) return array("code" => 500 , "msg" => "本活动每个用户只能抽取" . $lottery['lottery_limit_user'] . "次.");
		}
		//是否限制抽奖频率
		if(!empty($lottery['lottery_interval']) && !empty($lottery['lottery_interval_num']) ) {
			$time = 0;
			if($lottery['lottery_interval_unit'] == 'minute') {
				$time = TIME-$lottery['lottery_interval']*60;
				$unit = '分钟';
			} else if($lottery['lottery_interval_unit'] == 'hour') {
				$time = TIME-$lottery['lottery_interval']*3600;
				$unit = '小时';
			} else {
				$time = strtotime(date("Y-m-d" , TIME-$lottery['lottery_interval']*86400+86400));//每天算法不一样
				$unit = '天';
			}
			$obj_log = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "act_lottery_record where (record_wx_id='" . $wx_id . "' or record_user_id='" . $uid . "') and record_lottery_id='" . $lottery['lottery_id'] . "' and record_datetime>='" . date('Y-m-d H:i:s' , $time) . "'");
			if(!empty($obj_log) && $obj_log['num']>=$lottery['lottery_interval_num']) return array("code" => 500 , "msg" => "本活动限制每" . $unit . "只能抽"  . $lottery['lottery_interval_num'] . "次.");
		}
		$arr_award_lottery = array();
		$lottery_rate = $lottery['lottery_rate'];
		foreach($arr_award as $item) {
			if($item['award_num_exchange'] < $item['award_num_total']) {
				$arr_award_lottery[] = $item;
			} else {
				$lottery_rate = $lottery_rate - $item['award_rate'];
			}
		}
		$obj_db->begin("lottery");
		//扣积分
		if(!empty($lottery['lottery_score'])) {
			$arr_msg = tab_sys_user_action::on_action( $uid , 'act_lottery' , array('score' => $lottery['lottery_score'] , "title" => "参与抽奖") );
			if($arr_msg['code']!=0) {
				$obj_db->rollback("lottery");
				return array("code"=> 500 , "msg" => $arr_msg['msg']);
			}
		}
		$award_id = tab_act_lottery::get_rand($lottery_rate,$arr_award_lottery);
		$arr = $obj_db->on_insert(cls_config::DB_PRE."act_lottery_record",array(
			'record_award_id' => $award_id,
			'record_lottery_id' => $lottery['lottery_id'],
			'record_datetime' => date("Y-m-d H:i:s"),
			'record_user_id' => cls_obj::get("cls_user")->uid,
			'record_wx_id' => cls_obj::get("cls_session")->get("wx_id"),
			'record_score' => $lottery['lottery_score'],
		));
		//未中
		if(empty($award_id) || !isset($arr_award['id_' . $award_id]) ) {
			$obj_db->commit("lottery");
			return array("code" => 0 , "award_id" => 0);
		}
		//此奖品是否已抽完
		if($arr_award['id_' . $award_id]['award_num_exchange'] >= $arr_award['id_' . $award_id]['award_num_total']) {
			$obj_db->commit("lottery");
			return array("code" => 0 , 'id' => 0);
		}
		//奖品是否有用户限制
		if(!empty($arr_award['id_' . $award_id]['award_limit_user'])) {
			$obj_log = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "act_lottery_log where (log_user_id='" . $uid . "' || log_wx_id='" . $wx_id . "') and log_lottery_id='" . $lottery['lottery_id'] . "' and log_award_id='" . $award_id . "'");
			if(!empty($obj_log) && $obj_log['num']>=$arr_award['id_' . $award_id]['award_limit_user']) {
				$obj_db->commit("lottery");
				return array("code" => 0 , 'id' => 0);
			}
		}
		//中奖了
		//插入中奖记录
		$log_key = date("YmdHis") . rand(1000,9999) . $award_id . $lottery['lottery_id'];
		$arr = $obj_db->on_insert(cls_config::DB_PRE."act_lottery_log",array(
			'log_award_id' => $award_id,
			'log_lottery_id' => $lottery['lottery_id'],
			'log_datetime' => date("Y-m-d H:i:s"),
			'log_user_id' => cls_obj::get("cls_user")->uid,
			'log_wx_id' => cls_obj::get("cls_session")->get("wx_id"),
			'log_tel' => '',
		));
		if($arr['code'] != 0 ) {
			$obj_db->rollback("lottery");
			return array("code" => 500 , 'award_id' => 0 , "msg" => '系统繁忙');
		}
		//修改奖品抽出数量
		$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "act_lottery_award set award_num_exchange=award_num_exchange+1 where award_id='" . $award_id . "'");
		if($arr['code'] != 0) {
			$obj_db->rollback("lottery");
			return array("code" => 500 , 'award_id' => 0 , "msg" => '系统繁忙');
		}
		cls_obj::get("cls_session")->set("act.lottery." . $lottery['lottery_id'] , $award_id);
		$obj_db->commit("lottery");
		return array("code" => 0 , "id" => $award_id , 'name' => $arr_award['id_' . $award_id]['award_name'] , 'pic' => $arr_award['id_' . $award_id]['award_pic']);
	}
}