<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_sys_user {
	static $perms;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"type" => array( cls_language::get("default") => "default", cls_language::get("shop") => "shop" ) ,
				"state" => array( cls_language::get("normal") => 1 , cls_language::get("wait_check") => 0) ,
				"level" => array("level_1"=>100,"level_2"=>300,"level_3"=>800,"level_4"=>2000,"level_5"=>5000,"level_6"=>10000)
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function get_level($score) {
		$level = 0;
		$arr_level = self::get_perms("level");
		foreach($arr_level as $item=>$val) {
			if($val >= $score) break;
			$level ++;
		}
		return $level;
	}
	static function get_level_next($score) {
		$return = 0;
		$arr_level = self::get_perms("level");
		foreach($arr_level as $item=>$val) {
			$return = $val;
			if($val > $score) break;
		}
		return $return;
	}
	static function get_level_next_score($level) {
		$arr_level = self::get_perms("level");
		$score = 0;
		if(isset($arr_level["level_" . $level])) $score = $arr_level["level_" . $level];
		$level++;
		$score_next = $score;
		if(isset($arr_level["level_" . $level])) $score_next = $arr_level["level_" . $level];
		return $score_next-$score;
	}

	static function on_insert($arr_fields) {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(!isset($arr_fields['user_id']) || empty($arr_fields['user_id']) ) return array('code' => 500 , 'msg' =>'用户id不能为空');
		//查看站点
		$arr_site = fun_kj::get_site();
		$arr_fields['user_site_id'] = $arr_site['site_id'];
		//规则验证
		$arr = self::chk_rule($arr_fields);
		if($arr['code'] != 0) {
			$arr_return = $arr;
			return $arr_return;
		}

		//默认注册状态
		if(!isset($arr_fields["user_state"])) {
			$arr_fields["user_state"] = (int)cls_config::get("user_state" , "user");
		}
		if(!isset($arr_fields["user_type"])) $arr_fields["user_type"] = 'default';

		//初始必要值
		if(!isset($arr_fields['user_regtime'])) $arr_fields['user_regtime'] = TIME;
		if(!isset($arr_fields['user_regdate'])) $arr_fields['user_regdate'] = date("Y-m-d H:i:s" , $arr_fields['user_regtime']);
		if(!isset($arr_fields['user_regip'])) $arr_fields['user_regip'] = fun_get::ip();
		if(!isset($arr_fields['user_logintime'])) $arr_fields['user_logintime'] = TIME;
		if(!isset($arr_fields['user_loginip'])) $arr_fields['user_loginip'] = $arr_fields['user_regip'];
		if(fun_get::get("inviteid")!="") {
			$arr_fields['user_invite_uid'] = (int)fun_get::get('inviteid');
			$arr_fields['user_invite_type'] = fun_get::get('invitetype');
		} else if(fun_get::get("jump_url")!="") {
			$arr = parse_url($_REQUEST['jump_url']);
			$arr = isset($arr['query']) ?  explode("&",$arr['query']) : array();
			foreach($arr as $item) {
				$arrx = explode("=" , $item);
				if($arrx[0]=='inviteid') {
					$arr_fields['user_invite_uid'] = (int)$arrx[1];
				}
				if($arrx[0]=='invitetype') {
					$arr_fields['user_invite_type'] = $arrx[1];
				}
			}
		}
		if( isset($arr_fields['user_invite_uid']) && !empty($arr_fields['user_invite_uid']) ) {
			$obj_user = cls_obj::db()->get_one("select user_id,user_invite_uids from " . cls_config::DB_PRE . "sys_user where user_id='" . $arr_fields['user_invite_uid'] . "'");
			if(!empty($obj_user)) {
				if(empty($obj_user['user_invite_uids'])) {
					$arr_fields['user_invite_uids'] = $obj_user['user_id'];
				} else {
					$arr = explode("," , $obj_user['user_invite_uids']);
					if(count($arr)>20) {
						$ii = count($arr)-19;
						$arr2 = array();
						for($i = $ii ; $i < count($arr) ; $i++) {
							$arr2[] = $arr[$i];
						}
						$arr2[] = $obj_user['user_id'];
						$arr_fields['user_invite_uids'] = implode("," , $arr2);
					} else {
						$arr_fields['user_invite_uids'] = $obj_user['user_invite_uids'] . "," . $obj_user['user_id'];
					}
				}
			} else {
				unset($arr_fields['user_invite_uid']);
			}
		}
		if($arr_fields['user_type'] == 'shop' && (!isset($arr_fields['user_group_id']) || empty($arr_fields['user_group_id']))) {
			$arr_fields['user_group_id'] = (int)cls_config::get("shop_default_group" , "user");
		}
		$obj_db = cls_obj::db_w();

		//插入到用户表
		$arr = $obj_db->on_insert(cls_config::DB_PRE."sys_user",$arr_fields);
		if($arr['code'] == 0) {
			//每日登录行为事件
			tab_sys_user_action::on_action( $arr_fields['user_id'] , 'meal_init' );
			$arr_return['id'] = $arr_fields['user_id'];
			//分享邀请
			if(fun_get::get("share_key")!='') {
				cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "other_share set share_user_num=share_user_num+1 where share_key='" . fun_get::get('share_key') . "'");
			}
			//邀请数量增加
			if(isset($arr_fields['user_invite_uid']) && !empty($arr_fields['user_invite_uid'])) {
				$obj_one = cls_obj::db()->get_one("select count(1) as 'num ' from " . cls_config::DB_PRE . "sys_user where user_invite_uid='" . $arr_fields['user_invite_uid'] . "'");
				$num = empty($obj_one) ? 1 : $obj_one['num']+1;
				cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "sys_user set user_invite_num='" . $num . "' where user_id='" . $arr_fields['user_invite_uid'] . "'");
			}

		} else {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
		}
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['user_id'])) {
			$arr_fields['id'] = $arr_fields['user_id'];
			unset($arr_fields['user_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " user_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and user_id='" . $arr_return['id'] . "'";
				}
			}
		}
		//规则验证
		$arr = self::chk_rule($arr_fields);
		if($arr['code'] != 0) {
			$arr_return = $arr;
			return $arr_return;
		}
		if( empty($where) ) {
			return array('code' => 500 , 'msg' =>'保存条件不能为空');
		} else {

			$obj_db = cls_obj::db_w();
			//注销账号修改
			if( isset($arr_fields["user_id"]) ) unset($arr_fields["user_id"]);

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select user_id from ".cls_config::DB_PRE."sys_user where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['user_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "user_id='".$arr_return['id']."'";
			}
			//是否修改密码
			if( isset($arr_fields['user_pwd']) ) {
				if(empty($arr_fields['user_pwd'])) {
					unset($arr_fields['user_pwd']);//为空，则取消密码修改
				} else if(!fun_is::pwd($arr_fields['user_pwd'])) {
					$arr_return['code'] = 113;
					$arr_return['msg']  = fun_get::rule_pwd("tips");//密码格式不对
					return $arr_return;
				} else {
					$arr_fields['user_pwd_key'] = self::get_pwd_key();//返回四位数加密字符
					$arr_fields["user_pwd"] = fun_format::pwd($arr_fields['user_pwd'],$arr_fields['user_pwd_key']);//取加密码
				}
			}

			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."sys_user" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
	static function chk_rule($arr_fields) {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if( isset($arr_fields["user_tel"]) && !empty($arr_fields["user_tel"]) && fun_is::tel($arr_fields["user_tel"]) == false) {
			$arr_return['code'] = 115;
			$arr_return['msg']  = cls_language::get("tel","rule");//电话格式不对
			return $arr_return;
		}
		if( isset($arr_fields["user_mobile"]) && !empty($arr_fields["user_mobile"]) && fun_is::mobile($arr_fields["user_mobile"]) == false) {
			$arr_return['code'] = 115;
			$arr_return['msg']  = cls_language::get("mobile","rule");//手机格式不对
			return $arr_return;
		}
		if( isset($arr_fields["user_email"]) && !empty($arr_fields["user_email"]) && fun_is::email($arr_fields["user_email"]) == false) {
			$arr_return['code'] = 115;
			$arr_return['msg']  = cls_language::get("email","rule");//邮箱格式不对
			return $arr_return;
		}
		if( isset($arr_fields["user_birthday"]) && !empty($arr_fields["user_birthday"]) && fun_is::isdate($arr_fields["user_birthday"]) == false) {
			$arr_return['code'] = 115;
			$arr_return['msg']  = cls_language::get("birthday","rule");//邮箱格式不对
			return $arr_return;
		}
	}
	/*
	 * 回收站或还原操作
	 * isdel 决定是回收还是还原，1:回收，0:还原
	 */
	static function on_del($arr_id , $isdel = 1) {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if($str_id == ""){
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$arr_fields = array("user_isdel" => $isdel);
		if(is_numeric($str_id)) {
			$arr_return=cls_obj::db_w()->on_update(cls_config::DB_PRE."sys_user",$arr_fields,"user_id='".$str_id."'");
		} else {
			$arr_return=cls_obj::db_w()->on_update(cls_config::DB_PRE."sys_user",$arr_fields,"user_id in(".$str_id.")");
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
			(is_numeric($str_id)) ? $arr_where[] = "user_id='".$str_id."'" : $arr_where[] = "user_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."sys_user" , $where);
		return $arr_return;
	}
}