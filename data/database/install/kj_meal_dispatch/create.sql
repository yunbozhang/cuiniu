#-----------------创建表--- kj_meal_dispatch
DROP TABLE IF EXISTS `{DB_PRE}meal_dispatch`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_dispatch` (
`dispatch_id` int(10) NOT NULL auto_increment,`dispatch_area_id` int(50) NOT NULL COMMENT '地区id',`dispatch_price` decimal(10,2) NOT NULL COMMENT '起送价',`dispatch_number` int(10) NOT NULL,`dispatch_time` int(10) NOT NULL COMMENT '送达时间(分)',`dispatch_shop_id` int(10) NOT NULL COMMENT '店铺id',`dispatch_addtime` int(10) NOT NULL,`dispatch_pid` int(10) NOT NULL,`dispatch_addprice` decimal(10,2) default '0.00' COMMENT '配送费',`dispatch_smstel` varchar(50) NOT NULL COMMENT '短信电话',PRIMARY KEY (`dispatch_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

