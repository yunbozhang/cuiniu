<?php
/* �����ֵ�
 * ���ݿ� -> �� -> �ֶ�
 * ֵ������ 10 ��ʾ�������û����� , 1��ʾ��ʾ��0��ʾ����ʾ
 */
return array(
	"sys.area" => array(
	//�û���,sys_user
		"area_id" => array("val" => 0,"w" => 0), //����id
		"area_sort" => array("val" => 1,"w" => 50), //����
		"area_name" => array("val" => 1,"w" => 120), //����
		"area_val" => array("val" => 1,"w" => 0), //ֵ
		"area_tag" => array("val" => 1,"w" => 50), //��������
		"area_pin" => array("val" => 1,"w" => 50), //��������
		"area_jian" => array("val" => 1,"w" => 50), //��������
	),
	"sys.config" => array(
	//�û���,sys_user
		"config_id" => array("val" => 2,"w" => 0), //id
		"config_intro" => array("val" => 1,"w" => 150), //ע��ʱ��
		"config_val" => array("val" => 1,"w" => 380), //ֵ
		"config_name" => array("val" => 1,"w" => 100), //��������
	),
	"sys.user" => array(
	//�û���,sys_user
		"user_id" => array("val" => 0,"w" => 0), //�û�id
		"user_name" => array("val" => 1,"w" => 50), //�û���
		"user_email" => array("val" => 1,"w" => 120), //��������
		"user_regtime" => array("val" => 1,"w" => 100), //ע��ʱ��
		"user_regip" => array("val" => 0,"w" => 50), //ע��IP
		"user_loginip" => array("val" => 0,"w" => 50), //��¼IP
		"user_netname" => array("val" => 0,"w" => 50), //�ǳ�
		"user_logintime" => array("val" => 1,"w" => 100), //��¼ʱ��
		"user_loginnum" => array("val" => 1,"w" => 0), //��¼����
		"user_type" => array("val" => 0,"w" => 50), //����
		"user_state" => array("val" => 1,"w" => 0), //״̬
		"user_score" => array("val" => 0,"w" => 0), //��ǰ����
		"user_experience" => array("val" => 0,"w" => 0), //��ǰ����
		"user_email_verify" => array("val" => 0,"w" => 0), //�Ƿ��ʼ���֤
		"user_birthday" => array("val" => 0,"w" => 80), //��������
		"user_sex" => array("val" => 0,"w" => 0), //�Ա�
		"user_location" => array("val" => 0,"w" => 0), //��ǰ���ڵ�
		"user_house_location" => array("val" => 0,"w" => 0), //����
		"user_tel" => array("val" => 0,"w" => 0), //�绰
		"user_mobile" => array("val" => 0,"w" => 0), //�ֻ���
		"user_address" => array("val" => 0,"w" => 0), //��ϵ��ַ
		"user_realname" => array("val" => 0,"w" => 0), //��ʵ����
		"user_invite_uid" => array("val" => 0,"w" => 0), //������
		"user_group_id" => array("val" => 10,"w" => 0), //�û���id
		"group_name" => array("val" => 1,"w" => 0), //�û���"
	),
	"sys.user.group" => array(
	//�û��� , sys_user_group;
		"group_id" => array("val" => 1,"w" => 0), //�û���id
		"group_name" => array("val" => 1,"w" => 0), //����
		"group_addtime" => array("val" => 1,"w" => 0), //���ʱ��
		"group_updatetime" => array("val" => 1,"w" => 0), //����ʱ��
		"group_sort" => array("val" => 1,"w" => 0), //����
		"group_pid" => array("val" => 1,"w" => 0), //�ϼ�id
		"group_limit_admin" => array("val" => 1,"w" => 0), //Ȩ��
	),
	"sys.user.log" => array(
	//�û���־ , sys_user_log;
		"log_id" => array("val" => 2,"w" => 0), //��־id
		"log_user_id" => array("val" => 2,"w" => 0), //�û�id
		"user_name" => array("val" => 1,"w" => 50), //�û�
		"log_app_module" => array("val" => 1,"w" => 80), //ģ��
		"log_app" => array("val" => 1,"w" => 80), //ҳ��
		"log_app_act" => array("val" => 1,"w" => 80), //��Ϊ
		"log_cont" => array("val" => 1,"w" => 200), //��ϸ
		"log_ip" => array("val" => 1,"w" => 50), //IP
		"log_addtime" => array("val" => 1,"w" => 100), //ʱ��
		"log_module" => array("val" => 2,"w" => 0), //ģ��
		"log_key" => array("val" => 2,"w" => 0), //��
	),
	"sys.user.score" => array(
	//�û����� , sys_user_score;
		"score_id" => array("val" => 1,"w" => 0), //��־id
		"score_user_id" => array("val" => 1,"w" => 0), //�û�id
		"score_val" => array("val" => 1,"w" => 0), //����
		"score_key" => array("val" => 1,"w" => 0), //��Դ
		"score_addtime" => array("val" => 1,"w" => 0), //ʱ��
	),
	"sys.user.action" => array(
	//�û����� , sys_user_score;
		"action_id" => array("val" => 1,"w" => 0), //id
		"action_user_id" => array("val" => 11,"w" => 100), //�û�id
		"user_name" => array("val" => 1,"w" => 100), //�û�id
		"action_score" => array("val" => 1,"w" => 100), //����
		"action_experience" => array("val" => 1,"w" => 100), //����ֵ
		"action_key" => array("val" => 1,"w" => 200), //��Ϊ
		"action_addtime" => array("val" => 1,"w" => 150), //���ʱ��
		"action_beta" => array("val" => 1,"w" => 200), //��Ϊ
	)
);
?>