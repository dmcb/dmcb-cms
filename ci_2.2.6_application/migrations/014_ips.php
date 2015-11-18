<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Ips extends CI_Migration {

	public function up()
	{
		$this->db->query("ALTER TABLE ci_sessions CHANGE ip_address ip_address varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE views CHANGE ip ip varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE walls CHANGE ip ip varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE users_subscriptions_views CHANGE ip ip varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE posts_comments_banned CHANGE ip ip varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE posts_comments CHANGE ip ip varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE pingbacks CHANGE ip ip varchar(45) default '0' NOT NULL");
		$this->db->query("ALTER TABLE forms CHANGE ip ip varchar(45) default '0' NOT NULL");
	}

	public function down()
	{
		$this->db->query("ALTER TABLE ci_sessions CHANGE ip_address ip_address varchar(39) default '0' NOT NULL");
		$this->db->query("ALTER TABLE views CHANGE ip ip varchar(15) default '0' NOT NULL");
		$this->db->query("ALTER TABLE walls CHANGE ip ip varchar(39) default '0' NOT NULL");
		$this->db->query("ALTER TABLE users_subscriptions_views CHANGE ip ip varchar(15) default '0' NOT NULL");
		$this->db->query("ALTER TABLE posts_comments_banned CHANGE ip ip varchar(39) default '0' NOT NULL");
		$this->db->query("ALTER TABLE posts_comments CHANGE ip ip varchar(39) default '0' NOT NULL");
		$this->db->query("ALTER TABLE pingbacks CHANGE ip ip varchar(39) default '0' NOT NULL");
		$this->db->query("ALTER TABLE forms CHANGE ip ip varchar(39) default '0' NOT NULL");
	}
}