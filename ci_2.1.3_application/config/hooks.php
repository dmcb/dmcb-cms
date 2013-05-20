<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['cache_override'] = array(
    'class'    => 'MYCache',
    'function' => 'display_cache_override',
    'filename' => 'MYCache.php',
    'filepath' => 'hooks',
    'params'   => array()
);

$hook['post_controller_constructor'] = array(
    'class'    => 'MYCache',
    'function' => 'post_controller_constructor_cache',
    'filename' => 'MYCache.php',
    'filepath' => 'hooks',
    'params'   => array()
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */