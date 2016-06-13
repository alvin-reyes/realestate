<?php

class User extends Admin_Controller 
{

	public function __construct(){
		parent::__construct();
        
        $this->load->model('file_m');
        $this->load->model('repository_m');
        $this->load->model('qa_m');
        $this->load->model('packages_m');
        
        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
	}
    
    public function index($pagination_offset=0)
	{
	    $this->load->library('pagination');

        prepare_search_query_GET(array('type'), array('id', 'username', 'name_surname', 'address', 'description'));
       
	    // Fetch all users
		$this->data['users'] = $this->user_m->get();
        
        // pagination
        $config['base_url'] = site_url('admin/user/index');
        $config['uri_segment'] = 4;
        $config['total_rows'] = count($this->data['users']);
        $config['per_page'] = 20;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['link_suffix'] = '#content';
        $config['additional_query_string'] = regenerate_query_string();
        
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        
        prepare_search_query_GET(array('type'), array('id', 'username', 'name_surname', 'address', 'description'));
        $this->data['users'] = $this->user_m->get_pagination($config['per_page'], $pagination_offset);
        
        $this->data['expert_categories'] = $this->qa_m->get_no_parents_expert($this->data['content_language_id']);
        
        // Load view
		$this->data['subview'] = 'admin/user/index';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function export()
    {
        $this->load->helper('download');
        
	    // Fetch all users
		$users = $this->user_m->get();
        
        $data = '';
        
        foreach($users as $row)
        {
            if(strpos($row->mail, '@') > 1)
            {
                $data.= $row->mail."\r\n";
            }
        }
        
        if(strlen($data) > 2)
            $data = substr($data,0,-1);
        
        $name = 'real-estate-users.txt';
        
        force_download($name, $data); 
    }
    
    public function edit($id = NULL)
	{
	    // Fetch a user or set a new one
	    if($id)
        {
            $this->data['user'] = $this->user_m->get($id);
            
            if(count($this->data['user']) == 0)
            {
                $this->data['errors'][] = 'User could not be found';
                redirect('admin/user');
            }
            
            //Check if user have permissions
            if($this->session->userdata('type') != 'ADMIN' &&
               $this->session->userdata('type') != 'AGENT_ADMIN' )
            {
                if($id == $this->session->userdata('id'))
                {
                    
                }
                else
                {
                    redirect('admin/user');
                }
            }
            
            // Fetch file repository
            $repository_id = $this->data['user']->repository_id;
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>'user_m'));
                
                // Update page with new repository_id
                $this->user_m->save(array('repository_id'=>$repository_id), $this->data['user']->id);
            }
        }
        else
        {
            $this->data['user'] = $this->user_m->get_new();
        }
       
		$id == NULL || $this->data['user'] = $this->user_m->get($id);
        
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
        
        
        $this->data['expert_categories'] = $this->qa_m->get_no_parents_expert($this->data['content_language_id']);
        $this->data['packages'] = $this->packages_m->get_form_dropdown('package_name');
        
        // Set up the form
        $rules = $this->user_m->rules_admin;
        $id || $rules['password']['rules'] .= '|required';
        //$rules['mail']['rules'] .= '|callback__unique_email';
            
        if($this->session->userdata('type') != 'ADMIN')
            unset($rules['type'], $rules['mail_verified'], $rules['phone_verified'], $rules['activated']);

        if($this->session->userdata('type') == 'AGENT_LIMITED')
        {
            unset($rules['mail'], $rules['language']);
        }


        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/edit/'.$id);
                exit();
            }

            $data = $this->user_m->array_from_post(array('name_surname', 'password', 'username',
                                                         'address', 'description', 'mail', 'phone', 'type', 
                                                         'qa_id', 'language', 'activated', 'package_id', 'package_last_payment', 'facebook_id', 'mail_verified', 'phone_verified'));
            
            if($this->session->userdata('type') != 'ADMIN')
            {
                unset($data['mail_verified'], $data['phone_verified'], $data['activated']);
            }
            
            // AGENT_LIMITED don't have permission to change this fields...
            if($this->session->userdata('type') == 'AGENT_LIMITED')
            {
                unset($data['mail'],
                      $data['language'],
                      $data['address'],  
                      $data['description'],  
                      $data['phone'],  
                      $data['type'],  
                      $data['qa_id'],  
                      $data['language'],  
                      $data['activated'],  
                      $data['package_id'],  
                      $data['package_last_payment'],    
                      $data['facebook_id'],    
                      $data['mail_verified'],    
                      $data['phone_verified']
                );
            }
            
            $original_password = '';
            if($data['password'] == '')
            {
                unset($data['password']);
            }
            else
            {
                $original_password = $data['password'];
                $data['password'] = $this->user_m->hash($data['password']);
            }
            
            if($id == NULL)
            {
                $data['mail_verified'] = 0;
                $data['phone_verified'] = 0;
            }
            else
            {
                if($this->data['user']->mail != $this->input->post('mail'))
                {
                    $data['mail_verified'] = 0;
                }
                
                if($this->data['user']->phone != $this->input->post('phone'))
                {
                    $data['phone_verified'] = 0;
                }
            }

            if($this->session->userdata('type') != 'ADMIN')
                unset($data['type']);

            if($id == NULL)
                $data['registration_date'] = date('Y-m-d H:i:s');
                
            if(empty($data['package_last_payment']))
            {
                $data['package_last_payment'] = NULL;
                if(!empty($data['package_id']))
                {
                    $package = $this->packages_m->get($data['package_id'], TRUE);
                    $days_limit = $package->package_days;
                    
                    if($days_limit > 0)
                    {
                        $data['package_last_payment'] = date('Y-m-d H:i:s', time() + (24*3600*$days_limit));
                    }
                }
            }
            
            $id = $this->user_m->save($data, $id);
            
            // [START] Email user about new changes
            $message_mail = '';
            if(config_item('email_profile_changed_enabled') == TRUE)
            if(ENVIRONMENT != 'development')
            if(!empty($data['mail']) && $this->session->userdata('type') == 'ADMIN' /*&& 
               $data['activated'] == 1 && 
               isset($data['password'])*/)
            {
                $this->load->library('email');
                $config_mail['mailtype'] = 'html';
                $this->email->initialize($config_mail);
                $this->email->from($this->data['settings']['noreply'], 'Web page');
                $this->email->to($data['mail']);
                
                $this->email->subject(lang_check('Changes on your user profile'));
                
                if(isset($data['password']))
                {
                    $data['password'] = $original_password;
                }
                else{
                    $data['password'] = '';
                }
                unset($data['qa_id'], $data['activated']);
                
                $data['profile_link'] = '<a href="'.site_url('frontend/login/').'?username='.$data['username'].'&$password='.$original_password.'#content">'.lang_check('Edit profile link').'</a>';
                
                $message='';
                foreach($data as $key=>$value){
                	$message.="$key:\n$value\n";
                }
                
                $message = $this->load->view('email/changed_profile_by_admin', array('data'=>$data), TRUE);

                $this->email->message($message);
                if ( ! $this->email->send())
                {
                    $message_mail = ', '.lang_check('Problem sending email to user');
                }
            }
            // [END] Email user about new changes
            
            $this->session->set_flashdata('message', 
                    '<p class="label label-success validation">'.lang_check('Changes saved').$message_mail.'</p>');

            redirect('admin/user/edit/'.$id);
        }
        
        // Load the view
		$this->data['subview'] = 'admin/user/edit';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function all_deactivate($user_id)
    {
        $this->load->model('estate_m');
        
        //Get user properties
        $user_properties = $this->estate_m->get_user_properties($user_id);
        
        //Activate/deactivate all user properties
        $this->estate_m->change_activated_properties($user_properties, 0);
        
        //Set message
        $this->session->set_flashdata('error', 
                        lang_check('All properties from specific user is deactivated!').' ('.$user_id.')');
        
        redirect('admin/user/');
    }
    
    public function all_activate($user_id)
    {
        $this->load->model('estate_m');
        
        //Get user properties
        $user_properties = $this->estate_m->get_user_properties($user_id);
        
        //Activate/deactivate all user properties
        $this->estate_m->change_activated_properties($user_properties, 1);
        
        //Set message
        $this->session->set_flashdata('error', 
                        lang_check('All properties from specific user is activated!').' ('.$user_id.')');
        
        redirect('admin/user/');
    }
    
    public function delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/user');
            exit();
        }
        
        if($this->session->userdata('type') != 'AGENT_ADMIN')
		  $this->user_m->delete($id);
    
        redirect('admin/user');
	}
    
    //public function login_secret()
    public function login()
	{
	    // Redirect a user if he's alredy logged in'
        
        
	    $dashboard = 'admin/dashboard';
        
        if($this->user_m->loggedin() === TRUE)
        {
            if($this->session->userdata('type') == 'USER')
            {
                redirect('frontend/login', 'refresh');
            }
            else
            {
                redirect($dashboard, 'refresh');
            }
        }
        
        // Set form
        $rules = $this->user_m->rules;
        $this->form_validation->set_rules($rules);
        
        // Process form
        if($this->form_validation->run() == TRUE)
        {
            // We can login and redirect
            if($this->user_m->login() == TRUE)
            {
                redirect($dashboard);
            }
            else
            {
                $this->session->set_flashdata('error', 
                        lang('That email/password combination does not exists or account not activated'));
                redirect('admin/user/login', 'refresh');                
            }
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/login';
        $this->load->view('admin/_layout_modal', $this->data);
	}
    
    public function register()
	{
	    // Redirect a user if he's alredy logged in'
	    $dashboard = 'admin/dashboard';
	    $this->user_m->loggedin() == FALSE || redirect($dashboard);
        
	    // Set a new user
        $this->data['user'] = $this->user_m->get_new();
        
        // Set up the form
        $rules = $this->user_m->rules_admin;
        $rules['password']['rules'] .= '|required';
        $rules['type']['rules'] = 'trim';
        $rules['address']['rules'] .= '|required';
        $rules['phone']['rules'] .= '|required|is_unique[user.phone]';
        $rules['mail']['rules'] .= '|is_unique[user.mail]';
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/register');
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
            
            if($id == NULL)
            {
                $data['mail_verified'] = 0;
                $data['phone_verified'] = 0;
            }
            
            $data['type'] = 'AGENT';
            $data['activated'] = '0';
            $data['description'] = '';
            $data['registration_date'] = date('Y-m-d H:i:s');
            
            if($this->config->item('def_package') !== FALSE)
                $data['package_id'] = $this->config->item('def_package');
            
            $this->user_m->save($data, NULL);
            
            $this->session->set_flashdata('error', 
                    lang('Thanks on registration, please wait account activation'));
            redirect('admin/user/login', 'refresh');
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/register';
        $this->load->view('admin/_layout_modal', $this->data);
	}
    
    public function forgetpassword()
    {
	    // Redirect a user if he's alredy logged in'
	    $dashboard = 'admin/dashboard';
	    $this->user_m->loggedin() == FALSE || redirect($dashboard);
        
        
        // Set up the form
        $rules = array('mail' => array('field'=>'mail', 'label'=>'lang:Mail', 'rules'=>'trim|required|exists[user.mail]|xss_clean'));
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/forgetpassword');
                exit();
            }
            
            $data = $this->user_m->array_from_post(array('mail'));          
            
            // Get user id && pass hash to generate new pass hash
            $user = $this->user_m->get_by(array('mail'=>$data['mail']), true);
            
            $new_hash = $this->user_m->hash($data['mail'].$user->id.$user->password);
            
            // Send reset link to email
            $this->load->library('email');
            
            $this->email->from($this->data['settings']['noreply'], lang_check('Web page reset password'));
            $this->email->to($data['mail']);
            $this->email->subject(lang_check('Web page reset password'));
            
            $message='';
            $message.=lang_check('Your username').": \n";
            $message.=$user->username."\n\n";
            $message.=lang_check('Your password reset link').": \n";
            $message.=site_url('admin/user/resetpassword/'.$user->id.'/'.$new_hash)."\n\n";
            
            $this->email->message($message);
            
            if ( ! $this->email->send())
            {
                $this->session->set_flashdata('error', 
                        lang('Email sending problem, please contact administrator.'));
                redirect('admin/user/forgetpassword', 'refresh');
            }
            else
            {
                $this->session->set_flashdata('error', 
                        lang('Reset link sent to email, please check your email.'));
                redirect('admin/user/login', 'refresh');
            }
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/forgetpassword';
        $this->load->view('admin/_layout_modal', $this->data);
    }
    
    public function resetpassword($user_id = NULL, $hash = NULL)
    {
	    // Redirect a user if he's alredy logged in'
	    $dashboard = 'admin/dashboard';
	    $this->user_m->loggedin() == FALSE || redirect($dashboard);
        
	    // Fetch user
        $user = $this->user_m->get_by(array('id'=>$user_id), true);
        
        // Check hash code
        $check_hash = $this->user_m->hash($user->mail.$user->id.$user->password);
        
        if($check_hash != $hash || $user_id == NULL || $hash == NULL)
        {
            $this->session->set_flashdata('error', 
                    lang('Link not valid'));
            redirect('admin/user/forgetpassword/');
        }
        
        // Set up the form
        $rules = array('password' => array('field'=>'password', 'label'=>'lang:Password', 'rules'=>'trim|required|matches[password_confirm]'),
                       'password_confirm' => array('field'=>'password_confirm', 'label'=>'lang:PasswordConfirm', 'rules'=>'trim|required|matches[password]'),);
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/resetpassword/'.$user_id.'/'.$hash);
                exit();
            }
            
            $data = $this->user_m->array_from_post(array('password'));

            $data['password'] = $this->user_m->hash($data['password']);

            $this->user_m->save($data, $user_id);
            
            $this->session->set_flashdata('error', 
                    lang('Password changed, you can login now'));
            redirect('admin/user/login', 'refresh');
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/resetpassword';
        $this->load->view('admin/_layout_modal', $this->data);
    }
    
    public function verifyemail($user_id = NULL, $hash = NULL)
    {
	    // Redirect a user if he's alredy logged in'
	    $dashboard = 'admin/dashboard';
        
	    if($user_id == NULL || $hash == NULL)
        {
            redirect($dashboard);
        }
        
	    // Fetch user
        $this->data['user'] = $user = $this->user_m->get_by(array('id'=>$user_id), true);
        
        // Check hash code
        $check_hash = substr($this->user_m->hash($user->mail.$user->id), 0, 5);

        if($check_hash != $hash)
        {
            $this->data['message'] = '<p class="label label-important validation">'.lang_check('Link not valid').'</p>';
        }
        else
        {
            $this->data['message'] = '<p class="label label-success validation">'.lang_check('Thank you, email verified and account activated!').'</p>';
            
            $data = array();
            $data['mail_verified'] = 1;
            $data['activated'] = 1;
            $this->user_m->save($data, $user_id);
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/verifyemail';
        $this->load->view('admin/_layout_modal', $this->data);
    }
    
    public function verifyphone($user_id = NULL, $hash = NULL)
    {
	    // Redirect a user if he's alredy logged in'
	    $dashboard = 'admin/dashboard';
        $this->data['is_logged'] = $is_logged = $this->user_m->loggedin();
        
	    if($is_logged == FALSE && $user_id == NULL && $hash == NULL)
        {
            redirect($dashboard);
        }
        
        if($user_id == NULL && $is_logged)
            $user_id = $this->session->userdata('id');
        
	    // Fetch user
        $this->data['user'] = $user = $this->user_m->get_by(array('id'=>$user_id), true);
        
        // Check hash code
        $check_hash = substr($this->user_m->hash($user->phone.$user->id), 0, 5);

        if($hash != NULL)
        {
            if($check_hash != $hash || $user_id == NULL)
            {
                $this->session->set_flashdata('error', 
                        lang('Link not valid'));
                redirect('admin/user/verifyphone/');
            }
            else
            {
                $data = array();
                $data['phone_verified'] = 1;
                $this->user_m->save($data, $user_id);
                
                $this->session->set_flashdata('message', 
                        lang_check('Thank you, phone number verified!'));
                redirect('admin/user/verifyphone/'.$user_id.'/');
            }
        }
        
        // Set up the form
        $rules = array('phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|required'),
                       'code' => array('field'=>'code', 'label'=>'lang:Code', 'rules'=>'trim') );
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE && $is_logged)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/verifyphone/'.$user_id.'/'.$hash);
                exit();
            }
            
            $data = $this->user_m->array_from_post(array('phone', 'code'));
            
            if(!empty($data['code']))
            {
                if(substr($this->user_m->hash($data['phone'].$this->data['user']->id), 0, 5) == $data['code'])
                {
                    unset($data['code']);
                    
                    $data['phone_verified'] = 1;
                    $this->user_m->save($data, $user_id);
                    
                    $this->session->set_flashdata('message', 
                            lang_check('Thank you, phone number verified!'));
                    redirect('admin/user/verifyphone', 'refresh');
                }
                else
                {
                    $this->session->set_flashdata('error', 
                            lang_check('Wrong verification code!'));
                    redirect('admin/user/verifyphone', 'refresh');
                }
            }
            else
            {
                unset($data['code']);
                
                $this->user_m->save($data, $user_id);
                
                if(!empty($data['phone']) &&
                   config_db_item('clickatell_api_id') != FALSE && config_db_item('phone_verification_enabled') === TRUE && 
                   file_exists(APPPATH.'libraries/Clickatellapi.php'))
                {
                    $data['phone_verified'] = 0;
                    
                    //TODO:Send SMS for phone verification
                    $new_hash = substr($this->user_m->hash($data['phone'].$this->data['user']->id), 0, 5);
                    
                    $message='';
                    $message.=lang_check('Your code').": \n";
                    $message.=$new_hash."\n";
                    $message.=lang_check('Verification link').": \n";
                    $message.=site_url('admin/user/verifyphone/'.$this->data['user']->id.'/'.$new_hash);
                    
                    $this->load->library('clickatellapi');
                    $return_sms = $this->clickatellapi->send_sms($message, $data['phone']);
    
                    if(substr_count($return_sms, 'successnmessage') == 0)
                    {
                        $this->session->set_flashdata('error', $return_sms);
                        redirect('admin/user/verifyphone', 'refresh');
                    }
                }
                
                $this->session->set_flashdata('message', 
                        lang_check('Phone changed, SMS to phone sent for verification'));
                redirect('admin/user/verifyphone', 'refresh');
            }

        }
        
        // Load view
		$this->data['subview'] = 'admin/user/verifyphone';
        $this->load->view('admin/_layout_modal', $this->data);
    }
    
    public function logout()
    {
        $this->user_m->logout();
        
        $logout_uri = 'admin/user/login';
        if(config_db_item('logout_redirection_uri') !== FALSE)
        {
            $logout_uri = config_db_item('logout_redirection_uri');
        }

        redirect($logout_uri);
    }
    
    public function _unique_username($str)
    {
        // Do NOT validate if username alredy exists
        // UNLESS it's the username for the current user
        
        $id = $this->uri->segment(4);
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
        // Do NOT validate if email alredy exists
        // UNLESS it's the email for the current user
        
        $id = $this->uri->segment(4);
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