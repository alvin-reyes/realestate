<?php

class Fresearch extends Frontuser_Controller
{

	public function __construct ()
	{
		parent::__construct();
	}
    
    public function index()
    {
        echo 'index';
    }
    
	public function myresearch()
	{
	    $this->load->model('savedsearch_m');
       
        $lang_id = $this->data['lang_id'];
        $this->data['content_language_id'] = $this->data['lang_id'];
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myresearch');
        $this->data['page_title'] = lang_check('Myresearch');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $user_id = $this->session->userdata('id');
        
        // Fetch all listings
        $this->data['listings'] = $this->savedsearch_m->get_by(array('user_id'=>$user_id));
        
        // Get templates
        $templatesDirectory = opendir(FCPATH.'templates/'.$this->data['settings_template'].'/components');
        // get each template
        $template_prefix = 'page_';
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != ".." && strpos($tempFile, '.php') !== FALSE) {
                if(substr_count($tempFile, $template_prefix) == 0)
                {
                    $template_output = $this->parser->parse($this->data['settings_template'].'/components/'.$tempFile, $this->data, TRUE);
                    //$template_output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $template_output);
                    $this->data['template_'.substr($tempFile, 0, -4)] = $template_output;
                }
            }
        }
        

        $output = $this->parser->parse($this->data['settings_template'].'/myresearch.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
	public function myresearch_edit()
	{
	   $this->load->model('savedsearch_m');
	   $research_id = $this->uri->segment(4);
       
        $lang_id = $this->data['lang_id'];
        $this->data['content_language_id'] = $this->data['lang_id'];
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myresearch');
        $this->data['page_title'] = lang_check('Myresearch');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $user_id = $this->session->userdata('id');
        
        // Fetch all listings
        $this->data['listing'] = $this->savedsearch_m->get_array($research_id);

        //Check if user have permision
        if(empty($this->data['listing']['user_id']) || $user_id != $this->data['listing']['user_id'])
        {
            redirect('fresearch/myresearch/'.$this->data['lang_code'].'#content');
            exit();  
        }

        // Set up the form
        $rules = $this->savedsearch_m->rules_admin;
        unset($rules['user_id']);
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('fresearch/myresearch/'.$this->data['lang_code'].'#content');
                exit();
            }
            
            $data = $this->savedsearch_m->array_from_post(array('activated'));
            
            $id = $this->savedsearch_m->save($data, $research_id, TRUE);
            
            $this->session->set_flashdata('message', 
                    '<p class="alert alert-success validation">'.lang_check('Changes saved').'</p>');
            
            if(!empty($id))
            {
                redirect('fresearch/myresearch/'.$this->data['lang_code'].'#content');
            }
            else
            {
                $this->output->enable_profiler(TRUE);
            }
        }
        
        
        
        // Get templates
        $templatesDirectory = opendir(FCPATH.'templates/'.$this->data['settings_template'].'/components');
        // get each template
        $template_prefix = 'page_';
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != ".." && strpos($tempFile, '.php') !== FALSE) {
                if(substr_count($tempFile, $template_prefix) == 0)
                {
                    $template_output = $this->parser->parse($this->data['settings_template'].'/components/'.$tempFile, $this->data, TRUE);
                    //$template_output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $template_output);
                    $this->data['template_'.substr($tempFile, 0, -4)] = $template_output;
                }
            }
        }
        

        $output = $this->parser->parse($this->data['settings_template'].'/editresearch.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
	public function myresearch_delete()
	{
	   $this->load->model('savedsearch_m');
	   $research_id = $this->uri->segment(4);
       
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('fresearch/myresearch/'.$this->data['lang_code'].'#content');
            exit();
        }
       
		$this->savedsearch_m->delete($research_id);
        redirect('fresearch/myresearch/'.$this->data['lang_code'].'#content');
    }
    

}