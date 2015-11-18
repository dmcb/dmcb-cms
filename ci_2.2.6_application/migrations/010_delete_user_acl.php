<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Delete_User_Acl extends CI_Migration {

	public function up()
	{
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`+1 WHERE `functionid` >= '27' ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = '33' WHERE `functionof` = '32' ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`+1 WHERE `functionid` >= '27' ORDER BY `functionid` DESC;");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('27', 'site', 'delete_users', '33', '1', '0', '0', 'Delete users');");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
	}

	public function down()
	{
		$this->db->query("DELETE FROM `acls_functions` WHERE `functionid` = '27'");
		$this->db->query("DELETE FROM `acls_roles_privileges` WHERE `functionid` = '27';");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`-1 WHERE `functionid` >= '28' ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = '32' WHERE `functionof` = '33';");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`-1 WHERE `functionid` >= '28' ORDER BY `functionid` ASC;");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
	}
}