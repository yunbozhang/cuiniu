#-----------------创建表--- kj_meal_order
DROP TABLE IF EXISTS `{DB_PRE}meal_order`;
CREATE TABLE IF NOT EXISTS `{DB_PRE}meal_order` (
`order_id` int(10) NOT NULL auto_increment,`order_area1` int(10) NOT NULL COMMENT '地区一',`order_area2` int(10) NOT NULL COMMENT '地区2',`order_area3` int(10) NOT NULL COMMENT '地区3',`order_louhao1` varchar(50) NOT NULL COMMENT '楼号1',`order_louhao2` varchar(20) NOT NULL COMMENT '楼号2',`order_company` varchar(50) NOT NULL COMMENT '公司',`order_depart` varchar(50) NOT NULL COMMENT '部门',`order_name` varchar(50) NOT NULL COMMENT '姓名',`order_sex` varchar(10) NOT NULL COMMENT '性别',`order_tel` varchar(20) NOT NULL COMMENT '电话',`order_telext` varchar(10) NOT NULL COMMENT '分机',`order_mobile` varchar(20) NOT NULL COMMENT '手机',`order_email` varchar(50) NOT NULL COMMENT '邮箱',`order_arrive` varchar(50) NOT NULL COMMENT '送达时间',`order_addtime` int(10) NOT NULL COMMENT '下单时间',`order_time` datetime NOT NULL COMMENT '下单时间',`order_user_id` int(10) NOT NULL COMMENT '用户id',`order_ids` text NOT NULL COMMENT '菜品ids',`order_total` decimal(10,2) default '0.00',`order_total_pay` decimal(10,2) default '0.00',`order_favorable` decimal(10,2) default '0.00',`order_number` varchar(25) NOT NULL COMMENT '编号',`order_ticket` int(10) NOT NULL COMMENT '发票积分',`order_beta` varchar(255) NOT NULL COMMENT '备注',`order_score_money` int(10) NOT NULL COMMENT '积分抵扣金额',`order_state` smallint(2) default '0' COMMENT '状态',`order_day` date NOT NULL COMMENT '日期',`order_date` datetime NOT NULL,`order_detail` text NOT NULL COMMENT '订单明细',`order_shop_id` int(10) NOT NULL COMMENT '所属店铺',`order_checkout_id` int(10) NOT NULL COMMENT '结算id号',`order_state_time` date NOT NULL COMMENT '接受时间',`order_award_time` date NOT NULL COMMENT '奖励时间',`order_state_beta` varchar(255) NOT NULL COMMENT '状态备注',`order_pay_method` varchar(50) NOT NULL COMMENT '支付方式',`order_pay_time` datetime NOT NULL COMMENT '支付时间',`order_pay_id` int(10) NOT NULL COMMENT '支付记录id',`order_pay_val` decimal(10,2) NOT NULL COMMENT '已支付金额',`order_act` varchar(500) NOT NULL,`order_act_ids` varchar(100) NOT NULL,`order_isprint` tinyint(1) NOT NULL COMMENT '是否已打印',`order_isaward` smallint(2) NOT NULL COMMENT '1:需要奖励2已经奖励，0不奖励',`order_area_id` int(10) NOT NULL COMMENT '地区一',`order_area_allid` varchar(50) NOT NULL COMMENT '地区2',`order_area` varchar(50) NOT NULL COMMENT '地区3',`order_comment` tinyint(1) default '0' COMMENT '是否评论了',`order_addprice` decimal(10,2) default '0.00' COMMENT '送配费',`order_repayment` decimal(10,2) default '0.00' COMMENT '预付款',`order_newuser` tinyint(1) default '0' COMMENT '新用户',`order_reserve_id` int(10) default '0',PRIMARY KEY (`order_id`)
) ENGINE=myisam  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1

