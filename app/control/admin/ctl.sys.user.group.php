<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_sys_user_group extends mod_sys_user_group {

	//默认浏览页
	function act_default() {
		$this->arr_group = tab_sys_user_group::get_list_layer();
		return $this->get_view(); //显示页面
	}
	//自定义菜单页
	function act_menu() {
		$this->arr_limit_dir = tab_sys_user_group::get_perms("limit_dir");
		$this->menu_list = $this->menu_list();
		return $this->get_view(); //显示页面
	}
	//菜单列表选择页
	function act_menu_list() {
		$this->menu_list = $this->menu_list(true);
		return $this->get_view(); //显示页面
	}
	//保存自定义菜单页
	function act_menu_save() {
		$arr_return = $this->on_menu_save();
		return fun_format::json($arr_return);
	}
	//移动显示页
	function act_move_open() {
		$this->group_select_html = $this->get_group_select();
		return $this->get_view(); //显示页面
	}
	//保存操作,返回josn数据
	function act_save_all() {
		$arr_return = $this->on_save_all();
		return fun_format::json($arr_return);
	}
	//保存操作,返回josn数据
	function act_move_save() {
		$arr_return = $this->on_move_save();
		return fun_format::json($arr_return);
	}

	//权限设置页
	function act_limit_edit() {
		$this->arr_limit_dir = tab_sys_user_group::get_perms("limit_dir");
		$this->this_limit_dir = fun_get::get("url_limit_dir");
		if( $this->this_limit_dir == '') {
			foreach($this->arr_limit_dir as $item => $key) {
				$this->this_limit_dir = $key;
				break;
			}
		}
		$this->arr_limit = $this->this_limit->get_dir_limit($this->this_limit_dir);
		$arr = $this->on_limit_edit();
		$this->group_limit = $arr['limit'];
		$this->limit_area = $arr['limit_area'];
		if(empty($arr['limit_area'])) {
			$this->limit_area_name = '';
		} else {
			$arr_name = array();
			$result = cls_obj::db()->select("select area_id,area_name from " . cls_config::DB_PRE . "sys_area where area_id in(" . $arr['limit_area'] . ")");
			while($obj_rs = cls_obj::db()->fetch_array($result)) {
				$arr_name[] = $obj_rs["area_name"];
			}
			$this->limit_area_name = implode("," , $arr_name);
		}
		return $this->get_view(); //显示页面
	}
	//权限保存
	function act_limit_save() {
		$arr_return = $this->on_limit_save();
		return fun_format::json($arr_return);
	}
	//文章权限设置页
	function act_limit_article() {
		$this->arr_limit_dir = tab_sys_user_group::get_perms("limit_dir");
		$id = (int)fun_get::get("id");
		$this->arr_list = $this->get_limit_article();
		$this->arr_limit = $this->get_article_limit($id);
		return $this->get_view(); //显示页面
	}
	//文章权限保存
	function act_limit_article_save() {
		$arr_return = $this->on_limit_article_save();
		return fun_format::json($arr_return);
	}
}