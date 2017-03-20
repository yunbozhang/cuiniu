<?php
class ctl_api_login extends cls_base {
	function act_default() {
		$jump_url = isset($_REQUEST['jump_url']) ? $_REQUEST['jump_url'] : '';    //获取跳转地址
		$plat = fun_get::get("plat");
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$jump_url = cls_config::get("url" , 'base')."/common.php?app=api.login&app_act=token&plat=" . $plat . "&url=".urlencode($jump_url);
		$arr = cls_obj::get('cls_com')->userapi("login" , array("jump_url"=>$jump_url , "plat" => $plat));
	}
	function act_token() {
		$jump_url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
		$plat = fun_get::get("plat");
		if(empty($jump_url)) $jump_url = cls_config::get("url" , 'base');
		$arr = cls_obj::get('cls_com')->userapi("login_token" , array("jump_url"=>$jump_url , "plat" => $plat));
	}
	/* 从第三方登录回来后所跳转到的已有登录账号绑定页面
	 *
	 */
	function act_bind_login() {
		if(cls_config::get("platmode") == 1) {
			$this->act_bind_auto();return;
		}
		$jump_url = isset($_REQUEST['jump_url']) ? $_REQUEST['jump_url'] : '';    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		
		$agent = fun_get::agent();
		if(fun_is::wap()) {
			echo cls_app::on_display(KJ_WAP_DIR , '' , "index" , "login.bind" , array("openinfo" => cls_obj::get("cls_session")->get("userapi") , "jump_fromurl"=>$jump_url ) );exit;
		} else {
			$this->jump_fromurl = $jump_url;
			$this->openinfo = cls_obj::get("cls_session")->get("userapi");
			return $this->get_view(); //显示页面
		}
	}
	/* 从第三方登录回来后所跳转到的注册账号绑定页面
	 *
	 */
	function act_bind_reg() {
		if(cls_config::get("platmode") == 1) {
			$this->act_bind_auto();return;
		}
		$jump_url = isset($_REQUEST['jump_url']) ? $_REQUEST['jump_url'] : '';    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$reg_switch = cls_config::get("reg_switch" , "user");
		$reg_switch_info = cls_config::get("reg_switch_info" , "user");

		$agent = fun_get::agent();
		if(fun_is::wap()) {
			echo cls_app::on_display(KJ_WAP_DIR , '' , "index" , "reg.bind" , array("openinfo" => cls_obj::get("cls_session")->get("userapi") , "jump_fromurl"=>$jump_url , "reg_switch" => $reg_switch , "reg_switch_info" => $reg_switch_info) );exit;
		} else {
			$this->jump_fromurl = $jump_url;
			$this->reg_switch = $reg_switch;
			$this->openinfo = cls_obj::get("cls_session")->get("userapi");
			$this->reg_switch_info = $reg_switch_info;
			return $this->get_view(); //显示页面
		}


		return $this->get_view(); //显示页面
	}
	//绑定当前账号
	function act_bind() {
		if(cls_config::get("platmode") == 1) {
			$this->act_bind_auto();return;
		}
		$openinfo = cls_obj::get("cls_session")->get("userapi");
		if(empty($openinfo) || !isset($openinfo['name']) ) {
			cls_error::on_exit('exit',array("tips"=>"第三方登录信息丢失"));
		}
		$jump_url = isset($_REQUEST['jump_url']) ? $_REQUEST['jump_url'] : '';    //获取跳转地址
		if( empty($jump_url) ) $jump_url = cls_config::get("url" , "base");
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "userapi_login set login_user_id='" . cls_obj::get("cls_user")->uid . "' where login_name='" . $openinfo['api_name'] . "'");
		$arr_fields = array('id' => cls_obj::get("cls_user")->uid ,'user_netname' => $openinfo['name'],'user_sex'=>$openinfo['sex'],'user_pic'=>$openinfo['pic']);
		$arr = tab_sys_user::on_save($arr_fields);
		fun_base::url_jump($jump_url);
	}
	//不绑定
	function act_bind_auto() {
		$openinfo = cls_obj::get("cls_session")->get("userapi");
		if(empty($openinfo) || !isset($openinfo['name']) ) {
			cls_error::on_exit('exit',array("tips"=>"第三方登录信息丢失"));
		}
		$jump_url = isset($_REQUEST['jump_url']) ? $_REQUEST['jump_url'] : '';    //获取跳转地址
		if( empty($jump_url) ) $jump_url = cls_config::get("url" , "base");
		//自动注册
		$obj_login = cls_obj::db()->get_one("select login_name from " . cls_config::DB_PRE . "userapi_login where login_name='" . $openinfo['api_name'] . "'");
		if(empty($obj_login)) cls_error::on_exit('exit',array("tips"=>"第三方登录信息丢失"));
		$arr_fields = array('user_name' => 'api_' . $obj_login['login_name'] , "user_pwd" => rand(1,99999),'user_netname' => $openinfo['name'],'user_sex'=>$openinfo['sex'],'user_pic'=>$openinfo['pic']);
		$arr_msg = cls_obj::get("cls_user")->on_login( $arr_fields , 2);
		if($arr_msg['code'] == 0) {
			cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "userapi_login set login_user_id='" . cls_obj::get("cls_user")->uid . "' where login_name='" . $openinfo['api_name'] . "'");
			//$arr_msg = cls_obj::get("cls_user")->on_login(array("user_id"=>$arr_msg['id']) , 1);
			fun_base::url_jump($jump_url);
		} else {
			cls_error::on_exit('exit',array("tips"=>"登录时发生错误","debug"=>$arr_msg['msg']));
		}
	}

}