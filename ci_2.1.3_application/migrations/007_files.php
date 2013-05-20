<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Files extends CI_Migration {

	public function up()
	{
		$this->db->query("ALTER TABLE `files` ADD `userid` INT( 10 ) UNSIGNED NOT NULL AFTER `fileid`;");
		$this->db->query("UPDATE `files` SET `userid` = '1';");
	}

	public function down()
	{
		$this->db->query("ALTER TABLE `files` DROP `userid`;");
	}
}