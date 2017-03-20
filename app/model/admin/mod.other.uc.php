<?php
/* KLKKDJ订餐之单店版
 * 版本号：3.0测试版
 * 官网：http://www.klkkdj.com
 * 2012-12-11
 */
class mod_other_uc extends inc_mod_admin {
	private $config_list = array("CONNECT" => 'mysql' , 'DBHOST' => 'localhost' , 'DBUSER' => 'root' , 'DBPW' => '' , 'DBNAME' => 'dz' , 'DBCHARSET' => 'utf8' , 'DBTABLEPRE' => '`dz`.pre_ucenter_' , 'KEY' => 'http://localhost/dz/uc_server' , 'API' => '' , 'CHARSET' => '' , 'IP' => '' , 'APPID' => '');

	/* 查询配置表指定id信息
	 * msg_id : sys_config 表中 config_id
	 */
	function get_editinfo() {
		$uc_path = KJ_DIR_DATA . "/config/cfg.uc.php";
		$arr = $this->config_list;
		if(!defined('UC_CONNECT') && file_exists($uc_path)) {
			include($uc_path);
		}
		if(defined("UC_CONNECT")) $arr['CONNECT'] = UC_CONNECT;
		if(defined("UC_DBHOST")) $arr['DBHOST'] = UC_DBHOST;
		if(defined("UC_DBUSER")) $arr['DBUSER'] = UC_DBUSER;
		if(defined("UC_DBPW")) $arr['DBPW'] = UC_DBPW;
		if(defined("UC_DBNAME")) $arr['DBNAME'] = UC_DBNAME;
		if(defined("UC_DBCHARSET")) $arr['DBCHARSET'] = UC_DBCHARSET;
		if(defined("UC_DBTABLEPRE")) $arr['DBTABLEPRE'] = UC_DBTABLEPRE;
		if(defined("UC_KEY")) $arr['KEY'] = UC_KEY;
		if(defined("UC_API")) $arr['API'] = UC_API;
		if(defined("UC_CHARSET")) $arr['CHARSET'] = UC_CHARSET;
		if(defined("UC_IP")) $arr['IP'] = UC_IP;
		if(defined("UC_APPID")) $arr['APPID'] = UC_APPID;
		return $arr;
	}

	/* 保存数据
	 * 
	 */
	function on_save() {
		$user = (fun_get::get("isstart") == '1') ? 'user.uc' : 'user.klkkdj';
		if($user == 'user.uc') {
			$uc_path = KJ_DIR_DATA . "/config/cfg.uc.php";
			if(!defined('UC_CONNECT') && file_exists($uc_path)) {
				include($uc_path);
			}
			$UC_DBPW = '';
			if(defined("UC_CONNECT")) $UC_DBPW = UC_DBPW;

			$arr = $this->config_list;
			$content = '';
			foreach($arr as $item => $key) {
				$val = fun_get::get($item);
				if($item == 'DBPW' && empty($val)) $val = $UC_DBPW;
				$content .=  chr(10) . "define('UC_" . $item . "', '" . $val . "');";
			}
			$content = '<' . '?php' . $content;
			fun_file::file_create($uc_path , $content , 1);
		}
		$this->save_env_cfg($user);
		return array('code' => 0 , 'msg' => '设置成功');
	}
	function save_env_cfg($val) {
		if(cls_config::USER_CENTER == $val) return;
		$html = file_get_contents(KJ_DIR_DATA . "/config/cfg.env.online.php");
		//转换{fun()} 包函文件代码
		if(defined("DB_CHARSET") && strtolower(DB_CHARSET)=="gbk"){
			$str_utf8_u="";
			$str_chinacode=chr(0xa1)."-".chr(0xff);
		}else{
			$str_utf8_u="u";
			$str_chinacode="\x{4e00}-\x{9fa5}";
		}
		$html=preg_replace("/(const[\s]+USER_CENTER[\s]+=[\s]+[\'|\"]{1,})[a-z\.]+([\'|\"]{1,})/".$str_utf8_u."is", "\\1" . $val . "\\2", $html);
		fun_file::file_create(KJ_DIR_DATA . "/config/cfg.env.online.php",$html,1);
	}
}