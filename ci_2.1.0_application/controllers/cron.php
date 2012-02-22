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
class Cron extends MY_Controller {

	function Cron()
	{
		parent::__construct();

		$this->load->model('crons_model');
	}

	function _remap()
	{
		if ($this->uri->segment(2))
		{
			$crons_to_run = $this->crons_model->get($this->uri->segment(2));
		}
		else
		{
			$crons_to_run = $this->crons_model->get_all($this->uri->segment(2));
		}

		if (!$crons_to_run->num_rows())
		{
			echo "No crons to run.";
		}

		foreach ($crons_to_run->result_array() as $cron)
		{
			echo "Running cron ".$cron['cron'].".\n";

			if ($cron == "count_views")
			{


			}
			else if ($cron == "site_backup")
			{
				system('mysqldump --opt -h '.$this->db['default']['hostname'].
					' -u '.$this->db['default']['username'].
					' -p '.$this->db['default']['password'].
					' '.$this->db['default']['urbanitedb'].
					' > backup/'.$this->db['default']['urbanitedb'].'.sql');
			}

			$this->crons_model->run($cron['cron']);
		}
	}
}