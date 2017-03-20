<?php
return array (
	'sys.user' => 
	array (
	'user_id' => '用户id',
	'user_name' => '用户名',
	'user_email' => '电子邮箱',
	'user_regtime' => '注册时间',
	'user_regip' => '注册IP',
	'user_loginip' => '登录IP',
	'user_netname' => '昵称',
	'user_logintime' => '登录时间',
	'user_loginnum' => '登录次数',
	'user_type' => '类型',
	'user_state' => '状态',
	'user_score' => '积分',
	'user_experience' => '经验值',
	'user_repayment' => '预付款',
	'user_email_verify' => '是否邮件验证',
	'user_birthday' => '出生日期',
	'user_sex' => '性别',
	'user_location' => '当前所在地',
	'user_house_location' => '家乡',
	'user_tel' => '电话',
	'user_mobile' => '手机号',
	'user_address' => '联系地址',
	'user_realname' => '真实姓名',
	'user_invite_uid' => '邀请人',
	'group_name' => '用户组',
	'user_order_num' => '订单数',
	'user_totalpay' => '订单金额',
	'user_site_id' => '站点ID',
	'site_name' => '站点名称',
	),
	'sys.user.group' => 
	array (
	'group_id' => '用户组id',
	'group_name' => '名称',
	'group_addtime' => '添加时间',
	'group_updatetime' => '更新时间',
	'group_sort' => '排序',
	'group_pid' => '上级id',
	'group_limit_admin' => '权限',
	),
	'sys.user.log' => 
	array (
	'log_id' => '日志id',
	'log_user_id' => '用户id',
	'log_ip' => 'IP',
	'log_app_act' => '行为',
	'log_app' => '页面',
	'log_app_module' => '模块',
	'log_addtime' => '时间',
	'log_cont' => '明细',
	'log_module' => '模块',
	'log_key' => '键',
	'user_name' => '用户名',
	),
	'sys.user.action' => 
	array (
	'action_id' => 'id',
	'action_user_id' => '用户id',
	'user_name' => '用户',
	'action_score' => '积分',
	'action_experience' => '经验值',
	'action_key' => '行为',
	'action_addtime' => '添加时间',
	'action_beta' => '备注',
	),
	'sys.user.repayment' => 
	array (
	'repayment_id' => 'id',
	'repayment_user_id' => '用户id',
	'repayment_val' => '金额',
	'repayment_time' => '时间',
	'repayment_beta' => '备注',
	'repayment_type' => '类型',
	'action_addtime' => '添加时间',
	'repayment_about_id' => '相关id',
	'user_name' => '用户名',
	),
	'sys.verify' => 
	array (
	'verify_id' => 'id',
	'verify_user_id' => '用户id',
	'user_name' => '用户名',
	'verify_type' => '验证类型',
	'verify_key' => '验证值',
	'verify_time' => '生成时间',
	'verify_retime' => '验证时间',
	'verify_state' => '状态',
	),
	'sys.user.address' => 
	array (
	'address_id' => 'id',
	'address_area' => '区域',
	'address_address' => '地址',
	'address_name' => '名称',
	'address_sex' => '性别',
	'address_tel' => '电话',
	'address_email' => '电子邮箱',
	'address_user_id' => '用户id',
	'user_name' => '用户',
	),
	'sys.config' => 
	array (
	'config_intro' => '说明',
	'config_val' => '值',
	'config_name' => '变量',
	),
	'sys.area' => 
	array (
	'area_id' => '区域id',
	'area_name' => '名称',
	'area_sort' => '排序',
	'area_val' => '值',
	'area_tag' => '附近建筑',
	'area_pin' => '拼音',
	'area_jian' => '简写',
	'area_position_lng' => '经度',
	'area_position_lat' => '纬度',
	'area_pic' => '图片',
	),
	'meal.menu' => 
	array (
	'menu_id' => '餐品id',
	'menu_title' => '名称',
	'menu_number' => '编号',
	'menu_type' => '类型',
	'menu_intro' => '介绍',
	'menu_price' => '单价',
	'menu_unit' => '单位',
	'menu_pic' => '图片',
	'menu_pic_small' => '略图',
	'menu_addtime' => '添加时间',
	'menu_updatetime' => '修改时间',
	'menu_group_id' => '分组id',
	'group_name' => '分组',
	'menu_attribute' => '属性',
	'menu_num' => '默认数量',
	'menu_state' => '状态',
	'menu_mode' => '提供模式',
	'menu_tj' => '推荐',
	'menu_sort' => '排序',
	),
	'meal.shop' => 
	array (
	'shop_id' => '店铺id',
	'shop_name' => '店铺名称',
	'shop_user_id' => '用户id',
	'user_name' => '用户名',
	'shop_addtime' => '添加时间',
	'shop_updatetime' => '修改时间',
	'shop_type' => '分类',
	'shop_scope' => '送餐范围',
	'shop_menu_mode' => '菜单模式',
	'shop_intro' => '介绍',
	'shop_linkname' => '联系人',
	'shop_linktel' => '联系人电话',
	'shop_tel' => '订餐电话',
	'shop_address' => '店铺地址',
	'shop_area_id' => '地区id',
	'shop_area_allid' => '地区id树',
	'shop_area' => '所在区域',
	'shop_dispatch_price' => '起送价格',
	'shop_state' => '状态',
	'shop_mode' => '显示样式',
	'shop_hits' => '访问量',
	'shop_sold' => '总销量',
	),
	'meal.checkout.no' => 
	array (
	'shop_id' => '店铺id',
	'shop_name' => '店铺',
	'moneyall' => '有效金额',
	'moneypay' => '在线支付',
	'moneyaffter' => '货到付款',
	'num' => '订单数量',
	'paynum' => '在线支付数量',
	'affternum' => '货到付款数量',
	'shop_rebate' => '抽成比例',
	'shop_checkout_money' => '最低结款',
	'money' => '抽成',
	'moneytotal' => '商品金额',
	'moneyrepay' => '预付金额',
	'moneyadd' => '配送金额',
	'moneyfav' => '优惠金额',
	),
	'meal.checkout' => 
	array (
	'checkout_id' => '结算id',
	'checkout_shop_id' => '店铺id',
	'shop_name' => '店铺',
	'checkout_money' => '抽成',
	'checkout_date1' => '开始时间',
	'checkout_date2' => '结束时间',
	'checkout_addtime' => '操作时间',
	'checkout_user_id' => '操作人id',
	'checkout_beta' => '备注',
	'checkout_moneyall' => '有效金额',
	'checkout_moneypay' => '在线支付',
	'checkout_moneyaffter' => '货到付款',
	'checkout_num' => '订单数量',
	'checkout_numpay' => '在线支付数量',
	'checkout_rebate' => '抽成比例',
	'checkout_rebate_money' => '起抽金额',
	'checkout_moneytotal' => '商品金额',
	'checkout_moneyrepay' => '预付款',
	'checkout_moneyadd' => '配送费',
	'checkout_moneyfav' => '优惠金额',
	),
	'meal.dispatch' => 
	array (
	'dispatch_id' => '区域id',
	'area_name' => '地址',
	'dispatch_price' => '起送价',
	'dispatch_addprice' => '配送费',
	'dispatch_number' => '排序值',
	'dispatch_time' => '时间(分)',
	'dispatch_smstel' => '短信电话',
	),
	'meal.menu.today' => 
	array (
	'today_id' => 'id',
	'today_menu_id' => '菜品id',
	'menu_title' => '菜品名称',
	'today_num' => '数量',
	'today_date' => '当天日期',
	'today_date_period' => '时间段',
	),
	'meal.menu.group' => 
	array (
	'group_id' => 'id',
	'group_sort' => '排序',
	'group_name' => '名称',
	),
	'meal.table.reserve' => 
	array (
	'reserve_id' => 'id',
	'shop_name' => '商家名称',
	'reserve_tablename' => '桌位',
	'reserve_datetime' => '预订时间',
	'reserve_addtime' => '下单时间',
	'reserve_name' => '联系人',
	'reserve_tel' => '电话',
	'reserve_num' => '数量',
	'reserve_beta' => '备注',
	'reserve_state' => '状态',
	'reserve_deposit' => '订金',
	'reserve_menutime' => '上菜时间',
	),
	'meal.act' => 
	array (
	'act_id' => 'id',
	'act_shop_id' => '店铺id',
	'shop_name' => '店铺名称',
	'act_name' => '活动名称',
	'act_where' => '条件',
	'act_where_val' => '条件值',
	'act_method' => '优惠',
	'act_method_val' => '优惠值',
	'act_starttime' => '开始时间',
	'act_endtime' => '结束时间',
	'act_addtime' => '添加时间',
	'act_state' => '状态',
	'act_beta' => '备注',
	),
	'meal.order' => 
	array (
	'order_id' => 'id',
	'order_state' => '状态',
	'order_isaward' => '是否奖励',
	'order_area' => '区域',
	'order_louhao1' => '地址',
	'order_louhao2' => '楼号2',
	'order_company' => '公司',
	'order_depart' => '部门',
	'order_name' => '姓名',
	'order_sex' => '性别',
	'order_tel' => '电话',
	'order_telext' => '分机号',
	'order_mobile' => '手机',
	'order_arrive' => '抵达时间',
	'order_addtime' => '添加时间',
	'order_time' => '订单时间',
	'order_user_id' => '用户id',
	'user_name' => '用户',
	'order_ids' => '订单列表',
	'order_total' => '消费金额',
	'order_score_money' => '抵扣金额',
	'order_favorable' => '优惠金额',
	'order_total_pay' => '结算金额',
	'order_pay_val' => '在线支付',
	'order_number' => '订单号',
	'order_act_ids' => '享受活动',
	'order_ticket' => '发票',
	'order_beta' => '备注',
	'shop_name' => '所属店铺',
	'order_shop_id' => '店铺Id',
	'order_repayment' => '预付款',
	'order_addprice' => '配送费',
	),
	'other.ticket' => 
	array (
	'ticket_id' => 'id',
	'ticket_title' => '标题',
	'ticket_addtime' => '添加时间',
	'ticket_starttime' => '开始时间',
	'ticket_endtime' => '结束时间',
	),
	'article.channel' => 
	array (
	'channel_id' => 'id',
	'channel_name' => '名称',
	'channel_html' => '是否生成html',
	'channel_html_dir' => 'html目录',
	'channel_html_dirstyle' => '目录分隔方式',
	'channel_addtime' => '添加时间',
	'channel_state' => '状态',
	),
	'article' => 
	array (
	'article_id' => 'id',
	'article_title' => '标题',
	'article_addtime' => '添加时间',
	'article_updatetime' => '修改时间',
	'article_attribute' => '属性',
	'folder_name' => '所属目录',
	'article_hits' => '浏览次数',
	'channel_name' => '频道名称',
	'article_source' => '来源',
	'article_author' => '作者',
	'article_state' => '状态',
	'article_tag' => '标签',
	'article_isread' => '是否已查看',
	'article_uid' => '添加人',
	'article_updateuid' => '最后修改人',
	'article_topic_id' => '所属专题',
	),
	'article.topic' => 
	array (
	'topic_id' => 'id',
	'topic_name' => '名称',
	'topic_tpl' => '模板',
	'topic_addtime' => '添加时间',
	'topic_state' => '状态',
	),
	'other.email' => 
	array (
	'email_id' => 'id',
	'email_title' => '标题',
	'email_account_mode' => '模式',
	'email_to' => '收件箱',
	'email_from' => '发件箱',
	'email_addtime' => '发送时间',
	'email_num' => '发送次数',
	),
	'other.sms' => 
	array (
	'sms_id' => 'id',
	'sms_content' => '发送内容',
	'sms_tel' => '接收号码',
	'sms_type' => '类型',
	'sms_time' => '发送时间',
	'sms_about_id' => '相关id',
	'sms_recont' => '回复内容',
	'sms_retime' => '回复时间',
	),
	'other.sms.re' => 
	array (
	're_id' => 'id',
	're_tel' => '回复号码',
	're_cont' => '回复内容',
	're_time' => '回复时间',
	),
	'other.ads' => 
	array (
	'ads_id' => 'id',
	'ads_title' => '标题',
	'ads_addtime' => '添加时间',
	'ads_starttime' => '开始时间',
	'ads_endtime' => '结束时间',
	'ads_state' => '状态',
	'ads_type' => '类型',
	'ads_position' => '广告位',
	),
	'other.pay' => 
	array (
	'pay_id' => 'id',
	'pay_number' => '订单号',
	'pay_user_id' => '用户id',
	'pay_val' => '充值金额',
	'pay_return_id' => '第三方返回id',
	'pay_type' => '支付类型',
	'pay_state' => '充值状态',
	'pay_about_id' => '相关id',
	'pay_method' => '支付方式',
	'pay_title' => '标题',
	'pay_beta' => '备注',
	'pay_time' => '充值时间',
	),
	'other.pay.refund' => 
	array (
	'refund_pay_id' => '支付记录id',
	'refund_state' => '状态',
	'refund_apply_time' => '处理时间',
	'refund_addtime' => '申请时间',
	'refund_return_time' => '完成时间',
	'refund_beta' => '备注',
	'refund_err' => '失败提示',
	'refund_uid' => '用户uid',
	'uname' => '用户名',
	'refund_admin_uid' => '处理人uid',
	'admin_uname' => '处理人',
	'refund_return_id' => '返回id',
	'refund_total' => '申请退款金额',
	'refund_retotal' => '实际退款金额',
	'refund_tips_tel' => '用户手机',
	),
	'other.share' => 
	array (
	'share_id' => '分享id',
	'share_user_id' => '分享用户id',
	'user_name' => '用户名',
	'share_datetime' => '分享日期',
	'share_type' => '分享类型',
	'share_num' => '点击次数',
	'share_key' => 'Key',
	'share_user_num' => '注册人数',
	'share_url' => '分享链接',
	),
	'other.language' => 
	array (
	'language_id' => 'id',
	'language_val' => '内容',
	'language_beta' => '备注',
	),
	'weixin.user' => 
	array (
	'user_openid' => '微信id',
	'user_name' => '昵称',
	'user_sex' => '性别',
	'user_area' => '地区',
	'user_addtime' => '关注时间',
	'user_canceltime' => '取消关注时间',
	'user_state' => '状态',
	'user_pic' => '头像',
	),
	'weixin.site' => 
	array (
	'site_id' => 'ID',
	'site_name' => '站点名称',
	'site_domain' => '域名',
	'site_wx_uname' => '微信号',
	'site_wx_token' => 'Token',
	'site_wx_appid' => 'AppId',
	'site_wx_appsecret' => 'AppSecret',
	'site_wx_certify' => '认证',
	'site_wx_msgmode' => '搜索',
	),
	'weixin.redpack' => 
	array (
		'redpack_id' => 'ID',
		'redpack_type' => '类型',
		'redpack_title' => '活动名称',
		'redpack_shopname' => '商家名称',
		'redpack_beta' => '备注',
		'redpack_greet' => '祝福语',
		'redpack_min_money' => '最小值',
		'redpack_max_money' => '最大值',
		'redpack_num' => '发放次数',
		'redpack_user_num' => '领取次数',
		'redpack_starttime' => '开始时间',
		'redpack_endtime' => '结束时间',
		'redpack_event' => '触发事件',
		'redpack_state' => '状态',
		'redpack_addtime' => '添加时间',
		'redpack_site_id' => '站点',
	),
	"act.lottery" => array(
		"lottery_id" => 'ID',
		"lottery_type" => '类型',
		"lottery_title" => '标题',
		"lottery_starttime" => '开始时间',
		"lottery_endtime" => '结束时间',
		"lottery_limit_user" => '用户抽奖次数',
	),
	"act.lottery.record" => array(
		"record_id" => 'ID',
		"record_lottery_id" => '类型',
		"lottery_title" => '标题',
		"record_user_id" => '开始时间',
		"user_name" => '用户名', 
		"user_netname" => '用户昵称',
		"record_wx_id" => '微信ID',
		"record_award_id" => '奖品ID',
		"award_name" => '奖品',
		"record_score" => '消耗积分',
		"record_datetime" => '抽奖时间',
	),
	"act.lottery.log" => array(
		"log_id" => 'ID',
		"log_lottery_id" => '类型',
		"lottery_title" => '标题',
		"log_user_id" => '开始时间',
		"user_name" => '用户名', 
		"user_netname" => '用户昵称',
		"log_wx_id" => '微信ID',
		"log_award_id" => '奖品ID',
		"award_name" => '奖品',
		"log_datetime" => '中奖时间',
		"log_state" => '状态',
	),
	'act.gift' => 
	array (
	'gift_id' => 'id',
	'gift_name' => '名称',
	'gift_total' => '总数',
	'gift_total_day' => '每天数量',
	'gift_num' => '已兑换',
	'gift_num_today' => '今日兑换',
	'gift_time' => '最后兑换时间',
	'gift_starttime' => '开始时间',
	'gift_endtime' => '结束时间',
	'gift_state' => '状态',
	'gift_group' => '分组',
	),
	'act.gift.record' => 
	array (
	'record_id' => 'id',
	'record_user_id' => '用户id',
	'user_name' => '用户名',
	'record_gift_id' => '礼品id',
	'gift_name' => '礼品名',
	'record_score' => '消耗积分',
	'record_datetime' => '兑换时间',
	'record_num' => '兑换数量',
	'record_ip' => 'IP',
	'record_linkname' => '收货人',
	'record_tel' => '联系电话',
	'record_address' => '地址',
	'record_send_time' => '发货时间',
	'record_receive_time' => '领取时间',
	),
	"act.voucher" => array(
	//用户表,sys_user
		"voucher_id" => 'Id',
		"voucher_user_id" => '用户Id',
		"user_name" => '用户名',
		"voucher_val" => '面值',
		"voucher_pwd" => '领取密码',
		"voucher_type" => '类型',
		"voucher_addtime" => '生成时间',
		"voucher_state" => '状态',
		"voucher_usetime" => '领取时间',
	),
  'quan.category' => 
  array (
    'category_id' => 'id',
    'category_sort' => '排序',
    'category_name' => '名称',
  ),
  'quan.user' => 
  array (
    'a.user_id' => 'id',
    'b.user_netname' => '昵称',
    'a.user_zan_state' => '点赞',
    'a.user_ping_state' => '评论',
    'a.user_pub_state' => '发言',
    'a.user_zan_num' => '点赞量',
    'a.user_ping_num' => '评论量',
    'a.user_bzan_num' => '被赞量',
    'a.user_bping_num' => '被评论量',
    'a.user_pub_num' => '发言量',
    'b.user_regtime' => '添加时间',
  ),
);