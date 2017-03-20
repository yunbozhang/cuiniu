#-----------------创建表--- kj_sys_user_tips
DROP TABLE IF EXISTS `{DB_PRE}sys_user_tips`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}sys_user_tips` (
`tips_user_id` int(10) NOT NULL,`tips_key` varchar(20) NOT NULL,`tips_num` int(11) default '0',`tips_msg` varchar(200) NOT NULL,`tips_updatetime` int(10) NOT NULL,PRIMARY KEY (`tips_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

