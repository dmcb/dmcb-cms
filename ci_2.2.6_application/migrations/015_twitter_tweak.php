<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Twitter_tweak extends CI_Migration {

	public function up()
	{
		$this->db->query("UPDATE blocks_variables SET variabledescription = 'This is the username to draw tweets from.' WHERE function = 'twitter' AND variablename = 'query';");
	}

	public function down()
	{
		$this->db->query("UPDATE blocks_variables SET variabledescription = 'This is the query string of the twitter feed, which should follow a format like \'&from=X&tag=Y\'.' WHERE function = 'twitter' AND variablename = 'query';");
	}
}