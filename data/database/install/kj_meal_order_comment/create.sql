#-----------------创建表--- kj_meal_order_comment
DROP TABLE IF EXISTS `{DB_PRE}meal_order_comment`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_order_comment` (
`comment_id` int(10) NOT NULL auto_increment,`comment_shop_id` int(10) default '0' COMMENT '店铺id',`comment_val` decimal(5,2) default '0.00' COMMENT '论评值',`comment_beta` varchar(255) NOT NULL COMMENT '论评内容',`comment_user_id` int(10) NOT NULL COMMENT '论评用户id',`comment_addtime` int(10) NOT NULL COMMENT '论评时间',`comment_order_id` int(10) default '0' COMMENT '订单id',`comment_pic` varchar(500) NOT NULL,`comment_list` varchar(100) NOT NULL,`comment_recont` varchar(255) NOT NULL,`comment_re_uid` int(10) NOT NULL,`comment_re_time` int(10) NOT NULL,PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

