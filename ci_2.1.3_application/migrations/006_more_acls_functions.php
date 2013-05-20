<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_More_Acls_functions extends CI_Migration {

	public function up()
	{
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`+6 WHERE `functionid` = '30';");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`+6 WHERE `functionof` = '30';");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`+6 WHERE `functionid` = '30';");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`+4 WHERE `functionid` >= '24' AND `functionid` <= '29' ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`+4 WHERE `functionof` >= '24' AND `functionid` <= '29' ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`+4 WHERE `functionid` >= '24' AND `functionid` <= '29' ORDER BY `functionid` DESC;");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('24', 'site', 'add_users', '32', '1', '0', '0', 'Add new users');");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('25', 'site', 'change_role', '32', '1', '0', '0', 'Change user role');");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('26', 'site', 'change_status', '32', '1', '0', '0', 'Change user status');");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('27', 'site', 'mail_users', '32', '1', '0', '0', 'Mail users');");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('34', 'site', 'set_password', '32', '1', '0', '0', 'Set user password');");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('35', 'site', 'set_subscription', '32', '1', '0', '0', 'Set user subscription');");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
	}

	public function down()
	{
		$this->db->query("DELETE FROM `acls_functions` WHERE `functionid` = '24' OR `functionid` = '25' OR `functionid` = '26' OR `functionid` = '27' OR `functionid` = '34' OR `functionid` = '35';");
		$this->db->query("DELETE FROM `acls_roles_privileges` WHERE `functionid` = 20;");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`-4 WHERE `functionid` >= '28' AND `functionid` <= '33' ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`-4 WHERE `functionof` >= '28' AND `functionid` <= '33' ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`-4 WHERE `functionid` >= '28' AND `functionid` <= '33' ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`-6 WHERE `functionid` = '36';");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`-6 WHERE `functionof` = '36';");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`-6 WHERE `functionid` = '36';");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
	}
}