<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_weixin_menu extends mod_weixin_menu {

	//默认浏览页
	function act_default() {
		if($this->weixin_site['id'] == -999) {
			$this->weixin_site = array("id" => -998 , "name" => "默认");
		}
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$this->arr_menu = tab_weixin_menu::get_list_layer($site_id);
		return $this->get_view(); //显示页面
	}
	function act_edit() {
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->select_folder = $this->get_menu_select($site_id , "menu_pid" , $this->editinfo["menu_pid"] , $this->editinfo["menu_id"]);
		return $this->get_view(); //显示页面
	}
	//保存关键词
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	//保存操作,返回josn数据
	function act_save_all() {
		return fun_format::json($arr_return);
	}
	//生成自定义菜单
	function act_save_exe() {
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$arr_return = $this->on_save_all();
		if($arr_return['code'] == 0) {
			$arr_return = cls_weixin::menu_create($site_id);
		}
		return fun_format::json($arr_return);
	}
}