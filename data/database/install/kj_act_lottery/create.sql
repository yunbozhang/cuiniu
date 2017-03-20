#-----------------创建表--- kj_act_lottery
DROP TABLE IF EXISTS `{DB_PRE}act_lottery`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_lottery` (
`lottery_id` int(10) NOT NULL auto_increment,`lottery_type` tinyint(1) default '0' COMMENT '奖抽类型',`lottery_title` varchar(50) NOT NULL,`lottery_starttime` datetime NOT NULL,`lottery_endtime` datetime NOT NULL,`lottery_desc` text NOT NULL,`lottery_limit_user` int(5) default '0',`lottery_isdel` tinyint(1) default '0',`lottery_pic` varchar(100) NOT NULL,`lottery_rate` int(10) default '0',`lottery_score` int(10) default '0',`lottery_experience` int(10) NOT NULL,`lottery_interval` int(10) default '0',`lottery_interval_unit` varchar(10) NOT NULL,`lottery_interval_num` int(10) default '0',PRIMARY KEY (`lottery_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

