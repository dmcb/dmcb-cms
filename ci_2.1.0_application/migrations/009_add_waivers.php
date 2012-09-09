<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Add_Waivers extends CI_Migration {

	public function up()
	{
		$this->db->query("CREATE TABLE `waivers` (`waiverid` int(10) unsigned NOT NULL, `title` varchar(100) NOT NULL, `content` varchar(10000) NOT NULL, `accept_message` varchar(500) NOT NULL, `is_mandatory` int(1) unsigned NOT NULL, `frequency` int(3) unsigned NOT NULL, PRIMARY KEY (`waiverid`)) ENGINE = MyISAM;"
		$this->db->query("CREATE TABLE `pages_waivers` (`pageid` INT( 10 ) UNSIGNED NOT NULL , `waiverid` INT( 10 ) UNSIGNED NOT NULL , PRIMARY KEY (  `pageid` ,  `waiverid` )) ENGINE = MYISAM ;");
		$this->db->query("CREATE TABLE `users_waivers` (`userid` INT( 10 ) UNSIGNED NOT NULL , `waiverid` INT( 10 ) UNSIGNED NOT NULL , `last_acknowledged` DATETIME NULL , PRIMARY KEY (  `userid` ,  `waiverid` )) ENGINE = MYISAM ;");
		$this->db->query("INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES ('37', 'site', 'waivers', NULL, '0', '0', '0', 'Manage waivers');");
	}

	public function down()
	{
		$this->db->query("DROP TABLE `waivers`");
		$this->db->query("DROP TABLE `pages_waivers`");
		$this->db->query("DROP TABLE `users_waivers`");
		$this->db->query("DELETE FROM `acls_functions` WHERE `functionid` = '37'");
	}
}