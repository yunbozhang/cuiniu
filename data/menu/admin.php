<?php
return array (
  '订餐' => 
  array (
    0 => 
    array (
      'url' => '?app_module=meal&app=shop',
      'name' => '店铺管理',
      'app' => 'shop',
      'app_module' => 'meal',
    ),
    1 => 
    array (
      'url' => '?app_module=meal&app=table',
      'name' => '桌位管理',
      'app' => 'table',
      'app_module' => 'meal',
    ),
	2 => 
	array (
	  'url' => '?app_module=meal&app=table.reserve',
	  'name' => '桌位预订',
	  'app' => 'table',
	  'app_module' => 'meal',
	),
   3 => 
    array (
      'url' => '?app_module=meal&app=menu.group',
      'name' => '商品分组',
      'app' => 'menu.group',
      'app_module' => 'meal',
    ),
    4 => 
    array (
      'url' => '?app_module=meal&app=menu',
      'name' => '商品管理',
      'app' => 'menu',
      'app_module' => 'meal',
    ),
    5 => 
    array (
      'url' => '?app_module=meal&app=menu.today',
      'name' => '当日商品',
      'app' => 'menu.today',
      'app_module' => 'meal',
    ),
    6 => 
    array (
      'url' => '?app_module=meal&app=order',
      'name' => '订单管理',
      'app' => 'order',
      'app_module' => 'meal',
    ),
    7 => 
    array (
      'url' => '?app_module=meal&app=dispatch',
      'name' => '派送范围',
      'app' => 'dispatch',
      'app_module' => 'meal',
    ),
    8 => 
    array (
      'url' => '?app_module=sys&app=area',
      'name' => '地区管理',
      'app' => 'area',
      'app_module' => 'sys',
    ),
    9 => 
    array (
      'url' => '?app_module=meal&app=checkout',
      'name' => '结算中心',
      'app' => 'checkout',
      'app_module' => 'meal',
    ),
    10 => 
    array (
      'url' => '?app_module=meal&app=comment',
      'name' => '订单评论',
      'app' => 'comment',
      'app_module' => 'meal',
    ),
  ),
  '用户' => 
  array (
    0 => 
    array (
      'url' => '?app_module=sys&app=user',
      'name' => '注册用户',
      'app_module' => 'sys',
      'app' => 'user',
    ),
    1 => 
    array (
      'url' => '?app_module=sys&app=user.group',
      'name' => '用 户 组',
      'app_module' => 'sys',
      'app' => 'user.group',
    ),
    2 => 
    array (
      'url' => '?app_module=sys&app=user.depart',
      'name' => '组织架构',
      'app_module' => 'sys',
      'app' => 'user.depart',
    ),
    3 => 
    array (
      'url' => '?app_module=sys&app=user.action',
      'name' => '经验积分',
      'app_module' => 'sys',
      'app' => 'user.action',
    ),
    4 => 
    array (
      'url' => '?app_module=sys&app=user.repayment',
      'name' => '预付款记录',
      'app_module' => 'sys',
      'app' => 'user.repayment',
    ),
    5 => 
    array (
      'url' => '?app_module=other&app=pay&app_act=record',
      'name' => '充值记录',
      'app' => 'pay',
      'app_module' => 'other',
    ),
    6 => 
    array (
      'url' => '?app_module=other&app=pay.refund',
      'name' => '退款记录',
      'app' => 'pay.refund',
      'app_module' => 'other',
    ),
    7 => 
    array (
      'url' => '?app_module=sys&app=user.address',
      'name' => '收货信息',
      'app_module' => 'sys',
      'app' => 'user.address',
    ),
  ),
  '系统' => 
  array (
    0 => 
    array (
      'url' => '?app_module=sys&app=config',
      'name' => '模块设置',
      'app_module' => 'sys',
      'app' => 'config',
    ),
    1 => 
    array (
      'url' => '?app_module=sys&app=user.log',
      'name' => '管理日志',
      'app_module' => 'sys',
      'app' => 'user.log',
    ),
    2 => 
    array (
      'url' => '?app_module=sys&app=log',
      'name' => '系统日志',
      'app_module' => 'sys',
      'app' => 'log',
    ),
    3 => 
    array (
      'url' => '?app_module=sys&app=verify',
      'name' => '验证记录',
      'app_module' => 'sys',
      'app' => 'verify',
    ),
    4 => 
    array (
      'url' => '?app_module=sys&app=database',
      'name' => '数 据 库',
      'app_module' => 'sys',
      'app' => 'database',
    ),
  ),
  '文章' => 
  array (
    0 => 
    array (
      'url' => '?app_module=article&app=channel',
      'name' => '频道管理',
      'app_module' => 'article',
      'app' => 'channel',
    ),
    1 => 
    array (
      'url' => '?app_module=article&app=topic',
      'name' => '专题管理',
      'app_module' => 'article',
      'app' => 'topic',
    ),
    2 => 
    array (
      'name' => '文章管理',
      'app' => 'article',
      'app_module' => '',
      'list' => 
      array (
        'app' => 'index',
        'app_act' => 'menu_article',
      ),
    ),
  ),
  '统计报表' => 
  array (
    0 => 
    array (
      'url' => '?app_module=report&app=order',
      'name' => '销售统计',
      'app' => 'report',
      'app_module' => 'report',
    ),
    1 => 
    array (
      'url' => '?app_module=report&app=user',
      'name' => '用户统计',
      'app' => 'report',
      'app_module' => 'report',
    ),
    2 => 
    array (
      'url' => '?app_module=report&app=top',
      'name' => '排行榜',
      'app' => 'top',
      'app_module' => 'report',
    ),
    3 => 
    array (
      'url' => '?app_module=report&app=menu',
      'name' => '商品统计',
      'app' => 'menu',
      'app_module' => 'report',
    ),
  ),
  '微信' => 
  array (
    0 => 
    array (
      'url' => '?app_module=weixin&app=message',
      'name' => '消息管理',
      'app' => 'message',
      'app_module' => 'weixin',
    ),
    1 => 
    array (
      'url' => '?app_module=weixin&app=menu',
      'name' => '自定义菜单',
      'app' => 'menu',
      'app_module' => 'weixin',
    ),
    2 => 
    array (
      'url' => '?app_module=weixin&app=user',
      'name' => '关注者管理',
      'app' => 'user',
      'app_module' => 'weixin',
    ),
    3 => 
    array (
      'url' => '?app_module=weixin&app=redpack',
      'name' => '微信红包',
      'app' => 'redpack',
      'app_module' => 'weixin',
    ),
    4 => 
    array (
      'url' => '?app_module=weixin&app=site',
      'name' => '微信站点',
      'app' => 'site',
      'app_module' => 'weixin',
    ),
  ),
  '营销' => 
  array (
    0 => 
    array (
      'url' => '?app_module=meal&app=act',
      'name' => '商家促销',
      'app' => 'act',
      'app_module' => 'meal',
    ),
    1 => 
    array (
      'url' => '?app_module=act&app=gift',
      'name' => '积分兑换',
      'app' => 'gift',
      'app_module' => 'act',
    ),
    2 => 
    array (
      'url' => '?app_module=act&app=gift.record',
      'name' => '兑换记录',
      'app' => 'gift.record',
      'app_module' => 'act',
    ),
    3 => 
    array (
      'url' => '?app_module=act&app=lottery',
      'name' => '抽奖活动',
      'app' => 'lottery',
      'app_module' => 'act',
    ),
    4 => 
    array (
      'url' => '?app_module=act&app=lottery.record',
      'name' => '抽奖记录',
      'app' => 'lottery.record',
      'app_module' => 'act',
    ),
    5 => 
    array (
      'url' => '?app_module=act&app=lottery.log',
      'name' => '中奖记录',
      'app' => 'lottery.log',
      'app_module' => 'act',
    ),
    6 => 
    array (
      'url' => '?app_module=act&app=voucher',
      'name' => '优惠券',
      'app' => 'voucher',
      'app_module' => 'act',
    ),
    7 => 
    array (
      'url' => '?app_module=other&app=share',
      'name' => '分享记录',
      'app' => 'other',
      'app_module' => 'share',
    ),
  ),
  '其它' => 
  array (
    0 => 
    array (
      'url' => '?app_module=other&app=ads',
      'name' => '广告管理',
      'app' => 'ads',
      'app_module' => 'other',
    ),
    1 => 
    array (
      'url' => '?app_module=other&app=email',
      'name' => '邮件管理',
      'app' => 'email',
      'app_module' => 'other',
    ),
    2 => 
    array (
      'url' => '?app_module=other&app=sms',
      'name' => '短信发送记录',
      'app' => 'sms',
      'app_module' => 'other',
    ),
    3 => 
    array (
      'url' => '?app_module=other&app=sms.re',
      'name' => '短信回复记录',
      'app' => 'sms.re',
      'app_module' => 'other',
    ),
    4 => 
    array (
      'url' => '?app_module=other&app=msg',
      'name' => '留言反馈',
      'app' => 'msg',
      'app_module' => 'other',
    ),
    5 => 
    array (
      'url' => '?app_module=other&app=link',
      'name' => '友情链接',
      'app' => 'link',
      'app_module' => 'other',
    ),
  ),
);