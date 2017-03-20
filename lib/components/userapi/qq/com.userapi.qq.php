<?php
class com_userapi_qq {
	static $openinfo = array();
	function login($arr_cfg , $jump_url) {
		fun_base::url_jump("https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" . $arr_cfg['key'] . "&redirect_uri=" . urlencode($jump_url));
	}
	/* 返回数组：code : 0 表示授权成功， 否则表示授权失败
	 * 成功时返回：openinfo ，包括：uid,uname
	 */
	function login_token($arr_cfg , $jump_url) {
		$url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=" . $arr_cfg['key'] . "&client_secret=" . $arr_cfg['secret'] . "&code=" . $_GET["code"] . "&redirect_uri=" . urlencode(cls_config::get("url","base"));
		$arr = fun_base::post($url , array() , array() , 'GET');
		$cont = isset($arr['cont']) ? $arr['cont'] : '';
		$arr = explode("&",$cont);
		$access_token = "";
		foreach($arr as $item) {
			$a = explode("=",$item);
			if(count($a)!=2) continue;
			if($a[0] == 'access_token') {
				$access_token = $a[1];
			}
		}
		if(empty($access_token)) {
			cls_error::on_exit('exit',"QQ授权失败，<a href='" . cls_config::get("url" , 'base') . "/common.php?app=api.login&plat=qq'>点击重新授权</a>");
		}
		$url = "https://graph.qq.com/oauth2.0/me?access_token=" . $access_token;
		$arr = fun_base::post($url , array() , array() , 'GET');
		$cont = $arr['cont'];
		$str = trim(substr($cont,10,-3));
		$arr = (array)json_decode($str);
		self::$openinfo = array("access_token" => $access_token , "key" => $arr_cfg['key']);
		$arr_openinfo = self::userinfo($arr['openid']);
		$arr_openinfo['access_token'] = $access_token;
		$arr_openinfo['key'] = $arr_cfg['key'];
		self::$openinfo = $arr_openinfo;
		//保存到session 表中
		cls_obj::get("cls_session")->set("userapi",$arr_openinfo);
		return array('code' => 0 , 'openinfo' => $arr_openinfo);
	}
	//取指定用户信息
	function userinfo($uid = 0) {
		if(empty(self::$openinfo)) {
			self::$openinfo = cls_obj::get("cls_session")->get("userapi");
		}
		if(empty($uid)) $uid = self::$openinfo['id'];
		$url = "https://graph.qq.com/user/get_user_info?access_token=" . self::$openinfo['access_token'] . "&oauth_consumer_key=" . self::$openinfo['key'] . "&openid=" . $uid;
		$arr = fun_base::post($url , array() , array() , 'GET');
		$cont = $arr['cont'];
		if(empty($cont)) {
			$cont = file_get_contents($url);
		}
		$arr_userinfo = array('name'=>'','pic'=>'','sex'=>0);
		if(!empty($cont)) {
			$arr = json_decode($cont,true);
			if( $arr['ret']==0 ) {
				$arr_userinfo['name'] = $arr['nickname'];
				$arr_userinfo['pic'] = $arr['figureurl_2'];
				if($arr['gender'] == '男') {
					$arr_userinfo['sex'] = 1;
				} else if($arr['gender'] == '女') {
					$arr_userinfo['sex'] = 2;
				}
			}
		}
		$arr_userinfo['api_name'] = "qq_".$uid;
		$arr_userinfo['api_id'] = $uid;
		return $arr_userinfo;
	}
}