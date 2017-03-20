#-----------------创建表--- kj_weixin_site
DROP TABLE IF EXISTS `{DB_PRE}weixin_site`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_site` (
`site_id` int(10) NOT NULL auto_increment,`site_domain` varchar(100) NOT NULL,`site_wx_uname` varchar(50) NOT NULL,`site_wx_token` varchar(20) NOT NULL,`site_wx_appid` varchar(50) NOT NULL,`site_wx_appsecret` varchar(50) NOT NULL,`site_wx_certify` tinyint(1) default '0',`site_wx_msgmode` tinyint(1) default '0',`site_name` varchar(50) NOT NULL,`site_state` smallint(2) default '0',`site_addtime` int(10) default '0',`site_shop_id` int(10) default '0',`site_customview` tinyint(1) default '0',`site_wx_mch_id` varchar(50) NOT NULL,`site_wx_mch_key` varchar(50) NOT NULL,`site_wx_customer_open` tinyint(1) default '0',PRIMARY KEY (`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

