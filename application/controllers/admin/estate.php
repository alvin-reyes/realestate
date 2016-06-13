<?php

class Estate extends Admin_Controller
{
    
	public function __construct()
    {
		parent::__construct();
        $this->load->model('estate_m');
        $this->load->model('option_m');
        $this->load->model('file_m');
        
        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
	}
    
    public function index($pagination_offset=0)
	{
	    $this->load->library('pagination');
        
        // Fetch all estates
        $this->data['estates'] = $this->estate_m->get_join();
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname'/*, array('type'=>'AGENT')*/);

        $config['base_url'] = site_url('admin/estate/index');
        $config['uri_segment'] = 4;
        $config['total_rows'] = count($this->data['estates']);
        $config['per_page'] = 20;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        
        $this->data['estates'] = $this->estate_m->get_join($config['per_page'], $pagination_offset);
        
        // Load view
		$this->data['subview'] = 'admin/estate/index';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function edit($id = NULL)
	{
        // If limit reached, error/warning!
        $this->load->model('packages_m');
        $this->load->model('treefield_m');
        
        $user = $this->user_m->get($this->session->userdata('id'));
        
        if(file_exists(APPPATH.'controllers/admin/packages.php'))
        if($user->package_id > 0 && $this->session->userdata('type') == 'AGENT')
        {
            $package = $this->packages_m->get($user->package_id);
            $listing_num = $this->packages_m->get_curr_listings(array('user_id'=>$user->id));
            
            if(config_item('enable_num_amenities_listing') == true)
                $this->data['package_num_amenities_limit'] = $package->num_amenities_limit;
            
            if(isset($listing_num[$user->id]))
            {
                if($listing_num[$user->id] >= $package->num_listing_limit && !$id)
                {
                    $this->session->set_flashdata('error', 
                            lang_check('Num listings max. reached for your package'));
                    redirect('admin/estate');
                    exit();
                }
                else if($package->package_days > 0 && strtotime($user->package_last_payment)<=time())
                {
                    $this->session->set_flashdata('error', 
                            lang_check('Date for your package expired, please extend'));
                    redirect('admin/estate');
                    exit();
                }
            }
        }
       
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['estate'] = $this->estate_m->get_dynamic($id);
            
            if(count($this->data['estate']) == 0)
            {
                $this->data['errors'][] = 'Estate could not be found';
                redirect('admin/estate');
            }
            
            //Check if user have permissions
            if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
            {
                if($this->data['estate']->agent == $this->session->userdata('id'))
                {
                    
                }
                else
                {
                    redirect('admin/estate');
                }
            }
            
            // Fetch file repository
            $repository_id = $this->data['estate']->repository_id;
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>'estate_m'));
                
                // Update page with new repository_id
                $this->estate_m->save(array('repository_id'=>$repository_id), $this->data['estate']->id);
            }
        }
        else
        {
            $this->data['estate'] = $this->estate_m->get_new();
        }
        
		// Pages for dropdown
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        
        // Get available agents
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'AGENT'));
        
        $this->data['available_agent'][''] = lang_check('Current user');
        
        // Get all options
        foreach($this->option_m->languages as $key=>$val){
            $this->data['options_lang'][$key] = $this->option_m->get_lang(NULL, FALSE, $key);
        }
        $this->data['options'] = $this->option_m->get_lang(NULL, FALSE, $this->data['content_language_id']);
        
        // Id's for key adjustments 
        // TODO: better solution needed, this is just hotfix
        $options = $this->data['options'];
        $this->data['options'] = array();
        foreach($options as $option_key=>$option_row)
        {
            $this->data['options'][$option_row->option_id] = $option_row;
        }
        
        // For other langs
        foreach($this->option_m->languages as $key=>$val){
            $options_key = $this->data['options_lang'][$key];
            $this->data['options_lang'][$key] = array();
            foreach($options_key as $option_key=>$option_row)
            {
                $this->data['options_lang'][$key][$option_row->option_id] = $option_row;
            }
        }
        // End id's for key adjustments
        
        
        $options_data = array();
        foreach($this->option_m->get() as $key=>$val)
        {
            $options_data[$val->id][$val->type] = 'true';
        }
        
        // Add rules for dynamic options
        $rules_dynamic = array();
        foreach($this->option_m->languages as $key_lang=>$val_lang){
            foreach($this->data['options'] as $key_option=>$val_option){
                $rules_dynamic['option'.$val_option->id.'_'.$key_lang] = 
                    array('field'=>'option'.$val_option->id.'_'.$key_lang, 'label'=>$val_option->option, 'rules'=>'trim');
                //if($id == NULL)$this->data['estate']->{'option'.$val_option->id.'_'.$key_lang} = '';
                if(!isset($this->data['estate']))$this->data['estate']->{'option'.$val_option->id.'_'.$key_lang} = '';
            }
            
            if(config_db_item('slug_enabled') === TRUE)
            {
                $rules_dynamic['slug_'.$key_lang] = 
                    array('field'=>'slug_'.$key_lang, 'label'=>'lang:URI slug', 'rules'=>'trim');
            }
        }
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->zoom_enabled = false;
            $file->download_url = base_url('files/'.$file->filename);
            $file->delete_url = site_url_q('files/upload/rep_'.$file->repository_id, '_method=DELETE&amp;file='.rawurlencode($file->filename));

            if(file_exists(FCPATH.'/files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $file->zoom_enabled = true;
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
            }
            
            $this->data['files'][$file->repository_id][] = $file;
        }
        
        // Set up the form
        $rules = $this->estate_m->rules;
        $this->form_validation->set_rules(array_merge($rules, $rules_dynamic));

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/estate/edit/'.$id);
                exit();
            }
            
            $data = $this->estate_m->array_from_post(array('gps', 'date', 'address', 'is_featured', 'is_activated'));
            $dynamic_data = $this->estate_m->array_from_post(array_keys($rules_dynamic));
            
            // AGENT_LIMITED don't have permission to change this fields...
            if($this->session->userdata('type') == 'AGENT_LIMITED')
            {
                unset($data['is_activated'],
                      $data['is_featured']
                );
            }
            
            $data['search_values'] = $data['address'];
            foreach($dynamic_data as $key=>$val)
            {
                $pos = strpos($key, '_');
                $option_id = substr($key, 6, $pos-6);
                $language_id = substr($key, $pos+1);
                
                if(!isset($options_data[$option_id]['TEXTAREA']) && !isset($options_data[$option_id]['CHECKBOX'])){
                    $data['search_values'].=' '.$val;
                }
                
                // TODO: test check, values for each language for selected checkbox
                if(isset($options_data[$option_id]['CHECKBOX'])){
                    if($val == 'true')
                    {
                        foreach($this->option_m->languages as $key_lang=>$val_lang){
                            foreach($this->data['options_lang'][$key_lang] as $key_option=>$val_option){
                                if($val_option->id == $option_id && $language_id == $key_lang)
                                {
                                    $data['search_values'].=' true'.$val_option->option;
                                }
                            }
                        }
                    }
                }
            }
            
            $data['date_modified'] = date('Y-m-d H:i:s');
            
            /* [Auto move gps coordinates few meters away if same exists in database] */
            $estate_same_coordinates = $this->estate_m->get_by(array('gps'=>$data['gps']), TRUE);

            if(is_object($estate_same_coordinates) && !empty($estate_same_coordinates))
            {
                $same_gps = explode(', ', $estate_same_coordinates->gps);
                // $same_gps[0] && $same_gps[1] available
                $rand_lat = rand(1, 9);
                $rand_lan = rand(1, 9);
                
                $data['gps'] = ($same_gps[0]+0.00001*$rand_lat).', '.($same_gps[1]+0.00001*$rand_lan);
            }
            /* [/Auto move gps coordinates few meters away if same exists in database] */
            
            $insert_id = $this->estate_m->save($data, $id);
            
            // add insert to search_values
            if(is_numeric($insert_id))
            {
                $update_data = array();
                $update_data['search_values'] = 'id: '.$id.$data['search_values'];
                
                $this->estate_m->save($update_data, $insert_id);
            }
            
            if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
            {
                $data['agent'] = $this->session->userdata('id');
            }
            else
            {
                $data['agent'] = $this->input->post('agent');
            }
            
            // Save dynamic options
            
            $dynamic_data['agent'] = $data['agent'];

            $this->estate_m->save_dynamic($dynamic_data, $insert_id);

            $this->load->library('sitemap');
            $this->sitemap->generate_sitemap();
            
            $this->session->set_flashdata('message', 
                    '<p class="label label-success validation">'.lang_check('Changes saved').'</p>');
            
            redirect('admin/estate/edit/'.$insert_id);
        }
        
        // Load the view
		$this->data['subview'] = 'admin/estate/edit';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function values_correction(&$str)
    {
        $str = str_replace(', ', ',', $str);
        
        return TRUE;
    }
    
    public function values_dropdown_check($str)
    {
        static $already_set = false;
        $comma_count = -1;
        
        if($already_set == true)
            return TRUE;
        
        foreach($this->option_m->languages as $key=>$value)
        {
            $values_post = $this->input->post("values_$key");
            
            $comma_cur_count = substr_count($values_post, ',');
            
            if($comma_count == -1)$comma_count = $comma_cur_count;
            
            if($comma_count != $comma_cur_count)
            {
                $this->form_validation->set_message('values_dropdown_check', lang_check('Values number must be same in all languages'));
                $already_set = true;
                return FALSE;
            }
        }
        
        return TRUE;
    }
    
	public function gps_check($str)
	{
        $gps_coor = explode(', ', $str);
        
        if(count($gps_coor) != 2)
        {
        	$this->form_validation->set_message('gps_check', lang_check('Please check GPS coordinates'));
        	return FALSE;
        }
        
        if(!is_numeric($gps_coor[0]) || !is_numeric($gps_coor[1]))
        {
        	$this->form_validation->set_message('gps_check', lang_check('Please check GPS coordinates'));
        	return FALSE;
        }
        
        return TRUE;
	}
    
    public function delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/estate');
            exit();
        }
        
        //Check if user have permissions
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            $this->data['estate'] = $this->estate_m->get_dynamic($id);
            
            if(count($this->data['estate']) > 0)
            {
                if($this->data['estate']->agent == $this->session->userdata('id'))
                {
                    
                }
                else
                {
                    redirect('admin/estate');
                }
            }
        }
       
		$this->estate_m->delete($id);
        redirect('admin/estate');
	}
    
    public function options()
	{
        // Fetch all estates
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options_no_parents'] = $this->option_m->get_no_parents($this->data['content_language_id']);
        $this->data['options'] = $this->option_m->get_lang(NULL, FALSE, $this->data['content_language_id']);
        $this->data['options_nested'] = $this->option_m->get_nested($this->data['content_language_id']);
        
        //var_dump($this->data['options_nested']);
        
        // Load view
		$this->data['subview'] = 'admin/estate/options';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function edit_option($id = NULL)
	{
	    // Fetch a record or set a new one
	    if($id)
        {
            $this->data['option'] = $this->option_m->get_lang($id, FALSE, $this->data['content_language_id']);
            count($this->data['option']) || $this->data['errors'][] = 'Could not be found';
        }
        else
        {
            $this->data['option'] = $this->option_m->get_new();
        }
        
		// Options for dropdown
        $this->data['options_no_parents'] = $this->option_m->get_no_parents($this->data['content_language_id']);
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');

        // Set up the form
        $rules = $this->option_m->get_all_rules();
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/estate/edit_option/'.$id);
                exit();
            }
            
            $data = $this->option_m->array_from_post($this->option_m->get_post_fields());
            if($id == NULL)
            {
                //get max order in parent id and set
                $parent_id = $this->input->post('parent_id');
                $data['order'] = $this->option_m->max_order($parent_id);
            }
            
            $data_lang = $this->option_m->array_from_post($this->option_m->get_lang_post_fields());
            $id = $this->option_m->save_with_lang($data, $data_lang, $id);
            
            //$this->output->enable_profiler(TRUE);
            //redirect('admin/estate/options');
            $this->session->set_flashdata('message', 
                    '<p class="label label-success validation">'.lang_check('Changes saved').'</p>');
            
            redirect('admin/estate/edit_option/'.$id);
        }
        
        // Load the view
		$this->data['subview'] = 'admin/estate/edit_option';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function update_ajax($filename = NULL)
    {
        // Save order from ajax call
        if(isset($_POST['sortable']) && $this->config->item('app_type') != 'demo')
        {
            $this->option_m->save_order($_POST['sortable']);
        }
        
        $data = array();
        $length = strlen(json_encode($data));
        header('Content-Type: application/json; charset=utf8');
        header('Content-Length: '.$length);
        echo json_encode($data);
        
        exit();
    }
    
    public function delete_option($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/estate/options');
            exit();
        }
        
        if($this->option_m->check_deletable($id))
        {
            $this->option_m->delete($id);
        }
        else
        {
            $this->session->set_flashdata('error', 
                    lang_check('Delete disabled, child or element locked/hardlocked! But you can change or unlock it.'));
        }
		
        redirect('admin/estate/options');
	}
    
}