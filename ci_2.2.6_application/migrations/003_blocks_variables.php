<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Blocks_variables extends CI_Migration {

	public function up()
	{
		$this->db->query("ALTER TABLE `blocks_variables` ADD `list` INT( 1 ) UNSIGNED NOT NULL;");
		$this->db->query("UPDATE `blocks_variables` SET `list` = '1' WHERE `function` = 'comments' AND `variablename` = 'page';");
		$this->db->query("UPDATE `blocks_variables` SET `list` = '1' WHERE `function` = 'comments' AND `variablename` = 'post';");
		$this->db->query("UPDATE `blocks_variables` SET `list` = '1' WHERE `function` = 'events' AND `variablename` = 'page';");
		$this->db->query("UPDATE `blocks_variables` SET `list` = '1' WHERE `function` = 'categories' AND `variablename` = 'page';");
		$this->db->query("UPDATE `blocks_variables` SET `list` = '1' WHERE `function` = 'posts' AND `variablename` = 'page';");
		$this->db->query("UPDATE `blocks_variables` SET `list` = '1' WHERE `function` = 'posts' AND `variablename` = 'user';");
	}

	public function down()
	{
		$this->db->query("ALTER TABLE `blocks_variables` DROP `list`;");
	}
}