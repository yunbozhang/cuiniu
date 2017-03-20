<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_ajax extends inc_mod_default {
	function on_quan_msg_pub() {
		$arr_fileds = array(
			'msg_cont' => fun_get::post("cont"),
			'msg_user_id' => cls_obj::get("cls_user")->uid,
			'msg_category_id' => fun_get::post("category_id")
		);
		$arr_x = fun_get::get("pic");
		$arr_pic = array();
		if(is_array($arr_x)) {
			foreach($arr_x as $pic) {
				if(!empty($pic)) $arr_pic[] = $pic;
			}
		}
		$arr_fileds['msg_pics'] = implode("||" , $arr_pic);
		$arr = tab_quan_msg::on_save($arr_fileds);
		return $arr;
	}
	function on_quan_msg_zan() {
		if(!cls_obj::get("cls_user")->is_login()) return array("code" => 500 , "msg" => "您还没有登录");
		$id = (int)fun_get::get("id");
		$uid = cls_obj::get("cls_user")->uid;
		$obj_db = cls_obj::db();
		$obj_msg = $obj_db->get_one("select msg_id,msg_user_id,msg_zan_ids from " . cls_config::DB_PRE . "quan_msg where msg_id='" . $id . "'");
		if(empty($obj_msg)) return array("code" => 500 , "msg" => "点赞内容不存在");

		$obj_zan = $obj_db->get_one("select zan_id from " . cls_config::DB_PRE . "quan_zan where zan_user_id='" . $uid . "' and zan_msg_id='" . $id . "'");
		if(!empty($obj_zan)) {
			$obj_db->on_exe("update " . cls_config::DB_PRE . "quan_zan set zan_cancel=1 where zan_id='" . $id . "'");
			return array("code" => 0 , "id" => $obj_zan['zan_id'] , "uid" => $uid , "uname" => cls_obj::get('cls_user')->name , "msgid" => $id , "id" => $obj_zan['zan_id'] , "iscancel" => 1);
		}
		$arr_fields = array(
			"zan_user_id" => $uid , 
			"zan_msg_id" => $id ,
			"zan_to_uid" => $obj_msg['msg_user_id'],
		);
		$arr = empty($obj_msg['msg_zan_ids']) ? array() : explode("," , $obj_msg['msg_zan_ids']);
		if(count($arr)<20) {
			$arr[] = $uid;
			$obj_msg["msg_zan_ids"] = implode("," , $arr);
		}
		$arr_return = tab_quan_zan::on_save($arr_fields);
		if($arr_return['code'] == 0) {
			$arrx = $obj_db->on_exe("update " . cls_config::DB_PRE . "quan_msg set msg_zan_time='" . TIME . "',msg_zan_num=msg_zan_num+1,msg_zan_ids='" . $obj_msg["msg_zan_ids"] . "' where msg_id='" . $id . "'");
		}
		$arr_return['uid'] = $uid;
		$arr_return['uname'] = cls_obj::get("cls_user")->name;
		$arr_return['msgid'] = $id;
		$arr_return['iscancel'] = 0;
		return $arr_return;
	}
	function on_quan_msg_ping() {
		if(!cls_obj::get("cls_user")->is_login()) return array("code" => 500 , "msg" => "您还没有登录");
		$id = (int)fun_get::get("id");
		$uid = cls_obj::get("cls_user")->uid;
		$cont = fun_get::get("cont");
		if(empty($cont))  return array("code" => 500 , "msg" => "评论内容不能为空");
		$obj_db = cls_obj::db();
		$obj_msg = $obj_db->get_one("select msg_id,msg_user_id,msg_hudong from " . cls_config::DB_PRE . "quan_msg where msg_id='" . $id . "'");
		if(empty($obj_msg)) return array("code" => 500 , "msg" => "评论内容不存在或已删除");
		$arr_hudong = array();
		if(!empty($obj_msg['msg_hudong'])) $arr_hudong = unserialize($obj_msg['msg_hudong']);

		$arr_fields = array(
			"ping_user_id" => $uid , 
			"ping_msg_id" => $id ,
			"ping_to_uid" => $obj_msg['msg_user_id'],
			"ping_cont" => $cont,
		);
		if(!isset($arr_hudong['ping'])) $arr_hudong['ping'] = array();
		if(count($arr_hudong['ping'])<20) {
			$arr_hudong['ping'][] = array("uid" => $uid , "cont" => $cont);
			$obj_msg['msg_hudong'] = serialize($arr_hudong);
		}
		$arr_return = tab_quan_ping::on_save($arr_fields);
		if($arr_return['code'] == 0) {
			$arrx = $obj_db->on_exe("update " . cls_config::DB_PRE . "quan_msg set msg_ping_time='" . TIME . "',msg_ping_num=msg_ping_num+1,msg_hudong='" . $obj_msg["msg_hudong"] . "' where msg_id='" . $id . "'");
		}
		$arr_return['uid'] = $uid;
		$arr_return['touid'] = $obj_msg['msg_user_id'];
		$arr_return['uname'] = cls_obj::get("cls_user")->name;
		$arr_return['msgid'] = $id;
		return $arr_return;
	}
	function on_quan_msg_del( $id ) {
		$where = " where msg_id='" . $id . "'";
		if(cls_obj::get('cls_user')->is_admin()==false) $where .= " and msg_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$arr_return = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "quan_msg set msg_state=-1" . $where);
		$arr_return['id'] = $id;
		return $arr_return;
	}
	function on_quan_msg_ping_del( $id ) {
		$where = " where ping_id='" . $id . "'";
		if(cls_obj::get('cls_user')->is_admin()==false) $where .= " and ping_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$arr_return = cls_obj::db()->on_exe("update " . cls_config::DB_PRE . "quan_ping set ping_isdel=1");
		$arr_return['id'] = $id;
		$arr_return['msgid'] = fun_get::get("msgid");
		return $arr_return;
	}
	function on_reg($user_type = ''){
		$arr_return=array("code"=>0,"msg"=>"注册成功");
		$str_pwd1 = fun_get::post('pwd1');
		$str_pwd2 = fun_get::post('pwd2');
		if($str_pwd1 != $str_pwd2){
			$arr_return["code"]=1;
			$arr_return["msg"]="两次输入密码不一致";
			return $arr_return;
		}
		$reg_switch = (int)cls_config::get("reg_switch" , "user");
		$reg_invite_code = cls_config::get("reg_invite_code" , "user");
		$reg_switch_info = cls_config::get("reg_switch_info" , "user");
		if($reg_switch == 1) {
			$arr_return["code"]=500;
			if(empty($reg_switch_info)) $reg_switch_info = "网站关闭了新用户注册功能";
			$arr_return["msg"]=$reg_switch_info;
			return $arr_return;
		} else if($reg_switch == 2) {
			$invite_code = fun_get::post('invite_code');
			if($invite_code != $reg_invite_code) {
				$arr_return["code"]=500;
				$arr_return["msg"] = "邀请码输入不正确";
				return $arr_return;
			}
		}
		$verifycode = fun_get::post("verifycode");
		$isverify = (cls_obj::get("cls_session")->get('verify_reg') > 0) ? false : true;
		$arr_user=array(
			"user_name" => fun_get::post("uname"),
			"user_pwd" => $str_pwd1,
		);
		if(cls_config::get('rule_uname','user')=='email') {
			$arr = tab_sys_verify::on_verify($arr_user['user_name'] , $verifycode ,3 ,86400 , $isverify);
			if($arr['code'] != 0) return $arr;
			cls_obj::get("cls_session")->set('verify_reg' , 1);//设置已验证标识
		} else if(cls_config::get('rule_uname','user')=='mobile') {
			$arr = tab_sys_verify::on_verify($arr_user['user_name'] , $verifycode , 4 ,600, $isverify);
			if($arr['code'] != 0) return array('code' => 500 , 'msg' => '短信验证码有误');
			cls_obj::get("cls_session")->set('verify_reg' , 1);//设置已验证标识
		} else if(cls_config::get('reg_verify' , 'user')){
		//是否需要验证码
			if(cls_verifycode::on_verify($verifycode) == false) {
				$arr_return["code"] = 11;
				$arr_return["msg"]  = cls_language::get("verify_code_err");
				return $arr_return;
			}
		}
		if(fun_is::set("email")) $arr_user['user_email'] = fun_get::post("email");
		if(!empty($user_type)) $arr_user['user_type'] = $user_type;
		//注册用户
		$arr = cls_obj::get("cls_user")->on_reg($arr_user);
		if($arr["code"] != 0){
			return $arr;
		} else {
			$arr_return['id'] = $arr['id'];
			$arr_login=array( "user_name"=>$arr_user["user_name"],"user_pwd"=>$arr_user["user_pwd"]);
			$arr=cls_obj::get("cls_user")->on_login($arr_login);
			if($arr["code"]!=0){
				return $arr;
			}
		}
		return $arr_return;
	}
	function on_findpwd_step1() {
		$verifycode = fun_get::get("verifycode");
		if(cls_verifycode::on_verify($verifycode) == false) {
			return array('code' => '11' , 'msg' => '验证码输入错误');
		}
		$uname = fun_get::get("uname");
		$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
		if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '输入账号不存在');
		$obj_rs = cls_obj::db()->get_one("select user_email,user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . $arr_user[$uname] . "'");
		if(empty($obj_rs)) {
			return array('code' => '500' , 'msg' => '注册账号没有邦定个人信息');
		}
		$arr_return = array('code' => '0' , 'email' => '');
		$arr_return['is_sms'] = (fun_is::com('sms'))? "1" : "0";
		$arr_return['is_email'] = (fun_is::com('email'))? "1" : "0";
		$arr_return['is_msg'] = (fun_is::com('msg'))? "1" : "0";
		$arr = explode("@" , $obj_rs['user_email']);
		if(strlen($arr[0])>3) {
			$arr_return['email'] = str_pad(substr($arr[0],0,3),strlen($arr[0]),"*") . "@" . $arr[1];
		} else if(count($arr)>1) {
			$arr_return['email'] = str_pad(substr($arr[0],0,1),strlen($arr[0]),"*") . "@" . $arr[1];
		}
		$arr_return['mobile'] = str_pad(substr($obj_rs['user_mobile'],0,3),strlen($obj_rs['user_mobile'])-4,"*") . substr($obj_rs['user_mobile'],-4);
		return $arr_return;
	}
	function on_findpwd_step2() {
		$method = (int)fun_get::get("method");
		$uname = fun_get::get("uname");
		$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
		if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '输入账号不存在');

		$obj_user = cls_obj::db()->get_one("select user_id,user_email,user_mobile from " . cls_config::DB_PRE . "sys_user where user_id='" . $arr_user[$uname] . "'");
		if(empty($obj_user)) {
			return array('code' => '500' , 'msg' => '输入账号不存在');
		}
		if($method == 1) {
			$arr_key = tab_sys_verify::get_key($obj_user['user_id'],1,$obj_user['user_email']);
			if($arr_key['code']!=0) return $arr_key;
			$url = cls_config::get("url" , 'base') . "/index.php?app_act=findpwd.email&val=" . urlencode($obj_user['user_email']) . "&key=" . $arr_key['key'];
			//取邮件内容
			$obj_cont = cls_obj::db()->get_one("select article_title,article_content from " . cls_config::DB_PRE . "article where article_key='findpwdwords'");
			if(empty($obj_cont)) {
				$obj_cont['article_title'] = cls_config::get("site_title" , "sys") . "找回密码";
				$obj_cont['article_content'] = "<a href='".$url."'>请点击链接重置登录密码</a>，如果未操作，系统将保留原密码<br>如果无法点击请复制以下代码，粘贴到浏览器地址栏访问<br>" . $url;
			} else {
				$obj_cont['article_content'] = fun_get::filter($obj_cont['article_content'] , true);
				$obj_cont['article_content'] = str_replace('{$url}' , $url , $obj_cont['article_content']);
			}
			$arr = cls_obj::get("cls_com")->email('send' , array('to_mail' => $obj_user['user_email'] , 'title' => $obj_cont['article_title'] , 'content' => $obj_cont['article_content'] ,'save' => 1));
			cls_obj::get("cls_session")->set('verify_val' , $obj_user['user_email']);
			return $arr;
		} else if($method == 2) {
			$arr_key = tab_sys_verify::get_key($obj_user['user_id'],2,$obj_user['user_mobile']);
			if($arr_key['code']!=0) return $arr_key;
			$arr = cls_obj::get('cls_com')->sms("on_send" , array("tel"=>$obj_user['user_mobile'] , "cont" => "您的验证码：" . $arr_key['key'] . ",请在网页上输入此号码，如非本人操作请忽略" ) );
			cls_obj::get("cls_session")->set('verify_val' , $obj_user['user_mobile']);
			return $arr;
		} else if($method == 3) {
			$arr_fields = array(
				"msg_name" => fun_get::get('name'),
				"msg_tel" => fun_get::get('tel'),
				"msg_cont" => fun_get::get('cont'),
				"msg_type" => 1,
				"msg_user_id" => $obj_user['user_id']
			);
			$arr = cls_obj::get("cls_com")->msg('on_save',$arr_fields);
			return $arr;
		} else {
			return array("code" => 500 , "msg" =>"传递参数有误");
		}
	}

	function on_findpwd_step3() {
		$isverify = cls_obj::get("cls_session")->get('sms_verify');
		$key = fun_get::get("key");
		$uname = fun_get::get("uname");
		$uid = fun_get::get("uid");
		$pwd = fun_get::get("pwd");
		if($uid < 1 ) {
			$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
			if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '验证账号不存在');
			$uid = $arr_user[$uname];
		} else {
			$arr_user = cls_obj::get("cls_user")->get_user($uid);
			if(empty($arr_user) || !in_array($uid , $arr_user)) return array('code' => '500' , 'msg' => '验证账号不存在');
			$uname = array_search($uid , $arr_user);
		}
		if($isverify != $uid) return array("code"=>500 , "msg" => "验证已过期，请重新验证");
		
		$arr = cls_obj::get("cls_user")->on_update_pwd('' , $pwd , $uid , false);
		if($arr["code"] != 0){
			$arr_return['code']=500;
			$arr_return['msg']=$arr['msg'];
			return $arr_return;
		}
		//注销标识
		cls_obj::get("cls_session")->destroy('sms_verify');
		return array("code"=>0,"msg"=>'');
	}

	function on_verify_mobile() {
		$mobile = cls_obj::get("cls_session")->get('verify_val');
		$key = fun_get::get("key");
		$uname = fun_get::get("uname");
		$arr_user = cls_obj::get("cls_user")->get_user($uname , false);
		if(empty($arr_user) || !isset($arr_user[$uname])) return array('code' => '500' , 'msg' => '验证账号不存在');
		$arr = tab_sys_verify::on_verify($mobile , $key , 2);
		if($arr['code'] == 0) {
			$isverify = cls_obj::get("cls_session")->set('sms_verify' , $arr_user[$uname]);//设置已验证标识
		}
		return $arr;
	}

	function on_user_save() {
		$arr_pic = fun_get::get("pic");
		if(!empty($arr_pic) && is_array($arr_pic)) {
			$arr = array();
			foreach($arr_pic as $pic) {
				if(empty($pic)) continue;
				$arr[] = $pic;
			}
			$pic = implode("||" , $arr);
		} else {
			$pic = '';
		}
		$arr_fields = array(
			'user_sign' => fun_get::get("user_sign"),
			'user_intro' => fun_get::get("user_intro"),
			'user_pics' => $pic,
			'user_id' => cls_obj::get("cls_user")->uid,
		);
		$arr = tab_quan_user::on_save($arr_fields);
		return $arr;
	}
	function on_wx_msg() {
		$type = fun_get::get("type");
		$msg = '';
		switch($type) {
			case 'ping':
				$msg = cls_obj::get("cls_user")->name . "评论了您的说说";
				break;
			case 'zan':
				$msg = cls_obj::get("cls_user")->name . "给了您一个赞";
				break;
			default:
				return array('code' => 500 , 'msg' => '消息类型不存在');
		}
		$touid = (int)fun_get::get("touid");
		if($touid == cls_obj::get("cls_user")->uid) return array("code" => 500 , "msg" => "自己不需要给自己发消息");
		$obj_user = cls_obj::db()->get_one("select user_name from " . cls_config::DB_PRE . "user where user_id='" . $touid . "'");
		if(empty($obj_user)) return array("code" => 500 , "msg" => "用户不存在");
		if(substr($obj_user['user_name'],0,6) != 'weixin') return array("code" => 500 , "msg" => "非微信账号不能发消息");
		$openid = substr($obj_user['user_name'] , 7);
		$msg = fun_get::filter(tab_weixin_message::format_text($msg , true));
		$arr_fields = array(
			"message_type"=>'text',
			"message_text"=>$msg,
			"message_media_id"=>'',
		);
		$arr_return = cls_weixin::on_send(0 , $openid , 'text' , $arr_fields);
		return $arr_return;
	}
}