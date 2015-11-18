<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Acls_functions extends CI_Migration {

	public function up()
	{
		$this->db->query("UPDATE `acls_functions` SET `ownerpossible` = '0' WHERE `functionid` = '18' OR `functionid` = '19' OR `functionid` = '21';");
		$this->db->query("DELETE FROM `acls_roles_privileges` WHERE `roleid` = '6' AND (`functionid` = '18' OR `functionid` = '19' OR `functionid` = '21');");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = '22' WHERE `functionid` = '20';");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`+1 WHERE `acls_functions`.`functionid` >= 18 ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`+1 WHERE `acls_functions`.`functionof` >= 18;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`+1 WHERE `acls_roles_privileges`.`functionid` >= 18 ORDER BY `functionid` DESC;");

		$result = $this->db->query("SELECT `enabled` FROM `acls_functions` WHERE `functionid` = '20';");
		$result_array = $result->row_array();
		if (!isset($result_array)) {
			$result_array['enabled'] = 0;
		}

		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('18', 'profile', 'add', NULL, ".$this->db->escape($result_array['enabled']).", '0', '0', 'Have profile');");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY  `functionid`;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = '18' WHERE `acls_roles_privileges`.`functionid` = '20';");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = '18' WHERE `functionid` = '19' OR `functionid` = '22';");
		$this->db->query("UPDATE `acls_functions` SET `name` = 'Edit other profiles', `enabled` = '0' WHERE `functionid` = '20';");
	}

	public function down()
	{
		$this->db->query("UPDATE `acls_functions` SET `functionof` = '20' WHERE `functionid` = '19' OR `functionid` = '21' OR `functionid` = '22';");
		$this->db->query("DELETE FROM `acls_functions` WHERE `acls_functions`.`functionid` = 18;");
		$this->db->query("DELETE FROM `acls_roles_privileges` WHERE `acls_roles_privileges`.`functionid` = 20;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = '20' WHERE `acls_roles_privileges`.`functionid` = '18';");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`-1 WHERE `acls_functions`.`functionid` > 18 ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`-1 WHERE `acls_functions`.`functionof` > 18 ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`-1 WHERE `acls_roles_privileges`.`functionid` > 18 ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `ownerpossible` = '1' WHERE `functionid` = '18' OR `functionid` = '19' OR `functionid` = '21';");

		$enabled = 0;
		$result = $this->db->query("SELECT COUNT(*) AS count FROM `acls_roles_privileges` WHERE `functionid` = '20';");
		$result_array = $result->row_array();
		if ($result_array['count']) {
			$enabled = 1;
		}

		$this->db->query("UPDATE `acls_functions` SET `name` = 'Edit profile', `enabled` = ".$this->db->escape($enabled)." WHERE `functionid` = '19';");
	}
}