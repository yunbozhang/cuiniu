#-----------------创建表--- kj_act_voucher
DROP TABLE IF EXISTS `{DB_PRE}act_voucher`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_voucher` (
`voucher_id` int(10) NOT NULL auto_increment,`voucher_user_id` int(10) default '0',`voucher_val` decimal(10,2) NOT NULL,`voucher_pwd` varchar(16) NOT NULL,`voucher_addtime` int(10) NOT NULL,`voucher_type` tinyint(1) default '0',`voucher_state` tinyint(1) default '0',`voucher_usetime` int(10) default '0',PRIMARY KEY (`voucher_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

