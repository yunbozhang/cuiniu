#-----------------创建表--- kj_weixin_message
DROP TABLE IF EXISTS `{DB_PRE}weixin_message`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_message` (
`message_id` int(10) NOT NULL auto_increment,`message_type` varchar(10) NOT NULL COMMENT '类型',`message_text` text NOT NULL COMMENT '文本消息',`message_media_id` varchar(100) NOT NULL COMMENT '体媒Id',`message_group` tinyint(1) NOT NULL COMMENT '分组',`message_addtime` int(10) NOT NULL,`message_updatetime` int(10) NOT NULL,`message_site_id` int(10) default '0',PRIMARY KEY (`message_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

