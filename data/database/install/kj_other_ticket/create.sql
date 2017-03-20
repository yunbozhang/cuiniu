#-----------------创建表--- kj_other_ticket
DROP TABLE IF EXISTS `{DB_PRE}other_ticket`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_ticket` (
`ticket_id` int(10) NOT NULL auto_increment,`ticket_title` varchar(50) NOT NULL,`ticket_cont` text NOT NULL,`ticket_needlogin` tinyint(1) NOT NULL,`ticket_starttime` int(10) NOT NULL,`ticket_endtime` int(10) NOT NULL,`ticket_addtime` int(10) NOT NULL,PRIMARY KEY (`ticket_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

