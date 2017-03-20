#-----------------创建表--- kj_act_lottery_award
DROP TABLE IF EXISTS `{DB_PRE}act_lottery_award`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}act_lottery_award` (
`award_id` int(10) NOT NULL auto_increment,`award_name` varchar(50) NOT NULL COMMENT '奖品',`award_pic` varchar(100) NOT NULL COMMENT '奖品图片',`award_num_total` int(10) default '0' COMMENT '总数',`award_num_exchange` int(10) default '0' COMMENT '已抽出数',`award_limit_user` int(5) default '0' COMMENT '每个用户限中次数',`award_rate` int(10) default '0' COMMENT '中奖机率',`award_lottery_id` int(10) default '0' COMMENT '项目id',PRIMARY KEY (`award_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

