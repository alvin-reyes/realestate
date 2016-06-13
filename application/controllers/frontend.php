<?php

class Frontend extends Frontend_Controller
{

	public function __construct ()
	{
		parent::__construct();
	}
    
    private function _get_purpose()
    {
        if(isset($this->select_tab_by_title))
        if($this->select_tab_by_title != '')
        {
            $this->data['purpose_defined'] = $this->select_tab_by_title;
            return $this->select_tab_by_title;
        }
        
        if(isset($this->data['is_purpose_sale'][0]['count']))
        {
            $this->data['purpose_defined'] = lang('Sale');
            return lang('Sale');
        }
        
        if(isset($this->data['is_purpose_rent'][0]['count']))
        {
            $this->data['purpose_defined'] = lang('Rent');
            return lang('Rent');
        }
        
        if(config_item('all_results_default') === TRUE)
        {
            $this->data['purpose_defined'] = '';
            return '';
        }
        
        $this->data['purpose_defined'] = lang('Sale');
        return lang('Sale');
    }
    
    public function maskingsubmit()
    {
        $this->load->model('masking_m');
        
        //Validation
        $rules = $this->masking_m->rules_admin;
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            $data = $this->masking_m->array_from_post(array('visitor_type', 'name', 'phone', 
                                                            'email', 'allow_contact', 'agent_id', 'property_id'));
            
            // Save to database
            $data['date_submit'] = date('Y-m-d H:i:s');
            $this->masking_m->save($data);
            
            // Save to session
            $this->load->library('session');
            $data_sess = $data;
            $data_sess['contacted_agents'] = $this->session->userdata('contacted_agents');
            $data_sess['contacted_agents'][] = $data_sess['agent_id'];
            $this->session->set_userdata($data_sess);
            
            // Fetch agent/user email
            $agent = $this->user_m->get($data['agent_id']);
            
            if(!empty($agent->mail))
            {
                // Send email to agent/user
                $this->load->library('email');
                
                $this->email->from($this->data['settings_noreply'], lang_check('Web page'));
                $this->email->to($agent->mail);
                
                $this->email->subject(lang_check('Masking submission from real-estate web'));
    
                $message='';
                foreach($data as $key=>$value){
                	$message.="$key:\n$value\n";
                }
                
                $this->email->message($message);
                if ( ! $this->email->send())
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_false');
                    //echo 'problem sending email';
                }
                else
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_true');
                    //echo 'successfully';
                }
            }
        
            echo 'successfully';
        }
        else
        {
            echo validation_errors();
        }

        exit();
    }
    
    private function check_login()
    {        
        $this->load->library('session');
        $this->load->model('user_m');
        
        // Login check
        if($this->user_m->loggedin() == FALSE)
        {
            redirect('frontend/login/'.$this->data['lang_code']);
        }
        else
        {
    	    $dashboard = 'admin/dashboard';
            
            if($this->session->userdata('type') == 'USER')
            {
                // LOGIN USER, OK
            }
            else
            {
                redirect($dashboard);
            }
        }
    }
    
	public function loginfacebook(){

		$this->load->library('facebook'); // Automatically picks appId and secret from config
        // OR
        // You can pass different one like this
        //$this->load->library('facebook', array(
        //    'appId' => 'APP_ID',
        //    'secret' => 'SECRET',
        //    ));

		$user = $this->facebook->getUser();
        
        if ($user) {
            
            echo 'user';
            exit();
            
            try {
                $data['user_profile'] = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                $user = null;
            }
        }else {
            
            echo 'destroy';
            exit();
            $this->facebook->destroySession();
        }

        if ($user) {

            $data['logout_url'] = site_url('facebookdemo/logout'); // Logs off application
            // OR 
            // Logs off FB!
            // $data['logout_url'] = $this->facebook->getLogoutUrl();

        } else {
            $data['login_url'] = $this->facebook->getLoginUrl(array(
                'redirect_uri' => site_url('facebookdemo/login'), 
                'scope' => array("email") // permissions here
            ));
        }
        $this->load->view('facebookdemo',$data);

	}
    
    private function load_head_data()
    {
        /* Helpers */
        $this->data['year'] = date('Y');
        /* End helpers */
                
        /* Widgets functions */
        $this->data['print_menu'] = get_menu($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_menu_realia'] = get_menu_realia($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_lang_menu'] = get_lang_menu($this->language_m->get_array_by(array('is_frontend'=>1)), $this->data['lang_code']);
        /* End widget functions */
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        $this->data['page_files'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
                $this->data['documents_'.$file->repository_id][] = $file;
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_documents'][] = $file;
                }
            }
            
            $this->data['files_'.$file->repository_id][] = $file;

            if($this->temp_data['page']->repository_id == $file->repository_id)
            {
                $this->data['page_files'][] = $file;
            }
        }

        /* Get all estates data */
        $estates = $this->estate_m->get_by(array('is_activated' => 1));
        $options = $this->option_m->get_options($this->data['lang_id']);
        
        $this->data['all_estates'] = array();
        foreach($estates as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = htmlentities($estate_obj->address);
            $estate['date'] = $estate_obj->date;
            $estate['is_featured'] = $estate_obj->is_featured;
            
            // All estate options
            if(isset($options[$estate_obj->id]))
            foreach($options[$estate_obj->id] as $key1=>$row1)
            {                
                if(substr($row1, -2) == ' -')$row1=substr($row1, 0, -2);
                $estate['option_'.$key1] = $row1;
                $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
            }
            
            // Url to preview
            if(isset($options[$estate_obj->id][10]))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_obj->id][10]));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_obj->repository_id]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_obj->repository_id][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }
            
            $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png';
                }
            }

            $this->data['all_estates'][] = $estate;
        }
        
        $this->data['all_estates_center'] = calculateCenter($estates);
        
        /* End get all estates data */
        
        $options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        $this->data['options_name'] = array();
        $this->data['options_suffix'] = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_prefix_'.$row->option_id] = $row->prefix;
            $this->data['options_values_'.$row->option_id] = '';
            $this->data['options_values_li_'.$row->option_id] = '';
            $this->data['options_values_arr_'.$row->option_id] = array();
            $this->data['options_values_radio_'.$row->option_id] = '';
            
            if(count(explode(',', $row->values)) > 0)
            {
                $options = '<option value="">'.$row->option.'</option>';
                $options_li = '';
                $radio_li = '';
                foreach(explode(',', $row->values) as $key2 => $val)
                {
                    $selected = '';
                    if($this->_get_purpose() == strtolower($val))$selected = 'selected';
                    $options.='<option value="'.$val.'" '.$selected.'>'.$val.'</option>';
                    $this->data['options_values_arr_'.$row->option_id][] = $val;
                    
                    $active = '';
                    if($this->_get_purpose() == strtolower($val))$active = 'active';
                    $options_li.= '<li class="'.$active.' cat_'.$key2.'"><a href="#">'.$val.'</a></li>';
                    
                    $checked = '';
                    if($this->_get_purpose() == strtolower($val))$checked = 'checked';
                    $radio_li.='<label class="checkbox" for="inputRent">
                                <input type="radio" rel="'.$val.'" name="search_option_'.$row->option_id.'" value="'.$key2.'" '.$checked.'> '.$val.'
                                </label>';
                }
                $this->data['options_values_'.$row->option_id] = $options;
                $this->data['options_values_li_'.$row->option_id] = $options_li;
                $this->data['options_values_radio_'.$row->option_id] = $radio_li;
            }
        }
    }
    
    
    public function myproperties()
    {
        $this->check_login();
        $this->load_head_data();
        
        $this->data['user'] = $this->user_m->get_array($this->session->userdata('id'));
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myproperties');
        $this->data['page_title'] = lang_check('Myproperties');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        
	    // Fetch all estates
        $this->data['estates'] = $this->estate_m->get_join();
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'USER'));
        
        // Fetch packages
        $this->load->model('packages_m');
        
        $this->data['packages'] = $this->packages_m->get();
        $this->data['packages_days'] = $this->packages_m->get_form_dropdown('package_days');
        $this->data['packages_listings'] = $this->packages_m->get_form_dropdown('num_listing_limit');
        $this->data['packages_price'] = $this->packages_m->get_form_dropdown('package_price');
        $this->data['curr_listings'] = $this->packages_m->get_curr_listings();
        
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

        $output = $this->parser->parse($this->data['settings_template'].'/myproperties.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function myreservations()
    {
        $this->check_login();
        $this->load_head_data();
        
        $this->load->model('reservations_m');
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myreservations');
        $this->data['page_title'] = lang_check('Myreservations');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        
	    // Fetch all estates
        $this->data['estates'] = $this->reservations_m->get_by(array('user_id' => $this->session->userdata('id'), 'date_to >'=>date("Y-m-d")));
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'USER'));
        
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

        $output = $this->parser->parse($this->data['settings_template'].'/myreservations.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function myrates()
    {
        $this->check_login();
        $this->load_head_data();
        
        $this->load->model('rates_m');
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myrates');
        $this->data['page_title'] = lang_check('Myrates');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        
	    // Fetch all rates
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['properties'] = $this->estate_m->get_form_dropdown('address');
        $this->data['rates'] = $this->rates_m->get_by_check(array());
        
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

        $output = $this->parser->parse($this->data['settings_template'].'/myrates.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function editrate()
    {
        $this->check_login();
        $this->load_head_data();
        
        $this->load->model('rates_m');
        $this->load->model('showroom_m');
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Editrate');
        $this->data['page_title'] = lang_check('Editrate');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
                        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['rate'] = $this->rates_m->get_lang($id, FALSE, $this->data['content_language_id']);
            count($this->data['rate']) || $this->data['errors'][] = 'Could not be found';
            
            if(!isset($this->data['rate']->property_id))
                redirect('frontend/myrates/'.$this->data['lang_code']);
            
            //Check if user have permissions
            $num_found = $this->estate_m->check_user_permission($this->data['rate']->property_id, $this->session->userdata('id'));
            
            if($num_found == 0)
                redirect('frontend/myrates/'.$this->data['lang_code']);
        }
        else
        {
            $this->data['rate'] = $this->rates_m->get_new();
        }
        
		// Pages for dropdown
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        
        //Simple way to featch only address:        
        $this->data['properties'] = $this->estate_m->get_form_dropdown('address');
        
        $this->load->model('payments_m');
        $this->data['currencies'] = $this->payments_m->currencies;
        
        $this->lang->load('calendar');
        $this->data['changeover_days'] = array(lang_check('Flexible'), 
                                               lang_check('cal_monday'),
                                               lang_check('cal_tuesday'),
                                               lang_check('cal_wednesday'),
                                               lang_check('cal_thursday'),
                                               lang_check('cal_friday'),
                                               lang_check('cal_saturday'),
                                               lang_check('cal_sunday'));
        
        // Set up the form
        $rules = $this->rates_m->get_all_rules();
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('frontend/editrate/'.$this->data['lang_code'].'/'.$id);
                exit();
            }
            
            $data = $this->rates_m->array_from_post(array('date_from', 'date_to', 'min_stay', 'changeover_day', 'property_id'));
            
            $data_lang = $this->rates_m->array_from_post($this->rates_m->get_lang_post_fields());
            
            //Check if user have permissions
            if($this->session->userdata('type') != 'ADMIN')
            {
                $num_found = $this->estate_m->check_user_permission($data['property_id'], $this->session->userdata('id'));
                
                if($num_found == 0)
                    exit(lang_check('Access not allowed'));
            }
            
            $id = $this->rates_m->save_with_lang($data, $data_lang, $id);
            
            $this->session->set_flashdata('message', 
                    '<p class="alert alert-success validation">'.lang_check('Changes saved').'</p>');
            
            if(!empty($id))
            {
                redirect('frontend/editrate/'.$this->data['lang_code'].'/'.$id);
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

        $output = $this->parser->parse($this->data['settings_template'].'/editrate.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function deleterate()
    {
        $this->check_login();
        $this->load->model('rates_m');
        
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang_check('Data editing disabled in demo'));
            redirect('frontend/myrates/'.$this->data['lang_code']);
            exit();
        }
        
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {

            $rate = $this->rates_m->get($id);
            
            if(!isset($rate->property_id))
                redirect('frontend/myrates/'.$this->data['lang_code']);
            
            $num_found = $this->estate_m->check_user_permission($rate->property_id, $this->session->userdata('id'));
            
            if($num_found == 0)
                redirect('frontend/myrates/'.$this->data['lang_code']);
           
    		$this->rates_m->delete($id);
        }           

        redirect('frontend/myrates/'.$this->data['lang_code']);
    }
    
    public function deletereservation()
    {
        $this->check_login();
        
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang_check('Data editing disabled in demo'));
            redirect('frontend/myreservations/'.$this->data['lang_code']);
            exit();
        }
        
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->load->model('reservations_m');
            $reservation = $this->reservations_m->get($id);
            
            if(!empty($reservation))
            {
                //Check if user have permissions
                if($reservation->user_id == $this->session->userdata('id') &&
                   $reservation->is_confirmed == 0 )
                {
                    $this->reservations_m->delete($id);
                }
            }
        }           

        redirect('frontend/myreservations/'.$this->data['lang_code']);
    }
    
    public function viewreservation()
    {
        $this->load->model('reservations_m');
        
        $this->check_login();
        $this->load_head_data();
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {
    	    // Fetch all estates
            $this->data['reservation'] = $this->reservations_m->get_array_by(array('user_id' => $this->session->userdata('id'), 'id'=>$id), TRUE);
            $this->data['languages'] = $this->language_m->get_form_dropdown('language');
            $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
            $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'USER'));
            
            // Main page data
            $this->data['page_navigation_title'] = lang_check('Reservation').' :: '.date('Y-m-d', strtotime($this->data['reservation']['date_from'])).' - '.date('Y-m-d', strtotime($this->data['reservation']['date_to']));
            $this->data['page_title'] = lang_check('Reservation').' :: '.date('Y-m-d', strtotime($this->data['reservation']['date_from'])).' - '.date('Y-m-d', strtotime($this->data['reservation']['date_to']));
            $this->data['page_body']  = '';
            $this->data['page_description']  = '';
            $this->data['page_keywords']  = '';
        }
        else
        {
            redirect('frontend/myreservations/'.$this->data['lang_code']);
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

        $output = $this->parser->parse($this->data['settings_template'].'/viewreservation.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function listproperty()
    {
        $this->check_login();
        $this->load_head_data();
        
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
        
        $output = $this->parser->parse($this->data['settings_template'].'/myproperties.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function deleteproperty()
    {
        $this->check_login();
        
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang_check('Data editing disabled in demo'));
            redirect('frontend/myproperties/'.$this->data['lang_code']);
            exit();
        }
        
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['estate'] = $this->estate_m->get_dynamic_array($id);
            
            if(count($this->data['estate']) > 0)
            {
                //Check if user have permissions
                if($this->data['estate']['agent'] == $this->session->userdata('id'))
                {
                    $this->estate_m->delete($id);
                }
            }
        }           

        redirect('frontend/myproperties/'.$this->data['lang_code']);
    }
    
    public function myprofile()
    {
        $this->check_login();
        $this->load_head_data();

        $this->data['content_language_id'] = $this->data['lang_id'];
        
        if($this->session->userdata('type') == 'USER')
        {
            // Load user data
            $this->data['user_data'] = $this->user_m->get_array($this->session->userdata('id'));
            
            $id = $this->data['user_data']['id'];
            //print_r($this->data['user_data']);
        }
        else
        {
             redirect('');
        }
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myprofile');
        $this->data['page_title'] = lang_check('Myprofile');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';

        // Fetch all files by repository_id
        $files = $this->file_m->get();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->zoom_enabled = false;
            $file->download_url = base_url('files/'.$file->filename);
            $file->delete_url = site_url_q('files/upload/rep_'.$file->repository_id, '_method=DELETE&amp;file='.rawurlencode($file->filename));

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
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
        $rules = $this->user_m->rules_admin;
        unset($rules['type'], $rules['language']);
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('frontend/myprofile/'.$this->data['lang_code'].'/');
                exit();
            }
            
            $data = $this->user_m->array_from_post(array('name_surname', 'mail', 'password', 'username',
                                                         'address', 'description', 'mail', 'phone', 
                                                         'facebook_id'));
            
            if($data['password'] == '')
            {
                unset($data['password']);
            }
            else
            {
                $data['password'] = $this->user_m->hash($data['password']);
            }
            
            $message_mail = '';
            if($this->data['user_data']['mail'] != $data['mail'] && config_db_item('email_activation_enabled') === TRUE)
            {
                $data['mail_verified'] = 0;
                // [START] Activation email
                
                if(ENVIRONMENT != 'development')
                if(!empty($data['mail']))
                {
                    $this->load->library('email');
                    $config_mail['mailtype'] = 'html';
                    $this->email->initialize($config_mail);
                    $this->email->from($this->data['settings_noreply'], lang_check('Web page'));
                    $this->email->to($data['mail']);
                    
                    $this->email->subject(lang_check('Activate your account'));
                    
                    $new_hash = substr($this->user_m->hash($data['mail'].$this->data['user_data']['id']), 0, 5);
                    
                    $data_m = array();
                    $data_m['name_surname'] = $data['name_surname'];
                    $data_m['username'] = $data['username'];
                    $data_m['activation_link'] = '<a href="'.site_url('admin/user/verifyemail/'.$this->data['user_data']['id'].'/'.$new_hash).'">'.lang_check('Activate your account').'</a>';
                    $data_m['login_link'] = '<a href="'.site_url('frontend/login/').'?username='.$this->data['user_data']['username'].'#content">'.lang_check('login_link').'</a>';
                    
                    $message = $this->load->view('email/email_activation', array('data'=>$data_m), TRUE);
                    
                    $this->email->message($message);
                    if ( ! $this->email->send())
                    {
                        $message_mail = ', '.lang_check('Problem sending email to user');
                    }
                }
                // [END] Activation email
            }
            
            if($this->data['user_data']['phone'] != $data['phone'] && !empty($data['phone']) &&
               config_db_item('clickatell_api_id') != FALSE && config_db_item('phone_verification_enabled') === TRUE &&
               file_exists(APPPATH.'libraries/Clickatellapi.php'))
            {
                $data['phone_verified'] = 0;
                
                //Send SMS for phone verification
                $new_hash = substr($this->user_m->hash($data['phone'].$this->data['user_data']['id']), 0, 5);
                
                $message='';
                $message.=lang_check('Your code').": \n";
                $message.=$new_hash."\n";
                $message.=lang_check('Verification link').": \n";
                $message.=site_url('admin/user/verifyphone/'.$this->data['user_data']['id'].'/'.$new_hash);
                
                $this->load->library('clickatellapi');
                $return_sms = $this->clickatellapi->send_sms($message, $data['phone']);
                
                if(substr_count($return_sms, 'successnmessage') == 0)
                {
                    $this->session->set_flashdata('error', $return_sms);
                }
            }
            
            $this->user_m->save($data, $id);

            $this->session->set_flashdata('message', 
                    '<p class="alert alert-success validation">'.lang_check('Changes saved').$message_mail.'</p>');
            
            redirect('frontend/myprofile/'.$this->data['lang_code'].'/');
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

        $output = $this->parser->parse($this->data['settings_template'].'/myprofile.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function editproperty()
    {
        $this->check_login();
        $this->load_head_data();

        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
        // If limit reached, error/warning!
        $this->load->model('packages_m');
        $this->load->model('treefield_m');
        $user = $this->user_m->get($this->session->userdata('id'));
        
        $package_mode=false;$package=NULL;
        if(file_exists(APPPATH.'controllers/admin/packages.php'))
        if($user->package_id > 0)
        {
            $package_mode=true;
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
                    redirect('frontend/myproperties/'.$this->data['lang_code'].'#content');
                    exit();
                }
                else if($package->package_days > 0 && strtotime($user->package_last_payment)<=time())
                {
                    $this->session->set_flashdata('error', 
                            lang_check('Date for your package expired, please extend'));
                    redirect('frontend/myproperties/'.$this->data['lang_code'].'#content');
                    exit();
                }
            }
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['estate'] = $this->estate_m->get_dynamic_array($id);
            
            if(count($this->data['estate']) == 0)
            {
                $this->data['errors'][] = 'Estate could not be found';
                redirect('frontend/myproperties/'.$this->data['lang_code'], 'refresh');
            }
            
            //Check if user have permissions
            if($this->data['estate']['agent'] == $this->session->userdata('id'))
            {
            
            }
            else
            {
                redirect('frontend/myproperties/'.$this->data['lang_code'], 'refresh');
                exit();                
            }
            
            //var_dump($this->data['estate']);
            
            // Fetch file repository
            $repository_id = $this->data['estate']['repository_id'];
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>'estate_m'));
                
                // Update page with new repository_id
                $this->estate_m->save(array('repository_id'=>$repository_id), $this->data['estate']['id']);
            }
        }
        else
        {            
            // Load estate data
            $this->data['estate'] = $this->estate_m->get_new_array();
        }
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Editproperty');
        $this->data['page_title'] = lang_check('Editproperty');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';

		// Pages for dropdown
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        
        // Get available agents
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'AGENT'));
        
        // Get all options
        foreach($this->option_m->languages as $key=>$val){
            $this->data['options_lang'][$key] = $this->option_m->get_lang(NULL, FALSE, $key);
        }
        $this->data['options'] = $this->option_m->get_lang_array(NULL, FALSE, $this->data['content_language_id']);
        
        $options_data = array();
        foreach($this->option_m->get() as $key=>$val)
        {
            $options_data[$val->id][$val->type] = 'true';
        }
        
        // Add rules for dynamic options
        $rules_dynamic = array();
        foreach($this->option_m->languages as $key_lang=>$val_lang){
            foreach($this->data['options'] as $key_option=>$val_option){
                $rules_dynamic['option'.$val_option['id'].'_'.$key_lang] = 
                    array('field'=>'option'.$val_option['id'].'_'.$key_lang, 'label'=>$val_option['option'], 'rules'=>'trim');
                //if($id == NULL)$this->data['estate']->{'option'.$val_option->id.'_'.$key_lang} = '';
                if(!isset($this->data['estate']))$this->data['estate']->{'option'.$val_option['id'].'_'.$key_lang} = '';
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

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
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
        $rules['date']['rules'] = 'trim';
        
        if(config_db_item('terms_link') !== FALSE)
        {
            $rules['option_agree_terms']['field'] = 'option_agree_terms';
            $rules['option_agree_terms']['label'] = 'lang:option_agree_terms';
            $rules['option_agree_terms']['rules'] = 'required';
            
            //echo $_POST['option_agree_terms'];
            //exit();
        }
        
        $this->form_validation->set_rules(array_merge($rules, $rules_dynamic));

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('frontend/editproperty/'.$this->data['lang_code'].'/'.$id);
                exit();
            }
            
            $data = $this->estate_m->array_from_post(array('gps', 'date', 'address', 'is_featured'));
            $dynamic_data = $this->estate_m->array_from_post(array_keys($rules_dynamic));
            
            if(empty($data['date']))
                $data['date'] = date('Y-m-d H:i:s');
            
            if(config_item('reactivation_enabled') === TRUE || $id == NULL)
                $data['is_activated'] = 0;
                
            if($package_mode === TRUE && isset($package->auto_activation))
            {
                if($package->auto_activation)
                    $data['is_activated'] = 1;
            }
            
            $data['search_values'] = $data['address'];
            foreach($dynamic_data as $key=>$val)
            {
                $pos = strpos($key, '_');
                $option_id = substr($key, 6, $pos-6);
                
                if(!isset($options_data[$option_id]['TEXTAREA'])){
                    $data['search_values'].=' '.$val;
                }
                
                // TODO: test check, values for each language for selected checkbox
                if(isset($options_data[$option_id]['CHECKBOX'])){
                    if($options_data[$option_id]['CHECKBOX'] == 'true')
                    {
                        foreach($this->option_m->languages as $key_lang=>$val_lang){
                            foreach($this->data['options'] as $key_option=>$val_option){
                                if($val_option['id'] == $option_id)
                                {
                                    $data['search_values'].=' '.$val_option['option'];
                                }
                            }
                        }
                    }
                }
            }
            
            $data['date_modified'] = date('Y-m-d H:i:s');
            
            $insert_id = $this->estate_m->save($data, $id);
            
            if($this->session->userdata('type') != 'ADMIN')
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
            
            if(is_numeric($insert_id))
            {
                $update_data = array();
                $update_data['search_values'] = 'id: '.$id.$data['search_values'];
                
                $this->estate_m->save($update_data, $insert_id);
            }
            
            if(isset($this->data['settings_email_alert']))
            if(isset($data['is_activated']) && $data['is_activated'] == 0 && $this->data['settings_email_alert'] == 1)
            {
                // Send email alert to contact address
                $this->load->library('email');
                
                $this->email->from($this->data['settings_noreply'], lang_check('Web page not-activated property'));
                $this->email->to($this->data['settings_email']);
                $this->email->subject(lang_check('Web page not-activated property'));
                
                $message='';
                $message.=lang_check('New not-activated property from user').": \n";
                $message.=$this->session->userdata('username')."\n\n";
                $message.=lang_check('Property edit link')." ($insert_id): \n";
                $message.=site_url('admin/estate/edit/'.$insert_id)."\n\n";
                
                $this->email->message($message);
                if ( ! $this->email->send())
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_false');
                }
                else
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_true');
                }
                
            }
            
            $this->session->set_flashdata('message', 
                    '<p class="alert alert-success">'.lang_check('Changes saved').'</p>');
            
            redirect('frontend/editproperty/'.$this->data['lang_code'].'/'.$insert_id);
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/editproperty.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function logout()
    {
        $this->user_m->logout();
        redirect($this->data['lang_code'], 'refresh');
    }
    
    public function login()
    {
        if($this->user_m->loggedin() == TRUE)
        {
    	    $dashboard = 'admin/dashboard';
            
            if($this->session->userdata('type') == 'USER')
            {
                if(config_item('enable_restricted_mode') === TRUE)
                {
                    redirect($this->data['lang_code']);
                }
                else
                {
                    redirect('frontend/myproperties/'.$this->data['lang_code']);
                }
            }
            else
            {
                if(config_item('enable_restricted_mode') === TRUE)
                {
                    redirect($this->data['lang_code']);
                }
                else
                {
                    redirect($dashboard);
                }
            }
        }
        
        $this->load_head_data();
        
        $current_language =  $this->language_m->get_name($this->data['lang_code']);
        if(empty($current_language))$current_language='';

        $user_facebook = FALSE;
        if($this->config->item('appId') != '')
        {
    		$this->load->library('facebook'); // Automatically picks appId and secret from config
    		$user_facebook = $this->facebook->getUser();
        }   
        
        if ($user_facebook) {
            try {
                $data['user_profile'] = $this->facebook->api('/me');

                // Register and login with Facebook if Facebook ID didn't exists'
                $user_face = $this->user_m->get_by(array('password'=>$this->user_m->hash($data['user_profile']['id']), 
                                                         'username'=>$data['user_profile']['email']), true);
                
                if(count($user_face) == 0)
                {
                    // Register user
                    $data_f['username'] = $data['user_profile']['email'];
                    $data_f['mail'] = $data['user_profile']['email'];
                    $data_f['password'] = $this->user_m->hash($data['user_profile']['id']);
                    $data_f['facebook_id'] = $data['user_profile']['link'];
                    $data_f['type'] = 'USER';
                    $data_f['name_surname'] = $data['user_profile']['name'];
                    $data_f['activated'] = '1';
                    $data_f['description'] = '';
                    $data_f['language'] = $current_language;
                    $data_f['registration_date'] = date('Y-m-d H:i:s');
                    $data_f['mail_verified'] = 0;
                    $data_f['phone_verified'] = 0;               
                    
                    if($this->config->item('def_package') !== FALSE)
                        $data_f['package_id'] = $this->config->item('def_package');
                    
                    $data_r['user_id'] = $this->user_m->save($data_f, NULL);
                } 
                
                // Login with facebook :: AUTO
                if($this->user_m->login($data['user_profile']['email'], $data['user_profile']['id']) == TRUE)
                {
                    redirect('frontend/myproperties/'.$this->data['lang_code']);
                    exit();
                }
                else
                {
                    $this->session->set_flashdata('error', 
                            lang_check('That email/password combination does not exists'));
                    redirect('frontend/login/'.$this->data['lang_code']); 
                    exit();
                }

            } catch (FacebookApiException $e) {
                $user = null;
                echo 'facebook loading error';
            }
        }else if($this->config->item('appId') != ''){
            $this->facebook->destroySession();
        }
        
        if(file_exists(APPPATH.'controllers/admin/packages.php'))
        {
            $this->load->model('packages_m');
            $this->data['packages'] = $this->packages_m->get();
            $this->data['packages_days'] = $this->packages_m->get_form_dropdown('package_days');
            $this->data['packages_listings'] = $this->packages_m->get_form_dropdown('num_listing_limit');
            $this->data['packages_price'] = $this->packages_m->get_form_dropdown('package_price');
        }

        $this->data['login_url_facebook'] = '';
        
        if ($user_facebook) {
            //echo 'logout';
            //exit();
            
            //$data['logout_url'] = site_url('facebookdemo/logout'); // Logs off application
            // OR 
            // Logs off FB!
            // $data['logout_url'] = $this->facebook->getLogoutUrl();

        } else if($this->config->item('appId') != ''){
            $this->data['login_url_facebook'] = $this->facebook->getLoginUrl(array(
                'redirect_uri' => site_url('frontend/login/'.$this->data['lang_code']), 
                'scope' => array("email") // permissions here
            ));
        }

        // Main page data
        $this->data['page_navigation_title'] = lang_check('Login');
        $this->data['page_title'] = lang_check('Login');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['is_registration'] = false;
        $this->data['is_login'] = false;

        // Set up the form for register
        if(isset($_POST['password_confirm']) || $this->session->flashdata('error_registration') != '')
        {
            $this->data['is_registration'] = true;
            
            $rules = $this->user_m->rules_admin;
            $rules['name_surname']['label'] = 'lang:FirstLast';
            $rules['password']['rules'] .= '|required';
            $rules['type']['rules'] = 'trim';
            $rules['language']['rules'] = 'trim';
            $rules['mail']['label'] = 'lang:Email';
            $rules['mail']['rules'] .= '|valid_email';
            
            if(config_db_item('register_reduced') === TRUE)
            {
                $rules['name_surname']['rules'] = 'trim|xss_clean';
                $rules['username']['rules'] = 'trim|xss_clean';
                
                $e_mail = $this->input->post('mail');
                if(!empty($e_mail))
                {
                    if(empty($_POST['username']))
                        $_POST['username'] = $e_mail;
                    if(empty($_POST['name_surname']))
                        $_POST['name_surname'] = $e_mail;
                }
            }
            
            if(config_item('captcha_disabled') === FALSE)
                $rules['captcha'] = array('field'=>'captcha', 'label'=>'lang:Captcha', 
                                          'rules'=>'trim|required|callback_captcha_check|xss_clean');
            
            $this->form_validation->set_rules($rules);
    
            // Process the form
            if($this->form_validation->run() == TRUE)
            {
                if($this->config->item('app_type') == 'demo')
                {
                    $this->session->set_flashdata('error_registration', 
                            lang_check('Data editing disabled in demo'));
                    redirect('frontend/login/'.$this->data['lang_code']);
                    exit();
                }
                
                $data = $this->user_m->array_from_post(array('name_surname', 'mail', 'password', 'username',
                                                             'address', 'description', 'mail', 'phone', 'type', 'language', 'activated'));
                if($data['password'] == '')
                {
                    unset($data['password']);
                }
                else
                {
                    $data['password'] = $this->user_m->hash($data['password']);
                }
                
                $data['type'] = 'USER';
                
                $data['activated'] = '1';
                if(config_db_item('email_activation_enabled') === TRUE)
                    $data['activated'] = '0';
                
                $data['description'] = '';
                $data['language'] = $current_language;
                $data['registration_date'] = date('Y-m-d H:i:s');
                $data['mail_verified'] = 0;
                $data['phone_verified'] = 0;

                if($this->config->item('def_package') !== FALSE)
                    $data['package_id'] = $this->config->item('def_package');
                
                $user_id = $this->user_m->save($data, NULL);
                
                $message_mail = '';

                if(!empty($data['mail']) && config_db_item('email_activation_enabled') === TRUE)
                {
                    $data['mail_verified'] = 0;
                    // [START] Activation email
                    
                    //if(ENVIRONMENT != 'development')
                    $this->load->library('email');
                    $config_mail['mailtype'] = 'html';
                    $this->email->initialize($config_mail);
                    $this->email->from($this->data['settings_noreply'], lang_check('Web page'));
                    $this->email->to($data['mail']);
                    
                    $this->email->subject(lang_check('Activate your account'));
                    
                    $new_hash = substr($this->user_m->hash($data['mail'].$user_id), 0, 5);
                    
                    $data_m = array();
                    $data_m['name_surname'] = $data['name_surname'];
                    $data_m['username'] = $data['username'];
                    $data_m['activation_link'] = '<a href="'.site_url('admin/user/verifyemail/'.$user_id.'/'.$new_hash).'">'.lang_check('Activate your account').'</a>';
                    $data_m['login_link'] = '<a href="'.site_url('frontend/login/').'?username='.$data['username'].'#content">'.lang_check('login_link').'</a>';
                    
                    $message = $this->load->view('email/email_activation', array('data'=>$data_m), TRUE);
                    
                    $this->email->message($message);
                    if ( ! $this->email->send())
                    {
                        $message_mail = ', '.lang_check('Problem sending email to user');
                    }
                    // [END] Activation email
                }

                if(!empty($data['phone']) && !empty($user_id) &&
                   config_db_item('clickatell_api_id') != FALSE && config_db_item('phone_verification_enabled') === TRUE &&
                   file_exists(APPPATH.'libraries/Clickatellapi.php'))
                {
                    $data['phone_verified'] = 0;
                    
                    //Send SMS for phone verification
                    $new_hash = substr($this->user_m->hash($data['phone'].$user_id), 0, 5);
                    
                    $message='';
                    $message.=lang_check('Your code').": \n";
                    $message.=$new_hash."\n";
                    $message.=lang_check('Verification link').": \n";
                    $message.=site_url('admin/user/verifyphone/'.$user_id.'/'.$new_hash);
                    
                    $this->load->library('clickatellapi');
                    $return_sms = $this->clickatellapi->send_sms($message, $data['phone']);
                    
                    if(substr_count($return_sms, 'successnmessage') == 0)
                    {
                        $this->session->set_flashdata('error_sms', $return_sms);
                    }
                }
                
                if(config_db_item('email_activation_enabled') === TRUE)
                {
                    $this->session->set_flashdata('error_registration', 
                            lang_check('Thanks on registration, please check and activate your email to login').$message_mail);
                }
                else
                {
                    $this->session->set_flashdata('error_registration', 
                            lang_check('Thanks on registration, you can login now').$message_mail);
                }

                redirect('frontend/login/'.$this->data['lang_code'], 'refresh');
            }
        }
        else
        {
            $this->data['is_login'] = true;
            
    	    $dashboard = 'admin/dashboard';
                       
            // Set form
            $rules = $this->user_m->rules;
            $this->form_validation->set_rules($rules);
            
            // Process form
            if($this->form_validation->run() == TRUE)
            {
                // We can login and redirect
                if($this->user_m->login() == TRUE)
                {
                    if(file_exists(APPPATH.'controllers/admin/booking.php') && config_item('reservations_disabled') === FALSE)
                    {
                        redirect('frontend/myreservations/'.$this->data['lang_code']);
                    }
                    
                    redirect('frontend/myproperties/'.$this->data['lang_code']);
                }
                else
                {
                    $this->session->set_flashdata('error', 
                            lang_check('That email/password combination does not exists'));
                    redirect('frontend/login/'.$this->data['lang_code']);                
                }
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/login.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function do_purchase()
    {
        $this->load->model('reservations_m');
        
        $this->check_login();
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        $price_pay = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
            $price_pay = $this->uri->segment(5);
        }

	    // Fetch a page or set a new one
	    if(!empty($id) && !empty($price_pay))
        {
    	    // Fetch all estates
            $this->data['reservation'] = $this->reservations_m->get_array_by(array('user_id' => $this->session->userdata('id'), 'id'=>$id), TRUE);
            $this->data['languages'] = $this->language_m->get_form_dropdown('language');
            $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
            $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'USER'));
            
            // Main page data
            $this->data['page_navigation_title'] = lang_check('Reservation').' :: '.date('Y-m-d', strtotime($this->data['reservation']['date_from'])).' - '.date('Y-m-d', strtotime($this->data['reservation']['date_to']));
            $this->data['page_title'] = lang_check('Reservation').' :: '.date('Y-m-d', strtotime($this->data['reservation']['date_from'])).' - '.date('Y-m-d', strtotime($this->data['reservation']['date_to']));
            $this->data['page_body']  = '';
            $this->data['page_description']  = '';
            $this->data['page_keywords']  = '';

            /* [Payment configuration] */
            
    		$config['business'] 			= $this->data['settings_paypal_email'];
    		$config['cpp_header_image'] 	= ''; //Image header url [750 pixels wide by 90 pixels high]
    		$config['return'] 				= site_url('frontend/myreservations/'.$this->data['lang_code']);
    		$config['cancel_return'] 		= site_url('frontend/cancel_payment/'.$this->data['lang_code']);
    		$config['notify_url'] 			= site_url('frontend/notify_payment/'.$this->data['lang_code']); //IPN Post
    		$config['production'] 			= (ENVIRONMENT == 'production'); //Its false by default and will use sandbox
    		//$config['discount_rate_cart'] 	= 0; //This means 20% discount
    		$config["invoice"]				= $this->data['reservation']['id'].'_RES_'.$price_pay;//.rand(1,10000); //The invoice id
            $config["currency_code"]        = $this->data['reservation']['currency_code'];
            
            if(empty($config['business']))
            {
                echo lang_check('PayPal email address missing');
                exit();
            }
            
    		$this->load->library('paypal', $config);
    		
    		#$this->paypal->add(<name>,<price>,<quantity>[Default 1],<code>[Optional]);
    		
    		$this->paypal->add('Reservation #'.$id, $price_pay, 1); //First item
    		//$this->paypal->add('Pants',1.99, 1); 	  //Second item
    		//$this->paypal->add('Blowse',10,10,'B-199-26'); //Third item with code
    		
    		$this->paypal->pay(); //Proccess the payment
            
            /* [/Payment configuration] */
        }
        else
        {
            redirect('frontend/myreservations/'.$this->data['lang_code']);
        }
    }
    
    public function do_purchase_package()
    {
        $this->load->model('packages_m');
        
        $this->check_login();
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        $price_pay = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
            $price_pay = $this->uri->segment(5);
        }
        
        $this->data['user'] = $this->user_m->get_array($this->session->userdata('id'));

	    // Fetch a page or set a new one
	    if(!empty($id) && !empty($price_pay))
        {
    	    // Fetch all estates
            $this->data['package'] = $this->packages_m->get_array($id);
            $this->data['languages'] = $this->language_m->get_form_dropdown('language');

            /* [Payment configuration] */
            
    		$config['business'] 			= $this->data['settings_paypal_email'];
    		$config['cpp_header_image'] 	= ''; //Image header url [750 pixels wide by 90 pixels high]
    		$config['return'] 				= site_url('frontend/myproperties/'.$this->data['lang_code']);
    		$config['cancel_return'] 		= site_url('frontend/cancel_payment/'.$this->data['lang_code']);
    		$config['notify_url'] 			= site_url('frontend/notify_payment/'.$this->data['lang_code']); //IPN Post
    		$config['production'] 			= (ENVIRONMENT == 'production'); //Its false by default and will use sandbox
    		//$config['discount_rate_cart'] 	= 0; //This means 20% discount
    		$config["invoice"]				= $this->data['package']['id'].'_PAC_'.$this->data['user']['id'].'_'.$price_pay.'_'.date('w');//.rand(1,10000); //The invoice id
            $config["currency_code"]        = $this->data['package']['currency_code'];
            
            if(empty($config['business']))
            {
                echo lang_check('PayPal email address missing');
                exit();
            }

    		$this->load->library('paypal', $config);
    		
    		#$this->paypal->add(<name>,<price>,<quantity>[Default 1],<code>[Optional]);
    		
    		$this->paypal->add('Package '.$this->data['package']['package_name'].'', $price_pay, 1); //First item
    		//$this->paypal->add('Pants',1.99, 1); 	  //Second item
    		//$this->paypal->add('Blowse',10,10,'B-199-26'); //Third item with code
    		
    		$this->paypal->pay(); //Proccess the payment
            
            /* [/Payment configuration] */
        }
        else
        {
            $this->session->set_flashdata('error_package', lang_check('Something goes wrong... contact admin please.'));
            redirect('frontend/myproperties/'.$this->data['lang_code']);
        }
    }
    
    public function do_purchase_activation()
    {
        $this->check_login();
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        $price_pay = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
            $price_pay = $this->uri->segment(5);
        }
        
        $this->data['user'] = $this->user_m->get_array($this->session->userdata('id'));

	    // Fetch a page or set a new one
	    if(!empty($id) && !empty($price_pay))
        {
    	    // Fetch all estates
            $this->data['languages'] = $this->language_m->get_form_dropdown('language');

            /* [Payment configuration] */
            
    		$config['business'] 			= $this->data['settings_paypal_email'];
    		$config['cpp_header_image'] 	= ''; //Image header url [750 pixels wide by 90 pixels high]
    		$config['return'] 				= site_url('frontend/myproperties/'.$this->data['lang_code']);
    		$config['cancel_return'] 		= site_url('frontend/cancel_payment/'.$this->data['lang_code']);
    		$config['notify_url'] 			= site_url('frontend/notify_payment/'.$this->data['lang_code']); //IPN Post
    		$config['production'] 			= (ENVIRONMENT == 'production'); //Its false by default and will use sandbox
    		//$config['discount_rate_cart'] 	= 0; //This means 20% discount
    		$config["invoice"]				= $id.'_ACT_'.$this->data['user']['id'].'_'.$price_pay.'_'.date('w');//.rand(1,10000); //The invoice id
            
            if(isset($this->data['settings_default_currency']))
            {
                $config["currency_code"] = $this->data['settings_default_currency'];
            }
            else
            {
                $config["currency_code"] = 'USD';
            }

            if(empty($config['business']))
            {
                echo lang_check('PayPal email address missing');
                exit();
            }

    		$this->load->library('paypal', $config);
    		
    		#$this->paypal->add(<name>,<price>,<quantity>[Default 1],<code>[Optional]);
    		
    		$this->paypal->add('Activation property #'.$id.'', $price_pay, 1); //First item
    		//$this->paypal->add('Pants',1.99, 1); 	  //Second item
    		//$this->paypal->add('Blowse',10,10,'B-199-26'); //Third item with code
    		
    		$this->paypal->pay(); //Proccess the payment
            
            /* [/Payment configuration] */
        }
        else
        {
            $this->session->set_flashdata('error_package', lang_check('Something goes wrong... contact admin please.'));
            redirect('frontend/myproperties/'.$this->data['lang_code']);
        }
    }

    public function do_purchase_featured()
    {
        $this->check_login();
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        $price_pay = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
            $price_pay = $this->uri->segment(5);
        }
        
        $this->data['user'] = $this->user_m->get_array($this->session->userdata('id'));

	    // Fetch a page or set a new one
	    if(!empty($id) && !empty($price_pay))
        {
    	    // Fetch all estates
            $this->data['languages'] = $this->language_m->get_form_dropdown('language');

            /* [Payment configuration] */
            
    		$config['business'] 			= $this->data['settings_paypal_email'];
    		$config['cpp_header_image'] 	= ''; //Image header url [750 pixels wide by 90 pixels high]
    		$config['return'] 				= site_url('frontend/myproperties/'.$this->data['lang_code']);
    		$config['cancel_return'] 		= site_url('frontend/cancel_payment/'.$this->data['lang_code']);
    		$config['notify_url'] 			= site_url('frontend/notify_payment/'.$this->data['lang_code']); //IPN Post
    		$config['production'] 			= (ENVIRONMENT == 'production'); //Its false by default and will use sandbox
    		//$config['discount_rate_cart'] 	= 0; //This means 20% discount
    		$config["invoice"]				= $id.'_FEA_'.$this->data['user']['id'].'_'.$price_pay.'_'.date('w');//.rand(1,10000); //The invoice id
            
            if(isset($this->data['settings_default_currency']))
            {
                $config["currency_code"] = $this->data['settings_default_currency'];
            }
            else
            {
                $config["currency_code"] = 'USD';
            }

            if(empty($config['business']))
            {
                echo lang_check('PayPal email address missing');
                exit();
            }

    		$this->load->library('paypal', $config);
    		
    		#$this->paypal->add(<name>,<price>,<quantity>[Default 1],<code>[Optional]);
    		
    		$this->paypal->add('Featured property #'.$id.'', $price_pay, 1); //First item
    		//$this->paypal->add('Pants',1.99, 1); 	  //Second item
    		//$this->paypal->add('Blowse',10,10,'B-199-26'); //Third item with code
    		
    		$this->paypal->pay(); //Proccess the payment
            
            /* [/Payment configuration] */
        }
        else
        {
            $this->session->set_flashdata('error_package', lang_check('Something goes wrong... contact admin please.'));
            redirect('frontend/myproperties/'.$this->data['lang_code']);
        }
    }

    public function notify_payment()
    {
//        Array
//        (
//            [mc_gross] => 2.99
//            [invoice] => 12373469
//            [protection_eligibility] => Eligible
//            [address_status] => confirmed
//            [item_number1] => 
//            [payer_id] => ER2LXCHKVY38Q
//            [tax] => 0.00
//            [address_street] => 1 Main St
//            [payment_date] => 12:56:41 Jun 03, 2014 PDT
//            [payment_status] => Completed
//            [charset] => windows-1252
//            [address_zip] => 95131
//            [mc_shipping] => 0.00
//            [mc_handling] => 0.00
//            [first_name] => Info
//            [mc_fee] => 0.39
//            [address_country_code] => US
//            [address_name] => Info Winter
//            [notify_version] => 3.8
//            [custom] => 
//            [payer_status] => verified
//            [business] => sandi@iwinter.com.hr
//            [address_country] => United States
//            [num_cart_items] => 1
//            [mc_handling1] => 0.00
//            [address_city] => San Jose
//            [verify_sign] => AI36sk2Aln3iC.t.mla1wMizPRcQA8RKkhVgDKLdhbV.2hZelzrKnqbO
//            [payer_email] => info@iwinter.com.hr
//            [mc_shipping1] => 0.00
//            [tax1] => 0.00
//            [txn_id] => 5NJ254081K680701C
//            [payment_type] => instant
//            [last_name] => Winter
//            [address_state] => CA
//            [item_name1] => T-shirt
//            [receiver_email] => sandi@iwinter.com.hr
//            [payment_fee] => 0.39
//            [quantity1] => 1
//            [receiver_id] => S63XQYGHM4X8N
//            [txn_type] => cart
//            [mc_gross_1] => 2.99
//            [mc_currency] => USD
//            [residence_country] => US
//            [test_ipn] => 1
//            [transaction_subject] => 
//            [payment_gross] => 2.99
//            [ipn_track_id] => 9d77baf4e8f10
//        )
        
        $this->load->model('reservations_m');
        
        $received_post = $this->input->post();

        $data = array();
        $data['invoice_num'] = $received_post['invoice'];
        $data['date_paid'] = date('Y-m-d H:i:s');
        $data['data_post'] = serialize($received_post);
        $data['payer_id'] = $received_post['payer_id'];
        $data['txn_id'] = $received_post['txn_id'];
        $data['paid'] = $received_post['mc_gross'];
        $data['currency_code'] = $received_post['mc_currency'];
        $data['payer_email'] = $received_post['payer_email'];
        
        $this->load->model('payments_m');
        $this->payments_m->save($data);
        
        // update reservation
        $inv_ex = explode('_', $data['invoice_num']);

        if($inv_ex[1] == 'RES'){
            $table_id = $inv_ex[0];
            
            // Set reservations paid
            $reservation = $this->reservations_m->get_array_by(array('id'=>$table_id), TRUE);
            
            $data_r = array();
            
            if(empty($reservation['total_paid']))
                $reservation['total_paid'] = 0;
    
            $data_r['total_paid'] = $reservation['total_paid'] + $data['paid'];
            
            if($data_r['total_paid'] >= $reservation['total_price'])
            {
                $data_r['date_paid_total'] = date('Y-m-d H:i:s');
            }
            else
            {
                $data_r['date_paid_advance'] = date('Y-m-d H:i:s');
            }
            
            $data_r['is_confirmed'] = '1';
            
            $this->reservations_m->save($data_r, $table_id);
        }
        else if($inv_ex[1] == 'PAC')
        {
            $table_id = $inv_ex[2];
            $package_id = $inv_ex[0];
            
            // check if extend or buy
            $user = $this->user_m->get($table_id);
            $from_time = time();
            if(strtotime($user->package_last_payment) > $from_time)
                $from_time = strtotime($user->package_last_payment);
            
            $this->load->model('packages_m');
            $package = $this->packages_m->get($package_id);
            $days_extend = $package->package_days;
            
            // Set package paid
            $data_r = array();
            $data_r['package_last_payment'] = date('Y-m-d H:i:s', $from_time + 86400*intval($days_extend));
            $data_r['package_id'] = $package_id;
            
            $this->user_m->save($data_r, $table_id);
        }
        else if($inv_ex[1] == 'ACT')
        {
            $table_id = $inv_ex[2];
            $property_id = $inv_ex[0];
            
            // check if extend or buy
            $this->load->model('estate_m');
            $estate = $this->estate_m->get($property_id);
            
            // Set package paid
            $data_r = array();
            $data_r['is_activated'] = '1';
            $data_r['activation_paid_date'] = date('Y-m-d H:i:s');
            
            $this->estate_m->save($data_r, $property_id);
        }
        else if($inv_ex[1] == 'FEA')
        {
            $table_id = $inv_ex[2];
            $property_id = $inv_ex[0];
            
            // check if extend or buy
            $this->load->model('estate_m');
            $estate = $this->estate_m->get($property_id);
            
            // Set package paid
            $data_r = array();
            $data_r['is_featured'] = '1';
            $data_r['featured_paid_date'] = date('Y-m-d H:i:s');
            
            $this->estate_m->save($data_r, $property_id);
        }
        
        exit();
    }
    
    public function cancel_payment()
    {
        $this->session->set_flashdata('error', 
                lang_check('Payment canceled'));
        redirect('frontend/myreservations/'.$this->data['lang_code']);    
    }
    
    public function login_book()
    {
        if($this->user_m->loggedin() == TRUE)
        {
    	    $dashboard = 'admin/dashboard';
            
            if($this->session->userdata('type') == 'USER')
            {
                redirect('frontend/myproperties/'.$this->data['lang_code']);
            }
            else
            {
                redirect($dashboard);
            }
        }
        
        $data_r = unserialize($this->session->flashdata('data_r'));
        
        if(!isset($data_r['date_from']))
            redirect('');
        
        $this->session->keep_flashdata('data_r');
        
        $this->data['reservation'] = $data_r;
        
        $this->load_head_data();
        $this->data['content_language_id'] = $this->data['lang_id'];
        
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Register and book online');
        $this->data['page_title'] = lang_check('Register and book online');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';

        // Set up the form for register and book online
        
        $rules = $this->user_m->rules_admin;
        $rules['name_surname']['label'] = 'lang:FirstLast';
        $rules['password']['rules'] .= '|required';
        $rules['type']['rules'] = 'trim';
        $rules['language']['rules'] = 'trim';
        $rules['mail']['label'] = 'lang:Email';
        $rules['mail']['rules'] .= '|valid_email|is_unique[user.mail]';
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error_registration', 
                        lang_check('Data editing disabled in demo'));
                redirect('frontend/login/'.$this->data['lang_code']);
                exit();
            }
            
            // Register and book
            
            $data = $this->user_m->array_from_post(array('name_surname', 'mail', 'password', 'username',
                                                         'address', 'description', 'mail', 'phone', 'type', 'language', 'activated'));
            if($data['password'] == '')
            {
                unset($data['password']);
            }
            else
            {
                $data['password'] = $this->user_m->hash($data['password']);
            }
            
            $data['type'] = 'USER';
            $data['activated'] = '1';
            $data['description'] = '';
            $data['language'] = '';
            $data['registration_date'] = date('Y-m-d H:i:s');
            
            if($this->config->item('def_package') !== FALSE)
                $data['package_id'] = $this->config->item('def_package');
            
            $data_r['user_id'] = $this->user_m->save($data, NULL);
            
            // save reservation
            $this->load->model('reservations_m');
            $reservation_id = $this->reservations_m->save($data_r, NULL);

            // auto login
            if($this->user_m->login() == TRUE)
            {
                // view reservation
                $this->session->set_flashdata('error_registration', 
                        lang_check('Thanks on registration, you can book now'));
                
                redirect('frontend/viewreservation/'.$this->data['lang_code'].'/'.$reservation_id, 'refresh');
            }
            else
            {
                $this->session->set_flashdata('error', 
                        lang_check('That email/password combination does not exists'));
                redirect('frontend/login/'.$this->data['lang_code']); 
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/login_book.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }

    private function _custom_search_filtering(&$res_array, $options, $post_option)
    {
        foreach($res_array as $key=>$row)
        {
            foreach($post_option as $key1=>$val1)
            {
                if(is_numeric($val1) && $key1 != 'smart')
                {
                    $option_num = $key1;

                    if(strrpos($option_num, 'from') > 0)
                    {
                        $option_num = substr($option_num,0,-5);
                        
                        // For rentable
                        if($option_num == 36 && isset($this->data['is_purpose_rent'][0]['count']))
                            $option_num++;
                        
                        if(!isset($this->data['is_purpose_rent'][0]['count']) &&
                                !isset($this->data['is_purpose_sale'][0]['count']))
                        {
                            if( ($options[$row['id']][$option_num] < $val1 || empty($options[$row['id']][$option_num])) && 
                                ($options[$row['id']][$option_num+1] < $val1 || empty($options[$row['id']][$option_num+1]))  )
                            {
                                unset($res_array[$key]);
                            }
                        }
                        else if(!isset($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] < $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                    else if(strrpos($option_num, 'to') > 0)
                    {
                        $option_num = substr($option_num,0,-3);
                        
                        // For rentable
                        if($option_num == 36 && isset($this->data['is_purpose_rent'][0]['count']))
                            $option_num++;
                        
                        if(!isset($this->data['is_purpose_rent'][0]['count']) &&
                                !isset($this->data['is_purpose_sale'][0]['count']))
                        {
                            if(!isset($options[$row['id']][$option_num]))
                            {
                                unset($res_array[$key]);
                            }
                            else
                            {
//                                echo $val1."\r\n";
//                                echo $options[$row['id']][$option_num]."\r\n";
//                                echo $options[$row['id']][$option_num+1]."\r\n";
                                
                                if( ($options[$row['id']][$option_num] > $val1 || empty($options[$row['id']][$option_num])) && 
                                    ($options[$row['id']][$option_num+1] > $val1 || empty($options[$row['id']][$option_num+1]) || $row['id'] != 36 )  )
                                {
                                    unset($res_array[$key]);
//                                    echo "unset\r\n";
                                }
                            }
                        }
                        else if(!isset($options[$row['id']][$option_num]) || empty($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] > $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                    else
                    {
                        if(!isset($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] != $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                }
            }
        }
    }
    
    public function typeahead ()
    {
        $q = $this->input->post('q');
        $limit = $this->input->post('limit');
        $option_id = (string) $this->uri->segment(5);
        $option_ids = array(5,7,40);
        $language_id = $this->data['lang_id'];
        
        if(is_array(config_item('additional_typeahead_ids')))
        {
            $option_ids = array_merge($option_ids, config_item('additional_typeahead_ids'));
        }
        
        if($option_id != 'smart')
        {
            $option_ids = array(intval($option_id));
        }
        
        if($limit == '')
        {
            $limit = 8;
        }
        
        if(empty($q))
        {
            echo json_encode(array());
            exit();
        }

        $results = $this->option_m->get_typeahead($q, $limit, $option_ids, $language_id);
        
        $this->output->enable_profiler(FALSE);
        echo json_encode($results);
        //echo '["Electric Light Orchestra", "Elvis Costello", "Eric Clapton"]';
        //exit();
    }

    public function ajax ($page_id)
    {
        // Prevent direct access for google and simmilar
        if(!isset($_POST['page_num']))
            redirect('');

        $this->load->model('treefield_m');
        
        // Fetch post values
        $address = $this->input->post('address');
        $order = $this->input->post('order');
        $view = $this->input->post('view');

        $post_option = array();
        $post_option_sum = ' ';
        foreach($_POST as $key=>$val)
        {
            $tmp_post = $this->input->post($key);
            if(!empty($tmp_post) && strrpos($key, 'tion_') > 0){
                $post_option[$key] = $tmp_post;
                $post_option_sum.=$tmp_post.' ';
            }
            
            if(is_array($tmp_post))
            {
                $category_num = substr($key, strrpos($key, 'gory_')+5);
                
                foreach($tmp_post as $key=>$val)
                {
                    $post_option['0'.$category_num.'9999'.$key] = $val;
                    $post_option_sum.=$val.' ';
                }
            }
        }
        // End fetch post values     
        
        $lang_id = $this->data['lang_id'];
        
        /* Define order */
        if(empty($order))$order='id DESC';

        $this->data['order_dateASC_selected'] = '';
        if($order=='id ASC')
            $this->data['order_dateASC_selected'] = 'selected';
            
        $this->data['order_dateDESC_selected'] = '';
        if($order=='id DESC')
            $this->data['order_dateDESC_selected'] = 'selected';
            
        $this->data['order_priceASC_selected'] = '';
        if($order=='price ASC')
            $this->data['order_priceASC_selected'] = 'selected';
            
        $this->data['order_priceDESC_selected'] = '';
        if($order=='price DESC')
            $this->data['order_priceDESC_selected'] = 'selected';

        $this->data['order_livingarea_selected'] = '';
        if($order=='livingArea')
            $this->data['order_livingarea_selected'] = 'selected';
        /* End define order */
        
        /* Define view */
        if(empty($view))$view='grid';
        
        $this->data['view_grid_selected'] = '';
        $this->data['has_view_grid'] = array();
        if($view=='grid')
        {
            $this->data['view_grid_selected'] = 'active';
            $this->data['has_view_grid'][] = array('view' => 'grid');
        }
        
        $this->data['view_list_selected'] = '';
        $this->data['has_view_list'] = array();
        if($view=='list')
        {
            $this->data['view_list_selected'] = 'active';
            $this->data['has_view_list'][] = array('view' => 'list');
        }
        /* End define view */  
        
        /* Define purpose */
        $this->data['is_purpose_rent'] = array();
        $this->data['is_purpose_sale'] = array();
        //$this->data['is_purpose_sale'][] = array('count'=>'1');
        
        
        
        if(strpos($post_option_sum, lang_check('Rent')) !== FALSE)
        {
            $this->data['is_purpose_rent'][] = array('count'=>'1');
            $order = str_replace("price", "field_37_int", $order);
        }
        if(strpos($post_option_sum, lang_check('Sale')) !== FALSE)
        {
            $this->data['is_purpose_sale'][] = array('count'=>'1');
        }
        
        $order = str_replace("price", "field_36_int", $order);
        
//        if(strpos($post_option_sum, lang_check('Rent')) !== FALSE && 
//           strpos($post_option_sum, lang_check('Sale')) !== FALSE)
//        {
//            $this->data['is_purpose_rent'][] = array('count'=>'1');
//        }
//        else if(strpos($post_option_sum, lang_check('Rent')) !== FALSE)
//        {
//            $this->data['is_purpose_sale'] = array();
//            $this->data['is_purpose_rent'][] = array('count'=>'1');
//        }
        /* End define purpose */
        
        /* Pagination configuration */ 
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '<ul>';
        
        $config['base_url'] = $this->data['ajax_load_url'];
        $config['total_rows'] = 200;
        $config['per_page'] = config_item('per_page');
        $config['uri_segment'] = 5;
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        /* End Pagination */
        
        //$options = $this->option_m->get_options($lang_id);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);
        
        /* [Booking check availability] */
        $available_properties = NULL;
        if(file_exists(APPPATH.'controllers/admin/booking.php'))
        {
            $this->load->model('reservations_m');
            
            $booking_date_from = $this->input->post('v_booking_date_from');
            $booking_date_to = $this->input->post('v_booking_date_from');
            
            if(!empty($booking_date_from) && !empty($booking_date_to))
            {
                $available_properties = $this->reservations_m->get_available_properties($booking_date_from, $booking_date_to);
            }
        }
        /* [/Booking check availability] */

        /* Search */
        $offset_properties = $this->data['pagination_offset'];
        
        $search_array = $post_option;
        
        $where = array();
        $where['is_activated'] = 1;
        $where['language_id']  = $lang_id;
        
        if(isset($this->data['settings_listing_expiry_days']))
        {
            if(is_numeric($this->data['settings_listing_expiry_days']) && $this->data['settings_listing_expiry_days'] > 0)
            {
                 $where['property.date_modified >']  = date("Y-m-d H:i:s" , time()-$this->data['settings_listing_expiry_days']*86400);
            }
        }

        $data = array();
        
        $this->data['options_name'] = array();
        $this->data['options_suffix'] = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_prefix_'.$row->option_id] = $row->prefix;
        }
        
        // Fetch all files by repository_id
        $files = array();
//        $files = $this->file_m->get();
//        foreach($files as $key=>$file)
//        {
//            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
//            $file->url = base_url('files/'.$file->filename);
//            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
//            {
//                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
//                $this->data['images_'.$file->repository_id][] = $file;
//            }
//        }
        
        /* End fetch files */

        /* Get estates for map data */
        $results_obj = $this->estate_m->get_by($where, false, 100, 'property.is_featured DESC, '.$order, 
                                               NULL, $search_array, $available_properties);
        
        //echo $this->db->last_query();
        
        $this->data['has_no_results'] = array();
        if(count($results_obj) == 0)
            $this->data['has_no_results'][] = array('count'=>count($results_obj));
        
        /* Get all estates data for json */
        $this->data['results_json'] = array();
        foreach($results_obj as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = $estate_obj->address;
            $estate['date'] = $estate_obj->date;
            $estate['repository_id'] = $estate_obj->repository_id;
            $estate['is_featured'] = $estate_obj->is_featured;
            
            $json_obj = json_decode($estate_obj->json_object);
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
                    if(substr($row1, -2) == ' -')$row1=substr($row1, 0, -2);
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';

                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                }
            }
            
            // [START] custom price field
            $estate['custom_price'] = '';
            if(!empty($estate_obj->field_36))
                $estate['custom_price'].=$this->data['options_prefix_36'].$estate_obj->field_36.$this->data['options_suffix_36'];
            if(!empty($estate_obj->field_37))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_37'].$estate_obj->field_37.$this->data['options_suffix_37'];
            }
                
            if(empty($estate_obj->field_37) && !empty($estate_obj->field_56))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_56'].$estate_obj->field_56.$this->data['options_suffix_56'];
            }
            // [END] custom price field

            $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
            if(isset($json_obj->field_6))
            {
                if($json_obj->field_6 != '' && $json_obj->field_6 != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].$json_obj->field_6.'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].$json_obj->field_6.'.png';
                }
            }
            
            // Url to preview
            if(isset($json_obj->field_10))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code'].'/'.url_title_cro($json_obj->field_10));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($estate_obj->image_filename))
            {
                $estate['thumbnail_url_json'] = base_url('files/thumbnail/'.$estate_obj->image_filename);
            }
            else
            {
                $estate['thumbnail_url_json'] = base_url('templates/'.$this->data['settings_template']).'/assets/img/no_image.jpg';
            }
            
            $estate_obj_gen = new StdClass;
            $estate_obj_options = new StdClass;
            $estate_obj_options->icon = base_url('templates/'.$this->data['settings_template']).'/'.$estate['icon'];
            
            if(!empty($json_obj->field_6))
            {
                $estate_obj_options->cssclass = $json_obj->field_6;
            }
            else
            {
                $estate_obj_options->cssclass = 'not-defined';
            }

            $gps_coo = explode(', ', $estate_obj->gps);
            if(count($gps_coo) == 2)$estate_obj_gen->latLng = array(floatval($gps_coo[0]), floatval($gps_coo[1]));
            $estate_obj_gen->options = $estate_obj_options;
            if(!isset($estate['option_2']))$estate['option_2'] = '{option_2}';
            if(!isset($estate['option_4']))$estate['option_4'] = '{option_4}';
            if($this->data['settings_template'] != 'realia')
            $estate_obj_gen->data = "<img style=\"width: 150px; height: 100px;\" src=\"".$estate['thumbnail_url_json']."\" /><br />".
                                $estate_obj->address."<br />".$estate['option_2']."<br /><span class=\"label label-info\">&nbsp;&nbsp;".$estate['option_4']."&nbsp;&nbsp;</span>".
                                "<br /><a href=\"".$estate['url']."\">".lang('Details')."</a>";
            if($this->data['settings_template'] == 'realia')
            $estate_obj_gen->data_realia = "<div class=\"image\"><img style=\"width: 100px; height: 74px;\" src=\"".$estate['thumbnail_url_json']."\" alt=\"\"></div><div class=\"title\"><a href=\"".$estate['url']."\">".$estate['option_10']."</a></div><div class=\"area\"><span class=\"key\">".$this->data['options_name_3'].":</span><span class=\"value\">".$estate['option_3'].$this->data['options_suffix_3']."</span></div>".$estate['option_2']."&nbsp;&nbsp;<span class=\"label label-info\">&nbsp;&nbsp;".$estate['option_4']."&nbsp;&nbsp;</span><div class=\"price\">".
                                       (!empty($estate['option_36'])?$this->data['options_prefix_36']." ".$estate['option_36']." ".$this->data['options_suffix_36']:'').(!empty($estate['option_37'])?$this->data['options_prefix_36']." ".$estate['option_37']." ".$this->data['options_suffix_36']:'').
                                       "</div><div class=\"link\"><a href=\"".$estate['url']."\">".lang('Details')."</a></div>";

            
            "<img style=\"width: 150px; height: 100px;\" src=\"".$estate['thumbnail_url_json']."\" /><br />".
                                $estate_obj->address."<br />".$estate['option_2']."<br /><span class=\"label label-info\">&nbsp;&nbsp;".$estate['option_4']."&nbsp;&nbsp;</span>".
                                "<br /><a href=\"".$estate['url']."\">".lang('Details')."</a>";
            $estate_obj_gen->adr = $estate_obj->address;
            
            $this->data['results_json'][] = $estate_obj_gen;
        }
        
        $results_center = calculateCenterArray($results_obj);

        /* Get all estates data */
        $config['total_rows'] = $this->estate_m->count_get_by($where, false, NULL, 'property.is_featured DESC, '.$order, 
                                               NULL, $search_array, $available_properties);
        
        /* Pagination in query */
        $this->data['total_rows'] = $config['total_rows'];
        
        $estates = $this->estate_m->get_by($where, false, $config['per_page'], 'property.is_featured DESC, '.$order, 
                                               $offset_properties, $search_array, $available_properties);
        
        $this->data['results'] = array();
        foreach($estates as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr->id;
            $estate['gps'] = $estate_arr->gps;
            $estate['address'] = $estate_arr->address;
            $estate['date'] = $estate_arr->date;
            $estate['repository_id'] = $estate_arr->repository_id;
            $estate['is_featured'] = $estate_arr->is_featured;
            $estate['counter_views'] = $estate_arr->counter_views;
            $estate['estate_data_id'] = $estate_arr->id;
            $estate['icons'] = array();
            
            $json_obj = json_decode($estate_arr->json_object);
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
                    if(substr($row1, -2) == ' -')$row1=substr($row1, 0, -2);
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                }
            }
            
            // [START] custom price field
            $estate['custom_price'] = '';
            if(!empty($estate['option_36']))
                $estate['custom_price'].=$this->data['options_prefix_36'].$estate['option_36'].$this->data['options_suffix_36'];
            if(!empty($estate['option_37']))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_37'].$estate['option_37'].$this->data['options_suffix_37'];
            }
                
            if(empty($estate['option_37']) && !empty($estate['option_56']))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_56'].$estate['option_56'].$this->data['options_suffix_56'];
            }
            // [END] custom price field
            
            // Url to preview
            if(isset($json_obj->field_10))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr->id.'/'.$this->data['lang_code'].'/'.url_title_cro($json_obj->field_10));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($estate_arr->image_filename))
            {
                $estate['thumbnail_url'] = base_url('files/thumbnail/'.$estate_arr->image_filename);
                $estate['thumbnail_url_json'] = $estate['thumbnail_url'];
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
                $estate['thumbnail_url_json'] = base_url('templates/'.$this->data['settings_template']).'/assets/img/no_image.jpg';
            }
            
            // [agent second image]
            if(isset($estate_arr->agent_rep_id))
            if(isset($this->data['images_'.$estate_arr->agent_rep_id]))
            {
                if(isset($this->data['images_'.$estate_arr->agent_rep_id][1]))
                $estate['agent_sec_img_url'] = $this->data['images_'.$estate_arr->agent_rep_id][1]->thumbnail_url;
            }
            
            $estate['has_agent_sec_img'] = array();
            if(isset($estate['agent_sec_img_url']))
                $estate['has_agent_sec_img'][] = array('count'=>'1');
            // [/agent second image]

            $this->data['results'][] = $estate;
        }

        /* Pagination load */ 
        $this->pagination->initialize($config);
        $this->data['pagination_links'] =  $this->pagination->create_links();
        /* End Pagination */
        
        $output = $this->parser->parse($this->data['settings_template'].'/results.php', $this->data, TRUE);
        $output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
        
        header('Content-Type: application/json');
        echo json_encode(array('results'=>$this->data['results_json'], 'results_center'=>$results_center, 'print' => $output, 'order'=>$order, 'lang_id'=>$lang_id, 'total_rows'=>$config['total_rows']));
        exit();
    }
    
	public function index ()
	{
        $lang_id = $this->data['lang_id'];
        
        // Fetch all files by repository_id
        
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        $this->data['page_files'] = array();
        
        $where_in = array($this->temp_data['page']->repository_id);
        
        $this->load->model('ads_m');
        $ads_act = $this->ads_m->get_by(array('is_activated'=>1));
        foreach($ads_act as $row)
        {
            $where_in[] = $row->repository_id;
        }

        // Fetch all files by repository_id
        $files = $this->file_m->get_where_in($where_in);
        $rep_file_count = array();
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        $this->data['page_files'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
                $this->data['documents_'.$file->repository_id][] = $file;
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_documents'][] = $file;
                }
            }
            
            $this->data['files_'.$file->repository_id][] = $file;

            if($this->temp_data['page']->repository_id == $file->repository_id)
            {
                $this->data['page_files'][] = $file;
            }
        }
        
        
        // Has attributes
        $this->data['has_page_documents'] = array();
        if(count($this->data['page_documents']))
            $this->data['has_page_documents'][] = array('count'=>count($this->data['page_documents']));
        
        $this->data['has_page_images'] = array();
        if(count($this->data['page_images']))
            $this->data['has_page_images'][] = array('count'=>count($this->data['page_images']));
            
        $this->data['has_page_files'] = array();
        if(count($this->data['page_files']))
            $this->data['has_page_files'][] = array('count'=>count($this->data['page_files']));
        /* End fetch files */

        /* Helpers */
        $this->data['year'] = date('Y');
        /* End helpers */
        
        /* Widgets functions */
        $this->data['print_menu'] = get_menu($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_menu_realia'] = get_menu_realia($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_lang_menu'] = get_lang_menu($this->language_m->get_array_by(array('is_frontend'=>1)), $this->data['lang_code']);
        $this->data['page_template'] = $this->temp_data['page']->template;
        /* End widget functions */

        // [JSON_SEARCH]
        // Example: ?search={"search_option_smart": "zagreb"}
        $search_json = NULL;
        if(isset($_GET['search']))$search_json = json_decode($_GET['search']);
        
        if($search_json !== FALSE && $search_json !== NULL)
        {
            $post_option = array();
            $post_option_sum = ' ';
            
            if(is_array($search_json))
            foreach($search_json as $key=>$val)
            {
                $tmp_post = $val;
                if(!empty($tmp_post) && strrpos($key, 'tion_') > 0){
                    $post_option[substr($key, strrpos($key, 'tion_')+5)] = $tmp_post;
                    $post_option_sum.=$tmp_post.' ';
                }
                
                if(is_array($tmp_post))
                {
                    $category_num = substr($key, strrpos($key, 'gory_')+5);
                    
                    foreach($tmp_post as $key=>$val)
                    {
                        $post_option['0'.$category_num.'9999'.$key] = $val;
                        $post_option_sum.=$val.' ';
                    }
                }
            }

            $this->data['search_query'] = '';
            if(!empty($post_option['smart']))
                $this->data['search_query'] = $post_option['smart'];
        }

        $this->g_post_option = &$post_option;
        
        // [/JSON_SEARCH]

        /* Define purpose */
        $purpose = '';
        $this->data['purpose_rent_active'] = '';
        $this->data['purpose_sale_active'] = '';
        
        $this->data['is_purpose_rent'] = array();
        $this->data['is_purpose_sale'] = array();
        
        if(strpos($this->temp_data['page']->template, 'rent') !== FALSE)
        {
            $purpose = 'rent';
            $this->data['purpose_rent_active'] = 'active';
            $this->data['is_purpose_rent'][] = array('count'=>'1');
        }
        else if(strpos($this->temp_data['page']->template, 'sale') !== FALSE ||
                (strpos($this->temp_data['page']->template, 'home') !== FALSE && config_item('all_results_default') !== TRUE))
        {
            $purpose = 'sale';
            $this->data['purpose_sale_active'] = 'active';
            $this->data['is_purpose_sale'][] = array('count'=>'1');
        }
        
        /* Get all estates data */
        $estates = $this->estate_m->get_by(array('is_activated' => 1, 'language_id' => $lang_id), FALSE, 100);
        //$options = $this->option_m->get_options($this->data['lang_id']);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        $this->data['search_purpose'] = NULL;
        
        $this->data['all_estates'] = array();
        $this->data['featured_properties'] = array();
        foreach($estates as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = $estate_obj->address;
            $estate['date'] = $estate_obj->date;
            $estate['is_featured'] = $estate_obj->is_featured;
            $estate['counter_views'] = $estate_obj->counter_views;
            
            $json_obj = json_decode($estate_obj->json_object);
            
            // All estate options            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
                    if(substr($row1, -2) == ' -')$row1=substr($row1, 0, -2);
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                    
                }
            }
            
            $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png';
                }
            }
            
            // Url to preview
            if(isset($json_obj->field_10))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code'].'/'.url_title_cro($json_obj->field_10));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(!empty($estate_obj->image_filename))
            {
                $estate['thumbnail_url'] = base_url('files/thumbnail/'.$estate_obj->image_filename);
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }
            
            
            if($estate_obj->is_featured)
            {
                if(!empty($_GET['search']))
                    $this->data['search_purpose'] = $_GET['search'];
                
                if($this->data['search_purpose'] != NULL)
                {
                    if(isset($estate['option_4']) && 
                       $estate['option_4'] == $this->data['search_purpose'])
                        $this->data['featured_properties'][] = $estate;
                }
                else
                {
                    $this->data['featured_properties'][] = $estate;
                }
            }
            
            $this->data['all_estates'][] = $estate;
        }
        
        $this->data['all_estates_center'] = calculateCenter($estates);
        
        /* End get all estates data */
        
        //$options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        /* Get last n properties */
        $last_n = 4;
        if(config_item('last_estates_limit'))
            $last_n = config_item('last_estates_limit');
        
        $last_n_estates = $this->estate_m->get_by(array('is_activated' => 1, 'language_id'=>$lang_id), FALSE, $last_n, 'id DESC');
        
        $this->data['last_estates_num'] = $last_n;
        $this->data['last_estates'] = array();
        foreach($last_n_estates as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr->id;
            $estate['gps'] = $estate_arr->gps;
            $estate['address'] = $estate_arr->address;
            $estate['date'] = $estate_arr->date;
            $estate['repository_id'] = $estate_arr->repository_id;
            $estate['is_featured'] = $estate_arr->is_featured;
            
            $json_obj = json_decode($estate_arr->json_object);
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
                    if(substr($row1, -2) == ' -')$row1=substr($row1, 0, -2);
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                }
            }
            
            // [START] custom price field
//            $estate['custom_price'] = '';
//            if(!empty($estate['option_36']))
//                $estate['custom_price'].=$this->data['options_prefix_36'].$estate['option_36'].$this->data['options_suffix_36'];
//            if(!empty($estate['option_37']))
//                $estate['custom_price'].=$this->data['options_prefix_37'].$estate['option_37'].$this->data['options_suffix_37'];
//            if(empty($estate['option_37']) && !empty($estate['option_56']))
//                $estate['custom_price'].=$this->data['options_prefix_56'].$estate['option_56'].$this->data['options_suffix_56'];
            // [END] custom price field
            
            // Url to preview
            if(isset($json_obj->field_10))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr->id.'/'.$this->data['lang_code'].'/'.url_title_cro($json_obj->field_10));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(!empty($estate_arr->image_filename))
            {
                $estate['thumbnail_url'] = base_url('files/thumbnail/'.$estate_arr->image_filename);
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['last_estates'][] = $estate;
        }
        
        /* Check for tab/purpose select */
        
        foreach($options_name as $key=>$row)
        {
            $this->data['options_val_'.$row->option_id] = $row->values;
        }
        
        $this->select_tab_by_title = '';
        if(isset($this->data['options_val_4']))
        {
            if(!empty($this->data['page_title']))
            if(strpos(strtolower($this->data['options_val_4']), strtolower($this->data['page_title'])) !== false)
            {
                $this->select_tab_by_title = strtolower($this->data['page_title']);
            }
        }

        // If no selection, then select first
        if(isset($this->data['options_val_4']))
        //if(strpos(strtolower(' '.$this->data['options_val_4']), strtolower($this->_get_purpose()))  === false)
        if($this->select_tab_by_title == '' && config_item('all_results_default') !== TRUE)
        {
            $vals = explode(',', $this->data['options_val_4']);
            if(count($vals)>0)
            $this->select_tab_by_title = strtolower($vals[0]);
        }

        /* End check for tab/purpose select */

        $this->data['options_name'] = array();
        $this->data['options_suffix'] = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_prefix_'.$row->option_id] = $row->prefix;
            $this->data['options_values_'.$row->option_id] = '';
            $this->data['options_values_li_'.$row->option_id] = '';
            $this->data['options_values_arr_'.$row->option_id] = array();
            $this->data['options_values_radio_'.$row->option_id] = '';
            
            if(count(explode(',', $row->values)) > 0)
            {
                $options_h = '<option value="">'.$row->option.'</option>';
                $options_li = '';
                $radio_li = '';
                $_s_value = strtolower(search_value($row->option_id));
                foreach(explode(',', $row->values) as $key2 => $val)
                {
                    $o_selected = '';
                    if(!empty($_s_value) && $_s_value == strtolower($val))
                    {
                        $o_selected = 'selected="selected"';
                    }
                    
                    $selected = '';
                    if($this->_get_purpose() == strtolower($val))$selected = 'selected="selected"';
                    if($o_selected == 'selected="selected"')$selected = 'selected="selected"';
                    
                    
                    $options_h.='<option value="'.$val.'" '.$selected.'>'.$val.'</option>';
                    $this->data['options_values_arr_'.$row->option_id][] = $val;
                    
                    $active = '';
                    if($this->_get_purpose() == strtolower($val))$active = 'active';
                    if($o_selected == 'selected="selected"')$active = 'active';
                    $options_li.= '<li class="'.$active.' cat_'.$key2.'"><a href="#">'.$val.'</a></li>';
                    
                    $checked = '';
                    if($this->_get_purpose() == strtolower($val))$checked = 'checked';
                    if($o_selected == 'selected="selected"')$checked = 'checked';
                    
                    $radio_li.='<label class="checkbox" for="inputRent">
                                <input type="radio" rel="'.$val.'" name="search_option_'.$row->option_id.'" value="'.$key2.'" '.$checked.'> '.$val.'
                                </label>';
                }
                $this->data['options_values_'.$row->option_id] = $options_h;
                $this->data['options_values_li_'.$row->option_id] = $options_li;
                $this->data['options_values_radio_'.$row->option_id] = $radio_li;
            }
        }

        $this->data['has_no_all_estates'] = array();
        if(count($this->data['all_estates']) == 0)
        {
            $this->data['has_no_all_estates'][] = array('count'=>count($this->data['all_estates']));
        }
        
        $this->data['featured_estates'] = $this->estate_m->get_by(array('is_featured'=>true));
        
        /* End fetch estate */
        
        /* Define order */
        if(empty($order))$order='id DESC';
        
        $this->data['order_dateASC_selected'] = '';
        if($order=='id ASC')
            $this->data['order_dateASC_selected'] = 'selected';
            
        $this->data['order_dateDESC_selected'] = '';
        if($order=='id DESC')
            $this->data['order_dateDESC_selected'] = 'selected';
        
        /* Pagination configuration */ 
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '<ul>';
        $config['base_url'] = $this->data['ajax_load_url'];
        $config['total_rows'] = 200;
        $config['per_page'] = config_item('per_page');
        $config['uri_segment'] = 5;
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        /* End Pagination */

        //$options = $this->option_m->get_options($lang_id);
        //$options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);
        
        $lang_purpose = lang_check(ucfirst($purpose));
        if($this->select_tab_by_title != '')
        {
            $lang_purpose = $this->select_tab_by_title;

            if($lang_purpose=='rent')
            {
                $this->data['purpose_rent_active'] = 'active';
                $this->data['is_purpose_rent'] = array();
                $this->data['is_purpose_rent'][] = array('count'=>'1');
                
                $this->data['purpose_sale_active'] = '';
                $this->data['is_purpose_sale'] = array();
            }
        }
        
        
        /* Search */
        $offset_properties = $this->data['pagination_offset'];
        
        $search_array = $search_json;
        
        if(!empty($this->data['search_query']))
        {
            $search_array['search_option_smart'] = $this->data['search_query'];
        }
        
        if(!empty($lang_purpose))
        {
            $search_array['v_search_option_4'] = $lang_purpose;
        }
        
        $where = array();
        $where['is_activated'] = 1;
        $where['language_id']  = $lang_id;
        
        if(isset($this->data['settings_listing_expiry_days']))
        {
            if(is_numeric($this->data['settings_listing_expiry_days']) && $this->data['settings_listing_expiry_days'] > 0)
            {
                 $where['property.date_modified >']  = date("Y-m-d H:i:s" , time()-$this->data['settings_listing_expiry_days']*86400);
            }
        }
        
        if(!empty($address)){
            $where['property.address']  = $address;
        }

        $config['total_rows'] = $this->estate_m->count_get_by($where, false, NULL, 'property.is_featured DESC, property.'.$order, 
                                               NULL, $search_array);
        
        /* Pagination in query */
        $this->data['total_rows'] = $config['total_rows'];
        
        $results_obj = $this->estate_m->get_by($where, false, $config['per_page'], 'property.is_featured DESC, property.'.$order, 
                                               $offset_properties, $search_array);


        $this->data['has_no_results'] = array();
        if(count($results_obj) == 0)
            $this->data['has_no_results'][] = array('count'=>count($results_obj));
        
        /* Get all estates data */
        $this->data['results'] = array();
        foreach($results_obj as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr->id;
            $estate['gps'] = $estate_arr->gps;
            $estate['address'] = $estate_arr->address;
            $estate['date'] = $estate_arr->date;
            $estate['repository_id'] = $estate_arr->repository_id;
            $estate['is_featured'] = $estate_arr->is_featured;
            $estate['counter_views'] = $estate_arr->counter_views;
            $estate['estate_data_id'] = $estate_arr->id;
            $estate['icons'] = array();
            
            $json_obj = json_decode($estate_arr->json_object);
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
                    if(substr($row1, -2) == ' -')$row1=substr($row1, 0, -2);
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                }
            }
            
            // [START] custom price field
            $estate['custom_price'] = '';
            if(!empty($estate['option_36']))
                $estate['custom_price'].=$this->data['options_prefix_36'].$estate['option_36'].$this->data['options_suffix_36'];
            if(!empty($estate['option_37']))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_37'].$estate['option_37'].$this->data['options_suffix_37'];
            }
                
            if(empty($estate['option_37']) && !empty($estate['option_56']))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_56'].$estate['option_56'].$this->data['options_suffix_56'];
            }
            // [END] custom price field
            
            $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png';
                }
            }
            
            // Url to preview
            if(isset($json_obj->field_10))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr->id.'/'.$this->data['lang_code'].'/'.url_title_cro($json_obj->field_10));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(!empty($estate_arr->image_filename))
            {
                $estate['thumbnail_url'] = base_url('files/thumbnail/'.$estate_arr->image_filename);
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }
            
            // [agent second image]
            if(isset($estate_arr->agent_rep_id))
            if(isset($this->data['images_'.$estate_arr->agent_rep_id]))
            {
                if(isset($this->data['images_'.$estate_arr['agent_rep_id']][1]))
                $estate['agent_sec_img_url'] = $this->data['images_'.$estate_arr['agent_rep_id']][1]->thumbnail_url;
            }
            
            $estate['has_agent_sec_img'] = array();
            if(isset($estate['agent_sec_img_url']))
                $estate['has_agent_sec_img'][] = array('count'=>'1');
            // [/agent second image]
            
            $this->data['results'][] = $estate;
        }
        
        /* Pagination load */ 
        $this->pagination->initialize($config);
        $this->data['pagination_links'] =  $this->pagination->create_links();
        /* End Pagination */
        
        /* Fetch all agents */
        
        $search_agent = $this->input->get('search-agent', TRUE);
        
        $all_agents = $this->user_m->get_counted('type LIKE \'AGENT%\'', FALSE, NULL, 'properties_count DESC, user_id', '', $search_agent);
        
        /* [Fetch agent listings num] */
        $this->load->model('packages_m');
        $listings_count = $this->packages_m->get_curr_listings();
        /* [/Fetch agent listings num] */        
        
        $this->data['all_agents'] = array();
        foreach($all_agents as $key=>$agent_obj)
        {
            $agent = array();
            $agent['name_surname'] = $agent_obj->name_surname;
            $agent['phone'] = $agent_obj->phone;
            $agent['mail'] = $agent_obj->mail;
            $agent['address'] = $agent_obj->address;
            
            $agent['agent_name_title'] = url_title_cro($agent_obj->name_surname);
            $agent['agent_url'] = site_url('profile/'.$agent_obj->id.'/'.$this->data['lang_code'].'/'.$agent['agent_name_title']);
            
            if(isset($listings_count[$agent_obj->id]))
                $agent['total_listings_num'] = $listings_count[$agent_obj->id];
            else
                $agent['total_listings_num'] = '0';
            
            if(isset($agent_obj->image_user_filename))
            {
                $agent['image_url'] =  base_url('files/thumbnail/'.$agent_obj->image_user_filename);
            }
            else
            {
                //$agent['image_url'] = 'assets/img/no_image.jpg';
                $agent['image_url'] = 'assets/img/user-agent.png';
            }
            
            // [agent second image]
            if(isset($agent_obj->image_agency_filename))
            {
                $agent['image_sec_url'] = base_url('files/thumbnail/'.$agent_obj->image_agency_filename);
            }
            // [/agent second image]

            $this->data['all_agents'][] = $agent;
        }
        
        $this->data['has_agents'] = array();
        if(count($all_agents))
            $this->data['has_agents'][] = array('count'=>count($all_agents));
            
        /* End fetch all agents */
        
        /* [Fetch paginated agents] */
        
        $offset = $this->uri->segment(4);
        if(empty($offset))$offset = 0;
        
        $agent_per_page = config_item('per_page_agents');
        if(empty($agent_per_page))
            $agent_per_page = 32;

        $paginated_agents = $this->user_m->get_counted('type LIKE \'AGENT%\'', FALSE, $agent_per_page, 'properties_count DESC, user_id', $offset, $search_agent);

        $this->data['paginated_agents'] = array();
        foreach($paginated_agents as $key=>$agent_obj)
        {
            $agent = array();
            $agent['name_surname'] = $agent_obj->name_surname;
            $agent['phone'] = $agent_obj->phone;
            $agent['mail'] = $agent_obj->mail;
            $agent['address'] = $agent_obj->address;
            $agent['description'] = $agent_obj->description;
            
            $agent['agent_name_title'] = url_title_cro($agent_obj->name_surname);
            $agent['agent_url'] = site_url('profile/'.$agent_obj->id.'/'.$this->data['lang_code'].'/'.$agent['agent_name_title']);
            
            $agent['total_listings_num'] = $agent_obj->properties_count;
            
            if(isset($agent_obj->image_user_filename))
            {
                $agent['image_url'] =  base_url('files/thumbnail/'.$agent_obj->image_user_filename);
            }
            else
            {
                $agent['image_url'] = 'assets/img/user-agent.png';
            }

            $this->data['paginated_agents'][] = $agent;
        }
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url($this->data['lang_code'].'/'.$this->data['page_id'].'/'.$this->uri->segment(3).'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['all_agents']);
        $config_2['per_page'] = $agent_per_page;
        $config_2['uri_segment'] = 4;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul class="pagination">';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_3 = new MY_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['agents_pagination'] = $pagination_3->create_links();
        
        
        /* [/Fetch paginated agents] */
        
        /* Validation for contact */
        $rules = array(
            'firstname' => array('field'=>'firstname', 'label'=>'lang:FirstLast', 'rules'=>'trim|required|xss_clean'),
            'email' => array('field'=>'email', 'label'=>'lang:Email', 'rules'=>'trim|required|xss_clean'),
            'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|xss_clean'),
            'message' => array('field'=>'message', 'label'=>'lang:Message', 'rules'=>'trim|required|xss_clean')
       );
       
       if(config_item('captcha_disabled') === FALSE)
            $rules['captcha'] = array('field'=>'captcha', 'label'=>'lang:Captcha', 'rules'=>'trim|required|callback_captcha_check|xss_clean');
       
       if(isset($_POST['question']))
       {
            unset($rules['message']);
            $rules['question'] = array('field'=>'question', 'label'=>'lang:Question', 'rules'=>'trim|required|xss_clean');
       }
       
       $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            $data = $this->page_m->array_from_post(array('firstname', 'email', 'phone', 'message'));

            // Send email
            $this->load->library('email');
            
            $this->email->from($this->data['settings_noreply'], lang_check('Web page'));
            $this->email->to($this->data['settings_email']);
            
            $this->email->subject(lang_check('Message from real-estate web'));
            
            if(isset($_POST['question']))
            {
                $this->load->model('qa_m');
                
                $data_t = $this->page_m->array_from_post(array('firstname', 'email', 'phone', 'question'));
                
                $data = array();
                $data['is_readed'] = 0;
                $data['date'] = date('Y-m-d H:i:s');
                $data['type'] = 'QUESTION';
                $data['answer_user_id'] = 0;
                $data['parent_id'] = 0;
                
                $data_lang = array();
                $data_lang['question_'.$lang_id] = $data_t['question'];
                
                $id = $this->qa_m->save_with_lang($data, $data_lang, NULL);
                $this->email->subject(lang_check('Expert question from real-estate web'));
    
                $data['name_surname'] = $data_t['firstname'];
                $data['phone'] = $data_t['phone'];
                $data['mail'] = $data_t['email'];
            }
            
            unset($_POST['captcha'], $_POST['captcha_hash']);
            
            $message='';
            foreach($_POST as $key=>$value){
            	$message.="$key:\n$value\n";
            }
            
            /* [START] HTML formating example */
//            $message='
//            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
//            <html xmlns="http://www.w3.org/1999/xhtml">
//             <head>
//              <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
//              <title>'.lang_check('Message from real-estate web').'</title>
//              <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
//            </head>
//            <body style="padding: 10px">
//            <a href="'.site_url().'"><img src="'.base_url('admin-assets/img/stamp.png').'" /></a>';
//            foreach($_POST as $key=>$value):
//            if(!empty($value)):
//            $message.='
//                <p>
//                <strong>'.lang_check($key).':</strong> '.$value.'<br />
//                </p>';
//            endif;
//            endforeach;
//            $message.='</body></html>';
            /* [END] HTML formating example */
            
            $this->email->message($message);
            if ( ! $this->email->send())
            {
                $this->session->set_flashdata('email_sent', 'email_sent_false');
            }
            else
            {
                $this->session->set_flashdata('email_sent', 'email_sent_true');
            }

            redirect($this->uri->uri_string());
        }
        
        $this->data['validation_errors'] = validation_errors();

        $this->data['form_sent_message'] = '';
        if($this->session->flashdata('email_sent'))
        {
            if($this->session->flashdata('email_sent') == 'email_sent_true')
            {
                $this->data['form_sent_message'] = '<p class="alert alert-success">'.lang_check('message_sent_successfully').'</p>';
            }
            else
            {
                $this->data['form_sent_message'] = '<p class="alert alert-error">'.lang_check('message_sent_error').'</p>';
            }  
        }
        
        // Form errors
        $this->data['form_error_firstname'] = form_error('firstname')==''?'':'error';
        $this->data['form_error_email'] = form_error('email')==''?'':'error';
        $this->data['form_error_phone'] = form_error('phone')==''?'':'error';
        $this->data['form_error_message'] = form_error('message')==''?'':'error';
        $this->data['form_error_question'] = form_error('question')==''?'':'error';
        $this->data['form_error_captcha'] = form_error('captcha')==''?'':'error';
        
        // Form values
        $this->data['form_value_firstname'] = set_value('firstname', '');
        $this->data['form_value_email'] = set_value('email', '');
        $this->data['form_value_phone'] = set_value('phone', '');
        $this->data['form_value_message'] = set_value('message', '');
        $this->data['form_value_question'] = set_value('question', '');
        
        /* End validation for contact */
        
        $page_id = $this->data['page_id'];
        
        /* {ARTICLES} */
        // Fetch all pages
        $this->data['news_articles'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, array('parent_id' => $page_id, 'type'=>'ARTICLE'), null, '', 'order');
        
        $this->data['news_articles_157'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, array('parent_id' => 157, 'type'=>'ARTICLE'), null, '', 'order');
        /* {/ARTICLES} */
        
        /* {MODULE_NEWS} */
        
        $category_id = 0;
        
        // Check for contained category/parent_id
        $news_category = $this->page_m->get_contained_news_category($page_id);
        $cat_merge = array();
        if(count($news_category)>0)
        {
            $cat_merge = array('parent_id' => $news_category->id);
            $category_id = $news_category->id;
        }
        
        $category_id_get = $this->input->get('cat', TRUE);
        if(!empty($category_id_get))
        {
            $cat_merge = array('parent_id' => $category_id_get);
            $category_id = $category_id_get;
        }
        
        $pagination_offset=0;
        
        // Fetch all pages
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['categories'] = $this->page_m->get_no_parents_news_category($lang_id);
        $this->data['news_module_all'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, array_merge($cat_merge, array('type'=>'MODULE_NEWS_POST')), null, '', 'date_publish DESC');
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url('news/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$category_id.'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['news_module_all']);
        $config_2['per_page'] = config_item('per_page');
        $config_2['uri_segment'] = 5;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul>';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_2 = new CI_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['news_pagination'] = $pagination_2->create_links();
        
        $this->data['news_module_all'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'MODULE_NEWS_POST')), 
                                                          $config_2['per_page'], $pagination_offset, 'date_publish DESC');
        
        if(file_exists(APPPATH.'controllers/admin/news.php'))
        {
            $this->data['news_module_latest_5'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, 
                                                              array_merge($cat_merge, array('type'=>'MODULE_NEWS_POST')), 
                                                              5, 0, 'date_publish DESC');
        }
        else
        {
            $this->data['news_module_latest_5'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, 
                                                              array('type'=>'ARTICLE'), 
                                                              5, 0, 'date DESC');
        }

        
        /* {/MODULE_NEWS} */
        
        /* {MODULE_ADS} */
        $this->load->model('ads_m');
        $this->data['ads'] = array();
        
        foreach($this->ads_m->ads_types as $type_key=>$type_name)
        {
            $ads_by_type = $this->ads_m->get_by(array('type'=>$type_key));
            
            $num_ads = count($ads_by_type);

            $this->data['has_ads_'.$type_name] = array();
            if(isset($ads_by_type[0]))
            if($num_ads > 0 && $ads_by_type[0]->is_activated)
            {
                $rand_ad_key=0;
                if($ads_by_type[0]->is_random)
                    $rand_ad_key = rand(0, $num_ads-1);
                
                if(isset($ads_by_type[$rand_ad_key]) && isset($this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id]))
                {
                    $rand_image = rand(0, count($this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id])-1);
                    
                    $this->data['random_ads_'.$type_name.'_link'] = $ads_by_type[$rand_ad_key]->link;
                    $this->data['random_ads_'.$type_name.'_repository'] = $ads_by_type[$rand_ad_key]->repository_id;
                    $this->data['random_ads_'.$type_name.'_image'] = $this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id][$rand_image]->url;
                    $this->data['has_ads_'.$type_name][] = array('count' => $num_ads);
                }
            }
        }
        /* {/MODULE_ADS} */
        
        /* {MODULE_SHOWROOM} */
        
        $this->load->model('showroom_m');
        
        $category_id = 0;
        
        // Check for contained category/parent_id
        $showroom_category = $this->showroom_m->get_contained_showroom_category($page_id);
        $cat_merge = array();
        if(count($showroom_category)>0)
        {
            $cat_merge = array('parent_id' => $showroom_category->id);
            $category_id = $showroom_category->id;
        }
        
        $category_id_get = $this->input->get('cat', TRUE);
        if(!empty($category_id_get))
        {
            $cat_merge = array('parent_id' => $category_id_get);
            $category_id = $category_id_get;
        }
        
        $pagination_offset=0;
        
        // Fetch all pages
        $this->data['categories_showroom'] = $this->showroom_m->get_no_parents_showrooms_category($lang_id);
        $this->data['showroom_module_all'] = $this->showroom_m->get_lang(NULL, FALSE, $lang_id, array_merge($cat_merge, array('type'=>'COMPANY')), null, '', 'date_publish DESC');
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url('showroom/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$category_id.'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['showroom_module_all']);
        $config_2['per_page'] = config_item('per_page');
        $config_2['uri_segment'] = 5;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul>';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_2 = new CI_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['showroom_pagination'] = $pagination_2->create_links();
        
        $this->data['showroom_module_all'] = $this->showroom_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'COMPANY')), 
                                                          $config_2['per_page'], $pagination_offset, 'date_publish DESC');
        
        $this->data['showroom_module_latest_5'] = $this->showroom_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'COMPANY')), 
                                                          5, 0, 'date_publish DESC');
        
        /* {/MODULE_SHOWROOM} */
        
        /* {MODULE_Q&A} */
        
        $this->load->model('qa_m');
        
        $category_id = 0;
        
        // Check for contained category/parent_id
        $expert_category = $this->qa_m->get_contained_expert_category($page_id);
        $cat_merge = array();
        if(count($expert_category)>0)
        {
            $cat_merge = array('parent_id' => $expert_category->id);
            $category_id = $expert_category->id;
        }
        
        $category_id_get = $this->input->get('cat', TRUE);
        if(!empty($category_id_get))
        {
            $cat_merge = array('parent_id' => $category_id_get);
            $category_id = $category_id_get;
        }
        
        $pagination_offset=0;
                
        // Fetch all pages
        $this->data['categories_expert'] = $this->qa_m->get_no_parents_expert_category($lang_id);
        $this->data['expert_module_all'] = $this->qa_m->get_lang(NULL, FALSE, $lang_id, array_merge($cat_merge, array('type'=>'QUESTION', 'is_readed'=>1)), null, '', 'date_publish DESC');
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url('expert/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$category_id.'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['expert_module_all']);
        $config_2['per_page'] = config_item('per_page');
        $config_2['uri_segment'] = 5;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul>';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_2 = new CI_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['expert_pagination'] = $pagination_2->create_links();
        
        $this->data['expert_module_all'] = $this->qa_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'QUESTION', 'is_readed'=>1)), 
                                                          $config_2['per_page'], $pagination_offset, 'date_publish DESC');
        
        $this->data['expert_module_latest_5'] = $this->qa_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'QUESTION', 'is_readed'=>1)), 
                                                          5, 0, 'date_publish DESC');
        
        // Fetch all experts
        $all_experts = $this->user_m->get_by(array('qa_id !='=>0, 'type !=' => 'USER'));
        
        $this->data['all_experts'] = array();
        foreach($all_experts as $key=>$expert_obj)
        {
            $agent = array();
            $agent['name_surname'] = $expert_obj->name_surname;
            $agent['phone'] = $expert_obj->phone;
            $agent['mail'] = $expert_obj->mail;
            $agent['address'] = $expert_obj->address;
            
            if(isset($expert_obj->image_user_filename))
            {
                $agent['image_url'] =  base_url('files/thumbnail/'.$expert_obj->image_user_filename);
            }
            else
            {
                $agent['image_url'] = 'assets/img/user-agent.png';
            }


            $this->data['all_experts'][$expert_obj->id] = $agent;
        }
        
        /* {/MODULE_Q&A} */
        
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/'.$this->temp_data['page']->template.'.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
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
    
	public function captcha_check($str)
	{
		if ($str != substr(md5($this->data['captcha_hash_old'].config_item('encryption_key')), 0, 5))
		{
			$this->form_validation->set_message('captcha_check', lang_check('Wrong captcha'));
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
    
    public function _unique_username($str)
    {
        // Do NOT validate if username alredy exists
        // UNLESS it's the username for the current user
        
        $id = $this->session->userdata('id');
        $this->db->where('username', $this->input->post('username'));
        !$id || $this->db->where('id !=', $id);
        
        $user = $this->user_m->get();
        
        if(count($user))
        {
            $this->form_validation->set_message('_unique_username', '%s '.lang('should be unique'));
            return FALSE;
        }
        
        return TRUE;
    }
    
    public function _unique_mail($str)
    {
        // Do NOT validate if mail alredy exists
        // UNLESS it's the mail for the current user
        
        $id = $this->session->userdata('id');
        $this->db->where('mail', $this->input->post('mail'));
        !$id || $this->db->where('id !=', $id);
        
        $user = $this->user_m->get();
        
        if(count($user))
        {
            $this->form_validation->set_message('_unique_mail', '%s '.lang('should be unique'));
            return FALSE;
        }
        
        return TRUE;
    }

}