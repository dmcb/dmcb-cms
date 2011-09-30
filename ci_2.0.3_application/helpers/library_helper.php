<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb library helper
 *
 * Instantiates a unique library based off the library type, id, and id type
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
 
// ------------------------------------------------------------------------

/**
 * Instantiate library
 *
 * Instantiates a library returning it's unique name
 *
 * @access	public
 * @param	string	library name
 * @param	string  id for library
 * @param   string  optional value of id type
 * @return	string
 */
if ( ! function_exists('instantiate_library'))
{
 	function instantiate_library($library, $id, $id_type = NULL)
	{
		$CI =& get_instance();
		// If id_type isn't supplied, we know that it's the primary key id, and not an arbitrary identifier like a url name
		// If it is supplied, we need to do a look up unfortunately to get the primary key id used to identify the object across libraries and controllers
		if ($id_type != NULL)
		{
			$model = $library.'s_model';
			$lookup = 'get_by_'.$id_type;
			$CI->load->model($model);
			//$id = $CI->cache->model($model, $lookup, $id);
			$id = $CI->$model->$lookup($id);
		}
		$object = $library.'_'.$id;
		$params = array('id' => $id);
		$CI->load->library($library.'_lib',$params,$object);
		return $CI->$object;
	}
}