#-----------------创建表--- kj_sys_safeask
DROP TABLE IF EXISTS `{DB_PRE}sys_safeask`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}sys_safeask` (
`safeask_question` varchar(50) NOT NULL COMMENT '提问',`safeask_answer` varchar(50) NOT NULL COMMENT '答案',`safeask_addtime` int(10) NOT NULL COMMENT '添加时间',`safeask_user_id` int(10) default '0' COMMENT '用户Id',`safeask_number` smallint(2) default '0' COMMENT '问题序号'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

