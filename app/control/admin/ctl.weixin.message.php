<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
 class ctl_weixin_message extends mod_weixin_message {

	//默认浏览页
	function act_default() {
		if($this->weixin_site['id'] == -999) {
			$this->weixin_site = array("id" => -998 , "name" => "默认");
		}
		$group = (int)fun_get::get("url_type");
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		if($group == 1) {
			$message = tab_weixin_message::get_remsg($site_id);
			$this->get_id = $message['message_id'];
		} else {
			$message = tab_weixin_message::get_guanzhu($site_id);
			$this->get_id = $message['message_id'];
			$group = 0;
		}
		if(isset($message['message_text'])) $message['message_text'] = tab_weixin_message::format_text($message['message_text'] , 1);
		$message['message_text_html'] = fun_get::filter($message['message_text'],true);
		$this->message = $message;
		$this->group = $group;
		return $this->get_view(); //显示页面
	}
	//关键词
	function act_keywords() {
		if($this->weixin_site['id'] == -999) {
			$this->weixin_site = array("id" => -998 , "name" => "默认");
		}
		$this->nowdate = date("m月d日");
		$site_id = ($this->weixin_site['id']>0) ? $this->weixin_site['id'] : 0;
		$arr = tab_weixin_message::get_rekeywords('' , $site_id);
		$arr_list = array();
		foreach($arr as $item) {
			$item['message_text'] = fun_get::filter(tab_weixin_message::format_text($item['message_text'] , 1),true);
			$arr_list[] = $item;
		}
		$this->arr_list = $arr_list;
		return $this->get_view(); //显示页面
	}
	function act_keywords_edit() {
		$this->editinfo = $this->get_editinfo( fun_get::get('id') );
		return $this->get_view(); //显示页面
	}
	//保存
	function act_save() {
		$arr_return = $this->on_save();
		return fun_format::json($arr_return);
	}
	//保存关键词
	function act_keywords_save() {
		if( !$this->this_limit->chk_act('save') ) {
			cls_error::on_error("no_limit");
		}
		$arr_return = $this->on_keywords_save();
		return fun_format::json($arr_return);
	}
	//彻底删除,返回josn数据
	function act_delete() {
		$arr_return = $this->on_delete();
		return fun_format::json($arr_return);
	}

}