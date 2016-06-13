<?php

class Enquire extends Admin_Controller 
{

	public function __construct(){
		parent::__construct();
        
        $this->load->model('estate_m');
	}
    
    public function index()
	{
	    // Fetch all users
		$this->data['enquires'] = $this->enquire_m->get();
        $this->data['all_estates'] = $this->estate_m->get_form_dropdown('address');
        
        // Fetch options
        // $this->data['content_language_id'] = $this->language_m->get_content_lang();
        // $this->load->model('option_m');
        // $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        // You can show it in view file with:
        // echo $options[$enquire->property_id][10];
        
        // Load view
		$this->data['subview'] = 'admin/enquire/index';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function edit($id = NULL)
	{
	    // Fetch a user or set a new one
	    if($id)
        {
            $this->data['enquire'] = $this->enquire_m->get($id);

            if(count($this->data['enquire']) == 0)
            {
                $this->data['errors'][] = 'Enquire could not be found';
                redirect('admin/enquire');
            }

            if($this->data['enquire']->fromdate == '0'){
                $this->data['enquire']->fromdate = '';
            }
            
            if($this->data['enquire']->todate == '0'){
                $this->data['enquire']->todate = '';
            }
            
            //Check if user have permissions
            if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
            {
                if($this->estate_m->check_user_permission($this->data['enquire']->property_id, 
                                         $this->session->userdata('id')) > 0)
                {
                }
                else
                {
                    
                    redirect('admin/enquire');
                }
            }
        }
        else
        {
            $this->data['enquire'] = $this->enquire_m->get_new();
        }
        
        $this->data['all_estates'] = $this->estate_m->get_form_dropdown('address', false, true, true);
        
        // Fetch options and show title in dropdown
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
        $this->load->model('option_m');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        foreach($this->data['all_estates'] as $key_estate=>$address_estate)
        {
            if(!empty($this->data['options'][$key_estate][10]))
            $this->data['all_estates'][$key_estate] = $address_estate.', '.$this->data['options'][$key_estate][10];
        }
        
        // Set up the form
        $rules = $this->enquire_m->rules_admin;

        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/enquire/edit/'.$id);
                exit();
            }
            
            $data = $this->enquire_m->array_from_post(array('name_surname', 'mail', 'message', 
                                                         'address', 'message', 'phone', 'readed', 'fromdate', 'todate', 'property_id'));
            
            if($id == NULL)
                $data['date'] = date('Y-m-d H:i:s');
            
            $this->enquire_m->save($data, $id);
            redirect('admin/enquire');
        }
        
        // Load the view
		$this->data['subview'] = 'admin/enquire/edit';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/enquire');
            exit();
        }
       
        $this->data['enquire'] = $this->enquire_m->get($id);
        
        //Check if user have permissions
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            if($this->estate_m->check_user_permission($this->data['enquire']->property_id, 
                                     $this->session->userdata('id')) > 0)
            {
            }
            else
            {
                redirect('admin/enquire');
            }
        }
       
		$this->enquire_m->delete($id);
        redirect('admin/enquire');
	}
}