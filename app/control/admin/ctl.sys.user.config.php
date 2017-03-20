<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_sys_user_config extends mod_sys_user_config{
	function act_default(){
		//系统自带菜单模块列表
		$this->arr_fields = $this->get_fields();
		return $this->get_view();
	}
	//保存操作,返回josn数据
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	//当用户拖动表格列时，自动保存当前配置
	function act_save_resize() {
		$arr_return = $this->on_save_resize();
		return fun_format::json($arr_return);
	}
	//保存排序
	function act_sort() {
		$arr_return = $this->on_sort();
		return fun_format::json($arr_return);
	}
	//保存分页大小
	function act_save_pagesize() {
		$arr_return = $this->on_save_pagesize();
		return fun_format::json($arr_return);
	}
}