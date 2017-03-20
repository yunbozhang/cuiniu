#-----------------创建表--- kj_sys_area
DROP TABLE IF EXISTS `{DB_PRE}sys_area`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}sys_area` (
`area_id` int(10) NOT NULL auto_increment,`area_name` varchar(50) NOT NULL COMMENT '地区名称',`area_val` varchar(50) NOT NULL COMMENT '地区值，为空则默认取area_name',`area_pid` int(10) NOT NULL COMMENT '父级id',`area_pids` varchar(50) NOT NULL COMMENT '所有父级id',`area_sort` int(10) NOT NULL COMMENT '排序',`area_pin` varchar(30) NOT NULL COMMENT '拼间',`area_jian` varchar(10) NOT NULL COMMENT '拼间简写',`area_tag` varchar(50) NOT NULL COMMENT '备注',`area_depth` smallint(2) NOT NULL COMMENT '深度',`area_childs` int(10) default '0',`area_pic` varchar(100) NOT NULL COMMENT '图片',`area_position_lng` double(11,6) NOT NULL,`area_position_lat` double(11,6) NOT NULL,PRIMARY KEY (`area_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

