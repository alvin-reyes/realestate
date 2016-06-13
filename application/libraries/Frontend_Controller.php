<?php

class Frontend_Controller extends MY_Controller 
{
	public function __construct(){
		parent::__construct();

        if(config_item('installed') == false)
        {
            redirect('configurator');
            exit();
        }
        
        if(ENVIRONMENT == 'development' || 
          md5($this->input->get('profiler'))=='b78ee15cb3ca6531667d47af5cdc61a1')
        {
            error_reporting(E_ALL | E_STRICT);
            $this->output->enable_profiler(TRUE);
        }
        
        $this->data['listing_uri'] = config_item('listing_uri');
        if(empty($this->data['listing_uri']))$this->data['listing_uri'] = 'property';
        
        /* Load Helpers */
        $this->load->helper('text');    
        
        /* Load libraries */
        $this->load->library('parser');
        
        $this->load->library('form_validation');
        
        $this->load->library('session');
        $this->load->library('pagination');
        
        /* Load models */
        $this->load->model('language_m');
        $this->load->model('page_m');
        $this->load->model('file_m');
        $this->load->model('user_m');
        $this->load->model('repository_m');
        $this->load->model('estate_m');
        $this->load->model('option_m');
        $this->load->model('settings_m');
        $this->load->model('slideshow_m');
        
        $this->form_validation->set_error_delimiters('<p class="alert alert-error">', '</p>');
        
        $CI =& get_instance();
        $CI->form_languages = $this->language_m->get_form_dropdown('language', FALSE, FALSE);
        
        // Fetch settings
        $this->load->model('settings_m');
        foreach($this->settings_m->get_fields() as $key=>$value)
        {
            if($key == 'address')
            {
                $value = str_replace('"', '\\"', $value);
            }
            
            $this->data['settings_'.$key] = $value;
            
            $this->data['has_settings_'.$key] = array();
            if(!empty($value))
            {
                $this->data['has_settings_'.$key][] = array('count'=>'1');
            }
        }
        
        // Extra JS features enabled
        $this->data['has_extra_js'] = array();
        if($this->uri->segment(2) == 'editproperty' ||
           $this->uri->segment(2) == 'myprofile' )
            $this->data['has_extra_js'][] = array('count'=>'1');
        
        if($this->uri->uri_string() == '' && count($this->language_m->db_languages_code) > 0)
        {
            $lang_autodetect = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            
            if(strlen($lang_autodetect)>0 && isset($this->language_m->db_languages_code[$lang_autodetect]))
            {
                redirect($lang_autodetect);
            }
        }
        
        // Get page data
        $this->data['lang_code'] = (string) $this->uri->segment(1);
        $this->data['page_id'] = (string) $this->uri->segment(2);
        $this->data['page_slug'] = (string) $this->uri->segment(3);
        $this->data['pagination_offset'] = 0;
        
        // If frontend
        if($this->data['page_id'] == 'typeahead')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = (string) $this->uri->segment(4);
            $this->data['pagination_offset'] = (string) $this->uri->segment(5);
        }
        else if($this->data['page_id'] == 'ajax')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = (string) $this->uri->segment(4);
            $this->data['pagination_offset'] = (string) $this->uri->segment(5);
        }
        else if($this->data['page_id'] == 'showroom' || $this->data['lang_code'] == 'showroom')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == 'expert' || $this->data['lang_code'] == 'expert')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == $this->data['listing_uri'] || 
                $this->data['lang_code'] == $this->data['listing_uri'] ||
                $this->data['page_id'] == 'profile' || 
                $this->data['lang_code'] == 'profile')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == 'login' || 
                $this->data['page_id'] == 'myproperties' ||
                $this->data['page_id'] == 'myprofile' ||
                $this->data['page_id'] == 'myrates' ||
                $this->data['page_id'] == 'editrate' ||
                $this->data['page_id'] == 'deleterate' ||
                $this->data['page_id'] == 'editproperty' ||
                $this->data['page_id'] == 'deleteproperty' ||
                $this->data['page_id'] == 'logout' ||
                $this->data['page_id'] == 'listproperty' ||
                $this->data['page_id'] == 'myreservations' ||
                $this->data['page_id'] == 'deletereservation' ||
                $this->data['page_id'] == 'viewreservation' ||
                $this->data['page_id'] == 'login_book'||
                $this->data['page_id'] == 'do_purchase' ||
                $this->data['page_id'] == 'notify_payment' || 
                $this->data['page_id'] == 'cancel_payment' ||
                $this->data['page_id'] == 'do_purchase_package' ||
                $this->data['page_id'] == 'do_purchase_featured' ||
                $this->data['page_id'] == 'loginfacebook' ||
                $this->data['page_id'] == 'do_purchase_activation')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == 'maskingsubmit')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['lang_code'] == 'treefield')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(2);
            $this->data['page_id'] = '';
        }

        if(empty($this->data['page_id']))
        {
            // Get first menu item page
            $first_page = $this->page_m->get_first();
            
            if(!empty($first_page))
                $this->data['page_id'] = $first_page->id;
        }
        else if(!is_numeric($this->data['page_id']))
        {
            $this->data['page_id'] = $this->page_m->get_id_by_name ($this->data['page_id']);
        }
        
        if(empty($this->data['lang_code']))
        {
            $this->data['lang_code'] = $this->language_m->get_default();
        }
        
        $this->data['lang_id'] = $this->language_m->get_id($this->data['lang_code']);
        
        if(empty($this->data['lang_id']))
            show_404(current_url());

        $this->data['page_current_url'] = site_url($this->uri->uri_string());
        
        // Check if is it RTL
        $this->data['is_rtl'] = array();
        $lang_data = $this->language_m->get($this->data['lang_id']);
        $rtl_test = $this->input->get('test', TRUE);
        if($lang_data->is_rtl == 1 || $rtl_test == 'rtl')
        {
            $this->data['is_rtl'][]= array('count'=>'1');
        }
        
        // Fetch menu
        $this->temp_data['menu'] = $this->page_m->get_nested($this->data['lang_id']);

        // Fetch current page
        $this->temp_data['page'] = $this->page_m->get_lang($this->data['page_id']);
               
        if(!empty($this->temp_data['page']) && !empty($this->data['page_id'])){
            $this->data['page_navigation_title'] = $this->temp_data['page']->{'navigation_title_'.$this->data['lang_id']};
            $this->data['page_title'] = $this->temp_data['page']->{'title_'.$this->data['lang_id']};
            $this->data['page_body']  = $this->temp_data['page']->{'body_'.$this->data['lang_id']};
            $this->data['page_description']  = character_limiter(strip_tags($this->temp_data['page']->{'description_'.$this->data['lang_id']}), 160);
            $this->data['page_keywords']  = $this->temp_data['page']->{'keywords_'.$this->data['lang_id']};
        }
        else
        {
            if (!is_resource($CI->db->conn_id) && !is_object($CI->db->conn_id))
                show_error('Database conenction failed');

            show_404(current_url());
        }
        
        // URL-s
        $this->data['ajax_load_url'] = site_url('frontend/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id']);
        $this->data['ajax_showroom_load_url'] = site_url('showroom/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$this->input->get('cat', TRUE));
        $this->data['ajax_expert_load_url'] = site_url('expert/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$this->input->get('cat', TRUE));
        $this->data['ajax_news_load_url'] = site_url('news/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$this->input->get('cat', TRUE));
        
        $this->data['typeahead_url'] = site_url('frontend/typeahead/'.$this->data['lang_code'].'/'.$this->data['page_id']);
        
        // Load custom translations
        $this->config->set_item('language', $this->language_m->get_name($this->data['lang_code']));
        //$this->lang->load('frontend_base');
        
        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].'/language/'.$this->language_m->get_name($this->data['lang_code'])))
        {
            $this->lang->load('frontend_template', '', FALSE, TRUE, FCPATH.'templates/'.$this->data['settings_template'].'/');
        }
        else
        {
            $this->config->set_item('language', 'english');
            $this->lang->load('frontend_template', '', FALSE, TRUE, FCPATH.'templates/'.$this->data['settings_template'].'/');
            //$this->config->set_item('language', $this->language_m->get_name($this->data['lang_code']));
        }
        
        if(!file_exists(APPPATH.'language/'.$this->language_m->get_name($this->data['lang_code']).'/form_validation_lang.php'))
        {
            $this->config->set_item('language', 'english');
        }
        
        // Define language for template
        $lang = $this->lang->get_array();
        foreach($lang as $key=>$row)
        {
            $this->data['lang_'.$key] = $row;
        }
        
        // Color definition for demo purposes
        $this->data['color'] = '';
        $this->data['color_path'] = '';
        $this->data['has_color'] = array();
        $this->data['has_color_picker'] = array();
        $color = $this->input->get('color', TRUE);
        if(empty($color))
        {
            $color = $this->session->userdata('color');
        }
        if($this->config->item('color') !== FALSE)
        {
            $color = $this->config->item('color');
        }
        if($this->config->item('color_picker') !== FALSE)
        {
            if($this->config->item('color_picker') == TRUE)
            {
                $this->data['has_color_picker'][] = array('selected_color'=>$color);
            }
        }
        
        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].'/assets/css/styles_'.$color.'.css') &&
           file_exists(FCPATH.'templates/'.$this->data['settings_template'].'/assets/img/markers/'.$color))
        {
            $this->data['color'] = $color;
            $this->data['color_path'] = $color.'/';
            $this->data['has_color'][] = array('color'=>$color);
            $this->session->set_userdata('color', $color);
        }
        
        // homepage_url
        $this->data['homepage_url'] = site_url('');
        $this->data['homepage_url_lang'] = site_url($this->data['lang_code']);
        
        /* Check login */
        $this->data['is_logged_user'] = array();
        $this->data['is_logged_other'] = array();
        $this->data['not_logged'][] = array('count'=>'1');
        if($this->user_m->loggedin() == TRUE)
        {
            if($this->session->userdata('type') == 'USER')
            {
                $this->data['is_logged_user'][] = array('count'=>'1');
                $this->data['not_logged'] = array();
            }
            else
            {
                $this->data['is_logged_other'][] = array('count'=>'1');
                $this->data['not_logged'] = array();
            }
            
            $this->data['loged_user'] = $this->session->userdata('name_surname');
        }
        
        $this->data['logout_url'] = site_url('frontend/logout/'.$this->data['lang_code']);
        $this->data['login_url'] = site_url('admin/dashboard');

        $this->data['front_login_url'] = site_url('frontend/login/'.$this->data['lang_code']);
        $this->data['myproperties_url'] = site_url('frontend/myproperties/'.$this->data['lang_code']);
        $this->data['myprofile_url'] = site_url('frontend/myprofile/'.$this->data['lang_code']);
        $this->data['myreservations_url'] = site_url('frontend/myreservations/'.$this->data['lang_code']);
        $this->data['myresearch_url'] = site_url('fresearch/myresearch/'.$this->data['lang_code']);
        $this->data['api_private_url'] = site_url('privateapi');
        $this->data['myrates_url'] = site_url('frontend/myrates/'.$this->data['lang_code']);
        $this->data['myfavorites_url'] = site_url('ffavorites/myfavorites/'.$this->data['lang_code']);
        
        if(config_item('enable_restricted_mode') === TRUE)
        {
            if(count($this->data['not_logged']) > 0 && $this->uri->segment(2) != 'login')
            {
                redirect($this->data['front_login_url']);
            }
        }
                
        $this->data['search_query'] = $this->input->get('search');

        if(empty($this->data['search_query']))
            $this->data['search_query'] = '';
        
        // Get slideshow
        $rep_slideshow_images = $this->slideshow_m->get_repository_images();
        
        $this->data['slideshow_images'] = array();
        foreach($rep_slideshow_images as $key=>$file)
        {
            $slideshow_image = array();
            $slideshow_image['num'] = $key;
            $slideshow_image['url'] = base_url('files/'.$file->filename);
            $slideshow_image['thumb_url'] = base_url('files/thumbnail/'.$file->filename);
            $slideshow_image['filename'] = url_title_cro($file->filename, ' ');
            $slideshow_image['first_active'] = '';
            if($key==0)$slideshow_image['first_active'] = 'active';
            
            $this->data['slideshow_images'][] = $slideshow_image;
        }
        // End Get slideshow
        
        /* [CAPTCHA Helper] */
        if(config_item('captcha_disabled') === FALSE)
        {
            $this->load->helper('captcha');
            $captcha_hash = substr(md5(rand(0, 999).time()), 0, 5);
            $captcha_hash_old = $this->session->userdata('captcha_hash');
            if(isset($_POST['captcha_hash']))
                $captcha_hash_old = $_POST['captcha_hash'];
            
            $this->data['captcha_hash_old'] = $captcha_hash_old;
            $this->session->set_userdata('captcha_hash', $captcha_hash);

            $vals = array(
                'word' => substr(md5($captcha_hash.config_item('encryption_key')), 0, 5),
                'img_path' => FCPATH.'files/captcha/',
                'img_url' => base_url('files/captcha').'/',
                'font_path' => FCPATH.'admin-assets/font/verdana.ttf',
                'img_width' => 100,
                'img_height' => 30,
                'expiration' => 7200
                );

            $this->data['captcha'] = create_captcha($vals);
            $this->data['captcha_hash'] = $captcha_hash;
        }
        /* [/CAPTCHA Helper] */
        
        
		// Load stuff
        //$this->load->model('page_m');
        
		// Fetch navigation
		//$this->data['menu'] = $this->page_m->get_nested();
        //$this->data['news_archive_link'] = $this->page_m->get_archive_link();
		//$this->data['meta_title'] = config_item('site_name');
	}
}