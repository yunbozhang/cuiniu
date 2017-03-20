<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class inc_mod_default extends cls_base{

	/**
	 * admin 目录 初始类，启动 : 登录检查，权限检查
	 */
	function __construct($arr_v) {
		parent::__construct($arr_v);
		$this->this_login_user = cls_obj::get("cls_user");
		$this->user_info = $this->get_quan_user(cls_obj::get('cls_user')->uid);
	}
	function get_quan_user($uid , $ismore = 0) {
		$obj_user = cls_obj::db()->edit(cls_config::DB_PRE."quan_user" , "user_id='".$uid."'");
		if(empty($obj_user['user_id'])) {
			$arr_fields = array('user_id' => $uid);
			$arr = tab_quan_user::on_insert($arr_fields);
		}
		if($ismore == 1) {
			$obj_sys_user = cls_obj::db()->get_one("select user_name,user_netname,user_pic from " . cls_config::DB_PRE . "sys_user where user_id='" . $uid . "'");
			if(empty($obj_sys_user)) {
				$obj_user['user_name'] = '';
				$obj_user['user_netname'] = '';
				$obj_user['user_pic'] = '';
			} else {
				if(empty($obj_sys_user['user_netname'])) $obj_sys_user['user_netname'] = $obj_sys_user['user_name'];
				$obj_user['user_name'] = $obj_sys_user['user_name'];
				$obj_user['user_netname'] = $obj_sys_user['user_netname'];
				$obj_user['user_pic'] = fun_get::html_url($obj_sys_user['user_pic']);
			}
		}
		if(empty($obj_user['user_pics'])) {
			$obj_user['user_pics'] = array();
		} else {
			$obj_user['user_pics'] = explode("||" , $obj_user['user_pics']);
		}
		return $obj_user;
	}

	function get_pagebtns( $arr_info , $listnum = 10 ) {
		$arr_return = array("pre" => 0, "next" => 0,"list" => array() ,"premore" => 0 , "nextmore" => 0 , "start" => 1 , "end" => $arr_info['pages']);
		if($arr_info['total'] < 1) return $arr_return;
		$arr_return['pre'] = max(0,$arr_info['page']-1);
		$arr_return['next'] = $arr_info['page']+1;
		if($arr_return['next'] > $arr_info['pages']) $arr_return['next'] = 0;

		if($arr_info["pages"] > $listnum) {
			$ii = intval($listnum/2);
			if($arr_info['page'] > $ii){
				$lng_pre=$arr_info['page'] - $ii;
				$lng_next=$arr_info['page'] + $ii;
			}else{
				$lng_pre=1;
				$lng_next = $listnum;
			}
		}else{
			$lng_pre=1;
			$lng_next=$arr_info['pages'];
		}
		if($lng_pre < 1) $lng_pre = 1;
		if( $lng_next >= $arr_info['pages'] ) $lng_next = $arr_info['pages'];
		$arr_return['start'] = $lng_pre;
		$arr_return['end'] = $lng_next;

		for($i=$lng_pre;$i<=$lng_next;$i++){
			$arr_return['list'][] = $i;
		}
		return $arr_return;
	}

	function get_isread() {
		$obj_db = cls_obj::db();
		$obj_zan = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "quan_zan a left join " . cls_config::DB_PRE . "quan_msg b on a.zan_msg_id=b.msg_id where zan_to_uid='" . cls_obj::get('cls_user')->uid . "' and zan_isread=0 and zan_cancel=0 and zan_to_uid!=zan_user_id");
		$obj_ping = $obj_db->get_one("select count(1) as 'num' from " . cls_config::DB_PRE . "quan_ping a left join " . cls_config::DB_PRE . "quan_msg b on a.ping_msg_id=b.msg_id where ping_to_uid='" . cls_obj::get('cls_user')->uid . "' and ping_isread=0 and ping_isdel=0 and ping_user_id!=ping_to_uid");
		$arr_return = array('zan' => $obj_zan['num'] , 'ping' => $obj_ping['num']);
		$arr_return['all'] = $arr_return['zan'] + $arr_return['ping'];
		return $arr_return;
	}
}