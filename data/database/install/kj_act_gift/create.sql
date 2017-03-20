#-----------------创建表--- kj_act_gift
DROP TABLE IF EXISTS `{DB_PRE}act_gift`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_gift` (
`gift_id` int(10) NOT NULL auto_increment,`gift_name` varchar(20) NOT NULL,`gift_pic` varchar(100) NOT NULL,`gift_total` int(10) default '0',`gift_total_day` int(10) default '0',`gift_opentime` varchar(200) NOT NULL COMMENT '开放时间',`gift_addtime` int(10) default '0',`gift_hits` int(10) default '0',`gift_desc` text NOT NULL COMMENT '描述',`gift_score` int(10) default '0' COMMENT '兑换所需积分',`gift_num` int(10) default '0' COMMENT '兑换总量',`gift_num_today` int(10) default '0' COMMENT '今天兑换数量',`gift_time` datetime NOT NULL COMMENT '最近兑换时间',`gift_starttime` datetime NOT NULL COMMENT '开始日期',`gift_endtime` datetime NOT NULL COMMENT '结束日期',`gift_state` smallint(2) default '0' COMMENT '状态',`gift_group` varchar(20) NOT NULL COMMENT '分组',`gift_user_num` int(10) default '0' COMMENT '每个用户限兑数量',PRIMARY KEY (`gift_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

