<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Captcha extends CI_Migration {

	public function up()
	{
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`+1 WHERE `functionid` >= '11' ORDER BY `functionid` DESC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`+1 WHERE `functionof` >= '11' ORDER BY `functionof` DESC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`+1 WHERE `functionid` >= '11' ORDER BY `functionid` DESC;");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('11', 'post', 'captcha', '9', '0', '1', '1', 'Require Captcha');");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
	}

	public function down()
	{
		$this->db->query("DELETE FROM `acls_functions` WHERE `functionid` = '11'");
		$this->db->query("DELETE FROM `acls_roles_privileges` WHERE `functionid` = '11';");
		$this->db->query("UPDATE `acls_functions` SET `functionid` = `functionid`-1 WHERE `functionid` >= '12' ORDER BY `functionid` ASC;");
		$this->db->query("UPDATE `acls_functions` SET `functionof` = `functionof`-1 WHERE `functionof` >= '12' ORDER BY `functionof` ASC;");
		$this->db->query("UPDATE `acls_roles_privileges` SET `functionid` = `functionid`-1 WHERE `functionid` >= '12' ORDER BY `functionid` ASC;");
		$this->db->query("ALTER TABLE `acls_functions` ORDER BY `functionid`;");
	}
}