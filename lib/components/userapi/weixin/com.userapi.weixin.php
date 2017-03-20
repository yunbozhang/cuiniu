<?php
class com_userapi_weixin {
	static $openinfo = array();
	function login($arr_cfg , $jump_url) {
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $arr_cfg['key'] . '&redirect_uri=' . urlencode($jump_url) . '&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect';
		fun_base::url_jump($url);
	}
	/* 返回数组：code : 0 表示授权成功， 否则表示授权失败
	 * 成功时返回：openinfo ，包括：uid,uname
	 */
	function login_token($arr_cfg , $jump_url) {
		$weixintoken = cls_obj::get("cls_session")->get("weixintoken");
		$arr = $arr_x = array();
		if(!empty($weixintoken)) {
			$arr_x = unserialize($weixintoken);
			//缓存时间
			if(isset($arr_x['time']) && (TIME-(int)$arr_x['time']<100) ) {
				$arr = $arr_x;
			}
		}
		if(empty($arr)) {
			$code = fun_get::get("code");
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $arr_cfg['key'] . "&secret=" . $arr_cfg['secret'] . "&code=" . $code . "&grant_type=authorization_code";
			$arr = fun_base::post($url , array() , array() , 'GET');
		}
		if($arr['code'] == 0 && !empty($arr['cont'])) {
			$arr_val = fun_format::toarray($arr['cont']);
			if(isset($arr_val['access_token'])) {
				//保存session防止重复点击
				$arr['time'] = TIME;
				if(empty($arr_x)) cls_obj::get("cls_session")->set("weixintoken",serialize($arr));
			} else {
				cls_error::on_exit('exit',"微信授权失败，错误代码：" . $arr_val['errcode'] . "，类型：" . $arr_val['errmsg']);
			}
		} else {
			cls_error::on_exit('exit',"微信授权失败");
		}
		self::$openinfo = array("access_token" => $arr_val['access_token'] , "key" => $arr_cfg['key'] , "scope" => strtolower($arr_val['scope']));
		$arr_openinfo = self::userinfo($arr_val['openid']);
		$arr_openinfo['access_token'] = $arr_val['access_token'];
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
		$arr_userinfo = array('name'=>'','pic'=>'','sex'=>0);
		if(self::$openinfo['scope'] != 'snsapi_userinfo') return $arr_userinfo;

		if(empty($uid)) $uid = self::$openinfo['id'];
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . self::$openinfo['access_token'] . "&openid=" . $uid . "&lang=zh_CN";
		$arr = fun_base::post($url , array() , array() , 'GET');
		$cont = $arr['cont'];
		if(!empty($cont)) {
			$arr = json_decode($cont,true);
			if( isset($arr['openid']) ) {
				$arr_userinfo['name'] = fun_format::emoji($arr['nickname']);
				$arr_userinfo['pic'] = $arr['headimgurl'];
				if($arr['sex'] == '1') {
					$arr_userinfo['sex'] = 1;
				} else if($arr['sex'] == '2') {
					$arr_userinfo['sex'] = 2;
				}
			}
		}
		$arr_userinfo['api_name'] = "weixin_".$uid;
		$arr_userinfo['api_id'] = $uid;
		return $arr_userinfo;
	}
}