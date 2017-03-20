<?php
/* 快捷订餐系统之多店版
 * 版本号：3.9
 * 官网：http://www.kjcms.com
 * 2016-08-30
 */
class mod_act_gift extends inc_mod_default {
	function get_gift_all($page = 1, $pagesize = 30) {
		$arr_return = array("list" => array());
		$page = (int)$page;
		$obj_db = cls_obj::db();
		$datetime = date("Y-m-d H:i:s");
		$where = " where gift_state>0 and gift_starttime<'" . $datetime . "' and gift_endtime>'" . $datetime . "'";
		$group = fun_get::get("group");
		$arr_group = cls_config::get("gift_group","actgift");
		if(!empty($group) && in_array($group , $arr_group)) $where .= " and gift_group='" . $group . "'";
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_gift" , $where , $page , $pagesize);
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "act_gift" . $where . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			$obj_rs['gift_pic'] = fun_get::html_url($obj_rs['gift_pic']);
			$arr_return['list'][] = $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo']);
		return $arr_return;
	}

	function get_top10() {
		$arr_return = array();
		$obj_db = cls_obj::db();
		$obj_result = $obj_db->select("select * from " . cls_config::DB_PRE . "act_gift where gift_state>0 order by gift_num desc limit 0,10");
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(!fun_is::isdate($obj_rs['gift_time']) || strtotime($obj_rs['gift_time']) < strtotime(date("Y-m-d 00:00:00"))) $obj_rs['gift_num_today'] = 0;
			$obj_rs['gift_pic'] = fun_get::html_url($obj_rs['gift_pic']);
			$arr_return[] = $obj_rs;
		}
		return $arr_return;
	}

	function get_giftinfo($id) {
		$obj_rs = cls_obj::db()->get_one("select * from " . cls_config::DB_PRE . "act_gift where gift_id='" . $id . "'");
		if(empty($obj_rs)) {
			cls_error::on_exit("礼品不存在");
		}
		if(!fun_is::isdate($obj_rs['gift_time']) || strtotime($obj_rs['gift_time']) < strtotime(date("Y-m-d 00:00:00"))) $obj_rs['gift_num_today'] = 0;
		$obj_rs['gift_pic'] = fun_get::html_url($obj_rs['gift_pic']);
		$obj_rs['opentime'] = $this->format_opentime($obj_rs['gift_opentime']);
		$obj_rs['gift_desc'] = fun_get::filter($obj_rs['gift_desc'],true);
		return $obj_rs;
	}
	function format_opentime($opentime) {
		$arr_return = array();
		if(empty($opentime)) return $arr_return;
		$opentime = str_replace(chr(13),chr(10),$opentime);
		$opentime = str_replace(chr(10),chr(10).chr(10),$opentime);
		$arr = explode(chr(10) , $opentime);
		foreach($arr as $item) {
			$arr1 = explode("=&gt;" , $item);
			if(count($arr1)>1) {
				$arr_return[$arr1[0]] = $arr1[1];
			} else {
				$arr_return[$arr1[0]] = $arr1[0];
			}
		}
		return $arr_return;
	}
	function get_record_info() {
		$arr_return = array("linkname"=>"","tel"=>"","address" => "");
		if(!cls_obj::get("cls_user")->is_login()) return $arr_return;
		$uid = cls_obj::get("cls_user")->uid;
		$obj_record = cls_obj::db()->get_one("select record_linkname as 'linkname',record_tel as 'tel',record_address as 'address' from " . cls_config::DB_PRE . "act_gift_record where record_user_id='" . $uid . "' order by record_id desc");
		if(empty($obj_record)) return $arr_return;
		return $obj_record;
	}
	function on_exchange() {
		if(!cls_obj::get("cls_user")->is_login()) return array("code" => 1 , "msg" => "您还没有登录");
		$sys_gift_state = cls_config::get("gift_state" , "actgift");
		if(!$sys_gift_state) return array("code" => 500 , "msg" => "系统关闭了积分兑换功能，请联系管理员");

		$exchange_verify = cls_config::get("exchange_verify","actgift");
		if($exchange_verify) {
			$verifycode = fun_get::get("verifycode");
			if(cls_verifycode::on_verify($verifycode) == false) {
				$arr_return["code"] = 11;
				$arr_return["msg"]  = cls_language::get("verify_code_err");
				return $arr_return;
			}
		}
		$uid = cls_obj::get("cls_user")->uid;
		$gift_id = (int)fun_get::get("id");
		$num = (int)fun_get::get("num");
		if(empty($num))  return array("code" => 500 , "msg" => "兑换礼品数量丢失");
		if(empty($gift_id)) return array("code" => 500 , "msg" => "兑换礼品信息丢失");
		$obj_db = cls_obj::db_w();
		$obj_gift = $obj_db->get_one("select * from " . cls_config::DB_PRE . "act_gift where gift_id='" . $gift_id . "'");
		if(empty($obj_gift)) return array("code" => 500 , "msg" => "兑换礼品信息丢失");

		$linkname = fun_get::post("linkname");
		if(empty($linkname)) return array("code" => 500 , "msg" => "收货人姓名不能为空");
		$tel = fun_get::post("tel");
		if(empty($tel)) return array("code" => 500 , "msg" => "请填写收货人联系电话，以便我们寄送礼品");
		$address = fun_get::post("address");
		if(empty($address)) return array("code" => 500 , "msg" => "请填写收货人地址，以便我们寄送礼品");
		if($obj_gift['gift_state']<1) return array("code"=>500,"msg"=>"【" . $obj_gift['gift_name'] . "】关闭了兑换功能");
		$datetime = date("Y-m-d H:i:s");
		if($obj_gift['gift_starttime']>$datetime) return array("code"=>500,"msg"=>"【" . $obj_gift['gift_name'] . "】将在" . $obj_gift['gift_starttime'] . "开始兑换,换个礼品吧");
		if($obj_gift['gift_endtime']<$datetime) return array("code"=>500,"msg"=>"【" . $obj_gift['gift_name'] . "】已于" . $obj_gift['gift_endtime'] . "兑换结束,换个礼品吧");
		if(!empty($obj_gift['gift_total'])) {
			if($obj_gift['gift_total'] <= $obj_gift['gift_num']) return array("code" => 500 , "msg" => "【" . $obj_gift['gift_name'] . "】已兑换完，请选择其它礼品");
			$havenum = $obj_gift['gift_total'] - $obj_gift['gift_num'];
			if($havenum < $num) return array("code" => 500 , "msg" =>  "【" . $obj_gift['gift_name'] . "】当前仅剩" . $havenum . "份，不足" . $num . "份，请调整兑换数量");
		}
		if( fun_is::isdate($obj_gift['gift_time']) && strtotime($obj_gift['gift_time']) < strtotime(date("Y-m-d 00:00:00"))) $obj_gift['gift_num_today'] = 0;
		if(!empty($obj_gift['gift_total_day'])) {
			if( $obj_gift['gift_num_today'] >= $obj_gift['gift_total_day'] ) return array("code" => 500 , "msg" => "【" . $obj_gift['gift_name'] . "】限制每天只能兑换" . $obj_gift['gift_total_day'] . "份，今天已兑完，明天再来吧");
			$x = $obj_gift['gift_total_day'] - $obj_gift['gift_num_today'];
			if( $num > $x ) return array("code" => 500 , "msg" => "【" . $obj_gift['gift_name'] . "】限制每天只能兑换" . $obj_gift['gift_total_day'] . "份，仅有" . $x . "份，请修改数量");
		}
		if(!empty($obj_gift['gift_user_num'])) {
			$obj_user_num = $obj_db->get_one("select sum(record_num) as 'num' from " . cls_config::DB_PRE . "act_gift_record where record_user_id='" . $uid . "' and record_gift_id='" . $gift_id . "'");
			if(!empty($obj_user_num) && $obj_user_num['num'] >= $obj_gift['gift_user_num']) return array('code' => 500 , "msg" => "【" . $obj_gift['gift_name'] . "】限制每人只能兑换" . $obj_gift['gift_user_num'] . "份，您已兑完，换个礼品吧");
		}
		//每天开放时间段，检测
		$obj_gift['opentime'] = $this->format_opentime($obj_gift['gift_opentime']);
		if(!empty($obj_gift['opentime'])) {
			$opentime = date("Hi");
			$isopen = true;
			$arr_opentime = array();
			foreach($obj_gift['opentime'] as $item=>$key) {
				$arr = explode("," , $item);
				if(count($arr)!=2) continue;
				if($opentime > (int)$arr[0] && $opentime < (int)$arr[1]) {
					$isopen = true;
					break;
				}
				$arr_opentime[] = $key;
				$isopen = false;
			}
			if($isopen == false) return array("code" => 500 , "msg" => "【" . $obj_gift['gift_name'] . "】每天兑换时间为：" . implode("，" , $arr_opentime));
		}
		$needscore = $obj_gift['gift_score'] * $num;
		$userscore = cls_obj::get("cls_user")->get_score();
		if($userscore < $needscore) return array("code" => 500 , "msg" => "兑换" . $num . "份【" . $obj_gift['gift_name'] . "】需要" . $needscore . "积分，当前仅有" . $userscore . "积分，不足兑换");
		//开始事务
		$obj_db->begin("gift_change");
		//修改礼品数量信息
		$arr_msg = $obj_db->on_exe("update " . cls_config::DB_PRE . "act_gift set gift_num=gift_num+" . $num . ",gift_num_today=" . ($obj_gift['gift_num_today']+$num) . ",gift_time='" . $datetime . "' where gift_id='" . $gift_id . "'");
		if($arr_msg['code']!=0) {
			$obj_db->rollback("gift_change");
			return array("code"=> 500 , "msg" => "兑换礼品失败1" . $arr_msg['msg']);
		}
		//扣减用户积分
		$arr_msg = tab_sys_user_action::on_action( $uid , 'act_gift_exchage' , array('score'=>$needscore , "title" => "兑换" . $num . "份【" . $obj_gift['gift_name'] . "】") );
		if($arr_msg['code']!=0) {
			$obj_db->rollback("gift_change");
			return array("code"=> 500 , "msg" => "兑换礼品失败" . $arr_msg['msg']);
		}
		//插入兑换记录
		$arr_msg = $obj_db->on_insert(cls_config::DB_PRE . "act_gift_record" , array(
			'record_user_id'=>$uid ,
			"record_gift_id"=>$gift_id,
			"record_score"=>$needscore ,
			"record_datetime"=>$datetime,
			"record_num" => $num,
			"record_ip" => fun_get::ip(),
			"record_linkname" => $linkname,
			"record_tel" => $tel,
			"record_address" => $address,
		));
		if($arr_msg['code']!=0) {
			$obj_db->rollback("gift_change");
			return array("code"=> 500 , "msg" => "兑换礼品失败3" . $arr_msg['msg']);
		}
		$obj_db->commit("gift_change");
		return array('code' => 0 , 'msg' => "兑换成功,我们将尽快为您寄出礼品");
	}
	function get_mygift() {
		if(!cls_obj::get("cls_user")->is_login()) return array('list'=>array(),"pagebtns"=>'');
		$obj_db = cls_obj::db();
		$page = (int)fun_get::get("page",1);
		//取排序字段
		$str_where = " where record_user_id='" . cls_obj::get("cls_user")->uid . "'";
		$str_key = ".act.gift.mygift";
		$arr_config_info = tab_sys_user_config::get_info($str_key  , $this->app_dir);
		$pagesize = $arr_config_info["pagesize"];

		$action_id = 0;
		$arr_return["list"] = array();
		$arr_return["pageinfo"] = $obj_db->get_pageinfo(cls_config::DB_PRE."act_gift_record" , $str_where , $page , $pagesize);
		$obj_result = $obj_db->select("select a.*,b.gift_name from " . cls_config::DB_PRE . "act_gift_record a left join " . cls_config::DB_PRE . "act_gift b on a.record_gift_id=b.gift_id" . $str_where . " order by record_id desc" . $arr_return['pageinfo']['limit']);
		while($obj_rs = $obj_db->fetch_array($obj_result)) {
			if(empty($obj_rs['record_send_time'])) {
				$obj_rs['state'] = '<font style="color:#ff0000">等待发货</font>';
			} else if(empty($obj_rs['record_receive_time'])) {
				$obj_rs['state'] = '发货时间：' . date("Y-m-d H:i" , $obj_rs['record_send_time']);
			} else {
				$obj_rs['state'] = "已领取";
			}
			$arr_return["list"][]= $obj_rs;
		}
		$arr_return['pagebtns']   = $this->get_pagebtns($arr_return['pageinfo'],5);
		return $arr_return;
	}

	function on_receive() {
		if(!cls_obj::get("cls_user")->is_login()) return array('code'=>500,"msg"=>'您还没有登录.');
		$id = (int)fun_get::get("id");
		if(empty($id)) return array('code'=>500,"msg"=>'领取记录不存在.');
		$arr = cls_obj::db_w()->on_exe("update " . cls_config::DB_PRE . "act_gift_record set record_receive_time='" . TIME  . "' where record_id='" . $id . "' and record_user_id='" . cls_obj::get("cls_user")->uid . "'");
		$arr['id'] = $id;
		if($arr['code'] == 0) {
			$arr['msg'] = "领取成功";
		} else {
			$arr['msg'] = "领取失败";
		}
		return $arr;
	}
}