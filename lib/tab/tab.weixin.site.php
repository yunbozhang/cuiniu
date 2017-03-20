<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_weixin_site {
	static $perms;
	static $value;
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"state" => array( "正常" => 1 , "待审核" => 0 , "关闭" => -1) ,
			);
		}
		$arr_return = array();
		if(isset(self::$perms[$key])) $arr_return = self::$perms[$key];
		return $arr_return;
	}
	static function on_save($arr_fields,$where = '') {
		$arr_return = array("code"=>0,"id"=>0,"msg"=>"");
		if(isset($arr_fields['site_id'])) {
			$arr_fields['id'] = $arr_fields['site_id'];
			unset($arr_fields['id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " site_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and site_id='" . $arr_return['id'] . "'";
				}
			}
		}
		unset($arr_fields['id']);
		if(isset($arr_fields['site_domain'])) $arr_fields['site_domain'] = fun_format::domain($arr_fields['site_domain'],true);
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {
			if(!isset($arr_fields['site_shop_id']) && empty($arr_fields['site_shop_id'])) return array("code" => 500 , "msg" => "请选择绑定的店铺");
			//必填项检查
			if(!isset($arr_fields['site_domain']) || $arr_fields['site_domain'] == '' ) return array("code" => 500 , "msg" => "绑定域名不能为空");
			if(!isset($arr_fields['site_wx_uname']) || $arr_fields['site_wx_uname'] == '' ) return array("code" => 500 , "msg" => "微信号不能为空");
			$obj_rs = $obj_db->get_one("select site_wx_uname,site_domain from " . cls_config::DB_PRE . "weixin_site where site_domain='" . $arr_fields['site_domain'] . "' or site_wx_uname='" . $arr_fields['site_wx_uname'] . "'");
			if(!empty($obj_rs) && $obj_rs['site_domain'] == $arr_fields['site_domain']) return array("code" => 500 , "msg" => "绑定域名已存在");
			if(!empty($obj_rs) && $obj_rs['site_wx_uname'] == $arr_fields['site_wx_uname']) return array("code" => 500 , "msg" => "微信号已存在");
			//初始必要值
			$arr_fields['site_addtime'] = TIME;
			//插入到表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."weixin_site",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$obj_rs = $obj_db->get_one("select site_id from ".cls_config::DB_PRE."weixin_site where site_addtime='" . $arr_fields["site_addtime"] . " and site_wx_uname='" . $arr_fields["site_wx_uname"] . "'");
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['site_id'];
				}
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if(isset($arr_fields['site_domain']) && $arr_fields['site_domain'] == '' ) return array("code" => 500 , "msg" => "绑定域名不能为空");
			if(isset($arr_fields['site_wx_uname']) && $arr_fields['site_wx_uname'] == '' ) return array("code" => 500 , "msg" => "微信号不能为空");
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select site_id from ".cls_config::DB_PRE."weixin_site where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['site_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "site_id='".$arr_return['id']."'";
			}
			$obj_rs = $obj_db->get_one("select site_wx_uname,site_domain from " . cls_config::DB_PRE . "weixin_site where site_id!='" . $arr_return['id'] . "' and (site_domain='" . $arr_fields['site_domain'] . "' or site_wx_uname='" . $arr_fields['site_wx_uname'] . "')");
			if(!empty($obj_rs) && $obj_rs['site_domain'] == $arr_fields['site_domain']) return array("code" => 500 , "msg" => "绑定域名已存在");
			if(!empty($obj_rs) && $obj_rs['site_wx_uname'] == $arr_fields['site_wx_uname']) return array("code" => 500 , "msg" => "微信号已存在");

			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."weixin_site" , $arr_fields , $where);
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
		if( empty($str_id) && empty($where) ){
			$arr_return["code"] = 22;
			$arr_return["msg"]=cls_language::get("not_where");
			return $arr_return;
		}
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "site_id='".$str_id."'" : $arr_where[] = "site_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);

		$arr_return=cls_obj::db_w()->on_delete(cls_config::DB_PRE."weixin_site" , $where);
		return $arr_return;
	}
}