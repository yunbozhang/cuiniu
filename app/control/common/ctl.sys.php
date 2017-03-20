<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */

class ctl_sys extends mod_sys {

	//用户列表样式一
	function act_user_dialog1() {
		//是否为管理员
		if(!cls_obj::get("cls_user")->is_admin()) {
			cls_error::on_error("no_limit");
		}
		$this->arr_list = $this->get_user_dialog1();
		return $this->get_view(); //显示页面
	}
	//默认浏览页
	function act_login() {
		$agent = fun_get::agent();
		if(!in_array($agent,array('','ipad')) ) {
			echo cls_app::on_display(KJ_WAP_DIR , '' , "index" , "login" , array() , KJ_WAP_DIR );
			exit;
		}
		$jump_url = fun_get::get("jump_url");    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$this->jump_fromurl = $jump_url;
		return $this->get_view(); //显示页面
	}
	//验证登录
	function act_login_verify() {
		$arr_return = $this->on_login_verify();
		return fun_format::json($arr_return);
	}
	//退出登录
	function act_login_out() {
		cls_obj::get("cls_user")->on_loginout(); //清除登录信息
		$jump_url = fun_get::get("jump_url");    //获取跳转地址
		if( empty($jump_url) && isset($_SERVER["HTTP_REFERER"]) ) $jump_url=$_SERVER["HTTP_REFERER"];
		$jump_url = str_replace("state=weixinlogin" , "state=" , $jump_url);
		fun_base::url_jump($jump_url);
	}
	//输出验证码
	function act_verifycode() {
		$name = fun_get::get("name");
		cls_verifycode::get_codepic($name);
		exit;
	}
	//输出缓存
	function act_cache_words() {
		$type = fun_get::get("cachetype");
		$id = fun_get::get("cacheid");
		$val = fun_get::get("cacheval");
		$arr_list = fun_kj::get_cache_words($type , $val);
		$arr_return = array(
			"cacheid" => $id , 
			"list" => $arr_list
		);
		return fun_format::json($arr_return);
	}
	//取站点配置信息
	function act_web_config() {
		$rule_uname = fun_get::rule_uname();
		$rule_pwd = fun_get::rule_pwd();
		$web_css = KJ_WEBCSS_PATH;
		$print_url = cls_config::get("url" , "print" , "" , "");
		$coinsign = cls_config::get("coinsign" , "sys","￥");
		$isremote = (empty($print_url)) ? 0 : 1;
		$iswap = fun_is::wap() ? 1 : 0;
		if(substr($web_css , 0, 5) != "http:") $web_css = cls_config::get("dirpath" , "base") . $web_css;
		$var = "var web_config = {";
		$var .= "domain : '" . cls_config::get("domain" , "base") . "',";
		$var .= "baseurl : '" . cls_config::get("url" , "base") . "',";
		$var .= "dirpath : '" . cls_config::get("dirpath" , "base") . "',";
		$var .= "basecss : '" . $web_css . "',";
		$var .= "cookie_pre : '" . cls_config::COOKIE_PRE . "',";
		$var .= "rule_uname : '" . $rule_uname['js'] . "',";
		$var .= "rule_uname_tips : '" . $rule_uname['tips'] . "',";
		$var .= "rule_pwd : '" . $rule_pwd['js'] . "',";
		$var .= "rule_pwd_tips : '" . $rule_pwd['tips'] . "',";
		$var .= "verision : '" . cls_config::get("web" , "version" , '' , '') . cls_config::get("version" , "version" , '' , '') . "',";
		$var .= "uid : " . (int)cls_obj::get("cls_user")->uid . ",";
		$var .= "uname : '" . cls_obj::get("cls_user")->uname . "',";
		$var .= "coinsign:'" . $coinsign . "',";
		$var .= "agent : '" . fun_get::agent() . "',";
		$var .= "isremote : '" . $isremote . "',";
		$var .= "iswap : '" . $iswap . "',";
		$var .= "};";
		return $var;
	}
	//注册验证码
	function act_verify_reg() {
		$arr_return = $this->on_verify_reg();
		return fun_format::json($arr_return);
	}
	//手机号验证
	function act_verify_sms() {
		$tel = fun_get::get("tel");
		$key = fun_get::get("key");
		$type = (int)fun_get::get("type");
		$arr_return = tab_sys_verify::on_verify($tel , $key , $type);
		return fun_format::json($arr_return);
	}
	//发送短信验证码
	function act_send_sms() {
		$tel = fun_get::get("tel");
		$type = (int)fun_get::get("type");
		$arr_return = $this->on_send_sms($tel , $type);
		return fun_format::json($arr_return);
	}

	//配送地区
	function act_area() {
		$ids = fun_get::get("ids");
		$this->sel_area = $this->get_area_sel($ids);
		$this->sel_area_ids = implode("," , $this->sel_area);
		$this->sel_ids = $ids;
		$this->arr_list = $this->get_area(0);
		$this->callback = fun_get::get("callback");
		$this->objid = fun_get::get("objid");
		return $this->get_view(); //显示页面
	}

	function act_area_childs() {
		$id = fun_get::get("id");
		$arr_list = $this->get_area($id);
		return fun_format::json($arr_list);
	}

}