<?php

class MYCache
{
    function display_cache_override()
    {
        /*
         * Normally, CI would display what's cached before almost eveything, 
		 * We are delaying the caching until a later step so we can grab sign on information
         */
    }
    
    function post_controller_constructor_cache()
    {
		// Now, during controller construction, where we can grab session info, will we possibly pull up the cache
        $CI = & get_instance();
        
        $CFG =& load_class('Config');
        $URI =& load_class('URI');
        $OUT =& load_class('Output');
		
        //Don't use cache if we have session or if it's a posted form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$CI->session->userdata('signedon'))
        {
            if ($OUT->_display_cache($CFG, $URI) == TRUE)
                exit;
        }
    }
}