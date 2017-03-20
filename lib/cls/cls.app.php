<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_app{
	static $perms;
	/* 启动程序
	 * msg_dir : 指定加载目录 , msg_viewdir : 指定加载模板目录
	 */
	static function on_load($msg_dir = "" , $msg_viewdir = null , $app = array()) {
		if(empty($msg_dir)) $msg_dir = 'default';
		self::$perms = array('dir' => $msg_dir , 'viewdir' => $msg_viewdir , "app" => $app);
		//测试执行开始时间
		$lng_start_time = microtime(true);
		(isset($app['app'])) ? $str_app = $app['app'] : $str_app = fun_get::get("app");
		(isset($app['app_act'])) ? $str_act = $app['app_act'] : $str_act = fun_get::get("app_act");
		(isset($app['app_module'])) ? $str_module = $app['app_module'] : $str_module = fun_get::get("app_module");
		if(empty($str_app)) {
			$str_app = "index";
		}
		if(empty($str_act)) {
			$str_act = "default";
		}
		if(cls_config::IS_TEST==0){
			error_reporting(0);//禁用错误报告
		}else{
			error_reporting(E_ALL);//报告所有错误
		}
		(cls_config::DB_CHARSET == "utf8")? $str_charset = "utf-8" : $str_charset = cls_config::DB_CHARSET;
		if(!isset($_GET['app_contenttype'])) header('Content-Type:text/html;charset='.$str_charset);//当非附件或非图片时输出
		//输入出头部信息
		if(fun_get::get("app_ajax") != '1') echo cls_obj::header_info();
		echo self::on_display($msg_dir , $str_module , $str_app , $str_act , array() , $msg_viewdir);
		//输出尾部信息
		if(fun_get::get("app_ajax") != '1') echo cls_obj::footer_info();
		if( fun_get::get("phpinfo") ){
			$lng_time = microtime(true)-$lng_start_time;
			$arr_memory_size = fun_get::memory_size();
			$str_pageinfo = chr(10)."<!--";
			$str_pageinfo .= "执行时间：".$lng_time." ms".chr(13);
			$str_pageinfo .= "占用内存：".$arr_memory_size["val"].$arr_memory_size["unit"].chr(13);
			if(isset($obj_ctl)){
				$str_pageinfo .= "查询数量：".$obj_ctl->get_db()->querynum.chr(13);
			}
			$str_pageinfo .= "-->";
			echo $str_pageinfo;
		}
	}

	static function on_display($msg_dir = null , $str_module , $str_app , $str_act , $arr_perms = array() , $msg_viewdir = null) {
		if(empty($msg_dir)) $msg_dir = self::$perms['dir'];
		if($msg_viewdir === null ) $msg_viewdir = self::$perms['viewdir'];
		if(empty($msg_viewdir)) $msg_viewdir = $msg_dir;
		set_include_path( get_include_path() . PATH_SEPARATOR . KJ_DIR_APP."/control/" . $msg_dir );
		set_include_path( get_include_path() . PATH_SEPARATOR . KJ_DIR_APP."/model/" . $msg_dir );
		$str_m = "";
		$str_m_obj = "";
		if(!empty($str_module)) {
			$str_m = $str_module . ".";
			$str_m_obj = $str_module . "_";
		}
		$mbfile = KJ_DIR_APP . "/view/" . $msg_viewdir . "/" . $str_m . $str_app . "." . $str_act . ".html";
		$mb_ext = cls_resolve::get_view_ext($mbfile);
		$mbfile = KJ_DIR_APP . "/view/" . $msg_viewdir . "/" . $str_m . $str_app . "." . $str_act . $mb_ext;
		if( is_file( KJ_DIR_APP . "/control/" . $msg_dir . "/ctl." . $str_m . $str_app . ".php") ) {
			//有控制器的情况下,从控制器加载
			$cls_obj = str_replace(".","_","ctl_" . $str_m_obj . $str_app);
			$cls_method = "act_" . $str_act;
			//初始化参数
			$arr_v = array("app_module" => $str_module,"app" => $str_app,"app_dir" => $msg_dir,"app_act" => $str_act ,"app_viewdir" => $msg_viewdir);
			$arr_v = array_merge($arr_v , $arr_perms);
			$obj_ctl = new $cls_obj($arr_v);
			$cls_method = str_replace("." , "_" , $cls_method);
			$str_cont = $obj_ctl->$cls_method();
			return $str_cont;
		} else if ( is_file( $mbfile ) ) {
			//没控制器的情况，直接加载模板文件
			$obj_ctl = new cls_base;
			$arr_v = array("app_module" => $str_module,"app" => $str_app,"app_dir" => $msg_dir,"app_act" => $str_act , "app_viewdir" => $msg_viewdir);
			//初始化参数
			$arr_v = array_merge($arr_v , $arr_perms);
			$obj_ctl->on_init($arr_v);
			$str_cont = $obj_ctl->get_view();
			return $str_cont;
		} else {
			cls_error::on_error("no_page_app");
		}
	}
}