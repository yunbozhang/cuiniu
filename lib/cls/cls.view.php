<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class cls_view {
	static function on_load($msg_path,$msg_var) {
		if(file_exists($msg_path)){
			//传递GET参数,必须在模型传递之前，以免替换了模型传过来同名的变量
			foreach($_GET as $item => $key) {
				$str_x="get_".$item;
				$$str_x = fun_get::get($item);
			}
			foreach($_POST as $item => $key) {
				$str_x="get_".$item;
				$$str_x = fun_get::post($item);
			}
			//模型传递过来参数
			foreach($msg_var as $item => $key) {
				$$item = $key;
			}
			ob_start();
			include $msg_path;
		    return ob_get_clean();
		}else{
			cls_error::on_error("no_page_act");
			return "";
		}
	}
}