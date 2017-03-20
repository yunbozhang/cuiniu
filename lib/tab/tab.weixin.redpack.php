<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_weixin_redpack {
	static $perms;

	//获取表配置参数
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"type" => array("现金红包" => 0 , "裂变红包" => 1),
				"state" => array("有效" => 1 , "关闭" => 2),
				"event" => array("subscribe" => "关注"  , "reg" => "注册" , "share" => "分享"),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}

	/* 保存操作
	 * arr_fields : 为字段数据，默认如果包函 id，则为修改，否则为插入
	 * where : 默认为空，用于有时候条件修改
	 */
	static function on_save($arr_fields , $where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['redpack_id'])) {
			$arr_fields['id'] = $arr_fields['redpack_id'];
			unset($arr_fields['redpack_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " redpack_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and redpack_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if(isset($arr_fields['redpack_event']) && !empty($arr_fields['redpack_event'])) $arr_fields['redpack_event'] = implode(",",$arr_fields['redpack_event']);
		if( empty($where) ) {
			$arr_fields["redpack_addtime"] = TIME;
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."weixin_redpack",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$where  = "redpack_type='" . $arr_fields['redpack_type'] . " and redpack_title='".$arr_fields['redpack_title'] . "' and redpack_addtime='".$arr_fields["redpack_addtime"]."'";
					$obj_rs = $obj_db->get_one("select redpack_id from ".cls_config::DB_PRE."weixin_redpack where ".$where);
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['redpack_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select redpack_id from ".cls_config::DB_PRE."weixin_redpack where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['redpack_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "redpack_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."weixin_redpack" , $arr_fields , $where);
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
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "redpack_id='".$str_id."'" : $arr_where[] = "redpack_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);
		$arr_return = $obj_db->on_delete(cls_config::DB_PRE."weixin_redpack" , $where);
		return $arr_return;
	}
	/*
	 * 抢红包
	 */
	static function on_open($id = 0, $event = '' , $openid = '') {
		if(empty($id) && empty($event)) return array('code' => 500 , 'msg' => "红包不存在");
		if(empty($openid)) {
			$obj_one = cls_obj::db()->get_one("select login_name from " . cls_config::DB_PRE . "userapi_login where login_user_id='" . cls_obj::get('cls_user')->uid . "' and login_plat='weixin'");
			if(empty($obj_one)) return array("code" => 500 , "msg" => "只有微信登录才能抢红包");
			$openid = substr($obj_one['login_name'],7);
			$uid = cls_obj::get('cls_user')->uid;
		} else {
			$obj_one = cls_obj::db()->get_one("select login_user_id from " . cls_config::DB_PRE . "userapi_login where login_name='weixin_" . $openid . "' and login_plat='weixin'");
			$uid = empty($obj_one) ? 0 : $obj_one['login_user_id'];
		}
		if(!empty($id)) {
			$obj_redpack = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "weixin_redpack where redpack_id='" . $id . "'");
			if(empty($obj_redpack)) return array('code' => 500 , "msg" => "红包活动不存在");
			$arrx = self::chk_redpack($obj_redpack , $openid);
			if($arrx['code'] != 0) return $arrx;
			$obj_redpack["opentotal"] = $arrx['total'];
		} else {
			$obj_redpack = array();
			$obj_result = cls_obj::db()->select("select * from " . cls_config::DB_PRE . "weixin_redpack where concat(',',redpack_event,',') like '%," . $event . ",%' and redpack_state>0 and redpack_starttime>='" . date('Y-m-d H:i:s') . "' and redpack_endtime<='" . date("Y-m-d H:i:s") . "' order by redpack_id desc");
			while($obj_rs = cls_obj::db()->fetch_array($obj_result)) {
				$arrx = self::chk_redpack($obj_rs , $openid);
				if($arrx['code'] != 0) continue;
				$obj_redpack["opentotal"] = $arrx['total'];
				$obj_redpack = $obj_rs;
				break;
			}
			if(empty($obj_redpack)) return array('code' => 500 , 'msg' => "没有抢到红包");
		}
		if($obj_redpack['redpack_rate']<100) {
			$rnd = rand(1,10000);
			$rate = $obj_redpack['redpack_rate'] * 100;
			if($rnd > $rate ) return array('code' => 1 , "msg" => "没有抢到红包");
		}
		$money = rand($obj_redpack["redpack_min_money"] , $obj_redpack["redpack_max_money"]);
		if(!empty($obj_redpack['redpack_total']) && $obj_redpack['redpack_total']-$obj_redpack['opentotal']<$money) $money = $obj_redpack['redpack_total']-$obj_redpack['opentotal'];
		$arr_fields = array(
			"record_redpack_id" => $obj_redpack['redpack_id'],
			"record_openid" => $openid,
			"record_user_id" => $uid,
			"record_money" => $money,
		);
		$arr_record = tab_weixin_redpack_record::on_save($arr_fields);
		if($arr_record['code'] != 0) return $arr_record;
		require_once( KJ_DIR_LIB . "/components/pay/weixin/pay.weixin.php" );
		$obj_pay=new pay_weixin;
		$arr_cfg = array(
			"number" => $arr_record['number'],
			"shopname" => $obj_redpack["redpack_shopname"],
			"openid" => $openid,
			"money" => $money,
			"greet" => $obj_redpack["redpack_greet"],
			"title" => $obj_redpack["redpack_title"],
			"beta" => $obj_redpack["redpack_beta"],
		);
		$arr_msg = $obj_pay->sendredpack($arr_cfg);
		if($arr_msg['code'] == 0) {
			cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "weixin_redpack_record set record_state=1 where record_id='" . $arr_record['id'] . "'");
			return array('code' => 0 , 'money' => $money);
		} else {
			cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "weixin_redpack_record set record_state=-1,record_err='" . $arr_msg['msg'] . "' where record_id='" . $arr_record['id'] . "'");
			return $arr_msg;
		}
	}
	static function chk_redpack($obj_rs , $openid) {
		$obj_record = cls_obj::db()->get_one("select count(1) as 'num',sum(record_money) as 'total' from " . cls_config::DB_PRE . "weixin_redpack_record where record_redpack_id='" . $obj_rs['redpack_id'] . "' and record_openid='" . $openid . "' and record_state>0");
		if(!empty($obj_rs["redpack_user_limit"])) {
			if(!empty($obj_record) && $obj_record['num']>=$obj_rs['redpack_user_limit']) return array('code' => 500 , 'msg' => '每个用户只能领取' . $obj_rs["redpack_user_limit"] . '次红包');
		}
		if(!empty($obj_rs["redpack_total"])) {
			if(!empty($obj_record) && $obj_record['total']>=$obj_rs['redpack_total']) return array('code' => 500 , 'msg' => '红包已被领完');
		}
		$open_total = $obj_record['total'];
		if(!empty($obj_rs['redpack_interval']) && !empty($obj_rs['redpack_interval_num'])) {
			if($obj_rs['redpack_interval_unit'] == 'minute') {
				$words = "分钟";
				$time = TIME - 60*$obj_rs['redpack_interval'];
			} else if($obj_rs['redpack_interval_unit'] == 'hour') {
				$words = "小时";
				$time = TIME - 3600*$obj_rs['redpack_interval'];
			} else {
				$words = "天";
				$time = TIME - 86400*$obj_rs['redpack_interval'];
			}
			$obj_record = cls_obj::db()->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "weixin_redpack_record where record_redpack_id='" . $obj_rs['redpack_id'] . "' and record_openid='" . $openid . "' and record_datetime>='" . date("Y-m-d H:i:s",$time) . "' and record_state>0");
			$interval = $obj_rs['redpack_interval']>1 ? $obj_rs['redpack_interval'] : '';
			if(!empty($obj_record) && $obj_record['num']>=$obj_rs['redpack_interval_num']) return array('code' => 500 , 'msg' => '每个用户每' . $interval . $words . '只能领取' . $obj_rs['redpack_interval_num'] . '次红包');
		}
		if(empty($open_total)) $open_total = 0;
		return array('code' => 0 , 'total' => $open_total);
	}
}