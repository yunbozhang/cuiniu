#-----------------创建表--- kj_other_ads
DROP TABLE IF EXISTS `{DB_PRE}other_ads`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}other_ads` (
`ads_id` int(10) NOT NULL auto_increment,`ads_title` varchar(50) NOT NULL,`ads_type` varchar(10) NOT NULL,`ads_html` text NOT NULL,`ads_cont` text NOT NULL,`ads_starttime` int(10) NOT NULL,`ads_endtime` int(10) NOT NULL,`ads_state` smallint(2) NOT NULL COMMENT '״̬',`ads_user_id` int(10) NOT NULL,`ads_addtime` int(10) NOT NULL,`ads_updatetime` int(10) NOT NULL,`ads_shop_id` int(10) NOT NULL COMMENT '店铺id',`ads_position` varchar(20) NOT NULL,`ads_sort` int(5) default '0',`ads_area_id` int(10) default '0' COMMENT '地区id',`ads_area_allid` varchar(50) NOT NULL COMMENT '地区id全路径',`ads_area` varchar(50) NOT NULL COMMENT '所在区域名',PRIMARY KEY (`ads_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

