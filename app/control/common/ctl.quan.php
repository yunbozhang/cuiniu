<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_quan extends mod_quan {
	//配送地区
	function act_category() {
		$ids = fun_get::get("ids");
		$this->sel_category = $this->get_category_sel($ids);
		$this->sel_category_ids = implode("," , $this->sel_category);
		$this->sel_ids = $ids;
		$this->arr_list = $this->get_category(0);
		$this->callback = fun_get::get("callback");
		$this->objid = fun_get::get("objid");
		return $this->get_view(); //显示页面
	}

	function act_category_childs() {
		$id = fun_get::get("id");
		$arr_list = $this->get_category($id);
		return fun_format::json($arr_list);
	}

}