<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 * 名称 ：会话类
 * 功能 ：操作客户端与服务器之间的会话信息，如登录状态
 */
class cls_session extends cls_session_base {
	private $is_update = false;
    function __construct() {
		parent::__construct();
		$this->_init();
	}

	//结束时判断是否有更新，有则同步到session表
    function __destruct() {
		if($this->is_update) $this->_save_update();
	}

	//初始化session值
	private function _init() {
		$isapp = 0;
		$str_sid = '';
		if(fun_get::get("app_load") == 1 && isset($_GET['code']) ) {//app启动
			$md5key = md5(cls_config::MD5_KEY . $_GET["code"]);
			if($md5key == $_GET["code_md5"]) {
				$str_sid = $_GET['code'];
				$isapp = 1;
				$obj_client = cls_obj::db()->get_one("select client_id,client_loadnum from " . cls_config::DB_PRE . "app_client where client_code='" . $str_sid . "'");
				if(empty($obj_client)) {
					$arr_fields = array(
						"client_type" => $_GET['app_client'],
						"client_code" => $_GET['code'],
						"client_version" => $_GET['version'],
					);
					$arr = tab_app_client::on_save($arr_fields);
				} else {
					$obj_client['client_loadnum']++;
					cls_obj::db_w()->on_exe("update " .cls_config::DB_PRE . "app_client set client_loadnum='" . $obj_client['client_loadnum'] . "' where client_id='" . $obj_client['client_id'] . "'");
				}
			}
		}
		if(empty($str_sid)) {
			$str_sid = $this->get_cookie("s_id");
			$str_sid = fun_get::safecode($str_sid,"decode");//解码
		}
		$this->session["id"] = '';
		if(!empty($str_sid)) {
			$str_sql = "select * from " . cls_config::DB_PRE . "sys_session where session_id='" . $str_sid . "'";
			$arr_session  = cls_obj::db()->get_one($str_sql);
			if(!empty($arr_session)) {
				if(!empty($arr_session["session_val"])) {
					$arr = unserialize($arr_session["session_val"]);
					//非自动登录
					$autologin = $this->get_cookie("autologin");
					if(empty($autologin) && empty($isapp) ) $arr['login_user'] = array();
					$this->session = $arr;
				}
				$this->session["id"] = $arr_session["session_id"];
			}
		}
		if(empty($this->session["id"])) {
			$arr_fields = array();
			$app_session_id = '';
			if($isapp) $app_session_id = $str_sid;
			$agent = fun_get::agent();
			if($isapp) {
				if($_GET['app_client'] == 'winphone') {
					$arr_fields['session_type'] = 5;
				} else if($_GET['app_client'] == 'iphone') {
					$arr_fields['session_type'] = 4;
				} else {
					$arr_fields['session_type'] = 3;
				}
			} else if(isset($this->session["weixin"]) && $this->session["weixin"] == 1) {
				$arr_fields['session_type'] = 2;
			} else if(!in_array($agent,array('','ipad')) || cls_config::get("basename") == 'wap.php' || cls_app::$perms['viewdir'] == KJ_WAP_DIR || $this->get("weixin")) {
				$arr_fields['session_type'] = 1;
			} else {
				$arr_fields['session_type'] = 0;
			}
			$arr_msg = tab_sys_session::on_save( $arr_fields , '' , $app_session_id);//生成session
			$this->session["id"] = $arr_msg["id"];
			$this->session["type"] = $arr_fields['session_type'];
			$str_sid = fun_get::safecode($this->session["id"]);
			$this->set_cookie("s_id" , $str_sid );   //session_id 对于一台设备标识id ,可以永久保存
			//首次进入，检查是否分享带来的客户
			if(fun_get::get("share_key")!='') {
				cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "other_share set share_num=share_num+1 where share_key='" . fun_get::get('share_key') . "'");
			}
		}
		if(isset($_GET['app_wx_id']) && !empty($_GET['app_wx_id'])) $this->set('wx_id' , fun_get::get('app_wx_id'));
		if($isapp && isset($_GET['latiude'])) {
			$arr = explode("," , $_GET['latiude']);
			$location = (count($arr)==2) ? array('lng' => $arr[1] , 'lat' => $arr[0]) : array('lng' => 0 , 'lat' => 0);
			$this->set("location" , $location);
		}
	}
	//将更新同步到 session表
	private function _save_update() {
		$arr_fields = array(
			"session_id"      => $this->session["id"],
			"session_user_id" => 0,
			"session_group_id"=> 0,
			"session_val"     => ''
		);
		if(isset($this->session["login_user"]) && !empty($this->session["login_user"]) ) {
			$arr_fields["session_user_id"] = $this->session["login_user"]["uid"];
			$arr_fields["session_group_id"] = $this->session["login_user"]["group_id"];
		}
		$arr_fields["session_val"] = serialize($this->session);
		$arr_msg = tab_sys_session::on_save($arr_fields);
	}

	//获取当前会话，指定关健
	function get($key) {
		if(isset($this->session[$key])) {
			return $this->session[$key];
		} else {
			return "";
		}
	}
	//设置session值
	function __set($name , $val) {
		$this->session[$name] = $val;
		$this->is_update = true;
	}
	//获取当前会话，指定关健
	function __get($key) {
		if(isset($this->session[$key])) {
			return $this->session[$key];
		} else {
			return "";
		}
	}
	//设置session值
	function set($name , $val) {
		$this->session[$name] = $val;
		$this->is_update = true;
	}
	//注销变量
	function destroy($name) {
		if(!isset($this->session[$name])) return;
		$this->is_update = true;
		unset($this->session[$name]);
	}
	//当前客户端类型
	function isapp() {
		if(!isset($this->session['type'])) return false;
		if($this->session['type'] == 3 || $this->session['type'] == 4 || $this->session['type'] == 5) return true;
		return false;
	}
}