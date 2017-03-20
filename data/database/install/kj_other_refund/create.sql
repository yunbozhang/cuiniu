#-----------------创建表--- kj_other_refund
DROP TABLE IF EXISTS `{DB_PRE}other_refund`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_refund` (
`refund_id` int(10) NOT NULL auto_increment,`refund_pay_id` int(10) NOT NULL,`refund_about_id` int(10) NOT NULL,`refund_val` decimal(10,2) NOT NULL,`refund_addtime` int(10) NOT NULL,PRIMARY KEY (`refund_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1

