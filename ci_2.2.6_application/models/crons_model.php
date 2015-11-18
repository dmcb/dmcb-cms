<?php
/**
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2012, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Crons_model extends CI_Model {

    function Crons_model()
    {
        parent::__construct();
    }

	function get($cron)
	{
		$date = date('Y-m-d H:i:s');
		return $this->db->query("SELECT * FROM crons WHERE cron = ".$this->db->escape($cron)." AND DATE_SUB(NOW(),INTERVAL 1 DAY) > DATE_SUB(last_run, INTERVAL 5 MINUTE)");
	}

	function get_all()
	{
		$date = date('Y-m-d H:i:s');
		return $this->db->query("SELECT * FROM crons WHERE DATE_SUB(NOW(),INTERVAL 1 DAY) > DATE_SUB(last_run, INTERVAL 5 MINUTE)");
	}

	function run($cron)
	{
		$this->db->query("UPDATE crons SET last_run = NOW() WHERE cron = ".$this->db->escape($cron));
	}
}