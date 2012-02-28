<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Acls_roles_privileges extends CI_Migration {

	public function up()
	{
		$this->db->query("RENAME TABLE `acls_roles_priveleges` TO `acls_roles_privileges`;");
	}

	public function down()
	{
		$this->db->query("RENAME TABLE `acls_roles_privileges` TO `acls_roles_priveleges`;");
	}
}