<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_quan_category {
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
		if(isset($arr_fields['category_id'])) {
			$arr_fields['id'] = $arr_fields['category_id'];
			unset($arr_fields['category_id']);
		}
		if( isset($arr_fields['id']) ) {
			$arr_return['id'] = (int)$arr_fields['id'];
			unset($arr_fields['id']);
			if( $arr_return['id'] > 0 ) { //大于零，为修改状态
				if( empty($where) ){
					$where = " category_id='" . $arr_return['id'] . "'";
				} else {
					$where = "(" . $where . ") and category_id='" . $arr_return['id'] . "'";
				}
			}
		}
		$obj_db = cls_obj::db_w();
		if( empty($where) ) {

			//必填项检查
			if(!isset($arr_fields['category_name']) || empty($arr_fields['category_name'])) {
				$arr_return['code'] = 113;
				$arr_return['msg']  = cls_language::get("category_name_is_null");//地区名称
				return $arr_return;
			}
			
			//初始默认值
			if(!isset($arr_fields["category_pid"])) $arr_fields["category_pid"] = 0;
			if(!isset($arr_fields['category_sort']) || empty($arr_fields['category_sort'])) {
				$obj_rs = $obj_db->get_one("select max(category_sort) as sort from " . cls_config::DB_PRE . "quan_category where category_pid=" . $arr_fields["category_pid"]);
				(!empty($obj_rs))? $arr_fields['category_sort'] = $obj_rs["sort"] + 1 : $arr_fields['category_sort'] = 1;
			}
			$arr_fields["category_depth"] = 1;
			//插入到用户表
			$arr = $obj_db->on_insert(cls_config::DB_PRE."quan_category",$arr_fields);
			if($arr['code'] == 0) {
				$arr_return['id'] = $obj_db->insert_id();
				//其它非mysql数据库不支持insert_id 时
				if(empty($arr_return['id'])) {
					$where  = "category_sort='" . $arr_fields['category_sort'] . " and category_name='".$arr_fields['category_name'] . "' and category_pid='".$arr_fields["category_pid"]."'";
					$obj_rs = $obj_db->get_one("select category_id from ".cls_config::DB_PRE."quan_category where ".$where);
					if(!empty($obj_rs)) $arr_return['id'] = $obj_rs['category_id'];
				}
				self::on_tongbu($arr_fields['category_pid'] , $arr_return['id']);
			} else {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		} else {
			if($arr_return['id'] < 1) {
				$obj_rs = $obj_db->get_one("select category_id from ".cls_config::DB_PRE."quan_category where ".$where);
				if(!empty($obj_rs)) {
					$arr_return['id'] = $obj_rs['category_id'];
				} else {
					$arr_return['code'] = 114;
					$arr_return['msg']  = cls_language::get("no_editinfo");//修改信息不在在
					return $arr_return;
				}
				$where = "category_id='".$arr_return['id']."'";
			}
			//不允许修改父id，只能通过移动
			if(isset($arr_fields['category_pid'])) unset($arr_fields['category_pid']);
			//修改数据表
			$arr = $obj_db->on_update(cls_config::DB_PRE."quan_category" , $arr_fields , $where);
			if($arr['code'] != 0) {
				$arr_return['code'] = $arr['code'];
				$arr_return['msg']  = cls_language::get("db_edit");
			}
		}
		return $arr_return;
	}
	/* 统计子集数及深度
	 * pid 为统计子集 , id 为计算深度
	 */
	static function on_tongbu($pid , $id = 0) {
		$arr_update = array();
		$obj_db = cls_obj::db_w();
		if( !empty($pid) ) {
			$category_childs = 0;
			$obj_rs = $obj_db->get_one("select category_pids,category_childs from ".cls_config::DB_PRE."quan_category where category_id=".$pid);
			if(!empty($obj_rs) && !empty($obj_rs["category_pids"])) {
				$arr_update["category_pids"] = $obj_rs["category_pids"] . "," . $id;
				$arr_update["category_depth"] = count(explode("," , $arr_update["category_pids"]));
				$category_childs = $obj_rs['category_childs'];
			} else {
				$arr_update["category_pids"] = $id;
				$arr_update["category_depth"] = 1;
			}
			//计算父级子集数量
			$obj_rs = $obj_db->get_one("select count(1) as num from " . cls_config::DB_PRE . "quan_category where category_pid='" . $pid . "'");
			if(!empty($obj_rs) && $category_childs != $obj_rs['num']) {
				$obj_db->on_update(cls_config::DB_PRE."quan_category" , array('category_childs'=>$obj_rs['num']) , "category_id=" . $pid);
			}
		} else {
			$arr_update["category_pids"] = $id;
			$arr_update["category_depth"] = 1;
		}
		if(!empty($id)) $arr = $obj_db->on_update(cls_config::DB_PRE."quan_category" , $arr_update , "category_id=" . $id);
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
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "category_id='".$str_id."'" : $arr_where[] = "category_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);
		$arr_id = array();
		$obj_result = $obj_db->select( "select category_id,category_pid from " . cls_config::DB_PRE . "quan_category where " . $where );
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$arr_id[] = $obj_rs;
		}
		$arr_return=$obj_db->on_delete(cls_config::DB_PRE."quan_category" , $where);
		if($arr_return['code']==0) {
			//删除子项
			foreach($arr_id as $item) {
				$arr = $obj_db->on_delete(cls_config::DB_PRE."quan_category" , $obj_db->concat(",",'category_pids',",") . " like '%," . $item['category_id'] . ",%'");
				if(!empty($item['category_pid'])) {
					self::on_tongbu($item['category_pid']);
				}

			}
		}
		return $arr_return;
	}
}