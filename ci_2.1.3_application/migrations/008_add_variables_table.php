<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Add_Variables_Table extends CI_Migration {

	public function up()
	{
		$this->db->query("CREATE TABLE `variables` (`variable_key` VARCHAR( 50 ) NOT NULL, `variable_value` VARCHAR( 1000 ) NOT NULL, PRIMARY KEY ( `variable_key` )) ENGINE = MYISAM;");
	}

	public function down()
	{
		$this->db->query("DROP TABLE `variables`");
	}
}