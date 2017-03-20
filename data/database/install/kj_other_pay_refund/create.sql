#-----------------创建表--- kj_other_pay_refund
DROP TABLE IF EXISTS `{DB_PRE}other_pay_refund`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_pay_refund` (
`refund_id` int(10) NOT NULL auto_increment,`refund_pay_id` int(10) NOT NULL COMMENT '支付记录id',`refund_user_id` int(10) NOT NULL COMMENT '用户id',`refund_order_id` varchar(30) NOT NULL COMMENT '订单id',`refund_reid` varchar(30) NOT NULL COMMENT '退款id',`refund_total` decimal(10,2) default '0.00' COMMENT '退款金额',`refund_money` double(10,0) default '0' COMMENT '退款金额',`refund_fee_type` varchar(10) NOT NULL COMMENT '货币种类',`refund_result_code` varchar(16) NOT NULL COMMENT '退款结果',`refund_err_code` varchar(32) NOT NULL COMMENT '退款错误码',`refund_state` tinyint(1) default '0' COMMENT '退款状态',`refund_apply_time` datetime NOT NULL COMMENT '退款申请时间',`refund_addtime` datetime NOT NULL COMMENT '添加时间',`refund_return_time` datetime NOT NULL COMMENT '退款处理成功时间',`refund_beta` varchar(255) NOT NULL COMMENT '备注',`refund_err` varchar(255) NOT NULL,`refund_uid` int(10) default '0' COMMENT '用户id',`refund_admin_uid` int(10) NOT NULL COMMENT '管理员uid',`refund_return_id` varchar(50) NOT NULL,`refund_retotal` decimal(10,2) default '0.00' COMMENT '实退金额',`refund_tips_tel` varchar(20) NOT NULL COMMENT '用户手机',PRIMARY KEY (`refund_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

