#-----------------创建表--- kj_weixin_talk
DROP TABLE IF EXISTS `{DB_PRE}weixin_talk`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_talk` (
`talk_id` int(11) NOT NULL auto_increment,`talk_openid` varchar(255) NOT NULL,`talk_cont` text NOT NULL,`talk_type` tinyint(1) NOT NULL,`talk_addtime` int(10) NOT NULL,`talk_site_id` int(10) default '0',PRIMARY KEY (`talk_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

