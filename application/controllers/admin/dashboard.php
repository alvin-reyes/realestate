<?php

class Dashboard extends Admin_Controller {
	
    public function __construct(){
		parent::__construct();
        $this->load->model('page_m');
        $this->load->model('estate_m');
        $this->load->model('option_m');
        
        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
	}
    
    public function index() 
    {
        $this->data['pages_nested'] = $this->page_m->get_nested($this->data['content_language_id']);
        $this->data['estates'] = $this->estate_m->get_last();
        $this->data['estates_all'] = $this->estate_m->get_join();
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
    	
    	$this->data['subview'] = 'admin/dashboard/index';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function search() 
    {
        $this->data['estates'] = $this->estate_m->get_search($this->input->post('search'));
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);

    	$this->data['subview'] = 'admin/dashboard/search';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function modal() {
    	$this->load->view('admin/_layout_modal', $this->data);
    }
    
}