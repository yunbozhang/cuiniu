#-----------------创建表--- kj_other_language
DROP TABLE IF EXISTS `{DB_PRE}other_language`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_language` (
`language_id` int(10) NOT NULL auto_increment,`language_val` varchar(255) NOT NULL,`language_beta` varchar(50) NOT NULL,PRIMARY KEY (`language_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

