#-----------------创建表--- kj_meal_table_reserve_list
DROP TABLE IF EXISTS `{DB_PRE}meal_table_reserve_list`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_table_reserve_list` (
`list_id` int(10) NOT NULL auto_increment,`list_reserve_id` int(10) NOT NULL,`list_shop_id` int(10) NOT NULL,`list_menu_id` int(10) NOT NULL,`list_menu_name` varchar(20) NOT NULL COMMENT '餐品',`list_num` int(10) default '0' COMMENT '数量',`list_price` decimal(10,2) default '0.00' COMMENT '单价',`list_total` decimal(10,2) default '0.00' COMMENT '小计',`list_state` tinyint(1) default '0',`list_addtime` datetime NOT NULL,PRIMARY KEY (`list_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

