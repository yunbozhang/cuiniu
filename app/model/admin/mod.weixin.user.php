<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_weixin_user extends inc_mod_weixin {
	function get_pagelist() {
		$arr_where = array();
		$arr_where_s = array();
		$str_where = '';
		$lng_issearch = 0;
		//取查询参数
		$arr_search_key = array(
			'addtime1' => fun_get::get("s_addtime1"),
			'addtime2' => fun_get::get("s_addtime2"),
			'key' => fun_get::get("s_key"),
			'area' => (int)fun_get::get("s_area"),
			'state' => (int)fun_get::get("s_state",-999),
		);
		if( fun_is::isdate( $arr_search_key['addtime1'] ) ) $arr_where_s[] = "user_addtime >= '" . strtotime( $arr_search_key['addtime1'] ) . "'"; 
		if( fun_is::isdate( $arr_search_key['addtime2'] ) ) $arr_where_s[] = "user_addtime <= '" . fun_get::endtime($arr_search_key['addtime2']) . "'"; 
		if( $arr_search_key['key'] != '' ) $arr_where_s[] = "user_name like '%" . $arr_search_key['key'] . "%'";
		if( $arr_search_key['area'] != '' ) $arr_where_s[] = "user_area like '%" . $arr_search_key['area'] . "%'";
		if($arr_search_key['state'] !=-999 ) $arr_where_s[] = "user_state='" . $arr_search_key['state'] . "'";
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$arr_where[] = "user_site_id='" . $site_id . "'";
		//合并查询数组
		$arr_where = array_merge($arr_where , $arr_where_s);
		if(count($arr_where)>0) $str_where = " where " . implode(" and " , $arr_where);
		$arr_return = $this->sql_list($str_where , (int)fun_get::get('page'));

		if( count($arr_where_s) > 0 ) $lng_issearch = 1;
		$arr_return['issearch'] = $lng_issearch;

		return $arr_return;
	}


	/* 实现按具体条件查询数据表，并返回分页信息
	 * str_where : sql 查询条件 , lng_page : 当前页
	 */
	function sql_list($str_where = "" , $lng_page = 1) {
		$arr_return = array("list" => array());
		$obj_db = cls_obj::db();
		//取字段信息
		$arr_cfg_fields = tab_sys_user_config::get_fields("weixin.user" , $this->app_dir , "weixin");
		$arr_return['tabtd'] = $arr_cfg_fields["tabtd"];
		$arr_return['tabtit'] = $arr_cfg_fields["tabtit"];
		//取排序字段
		$arr_config_info = tab_sys_user_config::get_info("weixin.user"  , $this->app_dir);
		$sort = $arr_config_info["sortby"];
		$lng_pagesize = $arr_config_info["pagesize"];
		$arr_return["sort"] = $arr_config_info["sort"];
		//取分页信息
		$arr_uid = array();
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."weixin_user" , $str_where , $lng_page , $lng_pagesize);
		$obj_result = $obj_db->select("SELECT " . $arr_cfg_fields["sel"] . " FROM ".cls_config::DB_PRE."weixin_user " . $str_where . $sort . $arr_return['pageinfo']['limit']);
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			if(isset($obj_rs['user_state'])) {
				$obj_rs['user_state'] = ($obj_rs['user_state']==1) ? "关注中" : "<font style='color:#ff0000'>已取消关注</font>";
			}
			if(isset($obj_rs['user_addtime'])) $obj_rs['user_addtime'] = date("Y-m-d H:i" , $obj_rs['user_addtime']);
			$arr_return["list"][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	function on_sendmsg() {
		$openid = fun_get::get("openid");
		$type = fun_get::post("message_type");
		if(empty($type)) $type = 'text';
		$arr_fields = array(
			"message_type"=>$type,
			"message_text"=>fun_get::post("message_text"),
			"message_media_id"=>fun_get::post("message_media_id"),
		);
		$arr_fields = $this->get_message_text($arr_fields);
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$arr_return = cls_weixin::on_send($site_id , $openid , $type , $arr_fields);
		return $arr_return;
	}
	function get_message_text($arr_message) {
		switch($arr_message['message_type']) {
			case 'text':
				$arr_message['message_text'] = fun_get::filter(tab_weixin_message::format_text(fun_get::get("message_text")) , true);
				break;
			case 'news':
				$message_title = fun_get::get("message_title");
				$message_url = fun_get::get("message_url");
				$message_pic = fun_get::get("message_pic");
				$message_desc = fun_get::filter(fun_get::get("message_desc"),true);
				$ii = count($message_title);
				$arr_news = array();
				$len = count($message_title);
				for($ii = 0 ; $ii < $len ; $ii++) {
					$arr_news[] = array(
						'id' => $ii,
						'title' => $message_title[$ii],
						'url' => $message_url[$ii],
						'pic' => $message_pic[$ii],
						'desc' => $message_desc[$ii],
					);
				}
				$arr_message['news'] = $arr_news;
				break;
			default:
				$arr_message['message_media_id'] = fun_get::get("message_media_id");
		}
		return $arr_message;
	}
	function on_tongbu() {
		$next_openid = fun_get::get("next_openid");
		$cache_file = KJ_DIR_CACHE . "/data/weixin/wxuser.php";
		$arr_list = cls_weixin::get_userlist($next_openid);
		if(empty($next_openid) ) {
			$arr_user = $arr_list['data']['openid'];
		} else {
			$arr_user = (is_file($cache_file)) ?  include($cache_file) : array();
		}
		$total = count($arr_user);
		$val=var_export($arr_user,true);
		$val = '<'.'?php'.chr(10).'return '.$val.";";
		fun_file::file_create($cache_file,$val,1);
		return array('code' => 0 , 'next_openid' => $arr_list['next_openid'] , 'total' => $total);
	}
	function on_tongbu_info() {
		$page = (int)fun_get::get("page");
		$cache_file = KJ_DIR_CACHE . "/data/weixin/wxuser.php";
		$arr_user = (is_file($cache_file)) ?  include($cache_file) : array();
		$total = count($arr_user);
		if($page>=$total) return array('code' => 0 , 'nextpage' => 0);
		$userinfo = cls_weixin::get_userinfo($arr_user[$page]);
		if(!empty($userinfo) && !empty($userinfo['openid'])) {
			$sex = '未知';
			if($userinfo['sex'] == 1) $sex = '男';
			if($userinfo['sex'] == 2) $sex = '女';
			$arr_fields = array(
				'user_openid' => $userinfo['openid'],
				'user_pic' => $userinfo['headimgurl'],
				'user_addtime' => $userinfo['subscribe_time'],
				'user_area' => $userinfo['country'] . " " . $userinfo['province'] . " " . $userinfo['city'],
				'user_sex' => $sex,
				'user_name' => $userinfo['nickname'],
				'user_state' => $userinfo['subscribe'],
			);
			$arr = tab_weixin_user::on_save($arr_fields);
			$obj_user = cls_obj::db()->get_one("select user_id from " . cls_config::DB_PRE . "user where user_name='weixin_" . $userinfo['openid'] . "'");
			if(!empty($obj_user)) {
				$arr_fields = array(
					'user_id' => $obj_user['user_id'],
					'user_pic' => $userinfo['headimgurl'],
					'user_sex' => $userinfo['sex'],
					'user_netname' => $userinfo['nickname'],
					'user_location' => $arr_fields['user_area'],
				);
				tab_sys_user::on_save($arr_fields);
			}
		}
		$nextpage = $page + 1;
		if($nextpage>=$total) $nextpage = 0;
		return array('code' => 0 , 'nextpage' => $nextpage , 'total' => $total);
	}
}