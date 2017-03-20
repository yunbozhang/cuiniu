<?php
/* 短信接口 */
class com_sms {
	//获取短信余量
	function get_over() {
		$account = cls_config::get("count_id" , "sms");
		$pswd = md5(cls_config::get("count_pwd" , "sms"));
		$arr = fun_base::post("http://www.smsbao.com/query?u=" . $account . "&p=" . $pswd);
		//$arr["cont"] 短信平台返回字符串
		//$arr["code"] post执行结果
		$str = $arr["cont"];
		$match = explode("\n",$str);
		$code = $match[0];//短信状态
		
		if($arr["code"] == 0 && $code == 0 ) 
		{   
			$cont = explode(",",$match[1]);//短信数量
			return array("code" => 0, "num" => $cont[1]);
		}
	    else 
		{
			switch($code) {
				case 30:
					$err = "密码错误";
					break;
				case 40:
					$err = "账号错误";
					break;
				case 42:
					$err = "账号过期";
					break;
				case 43:
					$err = "IP限制，禁止访问";
					break;
				default:
					$err = "访问服务器出错";
			}
			return array("code" => $code , "msg" => $err , "num" => 0);
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
		$pwd = md5(cls_config::get("count_pwd" , "sms"));

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

		//$arr_re = fun_base::post("http://www.smsbao.com/sms?u=".$count."&p=".$pwd."&m=".$arr["tel"] , array('c'=>$arr["cont"]));
		$arr_re = fun_base::post("http://www.smsbao.com/sms?u=".$count."&p=".$pwd."&m=".$arr["tel"] . "&c=" . urlencode($arr["cont"]));
		if($arr_re["code"] == 0 && $arr_re["cont"] >= 0) {
			$arr_return = array("code" => 0 , "id" => $arr_re["cont"] );
		} else {
			switch($code) {
				case 30:
					$err = "密码错误";
					break;
				case 40:
					$err = "账号错误";
					break;
				case 42:
					$err = "账号过期";
					break;
				case 43:
					$err = "IP限制，禁止访问";
					break;
				case 51:
					$err = "手机号错";
					break;
				case 41:
					$err = "额度不足";
					break;
				case 50:
					$err = "内容敏感";
					break;
				default:
					$err = "访问服务器出错";
					
			}
			$arr_return = array("code" => $code , "msg" => $err , "id" => 0);
		}
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
		return;
		$count = cls_config::get("count_id" , "sms");
		$pwd = cls_config::get("count_pwd" , "sms");
		//return array("code"=>0 , "cont"=>"||18665921015#a#2012-08-16 13:53:46#||18665921015#B#2012-08-16 13:53:58#");
		$arr = fun_base::post("http://120.132.132.102/WS/Get.aspx?CorpID=" . $count . "&Pwd=" . $pwd);
		if($arr['code']!=0) return $arr;
		$arr_list = array();
		if(!empty($arr['cont'])) {
			$arr = explode("||" , trim($arr['cont']));
			foreach($arr as $row) {
				if(empty($row)) continue;
				$arr_row = explode("#" , $row);
				if(count($arr_row)<3) continue;
				$arr_re = explode("," , $arr_row[1]);
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
					$cont = $arr_row[1];
				}
				$arr_list[] = array("tel"=>$arr_row[0],"confirm_id"=>$confirm_id,"cont"=>$cont,"time"=>$arr_row[2]);
			}
		}
		return array("code"=>0 , "list" => $arr_list);
	}
}