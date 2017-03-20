#-----------------创建表--- kj_weixin_keywords
DROP TABLE IF EXISTS `{DB_PRE}weixin_keywords`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_keywords` (
`keywords_id` int(10) NOT NULL auto_increment,`keywords_val` varchar(30) NOT NULL COMMENT '关键词',`keywords_mode` tinyint(1) default '0' COMMENT '匹配模式',`keywords_message_id` int(10) default '0' COMMENT '消息ids',`keywords_site_id` int(10) default '0',PRIMARY KEY (`keywords_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

