<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to CI 2.0.3 loader library
 *
 * Adds themeing by looking for views in theme directory first, and then base directory
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */
class MY_Loader extends CI_Loader {
	
	public function __construct()
	{
		parent::__construct();
		
		$CI =& get_instance();
		$this->_ci_view_paths = array(APPPATH.'views/'.$CI->config->item('dmcb_theme_directory') => TRUE, APPPATH.'views/' => FALSE);
	}

}