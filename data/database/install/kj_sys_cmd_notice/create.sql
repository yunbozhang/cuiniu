#-----------------创建表--- kj_sys_cmd_notice
DROP TABLE IF EXISTS `{DB_PRE}sys_cmd_notice`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}sys_cmd_notice` (
`notice_id` int(10) NOT NULL auto_increment,`notice_type` smallint(2) default '0',`notice_about_id` int(10) NOT NULL,`notice_tel` varchar(50) NOT NULL,`notice_email` varchar(50) NOT NULL,`notice_wx` varchar(50) NOT NULL,`notice_sms_cont` varchar(500) NOT NULL,`notice_wx_cont` varchar(500) NOT NULL,`notice_email_cont` varchar(500) NOT NULL,`notice_datetime` datetime NOT NULL,`notice_from_uid` int(10) NOT NULL,`notice_state` tinyint(1) default '0',`notice_sendtime` datetime NOT NULL,`notice_title` varchar(50) NOT NULL,PRIMARY KEY (`notice_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

