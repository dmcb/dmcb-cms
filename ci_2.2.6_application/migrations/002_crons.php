<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Crons extends CI_Migration {

	public function up()
	{
		$this->db->query("CREATE TABLE `crons` (`cron` VARCHAR( 50 ) NOT NULL, `last_run` DATETIME NOT NULL) ENGINE = MYISAM;");
		$this->db->query("ALTER TABLE `crons` ADD PRIMARY KEY(`cron`);");
		$this->db->query("INSERT INTO `crons` (`cron`, `last_run`) VALUES ('site_backup', '2012-02-21 19:51:53'), ('count_views', '2012-02-21 19:51:53');");
		$this->db->query("ALTER TABLE  `crons` ORDER BY  `cron`;");
	}

	public function down()
	{
		$this->db->query("DROP TABLE `crons`");
	}
}