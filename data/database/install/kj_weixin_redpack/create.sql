#-----------------创建表--- kj_weixin_redpack
DROP TABLE IF EXISTS `{DB_PRE}weixin_redpack`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_redpack` (
`redpack_id` int(11) NOT NULL auto_increment,`redpack_type` tinyint(1) NOT NULL COMMENT '红包类型',`redpack_title` varchar(10) NOT NULL COMMENT '活动名称',`redpack_shopname` varchar(10) NOT NULL COMMENT '商家名称',`redpack_beta` varchar(15) NOT NULL COMMENT '备注',`redpack_greet` varchar(20) NOT NULL COMMENT '祝福语',`redpack_min_money` decimal(10,2) NOT NULL COMMENT '最小值',`redpack_max_money` decimal(10,2) NOT NULL COMMENT '最大值',`redpack_num` int(10) default '0' COMMENT '发放次数',`redpack_user_num` int(10) default '0' COMMENT '领取次数',`redpack_starttime` datetime NOT NULL COMMENT '开始时间',`redpack_endtime` datetime NOT NULL COMMENT '结束时间',`redpack_event` varchar(100) NOT NULL COMMENT '触发事件',`redpack_state` tinyint(1) default '0' COMMENT '状态',`redpack_addtime` int(10) default '0',`redpack_site_id` int(10) default '0' COMMENT '站点id',`redpack_total` int(10) default '0' COMMENT '总金额',`redpack_user_limit` int(10) default '0' COMMENT '用户领取次数限制',`redpack_interval` int(5) default '0',`redpack_interval_unit` varchar(10) NOT NULL,`redpack_interval_num` int(5) default '0',`redpack_rate` decimal(10,2) default '0.00' COMMENT '抢到机率(%)',PRIMARY KEY (`redpack_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

