<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb log helper
 *
 * Logs note-worthy messages to the file system
 * This doesn't use CI's native logging, since these messages are more
 * important than debug messages, but not necessarily error messages
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
 * Log to file
 *
 * Writes message to a log file
 *
 * @access	public
 * @param	string	the file to log to
 * @param	string  the message to log
 * @return	string
 */
if ( ! function_exists('log_to_file'))
{
 	function log_to_file($logfile, $message)
	{
		$CI =& get_instance();
		$logfile = $CI->config->item('log_path').'/'.$logfile.".log";
		$message = "===========================================\n".date('Y/m/d H:i')."\n".$message."\n";
		if (file_exists($logfile))
		{
			$file = fopen($logfile, 'a');
		}
		else
		{
			$file = fopen($logfile, 'w');
		}
		fwrite($file, $message);
		fclose($file);	
	}
}