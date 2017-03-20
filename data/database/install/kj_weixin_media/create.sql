#-----------------创建表--- kj_weixin_media
DROP TABLE IF EXISTS `{DB_PRE}weixin_media`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}weixin_media` (
`media_id` varchar(100) NOT NULL COMMENT '体媒ID',`media_type` varchar(10) NOT NULL COMMENT '类型',`media_time` int(10) default '0' COMMENT '时间',`media_file` varchar(100) NOT NULL COMMENT '媒体文件',`media_uid` int(10) default '0' COMMENT '上传人',`media_size` int(10) NOT NULL,`media_title` varchar(100) NOT NULL COMMENT '标题',`media_desc` varchar(1000) NOT NULL COMMENT '描述',PRIMARY KEY (`media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

