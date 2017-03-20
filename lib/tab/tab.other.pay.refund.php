<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_other_pay_refund {
	static $perms;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				'state' => array("退款成功" => 1 , "待处理" => 0 , "退款处理中" => -1 , "拒绝退款" => -2 , "退款失败" => -3),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(!isset($arr_fields['refund_pay_id'])) return array("code" => 500 , "msg" => "支付记录号不能为空");
		$obj_db = cls_obj::db_w();
		$obj_refund = $obj_db->get_one("select refund_pay_id from " .cls_config::DB_PRE . "other_pay_refund where refund_pay_id='" . $arr_fields['refund_pay_id'] . "'");
		if( empty($obj_refund) ) {
			//初始必要值
			if(!isset($arr_fields['refund_addtime'])) $arr_fields['refund_addtime'] = date("Y-m-d");
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."other_pay_refund",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $arr_fields['refund_pay_id'];
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if(!empty($obj_refund["refund_uid"])) unset($arr_fields["refund_uid"]);
			if(!empty($obj_refund["refund_tips_tel"])) {
				unset($arr_fields["refund_tips_tel"]);
			} else if(isset($arr_fields["refund_tips_tel"])) {
				$obj_refund["refund_tips_tel"] = $arr_fields["refund_tips_tel"];
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."other_pay_refund" , $arr_fields , "refund_pay_id='" . $arr_fields['refund_pay_id'] . "'");
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
			$arr_return["id"] = $arr_fields['refund_pay_id'];
			$arr_return["tel"] = $obj_refund['refund_tips_tel'];
		}
		return $arr_return;
	}

}