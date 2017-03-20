#-----------------创建表--- kj_meal_menu_sold
DROP TABLE IF EXISTS `{DB_PRE}meal_menu_sold`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_menu_sold` (
`sold_id` int(10) NOT NULL auto_increment,`sold_datetime` datetime NOT NULL,`sold_menu_id` int(10) NOT NULL,`sold_order_id` int(10) NOT NULL,`sold_num` int(10) default '0',`sold_price` decimal(10,2) NOT NULL,`sold_total` decimal(10,2) NOT NULL,`sold_user_id` int(10) NOT NULL,`sold_shop_id` int(10) default '0',PRIMARY KEY (`sold_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

