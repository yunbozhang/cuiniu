#-----------------创建表--- kj_userapi_login
DROP TABLE IF EXISTS `{DB_PRE}userapi_login`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}userapi_login` (
`login_name` varchar(50) NOT NULL,`login_plat` varchar(10) NOT NULL,`login_addtime` int(10) default '0',`login_user_id` int(11) default '0',PRIMARY KEY (`login_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

