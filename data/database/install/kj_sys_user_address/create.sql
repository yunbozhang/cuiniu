#-----------------创建表--- kj_sys_user_address
DROP TABLE IF EXISTS `{DB_PRE}sys_user_address`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}sys_user_address` (
`address_id` int(10) NOT NULL auto_increment,`address_name` varchar(50) NOT NULL,`address_sex` varchar(10) NOT NULL,`address_tel` varchar(20) NOT NULL,`address_email` varchar(50) NOT NULL,`address_addtime` int(10) NOT NULL,`address_user_id` int(10) NOT NULL,`address_area_id` int(10) NOT NULL,`address_area_allid` varchar(50) NOT NULL,`address_area` varchar(50) NOT NULL,`address_address` varchar(50) NOT NULL,`address_order_time` int(10) default '0',`address_isdefault` tinyint(1) default '0',PRIMARY KEY (`address_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

