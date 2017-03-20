<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_act_voucher {
	static $perms;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				'type' => array('现金券'=>0),
				'state' => array("待领取" => 0 , "已领取" => 1 , "已作废" => -1),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	
	static function on_create($val , $num) {
		if(empty($val)) return array('code' => 500 , 'msg' => '面值不能为空');
		if(empty($num) || $num<0) return array('code'=>500 , 'msg' => '生成数量不对');
		$arr_fields = array();
		for($i = 0 ; $i < $num ;$i++) {
			$arr = array('voucher_val' => $val , 'voucher_addtime' => TIME);
			$arr['voucher_pwd'] = date("ymd") . rand(1000,9999) . rand(1000,9999) . rand(10,99);
			$arr_fields[] = $arr;
		}
		$arr_msg = cls_obj::db_w()->on_insert_all(cls_config::DB_PRE . "act_voucher" , $arr_fields);
		return $arr_msg;
	}

	static function get_voucher($uid ,$pwd) {
		if(empty($uid) || empty($pwd)) return array("code" => 500 , "msg" => "领取失败（密码不正确）");
		$obj_db = cls_obj::db_w();
		$obj_voucher = $obj_db->get_one("select voucher_id,voucher_user_id,voucher_state,voucher_val,voucher_type from " . cls_config::DB_PRE . "act_voucher where voucher_pwd='" . $pwd . "'");
		if(empty($obj_voucher)) return array("code" => 500 , "msg" => "优惠券不存在");
		if(!empty($obj_voucher['voucher_user_id'])) return array("code" => 500 , "msg" => "领取失败（券已领取过了）");
		if($obj_voucher['voucher_state']!=0) return array("code" => 500 , "msg" => "当前券无效，不能领取");
		//充值预付款
		$obj_db->begin('getvoucher');
		$arr_msg = tab_sys_user_repayment::on_admin_recharge(array(
			"repayment_val" => $obj_voucher['voucher_val'],
			"repayment_user_id" => $uid,
			"repayment_about_id" => $obj_voucher['voucher_id'],
			"repayment_beta" => "优惠券领取"
		));
		if($arr_msg['code']!=0) {
			$obj_db->rollback('getvoucher');
			return $arr_msg;
		}
		$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "act_voucher set voucher_state=1,voucher_user_id='" . $uid . "',voucher_usetime='" . TIME . "' where voucher_id='" . $obj_voucher['voucher_id'] . "'");
		if($arr_msg['code'] != 0) {
			$obj_db->rollback('getvoucher');
			return $arr_msg;
		}
		$obj_db->commit('getvoucher');
		return $arr_msg;
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
			(is_numeric($str_id)) ? $arr_where[] = "voucher_id='".$str_id."'" : $arr_where[] = "voucher_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."act_voucher" , $where);
		return $arr_return;
	}
}