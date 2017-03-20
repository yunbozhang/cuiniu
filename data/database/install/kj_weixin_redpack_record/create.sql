#-----------------创建表--- kj_weixin_redpack_record
DROP TABLE IF EXISTS `{DB_PRE}weixin_redpack_record`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_redpack_record` (
`record_id` int(10) NOT NULL auto_increment,`record_redpack_id` int(10) NOT NULL COMMENT '红包活动',`record_user_id` int(10) NOT NULL,`record_openid` varchar(50) NOT NULL,`record_money` int(10) default '0' COMMENT '红包',`record_datetime` datetime NOT NULL,`record_state` tinyint(1) default '0' COMMENT '状态',`record_number` varchar(50) NOT NULL,`record_err` varchar(50) NOT NULL,`record_wx_oid` varchar(50) NOT NULL COMMENT '微信回执单',PRIMARY KEY (`record_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

