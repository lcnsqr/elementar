<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Basic_schema extends CI_Migration {

	public function up()
	{
		/*
		 * config table
		 */
		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`value` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('config');
		
		/*
		 * String helper to generate encryption key
		 */
		$this->load->helper('string');

		$data = array(
			array(
				'name' => 'name',
				'value' => '{"pt":"Elementar","en":"Elementar"}'
			),
			array(
				'name' => 'i18n',
				'value' => '[{"name":"Portugu\\u00eas","code":"pt","default":false},{"name":"English","code":"en","default":true}]'
			),
			array(
				'name' => 'encryption_key',
				'value' => random_string('unique')
			)
		);
		$this->db->insert_batch('config', $data);
		
		/*
		 * session table
		 */
		$this->dbforge->add_field("`session_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
		$this->dbforge->add_field("`ip_address` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'");
		$this->dbforge->add_field("`user_agent` varchar(120) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`last_activity` int(10) unsigned NOT NULL DEFAULT '0'");
		$this->dbforge->add_field("`user_data` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('session_id', TRUE);
		$this->dbforge->add_key('last_activity');
		$this->dbforge->create_table('session');
		
		/*
		 * account table
		 */
		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`username` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`email` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`password` char(40) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`register_hash` char(32) COLLATE utf8_unicode_ci DEFAULT NULL");
		$this->dbforge->add_field("`reset_hash` char(32) COLLATE utf8_unicode_ci DEFAULT NULL");
		$this->dbforge->add_field("`created` timestamp NULL DEFAULT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('account');
		$data = array(
			'username' => '',
			'email' => '',
			'password' => '',
			'register_hash' => '',
			'created' => date("Y-m-d H:i:s")
		);
		$query = $this->db->insert('account', $data);
		
		/*
		 * group table
		 */
		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`description` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('group');
		$data = array(
			array(
				'name' => 'Administration',
				'description' => 'Site administrators'
			),
			array(
				'name' => 'Pending',
				'description' => 'Unconfirmed accounts'
			),
			array(
				'name' => 'Users',
				'description' => 'Confirmed accounts'
			)
		);
		$this->db->insert_batch('group', $data);

		/*
		 * account_group table
		 */
		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`account_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`group_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('account_group');
		$data = array(
			'account_id' => 1,
			'group_id' => 1
		);
		$this->db->insert('account_group', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`content_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`value` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('html_meta');

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`filter` text COLLATE utf8_unicode_ci");
		$this->dbforge->add_field("`html` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`css` text COLLATE utf8_unicode_ci");
		$this->dbforge->add_field("`javascript` text COLLATE utf8_unicode_ci");
		$this->dbforge->add_field("`head` text COLLATE utf8_unicode_ci");
		$this->dbforge->add_field("`created` timestamp NULL DEFAULT NULL");
		$this->dbforge->add_field("`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('template');
		$this->db->insert('template', array(
			'html' => '<h1>{name}</h1>
{if body != \'\'}
{body}
{/if}',
			'css' => 'body {
font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
font-size: 12px;
background-color: #fff;
color: #2d2d2d;
}',
			'created' => date("Y-m-d H:i:s")
		));
		$this->db->insert('template', array(
			'html' => '<h1>{name}</h1>
{if body != \'\'}
{body}
{/if}',
			'css' => '',
			'created' => date("Y-m-d H:i:s")
		));

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`i18n` tinyint(1) NOT NULL DEFAULT '1'");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('field_type');
		$data = array(
			array(
				'sname' => 'p',
				'i18n' => 1
			),
			array(
				'sname' => 'file',
				'i18n' => 1
			),
			array(
				'sname' => 'hypertext',
				'i18n' => 1
			),
			array(
				'sname' => 'line',
				'i18n' => 1
			),
			array(
				'sname' => 'target',
				'i18n' => 1
			),
			array(
				'sname' => 'textarea',
				'i18n' => 1
			),
			array(
				'sname' => 'menu',
				'i18n' => 1
			),
			array(
				'sname' => 'file_gallery',
				'i18n' => 1
			),
			array(
				'sname' => 'youtube_gallery',
				'i18n' => 1
			),
			array(
				'sname' => 'index',
				'i18n' => 0
			)
		);
		$this->db->insert_batch('field_type', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`content_type_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`template_id` int(10) unsigned DEFAULT NULL");
		$this->dbforge->add_field("`status` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`created` timestamp NULL DEFAULT NULL");
		$this->dbforge->add_field("`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('content');
		$data = array(
			'name' => '{"pt":"In&iacute;cio","en":"Home"}',
			'sname' => 'home',
			'template_id' => 1,
			'content_type_id' => 1,
			'status' => 'published',
			'created' => date("Y-m-d H:i:s")
		);
		$this->db->insert('content', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`content_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`content_type_field_id` int(10) unsigned DEFAULT NULL");
		$this->dbforge->add_field("`value` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('content_id');
		$this->dbforge->create_table('content_field');
		$data = array(
			'content_id' => 1,
			'content_type_field_id' => 1,
			'value' => '{"pt":"[\"<p>Bem-vindo &agrave; m&aacute;quina.<\\/p>\"]","en":"[\"<p>Welcome to the machine.<\\/p>\"]"}'
		);
		$this->db->insert('content_field', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`content_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`parent_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('content_parent');
		$data = array(
			'content_id' => 1, 
			'parent_id' => 0
		);
		$this->db->insert('content_parent', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`template_id` int(10) unsigned NOT NULL DEFAULT '2'");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('content_type');
		$data = array(
			array(
				'name' =>  'Home', 
				'template_id' => 1
			),
			array(
				'name' => 'Default', 
				'template_id' => 2
			)
		);
		$this->db->insert_batch('content_type', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`content_type_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`field_type_id` int(11) NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('content_type_field');
		$data = array(
			array(
				'content_type_id' =>  1, 
				'name' =>  'Body', 
				'sname' =>  'body', 
				'field_type_id' => 3
			),
			array(
				'content_type_id' => 2, 
				'name' => 'Body', 
				'sname' => 'body', 
				'field_type_id' => 3
			)
		);
		$this->db->insert_batch('content_type_field', $data);

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`element_type_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`spread` tinyint(1) NOT NULL DEFAULT '1'");
		$this->dbforge->add_field("`status` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`created` timestamp NULL DEFAULT NULL");
		$this->dbforge->add_field("`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('element');

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`element_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`element_type_field_id` int(10) unsigned DEFAULT NULL");
		$this->dbforge->add_field("`value` text COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('element_field');

		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`element_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`parent_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('element_parent');
		
		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('element_type');
		
		$this->dbforge->add_field("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`element_type_id` int(10) unsigned NOT NULL");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`field_type_id` int(11) NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('element_type_field');
		
		$this->dbforge->add_field("`id` int(11) NOT NULL AUTO_INCREMENT");
		$this->dbforge->add_field("`done` tinyint(1) NOT NULL DEFAULT '0'");
		$this->dbforge->add_field("`error` tinyint(1) NOT NULL DEFAULT '0'");
		$this->dbforge->add_field("`uri` varchar(2048) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_field("`name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL");
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('upload_session');
	}

	public function down()
	{
		$this->dbforge->drop_table('config');
		$this->dbforge->drop_table('session');
		$this->dbforge->drop_table('account');
		$this->dbforge->drop_table('group');
		$this->dbforge->drop_table('account_group');
		$this->dbforge->drop_table('html_meta');
		$this->dbforge->drop_table('template');
		$this->dbforge->drop_table('field_type');
		$this->dbforge->drop_table('content');
		$this->dbforge->drop_table('content_field');
		$this->dbforge->drop_table('content_parent');
		$this->dbforge->drop_table('content_type');
		$this->dbforge->drop_table('content_type_field');
		$this->dbforge->drop_table('element');
		$this->dbforge->drop_table('element_field');
		$this->dbforge->drop_table('element_parent');
		$this->dbforge->drop_table('element_type');
		$this->dbforge->drop_table('element_type_field');
		$this->dbforge->drop_table('upload_session');
	}
}
