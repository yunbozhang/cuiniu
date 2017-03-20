<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_sys_cmd_notice {
	static $perms;
	static $value;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				'type' => array("新订单提醒" => 1,"通知短信" => 0),
				'state' => array("未发送" => 0 , '已发送' => 1 , '发送失败' => -1  ),
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		$obj_db = cls_obj::db_w();
		if(!isset($arr_fields['notice_datetime'])) $arr_fields['notice_datetime'] = date("Y-m-d H:i:s");
		//插入到表
		$arr = $obj_db->on_insert(cls_config::DB_PRE."sys_cmd_notice",$arr_fields);
		if($arr['code'] == 0) {
			$arr_return['id'] = $obj_db->insert_id();
			//其它非mysql数据库不支持insert_id 时
			if(empty($arr_return['id'])) {
				$obj_rs = $obj_db->get_one("select notice_id from ".cls_config::DB_PRE."sys_cmd_notice where notice_datetime='" . $arr_fields["notice_datetime"] . " and notice_user_id='" . $arr_fields["notice_user_id"] . "' and notice_type='" . $arr_fields["notice_type"] . "'");
				if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['notice_id'];
			}
		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
		}
		return $arr_return;
	}
	static function on_send($id) {
		$obj_notice = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "sys_cmd_notice where notice_id='" . $id . "'");
		if(empty($obj_notice)) return array("code" => 500 , "msg" => "消息不存在");
		if(!empty($obj_notice['notice_tel']) && !empty($obj_notice['notice_sms_cont'])) {
			$cont = fun_get::filter($obj_notice['notice_sms_cont'] , true);
			$obj_notice['notice_tel'] = str_replace("，" , ";" , $obj_notice['notice_tel']);
			$obj_notice['notice_tel'] = str_replace("," , ";" , $obj_notice['notice_tel']);
			$arr_tel = explode(";" , $obj_notice['notice_tel']);
			foreach($arr_tel as $tel) {
				$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$tel , "cont" => $cont ,"id" => $obj_notice['notice_about_id'] , "type"=>$obj_notice['notice_type']) );
				print_r($arr);echo "<br><br>";
				usleep(5);
			}
		}
		if(!empty($obj_notice['notice_email']) && !empty($obj_notice['notice_email_cont'])) {
			$cont = fun_get::filter($obj_notice['notice_email_cont'] , true);
			$obj_notice['notice_email'] = str_replace("；" , ";" , $obj_notice['notice_email']);
			$arr_email = explode(";" , $obj_notice['notice_email']);
			foreach($arr_email as $email) {
				$arr = cls_obj::get("cls_com")->email('send' , array('to_mail' => $email , 'title' => $obj_notice['notice_title'] , 'content' => $cont ,'save' => 1));
				print_r($arr);echo "<br><br>";
				usleep(5);
			}
		}
		if(!empty($obj_notice['notice_wx']) && !empty($obj_notice['notice_wx_cont'])) {
			$cont = fun_get::filter($obj_notice['notice_wx_cont'] , true);
			$obj_notice['notice_wx'] = str_replace("；" , ";" , $obj_notice['notice_wx']);
			$obj_notice['notice_wx'] = str_replace("," , ";" , $obj_notice['notice_wx']);
			$arr_wx = explode(";" , $obj_notice['notice_wx']);
			foreach($arr_wx as $wx) {
				$arr = cls_weixin::on_send(0 , $wx , 'text' , array('message_text'=>$cont) );
				print_r($arr);echo "<br><br>";
				usleep(5);
			}
		}
		cls_obj::db()->on_exe("update " .cls_config::DB_PRE . "sys_cmd_notice set notice_state=1 where notice_id='" . $id . "'");
	}
}