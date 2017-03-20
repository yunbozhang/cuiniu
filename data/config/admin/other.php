<?php
return array (
  'other.ticket' => 
  array (
    'ticket_id' => array ( 'val' => 0,'w' => 0,),
    'ticket_title' =>  array ( 'val' => 1,  'w' => 300,
    ),
    'ticket_addtime' => 
    array (
      'val' => 11,
      'w' => 50,
    ),
    'ticket_starttime' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'ticket_endtime' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
  ),
  'other.email' => 
  array (
    'email_id' => 
    array (
      'val' => 0,
      'w' => 0,
    ),
    'email_title' => 
    array (
      'val' => 1,
      'w' => 300,
    ),
    'email_account_mode' => 
    array (
      'val' => 0,
      'w' => 50,
    ),
    'email_to' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'email_from' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'email_addtime' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'email_num' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
  ),
  'other.sms' => 
  array (
    'sms_id' => 
    array (
      'val' => 0,
      'w' => 0,
    ),
    'sms_content' => 
    array (
      'val' => 1,
      'w' => 200,
    ),
    'sms_tel' => 
    array (
      'val' => 1,
      'w' => 100,
    ),
    'sms_type' => 
    array (
      'val' => 0,
      'w' => 80,
    ),
    'sms_time' => 
    array (
      'val' => 1,
      'w' => 120,
    ),
    'sms_about_id' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'sms_recont' => 
    array (
      'val' => 1,
      'w' => 100,
    ),
    'sms_retime' => 
    array (
      'val' => 1,
      'w' => 120,
    ),
  ),
  'other.sms.re' => 
  array (
    're_id' => 
    array (
      'val' => 0,
      'w' => 0,
    ),
    're_tel' => 
    array (
      'val' => 1,
      'w' => 100,
    ),
    're_cont' => 
    array (
      'val' => 1,
      'w' => 300,
    ),
    're_time' => 
    array (
      'val' => 1,
      'w' => 120,
    ),
  ),
  'other.ads' => 
  array (
    'ads_id' => 
    array (
      'val' => 0,
      'w' => 0,
    ),
    'ads_position' => 
    array (
      'val' => 1,
      'w' => 100,
    ),
    'ads_title' => 
    array (
      'val' => 1,
      'w' => 300,
    ),
    'ads_type' => 
    array (
      'val' => 1,
      'w' => 50,
    ),
    'ads_html' => 
    array (
      'val' => 11,
      'w' => 80,
    ),
    'ads_cont' => 
    array (
      'val' => 11,
      'w' => 80,
    ),
    'ads_starttime' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'ads_endtime' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'ads_state' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'ads_addtime' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
  ),
  'other.pay' => 
  array (
    'pay_id' => 
    array (
      'val' => 0,
      'w' => 0,
    ),
    'pay_number' => 
    array (
      'val' => 1,
      'w' => 100,
    ),
    'pay_user_id' => 
    array (
      'val' => 1,
      'w' => 60,
    ),
    'pay_val' => 
    array (
      'val' => 1,
      'w' => 60,
    ),
    'pay_time' => 
    array (
      'val' => 1,
      'w' => 120,
    ),
    'pay_return_id' => 
    array (
      'val' => 0,
      'w' => 80,
    ),
    'pay_type' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'pay_state' => 
    array (
      'val' => 1,
      'w' => 80,
    ),
    'pay_about_id' => 
    array (
      'val' => 0,
      'w' => 100,
    ),
    'pay_method' => 
    array (
      'val' => 1,
      'w' => 100,
    ),
    'pay_title' => 
    array (
      'val' => 1,
      'w' => 200,
    ),
    'pay_beta' => 
    array (
      'val' => 1,
      'w' => 300,
    ),
  ),
	"other.pay.refund" => array(
		"refund_pay_id" => array("val" => 1,"w" => 0), //支付记录id
		"refund_state" => array("val" => 1,"w" => 80), //状态
		"refund_apply_time" => array("val" => 1,"w" => 120), //处理时间
		"refund_addtime" => array("val" => 1,"w" => 120), //申请时间
		"refund_return_time" => array("val" => 1,"w" => 120), //完成时间
		"refund_beta" => array("val" => 1,"w" => 100), //备注
		"refund_err" => array("val" => 0,"w" => 100), //失败提示
		"refund_uid" => array("val" => 0,"w" => 100), //用户uid
		"uname" => array("val" => 1,"w" => 100), //用户名
		"refund_admin_uid" => array("val" => 0,"w" => 100), //处理人uid
		"admin_uname" => array("val" => 1,"w" => 100), //处理人
		"refund_return_id" => array("val" => 1,"w" => 100), //返回id
		"refund_total" => array("val" => 1,"w" => 80), //申请退款金额
		"refund_retotal" => array("val" => 1,"w" => 80), //实际退款金额
		"refund_tips_tel" => array("val" => 1,"w" => 120), //用户手机
	),
	"other.share" => array(
		"share_id" => array("val" => 1,"w" => 0), //分享id
		"share_user_id" => array("val" => 1,"w" => 80), //分享用户id
		"user_name" => array("val" => 1,"w" => 120), //用户名
		"share_datetime" => array("val" => 1,"w" => 120), //分享日期
		"share_type" => array("val" => 1,"w" => 120), //分享类型
		"share_num" => array("val" => 1,"w" => 60), //点击次数
		"share_key" => array("val" => 1,"w" => 100), //Key
		"share_user_num" => array("val" => 1,"w" => 60), //注册人数
		"share_url" => array("val" => 1,"w" => 300), //注册人数
	),
	"other.language" => array(
		"language_id" => array("val" => 1,"w" => 0), //分享id
		"language_val" => array("val" => 1,"w" => 600), //内容
		"language_beta" => array("val" => 1,"w" => 200), //备注
	),
);