#-----------------创建表--- kj_article
DROP TABLE IF EXISTS `{DB_PRE}article`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}article` (
`article_id` int(10) NOT NULL auto_increment,`article_title` varchar(50) NOT NULL COMMENT '文章标题',`article_intro` text NOT NULL COMMENT '简介',`article_content` longtext NOT NULL COMMENT '内容',`article_addtime` int(10) NOT NULL COMMENT '添加时间',`article_updatetime` int(10) NOT NULL COMMENT '修改时间',`article_pic_big` varchar(100) NOT NULL COMMENT '大图',`article_pic` varchar(100) NOT NULL COMMENT '略图',`article_islink` tinyint(1) NOT NULL COMMENT '是否为超链接',`article_linkurl` varchar(100) NOT NULL COMMENT '超链接地址',`article_attribute` varchar(100) NOT NULL COMMENT '相关属性',`article_htmlname` varchar(50) NOT NULL COMMENT '静态文件名',`article_folder_id` int(10) NOT NULL COMMENT '所属目录',`article_hits` int(10) NOT NULL COMMENT '浏览量',`article_tpl` varchar(100) NOT NULL COMMENT '指定模板地址',`article_topic_id` int(10) NOT NULL COMMENT '专题id',`article_channel_id` int(10) NOT NULL COMMENT '频道 id',`article_source` varchar(50) NOT NULL COMMENT '来源',`article_author` varchar(50) NOT NULL COMMENT '作者',`article_pubtime` varchar(50) NOT NULL,`article_state` varchar(10) NOT NULL COMMENT '状态',`article_about_id` int(10) NOT NULL,`article_uid` int(10) NOT NULL COMMENT '创建用户',`article_updateuid` int(10) NOT NULL COMMENT '最后编辑用户',`article_css` varchar(100) NOT NULL COMMENT '标题相关样式',`article_tag` varchar(50) NOT NULL COMMENT '相关标签',`article_isread` smallint(2) default '0' COMMENT '是否被阅读',`article_isdel` tinyint(1) default '0' COMMENT '是否删除到回收站',`article_isdel_from` tinyint(1) default '0' COMMENT '从哪儿删除的',`article_top` int(10) default '0' COMMENT '置顶',`article_sort` int(10) default '0',`article_key` varchar(50) NOT NULL COMMENT '唯一关键词',PRIMARY KEY (`article_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=124

