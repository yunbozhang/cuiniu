<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_weixin_menu {
	static $perms;

	//获取表配置参数
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}

	/* 保存操作
	 * arr_fields : 为字段数据，默认如果包函 id，则为修改，否则为插入
	 * where : 默认为空，用于有时候条件修改
	 */
	static function on_save($arr_fields , $where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['menu_id'])) {
			$arr_fields['id'] = $arr_fields['menu_id'];
			unset($arr_fields['menu_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " menu_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and menu_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['menu_name']) || empty($arr_fields['menu_name'])) return array('code' => 500 , 'msg' => '菜单名称不能为空');
			//初始默认值
			$arr_fields['menu_addtime'] = $arr_fields['menu_updatetime'] = TIME;
			if(!isset($arr_fields["menu_pid"])) $arr_fields["menu_pid"] = 0;
			//检查菜单数量
			$obj_rs = $obj_db->get_one("select count(1) as num from " . cls_config::DB_PRE . "weixin_menu where menu_pid='" . $arr_fields['menu_pid'] . "' and menu_site_id='" . $arr_fields['menu_site_id']  . "'");
			if($obj_rs['num']>=3 && empty($arr_fields['menu_pid'])) return array("code" => 500 , "msg" => "一级菜单最多只能为3个");
			if($obj_rs['num']>=5 && !empty($arr_fields['menu_pid'])) return array("code" => 500 , "msg" => "二级菜单最多只能为5个");

			if(!isset($arr_fields['menu_sort']) || empty($arr_fields['menu_sort'])) {
				$obj_rs = $obj_db->get_one("select max(menu_sort) as sort from " . cls_config::DB_PRE . "weixin_menu where menu_pid='" . $arr_fields["menu_pid"] . "' and menu_site_id='" . $arr_fields['menu_site_id']  . "'");
				(!empty($obj_rs))? $arr_fields['menu_sort'] = $obj_rs["sort"] + 1 : $arr_fields['menu_sort'] = 1;
			}
			if($arr_fields["menu_pid"] > 0) {
				$obj_rs = $obj_db->get_one("select menu_pids from ".cls_config::DB_PRE."weixin_menu where menu_id=".$arr_fields["menu_pid"]);
				if(!empty($obj_rs) && !empty($obj_rs["menu_pids"])) {
					$arr_fields["menu_pids"] = $obj_rs["menu_pids"] . "," . $arr_fields["menu_pid"];
				} else {
					$arr_fields["menu_pids"] = $arr_fields["menu_pid"];
				}
			}
			//插入到用户表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."weixin_menu",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$where  = "menu_sort='" . $arr_fields['menu_sort'] . " and menu_addtime=" . $arr_fields['menu_addtime'] . " and menu_name='".$arr_fields['menu_name'] . "' and menu_pid='".$arr_fields["menu_pid"]."'";
					$obj_rs = $obj_db->get_one("select menu_id from ".cls_config::DB_PRE."weixin_menu where ".$where);
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['menu_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {

			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select menu_id from ".cls_config::DB_PRE."weixin_menu where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['menu_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "menu_id='".$arr_return['id']."'";
			}
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."weixin_menu" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
	/* 删除函数
	 * arr_id : 要删除的 id数组
	 * where : 删除附加条件
	 */
	static function on_delete($arr_id , $where = '') {
		$arr_return = array("code"=>0,"msg"=>"");
		$str_id = fun_format::arr_id($arr_id);
		if( $str_id == "" && empty($where) ){
			$arr_return["code"] = 22;
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "menu_id='".$str_id."'" : $arr_where[] = "menu_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);
		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."weixin_menu" , $where);
		return $arr_return;
	}


	/** 按层次返回列表记录
	 *	pid : 指定父级id , layer : 当前层次 ，where : 附加条件
	 */
	static function get_list_layer($site_id = 0 , $pid = 0 , $layer = 1 , $where = '' , $maxlayer = 0) {
		if(!empty($maxlayer) && $maxlayer==$layer) return array("list" => array());
		$arr_list = array();
		$max_layer = 0;
		$obj_db = cls_obj::db_w();
		$str_where = " where menu_pid='".$pid . "' and menu_site_id='" . $site_id . "'";
		if($where != '') $str_where .= " and " . $where;
		$obj_result = $obj_db->select("SELECT * FROM ".cls_config::DB_PRE."weixin_menu" . $str_where . " order by menu_sort,menu_id");
		while( $obj_rs = $obj_db->fetch_array($obj_result) ) {
			$obj_rs["layer"] = $layer;
			if($layer > $max_layer) $max_layer = $layer;
			$arr_list[] = $obj_rs;
			$arr = self::get_list_layer($site_id , $obj_rs["menu_id"] , $layer+1 , $where , $maxlayer);
			if( count($arr["list"])>0 ) {
				$arr_list = array_merge($arr_list , $arr["list"]);
				if($arr["maxlayer"] > $max_layer) $max_layer = $arr["maxlayer"];
			}
		}
		$arr_return=array("list" => $arr_list , "maxlayer" => $max_layer);
		return $arr_return ;
	}

}