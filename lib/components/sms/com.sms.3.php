<?php
/*********http://58.53.128.167:8080/mo/?cpid=2026108&cppwd=F5E95D636D75AF84F80AE9803CC05FCE************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class com_sms
{
	//获取短信余量
	public function get_over( ){
		$count = cls_config::get("count_id" , "sms");
		$pwd = cls_config::get("count_pwd" , "sms");
		$arr = fun_base::post("http://58.53.128.167:8080/qamount/?cpid=" . $count . "&cppwd=" . strtoupper(md5($pwd)));
		$arr['cont'] = (int)$arr['cont'];
		if($arr["code"] == 0 && $arr["cont"] >= 0) {
			return array("code" => 0 , "num" => (int)$arr["cont"] );
		} else {
			switch($arr['cont']) {
				case -1:
					$err = "账号未注册";
					$code = 502;
					break;
				case -2:
					$err = "其它错误";
					$code = 501;
					break;
				case -3:
					$err = "密码错误";
					$code = 503;
					break;
				default:
					$err = "访问短信服务器出错";
					$code = 501;
			}
			return array("code" => $code , "msg" => $err , "num" => 0);
		}
	}
	//发送短信
	public function on_send( $arr ){
		$state = cls_config::get( "state", "sms" );
		if ( empty( $state ) ){
			return array( "code" => 500, "msg" => "短信接口未开启，可以进入后台模块设置开启" );
		}
		if(!isset($arr["tel"]) || empty($arr["tel"])) {
			return array("code"=>500,"msg"=>"发送失败，电话号码为空");
		}
		if( !isset($arr["cont"]) && empty($arr["cont"]) ) {
			return array("code"=>500,"msg"=>"发送失败，短信内容为空");
		}
		//如果为测试环境，只允许测试手机号
		if ( 0 < cls_config::IS_TEST ){
			$arr_allowtel = cls_config::get( "test_tel", "sms" );
			if(!is_array($arr_allowtel) || !in_array($arr['tel'] , $arr_allowtel)) {
				return array("code" => 500 , "msg" => "该号码未开通测试权限");
			}
		}
		$count = cls_config::get( "count_id", "sms" );
		$pwd = cls_config::get( "count_pwd", "sms" );
		$template = cls_config::get( "template", "sms" );
		if ( !empty( $template ) ){
			$sitename = cls_config::get( "site_name", "sys" );
			//if ( empty( $sitename ) ){
//				$sitename = cls_config::get( "site_title", "sys" );
//			}
			$template = str_replace( "{\$sitename}", $sitename, $template );
			$arr['cont'] = str_replace( "{\$cont}", $arr['cont'], $template );
		}
		$arr_split = cls_config::get( "split", "sms" );
		foreach ( $arr_split as $item => $key ){
		$arr['cont'] = str_replace( $item, $key, $arr['cont'] );
			}
		$arr_fields = array(
			"cpid" => $count,
			"cppwd" => strtoupper(md5($pwd)),
			"phone" => $arr['tel'],
			"msgtext" => fun_format::utf8_gbk($arr['cont']),
			"encode" => "UTF-8"
		);
		$arr_re = fun_base::post( "http://58.53.128.167:8080/mt/?", $arr_fields );
		$arr_re['cont'] = ( integer )$arr_re['cont'];
		if ( $arr_re['code'] == 0 && 0 <= $arr_re['cont'] )
		{
			$arr_return = array(
				"code" => 0,
				"id" => $arr_re['cont']
			);
		}
		else
		{
			switch ( $arr['cont'] )
			{
				case -1 :
						$err = "账号未注册";
						break;
				case -2 :
						$err = "其它错误";
						break;
				case -3 :
						$err = "密码错误";
						break;
				case -4 :
						$err = "一次提交信息不能超过600个手机号码";
						break;
				case -5 :
						$err = "余额不足，请先充值";
						break;
				case -6 :
						$err = "定时发送时间不是有效的时间格式";
						break;
				case -8 :
						$err = "发送内容需在3到250字之间";
						break;
				case -9 :
						$err = "发送号码为空";
						break;
				default :
						$err = "访问短信服务器出错";
			}
				$arr_return = array(
					"code" => 500,
					"msg" => $err,
					"id" => 0
				);
		}
		if ( !isset( $arr['type'] ) ){
				$arr['type'] = 0;
		}
		if ( !isset( $arr['id'] ) ){
				$arr['id'] = 0;
		}
		if ( !isset( $arr['confirm_id'] ) ){
				$arr['confirm_id'] = 0;
		}
		$arr_fields = array(
				"sms_content" => $arr['cont'],
				"sms_tel" => $arr['tel'],
				"sms_type" => $arr['type'],
				"sms_addtime" => TIME,
				"sms_day" => date( "Y-m-d", TIME ),
				"sms_time" => date( "Y-m-d H:i:s", TIME ),
				"sms_about_id" => $arr['id'],
				"sms_confirm_id" => $arr['confirm_id']
		);
		tab_other_sms::on_save( $arr_fields );
		return $arr_return;
	}
	//获取发送状态
	public function get_sendstate( )
	{
		$count = cls_config::get( "count_id", "sms" );
		$pwd = cls_config::get( "count_pwd", "sms" );
		$arr = fun_base::post( "http://58.53.128.167:8080/qreport/?cpid=".$count."&cppwd=".strtoupper(md5($pwd)) );
								return $arr;
				}
	//获取短信回复
	public function get_recont( )
	{
		$count = cls_config::get( "count_id", "sms" );
		$pwd = cls_config::get( "count_pwd", "sms" );
		$arr = fun_base::post( "http://58.53.128.167:8080/mo/?cpid=".$count."&cppwd=".strtoupper(md5($pwd)) );
		
		if ( $arr['code'] != 0 )
		{
			return $arr;
		}
		$arr_list = array( );
		$arr['cont'] = fun_format::gbk_utf8($arr['cont']);
		if ( !empty( $arr['cont'] ) )
		{
			$arr1 = explode(chr(10) , $arr['cont']);
			$count = (int)$arr1[0];

			if($count>0) {
				//file_put_contents(KJ_DIR_ROOT."/test.txt",$arr1[1]);
				$arr_row = explode( "||", trim( $arr1[1] ) );
				foreach($arr_row as $row) {
					$arr = explode( "##", trim( $arr1[1] ) );
					$arr[2] = str_replace("，" , "," , $arr[2]);
					$arrx = explode(",",$arr[2]);
					$confirm_id = (int)$arrx[0];
					unset($arrx[0]);
					$cont = implode("," , $arrx);
					$arr_list[] = array("tel" => $arr[1],"confirm_id" => $confirm_id,"cont" => $cont,"time" => $arr[0]);
				}
			}
		}
		return array("code" => 0,"list" => $arr_list);
	}

}