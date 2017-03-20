<?php
return array (
  'alipay' => 
  array (
    'name' => '支付宝',
    'pic' => 'http://www.kjcms.com/webcss/api/images/pay_alipay.gif',
    'version' => '1.0',
    'installtime' => '2012-08-26',
    'currency' => 'rmb',
    'state' => '1',
    'type' => '',
    'fields' => 
    array (
      'email' => 'test@126.com',
      'title' => '支付宝支付',
      'parterid' => '2088002030995855',
      'key' => 'xxxxx',
      'feetype' => '0',
      'feeval' => '0',
      'intro' => '',
      'interfacetype' => 'js',
    ),
  ),
  'alipaywap' => 
  array (
    'name' => '手机支付宝',
    'pic' => 'http://www.kjcms.com/webcss/api/images/pay_alipay.gif',
    'version' => '1.0',
    'installtime' => '2014-08-25',
    'currency' => 'rmb',
    'state' => '1',
    'type' => 'wap',
    'fields' => 
    array (
      'email' => 'test@qq.com',
      'title' => '手机支付宝',
      'parterid' => '2088711593061',
      'key' => 'eeee',
      'feetype' => '0',
      'feeval' => '0',
      'intro' => '',
      'interfacetype' => 'wap',
    ),
  ),
  'chinabank' => 
  array (
    'name' => '网银在线',
    'pic' => 'http://www.kjcms.com/webcss/api/images/pay_chinabank.gif',
    'version' => '1.0',
    'installtime' => '2013-10-01',
    'currency' => 'CNY',
    'type' => '',
    'state' => '0',
    'fields' => 
    array (
      'mid' => '1001',
      'title' => '网银在线',
      'key' => '',
      'intro' => NULL,
    ),
  ),
  'weixin' => 
  array (
    'name' => '微信支付',
    'pic' => 'http://www.kjcms.com/webcss/api/images/pay_weixin.gif',
    'version' => '1.0',
    'installtime' => '2015-3-18',
    'currency' => 'CNY',
    'type' => '',
    'state' => '1',
    'fields' => 
    array (
      'mid' => '1',
      'title' => '微信支付',
      'key' => 'test',
      'intro' => NULL,
    ),
  ),
);