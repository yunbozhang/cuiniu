<?php
/* 短信接口 */
class com_sms {
	static $perms = array('uid' => 613);
	//获取短信余量
	function get_over() {
		$count = cls_config::get("count_id" , "sms");
		$pwd = cls_config::get("count_pwd" , "sms");
		$uid = self::$perms['uid'];
		$arr = fun_base::post("http://www.ok9288.com/sms.aspx?action=overage" , array("userid" => $uid , "account" => $count , "password" => $pwd));
		if($arr["code"] == 0 && !empty($arr["cont"]) ) {
			$arr_cont = fun_format::xml_array($arr['cont']);
			if(!isset($arr_cont['overage'])) return array("code" => 500 , "msg" => "短信接口访问返回失败");
			$overage = (int)$arr_cont['overage'];
			return array("code" => 0 , "num" => $overage );
		} else {
			return array("code" => 500 , "msg" => "短信接口访问返回失败");
		}
	}
	
	//发送短信
	function on_send( $arr ) {
		$state = cls_config::get("state","sms");
		if(empty($state)) return array('code' => 500 , 'msg' => '短信接口未开启，可以进入后台模块设置开启');
		if(!isset($arr["tel"]) || empty($arr["tel"])) {
			return array("code"=>500,"msg"=>"发送失败，电话号码为空");
		}
		if( !isset($arr["cont"]) && empty($arr["cont"]) ) {
			return array("code"=>500,"msg"=>"发送失败，短信内容为空");
		}
		//如果为测试环境，只允许测试手机号
		if(cls_config::IS_TEST>0) {
			$arr_allowtel = cls_config::get("test_tel" , "sms");
			if(!is_array($arr_allowtel) || !in_array($arr['tel'] , $arr_allowtel)) {
				return array("code" => 500 , "msg" => "该号码未开通测试权限");
			}
		}
		$count = cls_config::get("count_id" , "sms");
		$pwd = cls_config::get("count_pwd" , "sms");
		$uid = self::$perms['uid'];

		$template = cls_config::get("template" , "sms");

		if(!empty($template)) {
			$sitename = cls_config::get("site_name","sys");
			if(empty($sitename)) $sitename = cls_config::get("site_title","sys");
			$template = str_replace('#sitename#',$sitename,$template);
			$arr["cont"] = str_replace('#cont#',$arr['cont'],$template);
		}
		//通首管制，替换
		$arr_split = cls_config::get("split","sms");
		foreach($arr_split as $item=>$key) {
			$arr["cont"] = str_replace($item , $key , $arr["cont"]);
		}
		$arr_fields = array(
			"account" => $count,
			"password" => $pwd,
			"userid" => $uid,
			"mobile" => $arr["tel"],
			"content" => $arr["cont"],
			"sendTime" => '',//子号
		);
		$arr_re = fun_base::post("http://www.ok9288.com/sms.aspx?action=send" , $arr_fields);
		if($arr_re['code'] != 0) return array("code" => 500 , "msg" => "短信发送超时");
		$arr_cont = empty($arr_re["cont"]) ? array() : fun_format::xml_array($arr_re['cont']);
		if(!isset($arr_cont['returnstatus']) || $arr_cont['returnstatus'] != 'Success') {
			$msg = isset($arr_cont['message']) ? $arr_cont['message'] : "短信发送失败";
			return array("code" => 500 , "msg" => $msg);
		}
		$arr_return = array("code" => 0 , "id" => $arr['id']);

		if(!isset($arr["type"])) $arr["type"] = 0;
		if(!isset($arr["id"])) $arr["id"] = 0;
		if(!isset($arr["confirm_id"])) $arr["confirm_id"] = 0;
		$arr_fields = array(
			"sms_content" => $arr["cont"],
			"sms_tel" => $arr["tel"],
			"sms_type" => $arr["type"],
			"sms_addtime" => TIME,
			"sms_day" => date("Y-m-d" , TIME),
			"sms_time" => date("Y-m-d H:i:s" , TIME),
			"sms_about_id" => $arr['id'],
			"sms_confirm_id" => $arr['confirm_id']
		);
		tab_other_sms::on_save($arr_fields);
		return $arr_return;
	}

	//发送短信状态
	function get_sendstate() {
		return;
		$count = cls_config::get("count_id" , "sms");
		$pwd = cls_config::get("count_pwd" , "sms");
		$arr = fun_base::post("http://120.132.132.102/WS/BatchSend2.aspx?CorpID=" . $count . "&Pwd=" . $pwd);
		return $arr;
	}

	//获取短信回复
	function get_recont() {
		$count = cls_config::get("count_id" , "sms");
		$pwd = cls_config::get("count_pwd" , "sms");
		$uid = self::$perms['uid'];
		$arr = fun_base::post("http://www.ok9288.com/callApi.aspx?action=query&userid=" . $uid . "&account=" . $count . "&password=" . $pwd);
		if($arr['code'] != 0) return array("code" => 500 , "msg" => "短信接收超时");
		$arr_cont = empty($arr["cont"]) ? array() : fun_format::xml_array($arr['cont']);
		if(!isset($arr_cont['callbox'])) return array("code" => 500 , "msg" => "短信接收失败");
		
		if(isset($arr_cont['callbox']['mobile'])) {
			$arrx = $arr_cont['callbox'];
			$arr_cont['callbox'] = array($arrx);
		}

		foreach($arr_cont['callbox'] as $row) {
			if(empty($row['content'])) continue;
			$row['content'] = str_replace("，" , "," , $row['content']);
			$arr_re = explode("," , $row['content']);
			$arr_re[0] = trim($arr_re[0]);
			$cont = '';
			if(is_numeric($arr_re[0])) {
				$confirm_id = (int)$arr_re[0];
				if(count($arr_re)>1) {
					unset($arr_re[0]);
					$cont = implode("," , $arr_re);
				}
			} else {
				$confirm_id = 0;
				$cont = $row['content'];
			}
			$arr_list[] = array("tel"=>$row['mobile'],"confirm_id"=>$confirm_id,"cont"=>$cont,"time"=>strtotime($row['receivetime']));
		}
		return array("code"=>0 , "list" => $arr_list);
	}
}