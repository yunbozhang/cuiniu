<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_user extends inc_mod_default {
	function get_quan_zan() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page");
		$lng_pagesize = (int)fun_get::get('pagesize');
		if(empty($lng_pagesize)) $lng_pagesize = (int)cls_config::get("index_pagesize","view");
		if(empty($lng_pagesize)) $lng_pagesize = 20;
		$arr_return = array("list" => array());
		$arr_where = array("zan_user_id='" . cls_obj::get("cls_user")->uid . "' and zan_to_uid!=zan_user_id");
		$sort = ' order by zan_id desc';
		$where = " where " . implode(" and " , $arr_where);
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."quan_zan" , $where , $page , $lng_pagesize);
		$limit = $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select("select b.msg_id,b.msg_cont,a.zan_addtime,b.msg_user_id,b.msg_cont,b.msg_id,b.msg_pics from " . cls_config::DB_PRE . "quan_zan a left join " . cls_config::DB_PRE . "quan_msg b on a.zan_msg_id=b.msg_id" . $where . $sort . $limit);
		$arr_uid = $arr_msg_id = array();
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!in_array($obj_rs['msg_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['msg_user_id'];
			$obj_rs['pic'] = empty($obj_rs['msg_pics']) ? array() : explode("||" , $obj_rs['msg_pics']);
			$obj_rs['pic'] = empty($obj_rs['pic']) ? '' : $obj_rs['pic'][0];
			$obj_rs['time'] = fun_get::timer($obj_rs['zan_addtime']);
			$arr_return['list'][] = $obj_rs;
		}
		if(!empty($arr_uid)) {
			$ids = implode("," , $arr_uid);
			$obj_result = $obj_db->select("select user_id,user_name,user_netname,user_pic from " . cls_config::DB_PRE . "sys_user where user_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(empty($obj_rs['user_netname'])) $obj_rs['user_netname'] = $obj_rs['user_name'];
				$arr_return['user']['id_' . $obj_rs['user_id']] = $obj_rs;
			}
		}
		return $arr_return;
	}
	function get_quan_ping() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page");
		$lng_pagesize = (int)fun_get::get('pagesize');
		if(empty($lng_pagesize)) $lng_pagesize = (int)cls_config::get("index_pagesize","view");
		if(empty($lng_pagesize)) $lng_pagesize = 20;
		$arr_return = array("list" => array());
		$arr_where = array("ping_user_id='" . cls_obj::get("cls_user")->uid . "' and ping_to_uid!=ping_user_id");
		$sort = ' order by ping_id desc';
		$where = " where " . implode(" and " , $arr_where);
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."quan_ping" , $where , $page , $lng_pagesize);
		$limit = $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select("select b.msg_id,b.msg_cont,a.ping_addtime,a.ping_cont,b.msg_user_id,b.msg_cont,b.msg_id,b.msg_pics from " . cls_config::DB_PRE . "quan_ping a left join " . cls_config::DB_PRE . "quan_msg b on a.ping_msg_id=b.msg_id" . $where . $sort . $limit);
		$arr_uid = $arr_msg_id = array();
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!in_array($obj_rs['msg_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['msg_user_id'];
			$obj_rs['pic'] = empty($obj_rs['msg_pics']) ? array() : explode("||" , $obj_rs['msg_pics']);
			$obj_rs['pic'] = empty($obj_rs['pic']) ? '' : $obj_rs['pic'][0];
			$obj_rs['time'] = fun_get::timer($obj_rs['ping_addtime']);
			$arr_return['list'][] = $obj_rs;
		}
		if(!empty($arr_uid)) {
			$ids = implode("," , $arr_uid);
			$obj_result = $obj_db->select("select user_id,user_name,user_netname,user_pic from " . cls_config::DB_PRE . "sys_user where user_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(empty($obj_rs['user_netname'])) $obj_rs['user_netname'] = $obj_rs['user_name'];
				$arr_return['user']['id_' . $obj_rs['user_id']] = $obj_rs;
			}
		}
		return $arr_return;
	}
	function get_quan_bzan() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page");
		$lng_pagesize = (int)fun_get::get('pagesize');
		if(empty($lng_pagesize)) $lng_pagesize = (int)cls_config::get("index_pagesize","view");
		if(empty($lng_pagesize)) $lng_pagesize = 20;
		$arr_return = array("list" => array());
		$arr_where = array("zan_to_uid='" . cls_obj::get("cls_user")->uid . "' and zan_to_uid!=zan_user_id");
		$sort = ' order by zan_id desc';
		$where = " where " . implode(" and " , $arr_where);
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."quan_zan" , $where , $page , $lng_pagesize);
		$limit = $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select("select b.msg_id,b.msg_cont,a.zan_addtime,a.zan_user_id,b.msg_cont,b.msg_id,b.msg_pics from " . cls_config::DB_PRE . "quan_zan a left join " . cls_config::DB_PRE . "quan_msg b on a.zan_msg_id=b.msg_id" . $where . $sort . $limit);
		$arr_uid = $arr_msg_id = array();
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!in_array($obj_rs['zan_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['zan_user_id'];
			$obj_rs['pic'] = empty($obj_rs['msg_pics']) ? array() : explode("||" , $obj_rs['msg_pics']);
			$obj_rs['pic'] = empty($obj_rs['pic']) ? '' : $obj_rs['pic'][0];
			$obj_rs['time'] = fun_get::timer($obj_rs['zan_addtime']);
			$arr_return['list'][] = $obj_rs;
		}
		if(!empty($arr_uid)) {
			$ids = implode("," , $arr_uid);
			$obj_result = $obj_db->select("select user_id,user_name,user_netname,user_pic from " . cls_config::DB_PRE . "sys_user where user_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(empty($obj_rs['user_netname'])) $obj_rs['user_netname'] = $obj_rs['user_name'];
				$arr_return['user']['id_' . $obj_rs['user_id']] = $obj_rs;
			}
		}
		return $arr_return;
	}
	function get_quan_bping() {
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page");
		$lng_pagesize = (int)fun_get::get('pagesize');
		if(empty($lng_pagesize)) $lng_pagesize = (int)cls_config::get("index_pagesize","view");
		if(empty($lng_pagesize)) $lng_pagesize = 20;
		$arr_return = array("list" => array());
		$arr_where = array("ping_to_uid='" . cls_obj::get("cls_user")->uid . "' and ping_user_id!=ping_to_uid");
		$sort = ' order by ping_id desc';
		$where = " where " . implode(" and " , $arr_where);
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."quan_ping" , $where , $page , $lng_pagesize);
		$limit = $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select("select b.msg_id,b.msg_cont,a.ping_addtime,a.ping_cont,a.ping_user_id,b.msg_cont,b.msg_id,b.msg_pics from " . cls_config::DB_PRE . "quan_ping a left join " . cls_config::DB_PRE . "quan_msg b on a.ping_msg_id=b.msg_id" . $where . $sort . $limit);
		$arr_uid = $arr_msg_id = array();
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!in_array($obj_rs['ping_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['ping_user_id'];
			$obj_rs['pic'] = empty($obj_rs['msg_pics']) ? array() : explode("||" , $obj_rs['msg_pics']);
			$obj_rs['pic'] = empty($obj_rs['pic']) ? '' : $obj_rs['pic'][0];
			$obj_rs['time'] = fun_get::timer($obj_rs['ping_addtime']);
			$arr_return['list'][] = $obj_rs;
		}
		if(!empty($arr_uid)) {
			$ids = implode("," , $arr_uid);
			$obj_result = $obj_db->select("select user_id,user_name,user_netname,user_pic from " . cls_config::DB_PRE . "sys_user where user_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				if(empty($obj_rs['user_netname'])) $obj_rs['user_netname'] = $obj_rs['user_name'];
				$arr_return['user']['id_' . $obj_rs['user_id']] = $obj_rs;
			}
		}
		return $arr_return;
	}
}