<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_article_channel extends mod_article_channel {

	//Ĭ�����ҳ
	function act_default() {


		//��ҳ�б�
		$this->arr_list = $this->get_pagelist();

		return $this->get_view(); //��ʾҳ��
	}

	//�༭ ���� ҳ�� ,��idʱΪ�༭
	function act_edit() {

		//�û���������
		$this->arr_user_type = tab_sys_user::get_perms("type");
		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		$this->arr_state = tab_article_channel::get_perms("state");
		$this->arr_dirstyle = tab_article_channel::get_perms("dirstyle");
		$this->arr_mode = tab_article_channel::get_perms("mode");
		return $this->get_view();
	}
	//�������,����josn����
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}

	//����״̬
	function act_state() {
		$arr_return = $this->on_state();
		return fun_format::json($arr_return);
	}
	//ɾ��,����josn����
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

}