<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_index extends mod_index{
	function act_default(){
		$this->arr_isread = $this->get_isread();
		$this->arr_list = $this->get_msg_list();
		if(fun_get::get("app_ajax")=='1') {
			return $this->get_view('default.ajax');
		} else {
			$this->arr_category = $this->get_quan_category();
			return $this->get_view();
		}
	}
	function act_quan_msg() {
		$id = (int)fun_get::get("id");
		$this->arr_list = $this->get_msg_list(0 , array("msg_id='" . $id . "'"));
		return $this->get_view();
	}
	function act_user_quan_msg() {
		$uid = (int)fun_get::get("id");
		$this->arr_list = $this->get_msg_list($uid);
		if(fun_get::get("app_ajax")=='1') {
			return $this->get_view('default.ajax');
		} else {
			$this->arr_user = $this->get_quan_user($uid , 1);
			return $this->get_view();
		}
	}
	function act_quan_user() {
		$uid = (int)fun_get::get("id");
		$this->arr_user = $this->get_quan_user($uid , 1);
		return $this->get_view();
	}
	function act_quan_msg_pub() {
		$this->arr_category = $this->get_quan_category();
		return $this->get_view();
	}
	//ע��ҳ
	function act_reg() {
		$jump_url = fun_get::get("jump_url");    //��ȡ��ת��ַ
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		if(stristr($jump_url,"app_act=reg") || stristr($jump_url,"app_act=login")) {
			$jump_url = '/';
		}
		$this->jump_fromurl = $jump_url;
		$this->reg_switch = cls_config::get("reg_switch" , "user");
		$this->reg_switch_info = cls_config::get("reg_switch_info" , "user");
		//ȡע��Э��
		$this->reg_content = tab_article::get_bykey("regargreement");
		return $this->get_view(); //��ʾҳ��
	}
	//��¼ҳ
	function act_login() {
		$jump_url = fun_get::get("jump_url");    //��ȡ��ת��ַ
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$this->jump_fromurl = $jump_url;
		$this->verfifycode = cls_obj::get("cls_user")->is_verifycode();
		return $this->get_view(); //��ʾҳ��
	}

}