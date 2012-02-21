<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Blocks extends CI_Migration {

	public function up()
	{
		$this->db->query("INSERT INTO blocks (function, name, rsspossible, paginationpossible, enabled) VALUES ('signup_mailinglist', 'Mailing list sign up', '0', '0', '0')");
		$this->db->query("ALTER TABLE blocks ORDER BY function");
	}

	public function down()
	{
		$this->db->query("DELETE FROM blocks WHERE function = 'signup_mailinglist'");
	}
}