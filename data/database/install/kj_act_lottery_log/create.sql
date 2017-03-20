#-----------------创建表--- kj_act_lottery_log
DROP TABLE IF EXISTS `{DB_PRE}act_lottery_log`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_lottery_log` (
`log_id` int(10) NOT NULL auto_increment,`log_award_id` int(10) default '0',`log_lottery_id` int(10) NOT NULL,`log_datetime` datetime NOT NULL,`log_user_id` int(10) default '0',`log_wx_id` varchar(50) NOT NULL,`log_tel` varchar(20) NOT NULL,`log_name` varchar(20) NOT NULL,`log_state` smallint(2) default '0',PRIMARY KEY (`log_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

