<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_article extends mod_shop_article {

	//默认浏览页
	function act_default() {
		$folder_id = (int)fun_get::get("fid");
		$arr_where = array(
			"article_isdel=0",//非回收站数据
			"article_folder_id=" . $folder_id,//所属目录id
		);
		$this->arr_list = $this->get_pagelist($arr_where);
		//目录列表
		$this->arr_dirlist = $this->get_dirlist($folder_id);
		$this->arr_state = tab_article::get_perms("state");
		return $this->get_view(); //显示页面
	}
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}
	//编辑 新增 页面 ,有id时为编辑
	function act_edit() {
		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_state = tab_article::get_perms("state");
		$this->arr_attribute = cls_config::get("attribute" , "article" , array());
		//取目录列表
		$this->select_folder = $this->get_folder_select("article_folder_id" , $this->editinfo["article_folder_id"]);
		return $this->get_view();
	}
	//保存操作,返回josn数据
	function act_save_article() {
		$arr_return = $this->on_save_article();
		return fun_format::json($arr_return);
	}
	//删除文章到回收站操作,返回josn数据
	function act_del_article() {
		$arr_return = $this->on_del_article();
		return fun_format::json($arr_return);
	}


}