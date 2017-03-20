#-----------------创建表--- kj_other_language_version
DROP TABLE IF EXISTS `{DB_PRE}other_language_version`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_language_version` (
`version_id` int(11) NOT NULL auto_increment,`version_language_id` int(11) NOT NULL,`version_val` varchar(255) NOT NULL,`version_key` varchar(20) NOT NULL,PRIMARY KEY (`version_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

