<?php
/* �����ֵ�
 * ���ݿ� -> �� -> �ֶ�
 * ֵ������ 10 ��ʾ�������û����� , 1��ʾ��ʾ��0��ʾ����ʾ
 */
return array(
	"quan.category" => array(
	//�û���,sys_user
		"category_id" => array("val" => 0,"w" => 0), //id
		"category_sort" => array("val" => 1,"w" => 60), //����
		"category_name" => array("val" => 1,"w" => 150), //����
	),
	"quan.user" => array(
	//�û���,sys_user
		"a.user_id" => array("val" => 1,"w" => 0), //id
		"b.user_netname" => array("val" => 1,"w" => 160), //����
		"a.user_zan_state" => array("val" => 1,"w" => 80), //����
		"a.user_ping_state" => array("val" => 1,"w" => 80), //����
		"a.user_pub_state" => array("val" => 1,"w" => 80), //����
		"a.user_zan_num" => array("val" => 1,"w" => 80), //����
		"a.user_ping_num" => array("val" => 1,"w" => 80), //����
		"a.user_bzan_num" => array("val" => 1,"w" => 80), //����
		"a.user_bping_num" => array("val" => 1,"w" => 80), //����
		"a.user_pub_num" => array("val" => 1,"w" => 80), //����
		"b.user_regtime" => array("val" => 1,"w" => 120), //����
	),
);