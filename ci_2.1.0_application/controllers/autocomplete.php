<?php
/**
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Autocomplete extends MY_Controller {

	function Autocomplete()
	{
		parent::__construct();
	}

	function user()
	{
		$this->load->model('users_model');

		$value = $this->input->post(NULL, TRUE);
		$choices = $this->users_model->autocomplete(array_shift($value));

		if ($choices->num_rows() > 0)
		{
			echo '<ul>';
			foreach ($choices->result_array() as $choice)
			{
				echo '<li>'.$choice['displayname'].'</li>';
			}
			echo '</ul>';
		}
	}
}