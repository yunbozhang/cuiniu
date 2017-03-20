<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class tab_sys_safeask {
	static $perms;
	//获取表配置参数
	static function get_perms($key) {
		if( empty(self::$perms) ) {
			self::$perms = array(
				"ask1" => array("爸爸","妈妈","爷爷","奶奶","外公","外婆","丈夫","妻子","姐姐","妹妹","哥哥","弟弟","儿子","女儿"),
				"ask2" => array("的生日","的名字","的出生地"),
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
		$obj_db = cls_obj::db_w();
		//必填项检查
		if(!isset($arr_fields['safeask_user_id'])) $arr_fields['safeask_user_id'] = cls_obj::get("cls_user")->uid;
		if(empty($arr_fields['safeask_user_id'])) return array("code" => 500 , "msg" => "用户不存在或登录超时");
		if(!isset($arr_fields['safeask_question']) || empty($arr_fields['safeask_question'])) return array("code" => 500 , "msg" => "提问不能为空");
		if(!isset($arr_fields['safeask_answer']) || empty($arr_fields['safeask_answer'])) return array("code" => 500 , "msg" => "答案不能为空");
		//加密
		$arr_fields['safeask_answer'] = md5($arr_fields['safeask_answer']);
		if(!isset($arr_fields['safeask_addtime'])) $arr_fields['safeask_addtime'] = TIME;
		//插入到用户表
		$arr = $obj_db->on_insert(cls_config::DB_PRE."sys_safeask",$arr_fields);
		if($arr['code'] != 0) {
			$arr_return['code'] = $arr['code'];
			$arr_return['msg']  = cls_language::get("db_edit");
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
			$arr_return["msg"]="id".cls_language::get("not_null");
			return $arr_return;
		}
		$obj_db = cls_obj::db_w();
		if( !empty($str_id) ) {
			(is_numeric($str_id)) ? $arr_where[] = "safeask_id='".$str_id."'" : $arr_where[] = "safeask_id in(".$str_id.")";
		}
		if( !empty($where) ) {
			if(stristr($where , " or ") && substr(trim($where),0,1) != "(") $where = "(" . $where . ")";
			$arr_where[] = $where;
		}
		$where = implode(" and " , $arr_where);
		$arr_return=$obj_db->on_delete(cls_config::DB_PRE."sys_safeask" , $where);
		return $arr_return;
	}
}