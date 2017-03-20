<?php
class com_userapi_weibo {
	static $openinfo = array();
	function login($arr_cfg , $jump_url) {
		fun_base::url_jump("https://api.weibo.com/oauth2/authorize?client_id=" . $arr_cfg['key'] . "&redirect_uri=" . urlencode($jump_url));
	}
	/* 返回数组：code : 0 表示授权成功， 否则表示授权失败
	 * 成功时返回：openinfo ，包括：uid,uname
	 */
	function login_token($arr_cfg , $jump_url) {
		$url = "https://api.weibo.com/oauth2/access_token";
		$data = "client_id=" . $arr_cfg['key'] . "&client_secret=" . $arr_cfg['secret'] . "&grant_type=authorization_code&code=" . $_GET["code"] . "&redirect_uri=" . urlencode(cls_config::get("url","base"));
		$arr = fun_base::post($url,$data);
		if($arr['code']!=0) {
			return array("code" => 500 , "msg" => "授权失败1，");
		}
		$arr = json_decode($arr['cont'],true);
		if(!isset($arr['uid']) || !isset($arr['access_token']) ) {
			return array("code" => 500 , "msg" => "授权失败2，");
		}
		self::$openinfo = array("access_token" => $arr['access_token'] , "key" => $arr_cfg['key'] , "uid" => $arr['uid']);
		$arr_openinfo = self::userinfo($arr['uid']);
		$arr_openinfo['access_token'] = self::$openinfo['access_token'];
		$arr_openinfo['key'] = $arr_cfg['key'];
		self::$openinfo = $arr_openinfo;
		//保存到session 表中
		cls_obj::get("cls_session")->set("userapi",$arr_openinfo);
		return array('code' => 0 , 'openinfo' => $arr_openinfo);
	}
	//取指定用户信息
	function userinfo($uid = 0) {
		if(empty($uid)) $uid = self::$openinfo['uid'];
		$url = "https://api.weibo.com/2/users/show.json?access_token=" . self::$openinfo['access_token'] . "&uid=" . $uid ;
		$arr = fun_base::post($url , array() , array() , 'GET');
		$cont = $arr['cont'];
		$arr_userinfo = array('name'=>'','pic'=>'','sex'=>0);
		if(!empty($cont)) {
			$arr = json_decode($cont,true);
			if( isset($arr['id']) ) {
				$arr_userinfo = array('name' => $arr['name'] , 'pic' => $arr['profile_image_url']);
				if($arr['gender'] == 'm') {
					$arr_userinfo['sex'] = 1;
				} else if($arr['gender'] == 'w') {
					$arr_userinfo['sex'] = 2;
				}
			}
		}
		$arr_userinfo['api_name'] = "weibo_".$uid;
		$arr_userinfo['api_id'] = $uid;
		return $arr_userinfo;
	}
}