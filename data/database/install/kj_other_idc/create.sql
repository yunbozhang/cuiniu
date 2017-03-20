#-----------------创建表--- kj_other_idc
DROP TABLE IF EXISTS `{DB_PRE}other_idc`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_idc` (
`idc_id` int(10) NOT NULL auto_increment,`idc_user_id` int(10) NOT NULL,`idc_number` varchar(50) NOT NULL,`idc_pwd` varchar(50) NOT NULL,`idc_type` varchar(10) NOT NULL,`idc_loc` varchar(50) NOT NULL,`idc_period` varchar(10) NOT NULL,`idc_addtime` datetime NOT NULL,`idc_updatetime` datetime NOT NULL,`idc_lastlogin` datetime NOT NULL,`idc_pay_time` datetime NOT NULL,`idc_pay_val` decimal(10,2) NOT NULL,`idc_pay_id` int(10) NOT NULL,PRIMARY KEY (`idc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

