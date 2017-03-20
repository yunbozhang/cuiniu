<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class ctl_shop_weixin extends mod_shop_weixin {

	function act_site() {
		$this->editinfo = $this->get_site();
		return $this->get_view();
	}
	//保存微信站点资料
	function act_site_save(){
		$arr = fun_format::json( $this->on_site_save() );
		return $arr;
	}
	function act_message() {
		$group = (int)fun_get::get("group");
		if($group == 1) {
			$message = tab_weixin_message::get_remsg($this->shop_info['site_id']);
		} else {
			$message = tab_weixin_message::get_guanzhu($this->shop_info['site_id']);
			$group = 0;
		}
		fun_get::get('id',$message['message_id']);
		if(isset($message['message_text'])) $message['message_text'] = tab_weixin_message::format_text($message['message_text'] , 1);
		$message['message_text_html'] = fun_get::filter($message['message_text'],true);
		$this->message = $message;
		$this->group = $group;
		return $this->get_view();
	}
	//保存
	function act_message_save() {
		$arr_return = $this->on_message_save();
		return fun_format::json($arr_return);
	}

	//关键词
	function act_keywords() {
		$this->nowdate = date("m月d日");
		$site_id = $this->shop_info['site_id'];
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
		$this->editinfo = $this->get_keywords_editinfo( fun_get::get('id') );
		return $this->get_view(); //显示页面
	}
	//保存关键词
	function act_keywords_save() {
		$arr_return = $this->on_keywords_save();
		return fun_format::json($arr_return);
	}
	//彻底删除,返回josn数据
	function act_keywords_delete() {
		$arr_return = $this->on_keywords_delete();
		return fun_format::json($arr_return);
	}
	function act_menu() {
		$site_id = $this->shop_info['site_id'];
		$this->arr_menu = tab_weixin_menu::get_list_layer($site_id);
		return $this->get_view(); //显示页面
	}

	function act_menu_edit() {
		$site_id = $this->shop_info['site_id'];
		$this->editinfo = $this->get_menu_editinfo( fun_get::get('id') );
		$this->select_folder = $this->get_menu_select($site_id , "menu_pid" , $this->editinfo["menu_pid"] , $this->editinfo["menu_id"]);
		return $this->get_view(); //显示页面
	}
	//保存关键词
	function act_menu_save() {
		$arr_return = $this->on_menu_save();
		return fun_format::json($arr_return);
	}
	//保存操作,返回josn数据
	function act_menu_save_all() {
		return fun_format::json($arr_return);
	}
	//生成自定义菜单
	function act_menu_save_exe() {
		$site_id = $this->shop_info['site_id'];
		$arr_return = $this->on_menu_save_all();
		if($arr_return['code'] == 0) {
			$arr_return = cls_weixin::menu_create($site_id);
		}
		return fun_format::json($arr_return);
	}
	//默认浏览页
	function act_user() {
		$this->arr_list = $this->get_userlist();
		return $this->get_view(); //显示页面
	}
	function act_sendmsg_page() {
		$arr_list = $this->get_userlist();
		$page = (int)fun_get::get("page");
		if($page>$arr_list["pageinfo"]['pages']) {
			$arr_return = array("code" => 500 , "msg" => "发送完毕");
		} else {
			$arr_return = array('code' => 0 , 'pages' => $arr_list['pageinfo']['pages']);
			foreach($arr_list['list'] as $item) {
				$arr_return['list'][] = array("openid" => $item['user_openid'] , "name" => $item['user_name']);
			}
		}

		return fun_format::json($arr_return);
	}
	function act_sendmsg_go() {
		$arr_return = $this->on_sendmsg();
		return fun_format::json($arr_return);
	}

}