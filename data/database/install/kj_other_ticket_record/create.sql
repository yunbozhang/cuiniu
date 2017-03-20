#-----------------创建表--- kj_other_ticket_record
DROP TABLE IF EXISTS `{DB_PRE}other_ticket_record`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_ticket_record` (
`record_id` int(10) NOT NULL auto_increment,`record_ticket_id` int(10) NOT NULL,`record_user_id` int(10) NOT NULL,`record_cont` text NOT NULL,`record_ip` varchar(20) NOT NULL,`record_addtime` int(10) NOT NULL,PRIMARY KEY (`record_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

