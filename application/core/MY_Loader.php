<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
   function __construct()
   {
      parent::__construct();

      $this->_ci_view_paths += array(FCPATH.'templates/'=>TRUE);
   }
}

?>