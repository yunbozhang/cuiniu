<?php
/* 蹇嵎璁㈤绯荤粺涔嬪搴楃増
 * 鐗堟湰鍙凤細3.9
 * 瀹樼綉锛歨ttp://www.kjcms.com
 * 2016-08-30
 */

class ctl_article_channel extends mod_article_channel {

	//默认浏览页
	function act_default() {


		//分页列表
		$this->arr_list = $this->get_pagelist();

		return $this->get_view(); //显示页面
	}

	//编辑 新增 页面 ,有id时为编辑
	function act_edit() {

		//用户类型数组
		$this->arr_user_type = tab_sys_user::get_perms("type");
		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_state = tab_article_channel::get_perms("state");
		$this->arr_dirstyle = tab_article_channel::get_perms("dirstyle");
		$this->arr_mode = tab_article_channel::get_perms("mode");
		return $this->get_view();
	}
	//保存操作,返回josn数据
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}

	//设置状态
	function act_state() {
		$arr_return = $this->on_state();
		return fun_format::json($arr_return);
	}
	//删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

}