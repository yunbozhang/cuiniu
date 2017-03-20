<?php
class com_userapi {
	static $objapi = array();
	static function get_cfg($plat) {
		$arr_cfg = array(
			"qq" => array('key'=>cls_config::get("qq_key","userapi") , 'secret' => cls_config::get("qq_secret","userapi")),
			"weibo" => array('key'=>cls_config::get("weibo_key","userapi") , 'secret' => cls_config::get("weibo_secret","userapi")),
			"weixin" => array('key'=>cls_config::get("appid","weixin" , '' ,'') , 'secret' => cls_config::get("appsecret","weixin",'',''))
		);
		return isset($arr_cfg[$plat]) ? $arr_cfg[$plat] : array();
	}
	static function obj($plat) {
		if(empty($plat)) {
			cls_error::on_exit("exit" , "登录信息丢失");
		}
		if(!isset(self::$objapi[$plat])) {
			require dirname(__FILE__) . "/" . $plat . '/com.userapi.' . $plat . '.php';
			$objname = 'com_userapi_' . $plat;
			self::$objapi[$plat] = new $objname;
		}
		return self::$objapi[$plat];
	}
	function login($arr) {
		$jump_url = $arr['jump_url'];
		$plat = $arr['plat'];
		$arr_cfg = self::get_cfg($plat);
		self::obj($plat)->login($arr_cfg , $jump_url);
	}
	function login_token($arr) {
		$jump_url = $arr['jump_url'];
		$plat = $arr['plat'];
		$arr_cfg = self::get_cfg($plat);
		$arr = self::obj($plat)->login_token($arr_cfg , $jump_url);
		if($arr['code'] != 0) {
			cls_error::on_exit('exit',"授权失败，<a href='" . cls_config::get("url" , 'base') . "/common.php?app=api.login&plat=" . $plat . "&jump_url=" . urlencode($jump_url) . "'>点击重新授权</a>");
		} else {
			//授权成功，查看是否已经登录过
			$obj_rs = cls_obj::db()->get_one("select login_user_id from " . cls_config::DB_PRE . "userapi_login where login_name='" . $arr['openinfo']["api_name"] . "'");
			if(empty($obj_rs) || empty($obj_rs['login_user_id']) ) {
				cls_obj::db_w()->on_insert(cls_config::DB_PRE . "userapi_login" , array("login_name"=>$arr['openinfo']["api_name"],"login_addtime"=>TIME,"login_plat"=>$plat));
				if(cls_config::get("platmode") == 1) {
					//自动注册
					$arr_fields = array('user_name' => $arr['openinfo']['api_name'] , "user_pwd" => rand(1,99999),'user_netname' => $arr['openinfo']['name'],'user_sex'=>$arr['openinfo']['sex']);
					if(isset($arr['openinfo']['pic'])) $arr_fields['user_pic'] = $arr['openinfo']['pic'];
					$arr_msg = cls_obj::get("cls_user")->on_login( $arr_fields , 2);
					if($arr_msg['code'] == 0) {
						cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "userapi_login set login_user_id='" . cls_obj::get("cls_user")->uid . "' where login_name='" . $arr['openinfo']['api_name'] . "'");
						//$arr_msg = cls_obj::get("cls_user")->on_login(array("user_id"=>$arr_msg['id']) , 1);
						fun_base::url_jump($jump_url);
					} else {
						cls_error::on_exit('exit',array("tips"=>"登录时发生错误","debug"=>$arr_msg['msg']));
					}
				} else {
					$url = cls_config::get("url","base") . "/common.php?app=api.login&app_act=bind.login&jump_url=" . urlencode($jump_url);
					fun_base::url_jump($url);
					exit;
				}
				if($plat == 'weixin') {
					//注册事件红包
					$arrx = tab_weixin_redpack::on_open(0,'reg',$arr['openinfo']['api_id']);
				}
			}
			$arr_return = cls_obj::get("cls_user")->on_login(array('user_id'=>$obj_rs['login_user_id'],'autologin'=>1) , 1);
			if($arr_return['code']!=0) {
				cls_error::on_exit('exit',array("tips"=>"登录时发生错误","debug"=>$arr_return['msg']));
			}
			$arr_update = array();
			if(!empty($arr['openinfo']['pic']) && cls_obj::get("cls_user")->pic != $arr['openinfo']['pic']) {
				cls_obj::get("cls_user")->pic = $arr['openinfo']['pic'];
				$arr_update['user_pic'] = $arr['openinfo']['pic'];
			}
			if(!empty($arr['openinfo']['name']) && cls_obj::get("cls_user")->name != $arr['openinfo']['name']) {
				cls_obj::get("cls_user")->name = $arr['openinfo']['name'];
				$arr_update['user_netname'] = $arr['openinfo']['name'];
			}
			if(!empty($arr_update)) {
				$arr_update['user_id'] = cls_obj::get("cls_user")->uid;
				$arr = tab_sys_user::on_save($arr_update);
				if(cls_obj::get("cls_user")->uid==2) {
					//print_r($arr);exit;
				}
			}
			fun_base::url_jump($jump_url);
		}
	}
}