<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_sys_user_repayment {
	static $this_type = array("消费支付" => 1 , "网上充值" => 2 , "系统充值"=>3);
	//订单支付
	static function on_order_pay($user_id , $val , $about_id = 0 , $beta = '订餐支付') {
		$obj_db = cls_obj::db_w();
		$val = abs($val);
		$arr_fields = array(
			"repayment_user_id" => $user_id,
			"repayment_val" => -1*$val,
			"repayment_beta" => $beta,
			"repayment_type" => 1,
			"repayment_about_id" => $about_id,
		);
		$arr = self::on_save($arr_fields);
		return array('code' => 0 , 'id' => $arr['id']);
	}
	//预付款充值
	static function on_recharge($arr_fields) {
		if(!isset($arr_fields['repayment_val'])) return array('code'=>500 , 'msg'=>'请输入充值金额');
		$arr_fields['repayment_val'] = abs($arr_fields['repayment_val']);
		$arr_return = self::on_save($arr_fields);
		return $arr_return;
	}
	//管理员操作
	static function on_admin_recharge($arr_fields) {
		if(!isset($arr_fields['repayment_val'])) return array('code'=>500 , 'msg'=>'请输入调整金额');
		$arr_fields['repayment_type'] = 3;
		$arr_return = self::on_save($arr_fields);
		return $arr_return;
	}
	//保存预付款
	static function on_save($arr_fields = array() ) {
		$obj_db = cls_obj::db_w();
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(!isset($arr_fields['repayment_val'])) return array('code'=>500 , 'msg'=>'请输入充值金额');
		if(!isset($arr_fields['repayment_user_id'])) return array('code'=>500 , 'msg'=>'没有指定用户');
		$obj_db->begin('repayment_order_pay');
		if($arr_fields['repayment_val'] > 0) {
			$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "sys_user set user_repayment=user_repayment+" . $arr_fields['repayment_val'] . " where user_id='" . $arr_fields['repayment_user_id'] . "'");
			if($arr['code'] != 0) {
				$obj_db->rollback('repayment_order_pay');
				return array('code' => 500 , "msg" => "预付款操作失败");
			}
			if($obj_db->affected_rows()<1) {
				$obj_db->rollback('repayment_order_pay');
				return array("code" => 500 , "msg" => "预付款用户不存在");
			}
		} else {
			$arr = $obj_db->on_exe("update " . cls_config::DB_PRE . "sys_user set user_repayment=user_repayment-" . (-1*$arr_fields['repayment_val']) . " where user_repayment>=" . (-1*$arr_fields['repayment_val']) . " and user_id='" . $arr_fields['repayment_user_id'] . "'");
			if($arr['code'] != 0) {
				$obj_db->rollback('repayment_order_pay');
				return array('code' => 500 , "msg" => "预付扣减作失败");
			}
			if($obj_db->affected_rows()<1) {
				$obj_db->rollback('repayment_order_pay');
				return array("code" => 500 , "msg" => "预付款扣减失败(余额不足)");
			}
		}

		$arr_fields['repayment_addtime'] = TIME ;
		$arr_fields['repayment_day'] = date("Y-m-d" , TIME );
		$arr_fields['repayment_time'] = date("Y-m-d H:i:s" , TIME );
		//插入到表
		$arr = $obj_db->on_insert(cls_config::DB_PRE."sys_user_repayment",$arr_fields);
		if($arr['code'] == 0) {
			$arr_return['id'] = $obj_db->insert_id();
			$obj_db->commit('repayment_order_pay');
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
			$obj_db->rollback('repayment_order_pay');
			return array("code" => 500 , "msg" => "预付款操作失败");
		}
		return $arr_return;
	}
}