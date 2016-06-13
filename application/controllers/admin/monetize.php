<?php

class Monetize extends Admin_Controller
{

	public function __construct(){
		parent::__construct();
        $this->load->model('estate_m');

        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
        
        $this->data['template_css'] = base_url('templates/'.$this->data['settings']['template']).'/'.config_item('default_template_css');
	}
    
    public function index()
	{
	}
    
    public function payments($pay_type = '', $pagination_offset=0)
    {
	    $this->load->library('pagination');
        $this->load->model('payments_m');
        
        $reservation_selected = array('invoice_num LIKE'=>'%_ACT_%');
        
        // Fetch all pages
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['properties'] = $this->estate_m->get_form_dropdown('address');
        $this->data['payments'] = $this->payments_m->get_by($reservation_selected);
        
        $config['base_url'] = site_url('admin/monetize/payments/'.$pay_type.'/');
        $config['uri_segment'] = 5;
        $config['total_rows'] = count($this->data['payments']);
        $config['per_page'] = 20;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';

        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        
        $this->data['payments'] = $this->payments_m->get_by($reservation_selected, FALSE, $config['per_page'], NULL, $pagination_offset);
        
        // Load view
		$this->data['subview'] = 'admin/monetize/payments';
        $this->load->view('admin/_layout_main', $this->data);
    }
    
    public function payments_featured($pay_type = '', $pagination_offset=0)
    {
	    $this->load->library('pagination');
        $this->load->model('payments_m');
        
        $reservation_selected = array('invoice_num LIKE'=>'%_FEA_%');
        
        // Fetch all pages
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['properties'] = $this->estate_m->get_form_dropdown('address');
        $this->data['payments'] = $this->payments_m->get_by($reservation_selected);
        
        $config['base_url'] = site_url('admin/monetize/payments/'.$pay_type.'/');
        $config['uri_segment'] = 5;
        $config['total_rows'] = count($this->data['payments']);
        $config['per_page'] = 20;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';

        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        
        $this->data['payments'] = $this->payments_m->get_by($reservation_selected, FALSE, $config['per_page'], NULL, $pagination_offset);
        
        // Load view
		$this->data['subview'] = 'admin/monetize/payments';
        $this->load->view('admin/_layout_main', $this->data);
    }
    
    public function view_payment($id = NULL)
	{
        $this->load->model('payments_m');
        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['payment'] = $this->payments_m->get_by(array('id'=>$id), TRUE);
            count($this->data['payment']) || $this->data['errors'][] = 'Could not be found';
        }
        else
        {
            redirect('admin/monetize/payments/');
        }
                
        // Load the view
		$this->data['subview'] = 'admin/monetize/view_payment';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
}