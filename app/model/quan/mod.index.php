 <?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_index extends inc_mod_default {
	function get_quan_category() {
		$obj_db = cls_obj::db();
		$arr_category = array();
		$arr_result = $obj_db->select('select * from ' . cls_config::DB_PRE . "quan_category order by category_pid,category_sort");
		while($obj_rs = $obj_db->fetch_array($arr_result)) {
			$depth = $obj_rs['category_depth']-1;
			$arr_category[$depth]['id_'.$obj_rs['category_pid']][] = $obj_rs;
		}
		return $arr_category;
	}
	function get_msg_list($uid = 0 , $arr_where = array()) {
		$s_key = fun_get::get("s_key");
		$obj_db = cls_obj::db();
		$cid = (int)fun_get::get("cid");
		$page = (int)fun_get::get("page");
		$lng_pagesize = (int)fun_get::get('pagesize');
		if(empty($lng_pagesize)) $lng_pagesize = (int)cls_config::get("index_pagesize","view");
		if(empty($lng_pagesize)) $lng_pagesize = 20;
		$arr_return = array("list" => array() , 'zanid' => array() , 'pingid' => array());
		$arr_where[] = "msg_state>0";
		if(!empty($s_key)) $arr_where[] = "msg_cont like '%" . $s_key . "%'";
		if(!empty($cid)) $arr_where[] = "concat(',',category_pids,',') like '%," . $cid . ",%'";
		if(!empty($uid)) {
			$arr_where[] = "msg_user_id='" . $uid ."'";
		}
		$sort = ' order by msg_id desc';
		$where = " where " . implode(" and " , $arr_where);
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."quan_msg a left join " . cls_config::DB_PRE . "quan_category b on a.msg_category_id=b.category_id" , $where , $page , $lng_pagesize);
		$limit = $arr_return['pageinfo']['limit'];
		$obj_result = $obj_db->select("select a.*,b.category_pids from " . cls_config::DB_PRE . "quan_msg a left join " . cls_config::DB_PRE . "quan_category b on a.msg_category_id=b.category_id" . $where . $sort . $limit);
		$arr_uid = $arr_msg_id = array();
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			/*
			$obj_rs['zan'] = $obj_rs['ping'] = array();
			$arr_hudong = array();
			if(!empty($obj_rs['msg_hudong'])) $arr_hudong = unserialize($obj_rs['msg_hudong']);
			$obj_rs['zan'] = empty($obj_rs['msg_zan_ids']) ? array() : explode("," , $obj_rs['msg_zan_ids']);
			if(isset($arr_hudong['ping'])) $obj_rs['ping'] = $arr_hudong['ping'];
			foreach($obj_rs['zan'] as $item) {
				if(!in_array($item , $arr_uid)) $arr_uid[] = $item;
			}
			foreach($obj_rs['ping'] as $item) {
				if(!in_array($item['uid'] , $arr_uid)) $arr_uid[] = $item['uid'];
			}
			*/
			$arrx = explode(" " , $obj_rs['msg_category_name']);
			$arry = explode("," , $obj_rs['category_pids']);
			$arrz = array();
			for($i = 0 ; $i < count($arrx) ; $i++) {
				if(!isset($arrx[$i]) || !isset($arry[$i])) continue;
				$arrz[] = '<label onclick="javascript:thisjs.selcategory(' . $arry[$i] . ',\'' . $arrx[$i] . '\',1);">' . $arrx[$i] . '</label>';
			}
			$obj_rs['msg_category_name'] = implode("&nbsp»&nbsp;" , $arrz);
			if(!in_array($obj_rs['msg_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['msg_user_id'];
			$obj_rs['msg_cont']=fun_format::wap_tel($obj_rs['msg_cont']);
			$obj_rs['pics'] = empty($obj_rs['msg_pics']) ? array() : explode("||" , $obj_rs['msg_pics']);
			$obj_rs['time'] = fun_get::timer($obj_rs['msg_addtime']);
			$arr_msg_id[] = $obj_rs['msg_id'];
			$obj_rs['zan'] = $obj_rs['ping'] = array();
			$arr_return['list'][] = $obj_rs;
		}
		if(!empty($arr_msg_id)) {
			$arr_zan = $arr_ping = array();
			$ids = implode("," , $arr_msg_id);
			$obj_result = $obj_db->select("select zan_id,zan_msg_id,zan_user_id from " . cls_config::DB_PRE . "quan_zan where zan_cancel=0 and zan_msg_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_zan['id_msg_' . $obj_rs['zan_msg_id']][] = $obj_rs;
				if(!in_array($obj_rs['zan_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['zan_user_id'];
				if($obj_rs['zan_user_id'] == cls_obj::get("cls_user")->uid) $arr_return['zanid'][] = $obj_rs['zan_msg_id'];
			}
			for($i = 0 ; $i < count($arr_return['list']) ; $i++) {
				if(isset($arr_zan['id_msg_' . $arr_return['list'][$i]['msg_id']])) {
					$arr_return['list'][$i]['zan'] = $arr_zan['id_msg_' . $arr_return['list'][$i]['msg_id']];
				}
			}
			$ids = implode("," , $arr_msg_id);
			$obj_result = $obj_db->select("select ping_id,ping_user_id,ping_cont,ping_msg_id from " . cls_config::DB_PRE . "quan_ping where ping_isdel=0 and ping_msg_id in(" . $ids . ")");
			while($obj_rs = $obj_db->fetch_array($obj_result)) {
				$arr_ping['id_msg_' . $obj_rs['ping_msg_id']][] = $obj_rs;
				if(!in_array($obj_rs['ping_user_id'] , $arr_uid)) $arr_uid[] = $obj_rs['ping_user_id'];
				if($obj_rs['ping_user_id'] == cls_obj::get("cls_user")->uid) $arr_return['pingid'][] = $obj_rs['ping_msg_id'];
			}
			for($i = 0 ; $i < count($arr_return['list']) ; $i++) {
				if(isset($arr_ping['id_msg_' . $arr_return['list'][$i]['msg_id']])) $arr_return['list'][$i]['ping'] = $arr_ping['id_msg_' . $arr_return['list'][$i]['msg_id']];
			}
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