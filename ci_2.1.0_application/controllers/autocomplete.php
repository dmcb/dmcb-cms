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
class Autocomplete extends MY_Controller {

	function Autocomplete()
	{
		parent::__construct();
	}

	function _remap()
	{
		// Get input
		$value = array_shift($this->input->post(NULL, TRUE));

		$method = $this->uri->segment(2);
		$choices = $this->$method($value);

		if ($choices->num_rows() > 0)
		{
			echo '<ul>';
			foreach ($choices->result_array() as $choice)
			{
				echo '<li>'.$choice['result'].'</li>';
			}
			echo '</ul>';
		}
	}

	function category($value)
	{
		$this->load->model('categories_model');
		return $this->categories_model->autocomplete($value);
	}

	function page($value)
	{
		$this->load->model('pages_model');
		return $this->pages_model->autocomplete($value);
	}

	function post($value)
	{
		$this->load->model('posts_model');
		return $this->posts_model->autocomplete($value);
	}

	function user($value)
	{
		$this->load->model('users_model');
		return $this->users_model->autocomplete($value);
	}
}