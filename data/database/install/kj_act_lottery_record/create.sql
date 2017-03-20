#-----------------创建表--- kj_act_lottery_record
DROP TABLE IF EXISTS `{DB_PRE}act_lottery_record`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_lottery_record` (
`record_id` int(10) NOT NULL auto_increment,`record_lottery_id` int(10) NOT NULL,`record_user_id` int(10) NOT NULL,`record_wx_id` varchar(50) NOT NULL,`record_datetime` datetime NOT NULL,`record_award_id` int(10) NOT NULL,`record_score` int(10) default '0',PRIMARY KEY (`record_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

