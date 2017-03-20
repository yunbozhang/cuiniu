#-----------------创建表--- kj_meal_table_group
DROP TABLE IF EXISTS `{DB_PRE}meal_table_group`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_table_group` (
`group_id` int(10) NOT NULL auto_increment,`group_name` varchar(50) NOT NULL COMMENT '分组名称',`group_sort` int(10) NOT NULL COMMENT '排序',`group_addtime` int(10) NOT NULL COMMENT '添加时间',`group_updatetime` int(10) NOT NULL COMMENT '修改时间',`group_shop_id` int(10) NOT NULL COMMENT '店铺id',PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

