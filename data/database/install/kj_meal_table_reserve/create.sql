#-----------------创建表--- kj_meal_table_reserve
DROP TABLE IF EXISTS `{DB_PRE}meal_table_reserve`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_table_reserve` (
`reserve_id` int(10) NOT NULL auto_increment,`reserve_table_id` int(10) NOT NULL,`reserve_user_id` int(10) NOT NULL,`reserve_shop_id` int(10) NOT NULL,`reserve_datetime` datetime NOT NULL COMMENT '预订日期',`reserve_addtime` int(11) NOT NULL COMMENT '添加时间',`reserve_name` varchar(20) NOT NULL COMMENT '姓名',`reserve_tel` varchar(30) NOT NULL COMMENT '电话',`reserve_num` int(10) default '0' COMMENT '人数',`reserve_beta` varchar(100) NOT NULL,`reserve_state` tinyint(1) default '0',`reserve_deposit` decimal(10,2) default '0.00',`reserve_tablename` varchar(30) NOT NULL,`reserve_number` varchar(50) NOT NULL,`reserve_menutime` int(10) default '0' COMMENT '上菜时间',`reserve_paytime` datetime NOT NULL,`reserve_payval` decimal(10,2) default '0.00',`reserve_payid` int(10) default '0',`reserve_pwd` varchar(10) NOT NULL,PRIMARY KEY (`reserve_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

