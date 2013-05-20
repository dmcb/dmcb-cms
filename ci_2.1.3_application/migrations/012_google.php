<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Google extends CI_Migration {

	public function up()
	{
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`+1 WHERE `functionid` >= '21' ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`+1 WHERE `functionof` >= '21' ORDER BY `functionof` DESC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`+1 WHERE `functionid` >= '21' ORDER BY `functionid` DESC;");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('21', 'profile', 'google', '18', '0', '0', '0', 'Set Google account');");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
		$this->db->query("ALTER TABLE `users` ADD `google` VARCHAR( 25 ) NULL;");
	}

	public function down()
	{
		$this->db->query("DELETE FROM `acls_functions` WHERE `functionid` = '21'");
		$this->db->query("DELETE FROM `acls_roles_privileges` WHERE `functionid` = '21';");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`-1 WHERE `functionid` >= '22' ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`-1 WHERE `functionof` >= '22' ORDER BY `functionof` ASC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`-1 WHERE `functionid` >= '22' ORDER BY `functionid` ASC;");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
		$this->db->query("ALTER TABLE `users` DROP `google`;");
	}
}