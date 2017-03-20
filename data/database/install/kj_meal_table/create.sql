#-----------------创建表--- kj_meal_table
DROP TABLE IF EXISTS `{DB_PRE}meal_table`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_table` (
`table_id` int(10) NOT NULL auto_increment,`table_group_id` int(10) NOT NULL,`table_name` varchar(20) NOT NULL,`table_num` varchar(20) NOT NULL,`table_state` tinyint(1) default '0',`table_addtime` int(10) NOT NULL,`table_shop_id` int(10) NOT NULL,`table_sort` int(10) default '0',`table_updatetime` int(10) NOT NULL,`table_number` varchar(30) NOT NULL,PRIMARY KEY (`table_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

