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
			echo "No crons to run.\n";
		}

		foreach ($crons_to_run->result_array() as $cron)
		{
			echo "Running cron ".$cron['cron'].".\n";

			if ($cron['cron'] == "count_views")
			{
				$this->load->model('views_model');
				echo $this->views_model->archive();
			}
			else if ($cron['cron'] == "site_backup")
			{
				system('mysqldump -h '.$this->db->hostname.' -u'.$this->db->username.' -p'.$this->db->password.' --databases '.$this->db->database.' > backup/'.$this->db->database.'.sql', $return_value);
				echo "mysqldump return value: ".$return_value."\n";
				system("tar --exclude 'backup/*.tar.gz' -czf backup/".date("Ymd").".".$this->config->item('dmcb_server').".tar.gz .", $return_value);
				echo "tar return value: ".$return_value."\n";

				if ($backup_folder = opendir('backup'))
				{
					while (false !== ($file = readdir($backup_folder)))
					{
						if ($file != "." && $file != ".." && $file != ".htaccess" && time() - filemtime('backup/'.$file) > 2419200) {
							echo "Removing backup archive older than 4 weeks: backup/".$file."\n";
							unlink('backup/'.$file);
						}
					}
					closedir($backup_folder);
				}
			}

			$this->crons_model->run($cron['cron']);
		}
	}
}