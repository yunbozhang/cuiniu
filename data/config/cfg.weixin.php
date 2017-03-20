<?php
return array(
	'domain' => 'http://mall.meal.kjcms.com',
	'uname' => 'xx',
	'token' => 'xxx',
	'appid' => '',
	'mch_id' => '',//商户id
	'mch_key' => '',//商户key
	'customer_open' => 0,//多客服功能，为：1 表示开启，0 表示关闭
	'appsecret' => '',
	'certify' => '0',
	'msgmode' => 1,//0表示关闭站内搜索，1表示开启
	"mediatype" => array('voice' => array('mp3','wma','wav','amr'),
						 'video' => array('rm' , 'rmvb' , 'wmv' , 'avi' , 'mpg' , 'mpeg' , 'mp4'),
						 'image' => array('bmp', 'png', 'jpeg', 'jpg', 'gif'),
						 ),
	"mediasize" => array('voice' => 5,
						 'video' => 20,
						 'image' => 2,
						 ),
);