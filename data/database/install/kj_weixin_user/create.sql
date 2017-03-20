#-----------------创建表--- kj_weixin_user
DROP TABLE IF EXISTS `{DB_PRE}weixin_user`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_user` (
`user_openid` varchar(100) NOT NULL,`user_pic` varchar(200) NOT NULL,`user_name` varchar(50) NOT NULL,`user_sex` varchar(4) NOT NULL,`user_area` varchar(50) NOT NULL,`user_addtime` int(10) NOT NULL,`user_state` tinyint(1) default '0',`user_canceltime` int(10) default '0',`user_site_id` int(10) default '0',`user_position_lat` decimal(20,10) NOT NULL,`user_position_lng` decimal(20,10) NOT NULL,PRIMARY KEY (`user_openid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

