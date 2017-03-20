#-----------------创建表--- kj_act_gift_record
DROP TABLE IF EXISTS `{DB_PRE}act_gift_record`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_gift_record` (
`record_id` int(10) NOT NULL auto_increment,`record_user_id` int(10) NOT NULL COMMENT '用户id',`record_gift_id` int(10) NOT NULL COMMENT '礼品id',`record_score` int(10) NOT NULL COMMENT '积分',`record_datetime` datetime NOT NULL COMMENT '兑换时间',`record_num` int(10) NOT NULL COMMENT '兑换数量',`record_ip` varchar(20) NOT NULL,`record_linkname` varchar(20) NOT NULL,`record_tel` varchar(50) NOT NULL,`record_address` varchar(100) NOT NULL,`record_send_time` int(10) default '0' COMMENT '发货时间',`record_receive_time` int(11) default '0' COMMENT '领取时间',PRIMARY KEY (`record_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

