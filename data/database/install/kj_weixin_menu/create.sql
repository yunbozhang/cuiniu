#-----------------创建表--- kj_weixin_menu
DROP TABLE IF EXISTS `{DB_PRE}weixin_menu`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_menu` (
`menu_id` int(10) NOT NULL auto_increment,`menu_name` varchar(20) NOT NULL,`menu_pid` int(10) default '0' COMMENT '父级id',`menu_linkurl` varchar(100) NOT NULL COMMENT '跳转url',`menu_message_id` int(10) NOT NULL COMMENT '消息id',`menu_addtime` int(10) NOT NULL COMMENT '添加时间',`menu_updatetime` int(10) NOT NULL COMMENT '修改时间',`menu_sort` int(10) default '0' COMMENT '排序',`menu_pids` varchar(100) NOT NULL,`menu_site_id` int(10) default '0',PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

