<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Menu_Block_Variable extends CI_Migration {

	public function up()
	{
		$this->db->query("INSERT INTO `blocks_variables` (`function`, `variablename`, `variabledescription`, `pattern`, `rules`, `list`) VALUES ('menu', 'sort', 'Set the order of the menu.', 'page-order|alphabetical', NULL, 0);");
		$this->db->query("ALTER TABLE `blocks_variables` ORDER BY `variablename`;");
		$this->db->query("ALTER TABLE `blocks_variables` ORDER BY `function`;");
	}

	public function down()
	{
		$this->db->query("DELETE FROM `blocks_variables` WHERE `function` = 'menu' AND `variablename` = 'sort'");
	}
}