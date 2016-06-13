<?php

class Property extends Frontend_Controller
{

	public function __construct ()
	{
		parent::__construct();
	}
    
    public function _remap($method)
    {
        $this->index();
    }
    
    private function _get_purpose()
    {
        if(config_item('all_results_default') === TRUE)
        {
            $this->data['purpose_defined'] = '';
            return '';
        }
        
        if(isset($this->data['is_purpose_sale'][0]['count']))
        {
            return lang('Sale');
        }
        
        if(isset($this->data['is_purpose_rent'][0]['count']))
        {
            return lang('Rent');
        }
        
        return lang('Sale');
    }

    public function index()
    {
        $print = false;
        if($this->input->get('v')=='print')
            $print = true;
        
        $lang_code = (string) $this->uri->segment(3);
        $property_id = (string) $this->uri->segment(2);
        $property_slug = (string) $this->uri->segment(4);
        
        $lang_id = $this->data['lang_id'];
        
        $option_sum = '';
        
        /* Fetch estate data */
        $this->data['property_id'] = $property_id;
        
        // update counter
        $this->estate_m->update_counter($property_id);
        
        /* Fetch options names */
        //$this->data['options'] = $options = $this->option_m->get_options($lang_id);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);
        $option_categories = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_prefix_'.$row->option_id] = $row->prefix;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_name'] = $row->option;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_type'] = $row->type;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_suffix'] = $row->suffix;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_prefix'] = $row->prefix;
            
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_checkbox'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_dropdown'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_text'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_upload'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_tree'] = array();
            
            $this->data['category_options_count_'.$row->parent_id] = 0;
            
            $option_categories[$row->option_id] = $row->parent_id;
        }
        /* End fetch options names */
        
        /* Fetch estate data */
        $estate_data = $this->estate_m->get_array($property_id, TRUE, array('language_id'=>$lang_id));
        
        if(isset($estate_data['is_activated']) && $estate_data['is_activated'] != 1)
        {
            //show_error(lang_check('Propertyactivated'));
            redirect('');
        }
        
        if(!isset($estate_data['id']))
        {
            show_404();
        }
        
        foreach($estate_data as $key=>$val)
        {
            $this->data['estate_data_'.$key] = $val;
        }
        
        $json_obj = json_decode($estate_data['json_object']);
        
        if(!empty($json_obj))
        foreach($json_obj as $key_json=>$val)
        {
            $j_parts = explode('_',$key_json);
            $key = $j_parts[1];
            
            if($val != '')
            {
                if(substr($val, -2) == ' -')$val=substr($val, 0, -2);
                $this->data['estate_data_option_'.$key] = $val;
                
                // Set Category data
                if(isset($option_categories[$key]) && empty($options[$estate_data['id']][$option_categories[$key]]))
                {
                    $this->data['category_options_'.$option_categories[$key]][$key]['option_value'] = $val;
                    
//                    if(!empty($options[$estate_data['id']][$option_categories[$key]]))
//                    {
//                        print_r($option_categories[$key]);
//                        echo $options[$estate_data['id']][$option_categories[$key]];
//                        echo '<br />';
//                    }
                    
                    if(!empty($val))
                        $this->data['category_options_count_'.$option_categories[$key]]++;
    
                    if($this->data['category_options_'.$option_categories[$key]][$key]['option_type'] == 'CHECKBOX')
                    {
                        //you can define this via cms_config.php, $config['show_not_available_amenities'] = TRUE;
                        if(config_item('show_not_available_amenities') !== FALSE)
                        {
                            $this->data['category_options_'.$option_categories[$key]][$key]['is_checkbox'][] = array('true'=>'true');
                        }
                        else
                        {
                            if($val == 'true')
                                $this->data['category_options_'.$option_categories[$key]][$key]['is_checkbox'][] = array('true'=>'true');
                        }

                    }
                    elseif($this->data['category_options_'.$option_categories[$key]][$key]['option_type'] == 'DROPDOWN')
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_dropdown'][] = array('true'=>'true');
                    }
                    elseif($this->data['category_options_'.$option_categories[$key]][$key]['option_type'] == 'UPLOAD')
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_upload'][] = array('true'=>'true');
                    }
                    elseif($this->data['category_options_'.$option_categories[$key]][$key]['option_type'] == 'TREE')
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_tree'][] = array('true'=>'true');
                    }
                    else
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_text'][] = array('true'=>'true');
                    }
                    
                    $this->data['category_options_'.$option_categories[$key]][$key]['option_id'] = $key;
                    
                    /* icon */
                    $this->data['category_options_'.$option_categories[$key]][$key]['icon']='';
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/icons/option_id/'.$key.'.png'))
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['icon']=
                        '<img src="assets/img/icons/option_id/'.$key.'.png" alt="'.$val.'"/>';
                    }
                }
            }
        }
        
        if(!isset($this->data['estate_data_option_10']))$this->data['estate_data_option_10'] = '';
        $url_title = url_title_cro($this->data['estate_data_option_10']);
        if(empty($url_title))$url_title='title_undefined';
        $this->data['estate_data_printurl'] = 
            site_url_q($this->data['listing_uri'].'/'.$estate_data['id'].'/'.$lang_code.'/'.$url_title, 'v=print');
        
        $this->data['estate_data_icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
        if(isset($this->data['estate_data_option_6']))
        {
            if($this->data['estate_data_option_6'] != '' && $this->data['estate_data_option_6'] != 'empty')
            {
                if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                               '/assets/img/markers/'.$this->data['color_path'].$this->data['estate_data_option_6'].'.png'))
                $this->data['estate_data_icon'] = 'assets/img/markers/'.$this->data['color_path'].$this->data['estate_data_option_6'].'.png';
                
                if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].'selected/'.$this->data['estate_data_option_6'].'.png'))
                $this->data['estate_data_icon'] = 'assets/img/markers/'.$this->data['color_path'].'selected/'.$this->data['estate_data_option_6'].'.png';
            }
        }
        
        /* End Fetch estate data */ 
        
        /* Define purpose */
        $this->data['is_purpose_rent'] = array();
        $this->data['is_purpose_sale'] = array();
        $this->data['is_purpose_sale'][] = array('count'=>'1');
        
        if(isset($this->data['estate_data_option_4'])){
            if(stripos($this->data['estate_data_option_4'], lang_check('Rent')) !== FALSE)
            {
                $this->data['is_purpose_rent'][] = array('count'=>'1');
            }
            if(stripos($this->data['estate_data_option_4'], lang_check('Sale')) !== FALSE)
            {
                $this->data['is_purpose_sale'][] = array('count'=>'1');
            }
        }
        /* End define purpose */
        
        
        //generate repository list to laod images
        $where_in = array($estate_data['repository_id']);
        
        $this->load->model('ads_m');
        $ads_act = $this->ads_m->get_by(array('is_activated'=>1));
        foreach($ads_act as $row)
        {
            $where_in[] = $row->repository_id;
        }

        // Fetch all files by repository_id
        $files = $this->file_m->get_where_in($where_in);
        //$files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);
            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($estate_data['repository_id'] == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
                $this->data['documents_'.$file->repository_id][] = $file;
                if($estate_data['repository_id'] == $file->repository_id)
                {
                    $this->data['page_documents'][] = $file;
                }
            }
        }
        
        // Has attributes
        $this->data['has_page_documents'] = array();
        if(count($this->data['page_documents']))
            $this->data['has_page_documents'][] = array('count'=>count($this->data['page_documents']));
        
        $this->data['has_page_images'] = array();
        if(count($this->data['page_images']))
            $this->data['has_page_images'][] = array('count'=>count($this->data['page_images']));
        
        /* Fetch estate data end */
        
        if(!empty($property_id)){
            if(!isset($json_obj->field_10))$json_obj->field_10 = '-';
            if(!isset($json_obj->field_17))$json_obj->field_17 = '-';
            if(!isset($json_obj->field_8))$json_obj->field_8 = '-';
            
            $this->data['page_navigation_title'] = $json_obj->field_10;
            $this->data['page_title'] = $json_obj->field_10;
            $this->data['page_body']  = $json_obj->field_17;
            $this->data['page_description'] = character_limiter(strip_tags($json_obj->field_8), 160);
            
            $this->data['estate_image_url'] = 'assets/img/no_image.jpg';
            if(isset($this->data['page_images'][0]))
            {
                $this->data['estate_image_url'] = $this->data['page_images'][0]->url;
            }
        }
        else
        {
            show_404(current_url());
        }
        
        /* Fetch agent */
        
        $agent = $this->user_m->get_agent($this->data['property_id']);
        
        if(count($agent))
        {
            $this->data['agent_name_surname'] = $agent['name_surname'];
            $this->data['agent_phone'] = $agent['phone'];
            $this->data['agent_mail'] = $agent['mail'];
            $this->data['agent_address'] = $agent['address'];
            $this->data['agent_id'] = $agent['id'];
            $this->data['agent_name_title'] = url_title_cro($agent['name_surname']);
            $this->data['agent_url'] = site_url('profile/'.$agent['id'].'/'.$this->data['lang_code'].'/'.$this->data['agent_name_title']);
        }
        
        $this->data['has_agent'] = array();
        if(count($agent))
            $this->data['has_agent'][] = array('count'=>count($agent));
        
        // Thumbnail
        if(count($agent) && isset($agent['image_user_filename']))
        {
            $this->data['agent_image_url'] = base_url('files/thumbnail/'.$agent['image_user_filename']);
        }
        else
        {
            $this->data['agent_image_url'] = 'assets/img/user-agent.png';
        }
        
        // Get agent estates
        
        $agent_estates_list = array();
        if(isset($agent['id']))
            $agent_estates_list = $this->user_m->get_estates($agent['id']);
        
        /* End fetch agent */
        
        /* Get all estates data */
        $estates = $this->estate_m->get_by(array('is_activated' => 1, 'language_id' => $lang_id), false, 100, 'id DESC');
        //$options = $this->option_m->get_options($this->data['lang_id']);
        //$options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        $this->data['all_estates'] = array();
        $this->data['agent_estates'] = array();
        foreach($estates as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = $estate_obj->address;
            $estate['date'] = $estate_obj->date;
            $estate['is_featured'] = $estate_obj->is_featured;
            
            $json_obj = json_decode($estate_obj->json_object);
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['icons'][]['icon']= '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';
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
            
            if($this->data['property_id'] == $estate['id']){
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].'selected/'.$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'selected/'.$estate['option_6'].'.png';
                }
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
                $estate['thumbnail_url'] = base_url('files/thumbnail/'.$estate_obj->image_filename);
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['all_estates'][] = $estate;
            
            if($property_id != $estate_obj->id)
            if(in_array($estate_obj->id, $agent_estates_list))
                if(count($this->data['agent_estates'])<6)
                    $this->data['agent_estates'][] = $estate;
        }
        
        $this->data['all_estates_center'] = calculateCenter($estates);
        
        /* End get all estates data */
        
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
            $estate['counter_views'] = $estate_arr->counter_views;
            $estate['estate_data_id'] = $estate_arr->id;
            
            $json_obj = json_decode($estate_arr->json_object);
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($json_obj->{"field_$key1"}))
                {
                    $row1 = $json_obj->{"field_$key1"};
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
            if(isset($estate_arr->image_filename))
            {
                $estate['thumbnail_url'] = base_url('files/thumbnail/'.$estate_arr->image_filename);
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['last_estates'][] = $estate;
        }
        
        /* END Get last n properties */

        // Get slideshow
        //$files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['slideshow_property_images'] = array();
        $num=0;
//        foreach($files as $key=>$file)
//        {
//            if($estate_data['repository_id'] == $file->repository_id)
//            {
//                $slideshow_image = array();
//                $slideshow_image['num'] = $num;
//                $slideshow_image['url'] = base_url('files/'.$file->filename);
//                $slideshow_image['first_active'] = '';
//                if($num==0)$slideshow_image['first_active'] = 'active';
//                
//                $this->data['slideshow_property_images'][] = $slideshow_image;
//                $num++;
//            }
//        }

        foreach($this->data['page_images']  as $key=>$file)
        {
            if($estate_data['repository_id'] == $file->repository_id)
            {
                $slideshow_image = array();
                $slideshow_image['num'] = $num;
                $slideshow_image['url'] = str_replace(' ', '%20', base_url('files/'.$file->filename));
                $slideshow_image['first_active'] = '';
                if($num==0)$slideshow_image['first_active'] = 'active';
                
                $this->data['slideshow_property_images'][] = $slideshow_image;
                $num++;
            }
        }
        // End Get slideshow
        
        /* Helpers */
        $this->data['year'] = date('Y');
        /* End helpers */
        
        /* Widgets functions */
        $this->data['print_menu'] = get_menu($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_menu_realia'] = get_menu_realia($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_lang_menu'] = get_lang_menu($this->language_m->get_array_by(array('is_frontend'=>1)), $this->data['lang_code']);
        $this->data['page_template'] = $this->temp_data['page']->template;
        /* End widget functions */
        
        $this->data['validation_errors'] = '';
        $this->data['form_sent_message'] = '';
        
        $this->load->model('reviews_m');
        
        
        if(isset($_POST['stars']) && isset($this->data['loged_user']))
        {
            /* Validation for reviews */
            
            
            $this->form_validation->set_rules($this->reviews_m->rules);
            
            // Process the form
            if($this->form_validation->run() == TRUE)
            {
                $data_review = $this->page_m->array_from_post(array('stars', 'message'));
                
                // Save reviews to database
                $data = array();
                $data['listing_id'] = $property_id;
                $data['user_id'] = $this->session->userdata('id');
                $data['stars'] = $data_review['stars'];
                $data['message'] = $data_review['message'];
                $data['is_visible'] = 1;
                $data['date_publish'] = date('Y-m-d H:i:s');
                $this->reviews_m->save($data);
            }
            
            $this->data['reviews_validation_errors'] = validation_errors();

            /* End Validation for reviews */
        }
        else
        {
            /* Validation for contact */
            $rules = array(
                'firstname' => array('field'=>'firstname', 'label'=>'lang:FirstLast', 'rules'=>'trim|required|xss_clean'),
                'email' => array('field'=>'email', 'label'=>'lang:Email', 'rules'=>'trim|required|valid_email|xss_clean'),
                'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|required|xss_clean'),
                'address' => array('field'=>'address', 'label'=>'lang:Address', 'rules'=>'trim|required|xss_clean'),
                'message' => array('field'=>'message', 'label'=>'lang:Message', 'rules'=>'trim|required|xss_clean'),
                'fromdate' => array('field'=>'fromdate', 'label'=>'lang:FromDate', 'rules'=>'trim|xss_clean'),
                'todate' => array('field'=>'todate', 'label'=>'lang:ToDate', 'rules'=>'trim|xss_clean')
            );
            
            if(config_item('captcha_disabled') === FALSE)
                $rules['captcha'] = array('field'=>'captcha', 'label'=>'lang:Captcha', 'rules'=>'trim|required|callback_captcha_check|xss_clean');
            
            if(file_exists(APPPATH.'controllers/admin/booking.php') && count($this->data['is_purpose_rent']) && $this->session->userdata('type')=='USER' && config_item('reservations_disabled') === FALSE)
            {
                $rules['fromdate']['rules'].='|required|callback__check_availability';
                $rules['todate']['rules'].='|required';
            }
            
            $this->form_validation->set_rules($rules);
    
            // Process the form
            if($this->form_validation->run() == TRUE)
            {
                $data_t = $this->page_m->array_from_post(array('firstname', 'email', 'phone', 'message', 'address', 'fromdate', 'todate'));
                
                // Save enquire to database
                $this->load->model('enquire_m');
                $data = array();
                $data['name_surname'] = $data_t['firstname'];
                $data['phone'] = $data_t['phone'];
                $data['mail'] = $data_t['email'];
                $data['message'] = $data_t['message'];
                $data['address'] = $data_t['address'];
                $data['fromdate'] = $data_t['fromdate'];
                $data['todate'] = $data_t['todate'];
                $data['readed'] = 0;
                $data['property_id'] = $this->data['property_id'];
                $data['date'] = date('Y-m-d H:i:s');
                $this->enquire_m->save($data);
                
                $this->session->set_userdata(array('enquire_form'=>$data));
                
                $this->session->set_flashdata('email_sent', 'email_sent_true');
                
                // Send email
                $this->load->library('email');
                $config_mail['mailtype'] = 'html';
                $this->email->initialize($config_mail);
                
                $to_mail = '';
                
                if(count($agent))$to_mail = $agent['mail'];
                
                if(empty($to_mail))$to_mail = $this->data['settings_email'];
                
                $this->email->from($this->data['settings_noreply'], lang_check('Web page'));
                $this->email->to($to_mail);
                
                $this->email->subject(lang_check('Message from real-estate web'));
                
                // $data['property name'] = $this->data['page_title'];
                // unset($data['fromdate'], $data['todate'], $data['readed']);
                
//                $message='';
//                foreach($data as $key=>$value){
//                	$message.="$key:\n$value\n";
//                }

                $message = $this->load->view('email/email_property_inquiry', array('data'=>$data), TRUE);
                $this->email->message($message);
                
                if(ENVIRONMENT != 'development')
                if ( ! $this->email->send())
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_false');
                }
                else
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_true');
                }
                
                if(config_item('reservations_disabled') === FALSE)
                if(file_exists(APPPATH.'controllers/admin/booking.php') && count($this->data['is_purpose_rent']) && 
                   ($this->session->userdata('type')=='USER' || $this->session->userdata('type')==''))
                {
                    $this->load->model('reservations_m');
                    
                    // If user loged in create reservation and redirect to this reservation website
                    if(count($this->data['not_logged']) == 0)
                    {
                        $lang = $this->language_m->get($this->data['lang_id']);
                        $currency_code = $lang->currency_default;
        
                        $data_r = array();
                        $data_r['date_from'] = $data['fromdate'];
                        $data_r['date_to'] = $data['todate'];
                        $data_r['property_id'] = $this->data['property_id'];
                        $data_r['user_id'] = $this->session->userdata('id');
                        $data_r['total_paid'] = 0;
                        $data_r['date_paid_advance'] = NULL;
                        $data_r['date_paid_total'] = NULL;
                        $data_r['currency_code'] = $currency_code;
                        $data_r['is_confirmed'] = '0';
                        
                        $booking_price = $this->reservations_m->calculate_price($data_r['property_id'], 
                                                                                $data_r['date_from'], 
                                                                                $data_r['date_to'], 
                                                                                $data_r['currency_code']);
                        $data_r['total_price'] = $booking_price;
                        
                        $id = $this->reservations_m->save($data_r, NULL);
                        
                        redirect('frontend/viewreservation/'.$this->data['lang_code'].'/'.$id);
                    }
                    else // If NOT user logged in create reservation and redirect to this reservation website
                    {
                        $lang = $this->language_m->get($this->data['lang_id']);
                        $currency_code = $lang->currency_default;
        
                        $data_r = array();
                        $data_r['date_from'] = $data['fromdate'];
                        $data_r['date_to'] = $data['todate'];
                        $data_r['property_id'] = $this->data['property_id'];
                        $data_r['user_id'] = $this->session->userdata('id');
                        $data_r['total_paid'] = 0;
                        $data_r['date_paid_advance'] = NULL;
                        $data_r['date_paid_total'] = NULL;
                        $data_r['currency_code'] = $currency_code;
                        $data_r['is_confirmed'] = '0';
                        
                        $booking_price = $this->reservations_m->calculate_price($data_r['property_id'], 
                                                                                $data_r['date_from'], 
                                                                                $data_r['date_to'], 
                                                                                $data_r['currency_code']);
                        $data_r['total_price'] = $booking_price;
                        
                        $this->session->set_flashdata('data_r', serialize($data_r));
                        redirect('frontend/login_book/'.$this->data['lang_code']);
                    }
                }
                else
                {
                    redirect($this->uri->uri_string());
                }
            }
            
            $this->data['validation_errors'] = validation_errors();
            
            $this->data['form_sent_message'] = '';
            if($this->session->flashdata('email_sent'))
            {
                if($this->session->flashdata('email_sent') == 'email_sent_true')
                {
                    $this->data['form_sent_message'] = '<p class="alert alert-success">'.lang_check('message_sent_successfully').'</p>';
                    
    //                $this->data['form_sent_message'].=' <script type="text/javascript">
    //                                                    /* <![CDATA[ */
    //                                                    var google_conversion_id = 973185194;
    //                                                    var google_conversion_language = "en";
    //                                                    var google_conversion_format = "3";
    //                                                    var google_conversion_color = "ffffff";
    //                                                    var google_conversion_label = "7RR9CJ6C6AcQqsGG0AM";
    //                                                    var google_remarketing_only = false;
    //                                                    /* ]]> */
    //                                                    </script>
    //                                                    <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
    //                                                    </script>
    //                                                    <noscript>
    //                                                    <div style="display:inline;">
    //                                                    <img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/973185194/?label=7RR9CJ6C6AcQqsGG0AM&amp;guid=ON&amp;script=0"/>
    //                                                    </div>
    //                                                    </noscript>';
                }
                else
                {
                    $this->data['form_sent_message'] = '<p class="alert alert-error">'.lang_check('message_sent_error').'</p>';
                }  
            }
            
            
            /* End validation for contact */
        }
        
        // Form errors
        $this->data['form_error_firstname'] = form_error('firstname')==''?'':'error';
        $this->data['form_error_email'] = form_error('email')==''?'':'error';
        $this->data['form_error_phone'] = form_error('phone')==''?'':'error';
        $this->data['form_error_message'] = form_error('message')==''?'':'error';
        $this->data['form_error_address'] = form_error('address')==''?'':'error';
        $this->data['form_error_fromdate'] = form_error('fromdate')==''?'':'error';
        $this->data['form_error_todate'] = form_error('todate')==''?'':'error';
        $this->data['form_error_captcha'] = form_error('captcha')==''?'':'error';
        
        // Form values
        $session_enquire_data = $this->session->userdata('enquire_form');
        
        $sess_first_name = '';
        if(!empty($session_enquire_data['name_surname']))
            $sess_first_name = $session_enquire_data['name_surname'];
            
        $sess_mail = '';
        if(!empty($session_enquire_data['email']))
            $sess_mail = $session_enquire_data['email'];
            
        $sess_phone = '';
        if(!empty($session_enquire_data['phone']))
            $sess_phone = $session_enquire_data['phone'];
            
        $sess_address = '';
        if(!empty($session_enquire_data['address']))
            $sess_address = $session_enquire_data['address'];
            
        $sess_message = '';
        if(!empty($session_enquire_data['message']))
            $sess_message = $session_enquire_data['message'];
        
        $this->data['form_value_firstname'] = set_value('firstname', $sess_first_name);
        $this->data['form_value_email'] = set_value('email', $sess_mail);
        $this->data['form_value_phone'] = set_value('phone', $sess_phone);
        $this->data['form_value_message'] = set_value('message', $sess_message);
        //$this->data['form_value_message'] = set_value('message', 'test message: '.$this->data['property_id']);
        $this->data['form_value_address'] = set_value('address', $sess_address);
        $this->data['form_value_fromdate'] = set_value('fromdate', '');
        $this->data['form_value_todate'] = set_value('todate', '');           
        
        
        $this->data['reviews_submitted'] = false;
        if(file_exists(APPPATH.'controllers/admin/reviews.php'))
        {
            if(count($this->data['not_logged']) == 0)
            {
                $this->data['reviews_submitted'] = $this->reviews_m->check_if_exists($this->session->userdata('id'), $property_id); 
            }
            
            $this->data['reviews_all'] = $this->reviews_m->get_listing(array('listing_id'=>$property_id));
            
            $this->data['avarage_stars'] = intval($this->reviews_m->get_avarage_rating($property_id)+0.5);

        }

        
        /* Fetch options data */
        //$options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
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
                    $options.='<option value="'.$val.'">'.$val.'</option>';
                    $this->data['options_values_arr_'.$row->option_id][] = $val;
                    
                    $active = '';
                    if($this->_get_purpose() == strtolower($val))$active = 'active';
                    $options_li.= '<li class="'.$active.' cat_'.$key2.'"><a href="#">'.$val.'</a></li>';
                    
                    $radio_li.='<label class="checkbox" for="inputRent">
                                <input type="radio" rel="'.$val.'" name="search_option_'.$row->option_id.'" value="'.$key2.'" checked> '.$val.'
                                </label>';
                }
                $this->data['options_values_'.$row->option_id] = $options;
                $this->data['options_values_li_'.$row->option_id] = $options_li;
                $this->data['options_values_radio_'.$row->option_id] = $radio_li;
            }
        }

        /* {MOULE_ADS} */
        $this->load->model('ads_m');
        $this->data['ads'] = array();
        
        foreach($this->ads_m->ads_types as $type_key=>$type_name)
        {
            $ads_by_type = $this->ads_m->get_by(array('type'=>$type_key, 'is_activated'=>1));
            
            $num_ads = count($ads_by_type);

            $this->data['has_ads_'.$type_name] = array();
            if(isset($ads_by_type[0]))
            if($num_ads > 0)
            {
                $rand_ad_key = rand(0, $num_ads-1);
                
                if(isset($ads_by_type[$rand_ad_key]) && isset($this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id]))
                {
                    $rand_image=0;
                    if($ads_by_type[$rand_ad_key]->is_random)
                        $rand_image = rand(0, count($this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id])-1);
                    
                    $this->data['random_ads_'.$type_name.'_link'] = $ads_by_type[$rand_ad_key]->link;
                    $this->data['random_ads_'.$type_name.'_repository'] = $ads_by_type[$rand_ad_key]->repository_id;
                    $this->data['random_ads_'.$type_name.'_image'] = $this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id][$rand_image]->url;
                    $this->data['has_ads_'.$type_name][] = array('count' => $num_ads);
                }
            }
        }
        /* {/MOULE_ADS} */
        
        /* {MOULE_BOOKING} */
        $this->load->model('rates_m');
        $this->load->model('reservations_m');
        
        // Get property rates table
        $where = array('property_id'=>$property_id, 'date_to >'=>date("Y-m-d H:i:s"));
        $this->data['property_rates'] = $this->rates_m->get_lang(NULL, FALSE, $this->data['lang_id'], $where, null, '', 'date_from DESC');
        $this->data['changeover_days'] = array(lang_check('Flexible'), 
                                               lang_check('cal_monday'),
                                               lang_check('cal_tuesday'),
                                               lang_check('cal_wednesday'),
                                               lang_check('cal_thursday'),
                                               lang_check('cal_friday'),
                                               lang_check('cal_saturday'),
                                               lang_check('cal_sunday'));
        
        $this->data['available_dates'] = $this->reservations_m->get_available_dates($property_id);
        
        $prefs = array();
        $prefs['template'] = '
           {table_open}<table border="0" class="av_calender" cellpadding="0" cellspacing="0">{/table_open}
           {heading_row_start}<tr>{/heading_row_start}
           {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
           {heading_title_cell}<th colspan="{colspan}"><span>{heading}</span></th>{/heading_title_cell}
           {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        
           {heading_row_end}</tr>{/heading_row_end}
        
           {week_row_start}<tr>{/week_row_start}
           {week_day_cell}<td><span>{week_day}</span></td>{/week_day_cell}
           {week_row_end}</tr>{/week_row_end}
        
           {cal_row_start}<tr>{/cal_row_start}
           {cal_cell_start}<td>{/cal_cell_start}
        
           {cal_cell_content}<a {content} href="#form">{day}</a>{/cal_cell_content}
           {cal_cell_content_today}<a {content} style="background: red; color:white;" href="{content}">{day}</a>{/cal_cell_content_today}
        
           {cal_cell_no_content}<span class="disabled">{day}</span>{/cal_cell_no_content}
           {cal_cell_no_content_today}<div class="highlight disabled">{day}</div>{/cal_cell_no_content_today}
        
           {cal_cell_blank}<span>&nbsp;</span>{/cal_cell_blank}
        
           {cal_cell_end}</td>{/cal_cell_end}
           {cal_row_end}</tr>{/cal_row_end}
        
           {table_close}</table>{/table_close}
        ';
        
        $this->load->library('calendar', $prefs);
        $this->data['months_availability'] = array();
        $cal_data = array();
        
        $this->data['available_dates_not_selectable'] = $this->reservations_m->get_available_dates($property_id, FALSE);
        
        foreach($this->data['available_dates_not_selectable'] as $key=>$row_time)
        {
            $cal_data[date("m", $row_time)][date("j", $row_time)] = 'class="available not_selectable"';
        }

        foreach($this->data['available_dates'] as $key=>$row_time)
        {
            $cal_data[date("m", $row_time)][date("j", $row_time)] = 'class="available selectable" ref="'.date("Y-m-d", $row_time).'" ref_to="'.date("Y-m-d", strtotime(date("Y-m-d", $row_time).' +7 day')).'"';
        }
        
        for($i=0;$i < 6; $i++)
        {
            $next_month_time = strtotime("+$i month");
            
            if(!isset($cal_data[date("m", $next_month_time)]))
                $cal_data[date("m", $next_month_time)] = array();
            
            //echo date("m", strtotime("+$i month")).'<br />';
            //print_r($cal_data[date("m", strtotime("+$i month"))]);

            $this->data['months_availability'][date("Y-m", $next_month_time)] = $this->calendar->generate(date("Y", $next_month_time), date("m", $next_month_time), $cal_data[date("m", $next_month_time)]);
        }
        
        /* {/MOULE_BOOKING} */
        
        /* {PRIVATE_LISTINGS} */
        $this->data['is_private_listing'] = FALSE;
        
        if(config_db_item('enable_private_listing') === TRUE)
        {
            $this->data['is_private_listing'] = TRUE;
            
            if(count($this->data['not_logged'])==0 && $this->session->userdata('type') == 'ADMIN')
            {
                $this->data['is_private_listing'] = FALSE;
            }
            else if(count($this->data['not_logged'])==0)
            {
                // fetch user/agent
                $user_id = $this->session->userdata('id');
                $user_object = $this->user_m->get($user_id);
                
                // fetch user package
                $this->load->model('packages_m');
                
                if(!empty($user_object->package_id))
                {
                    $user_package = $this->packages_m->get($user_object->package_id);
                    if($user_package->show_private_listings == 1 && $user_package->package_price == 0)
                        $this->data['is_private_listing'] = FALSE;
                    else if($user_package->show_private_listings == 1 && strtotime($user_object->package_last_payment) > time())
                        $this->data['is_private_listing'] = FALSE;
                }
            }
        }
        /* {/PRIVATE_LISTINGS} */
        
        /* {STATS} */
        if(config_item('stats_enabled') == TRUE)
        {
            $this->load->model('stats_m');
            $this->data['views_last_30_min'] = $this->stats_m->views_last_minutes($property_id, 30);
            $this->stats_m->save_stats($property_id);
        }
        /* {/STATS} */

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
        
        if($print)
        {
            $output = $this->parser->parse($this->data['settings_template'].'/property_print.php', $this->data, TRUE);
        }
        else
        {
            $output = $this->parser->parse($this->data['settings_template'].'/property.php', $this->data, TRUE);
        }
        
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function _check_availability($str)
    {   
        $this->load->model('reservations_m');
        $this->load->model('rates_m');
        
        $id = $this->uri->segment(4);
        $date_from = $this->input->post('fromdate');
        $date_to = $this->input->post('todate');
        $property_id = $this->data['property_id'];
        
        $lang = $this->language_m->get($this->data['lang_id']);
        $currency_code = $lang->currency_default;
  
        // check 'from' before 'to', 'from' after 'now'
        if(strtotime($date_from) < time() || strtotime($date_to) < strtotime($date_from))
        {
            $this->form_validation->set_message('_check_availability', lang_check('Please correct dates'));
            return FALSE;
        }

        $is_booked = $this->reservations_m->is_booked($property_id, $date_from, $date_to, $id);
        
        if(count($is_booked) > 0)
        {
            $this->form_validation->set_message('_check_availability', lang_check('Dates already booked'));
            return FALSE;
        }
        
        $changeover_day = $this->reservations_m->changeover_day($property_id, $date_from);
        if($changeover_day  === FALSE)
        {
            $this->form_validation->set_message('_check_availability', lang_check('Changeover day condition is not met'));
            return FALSE;
        }
        
        $min_stay = $this->reservations_m->min_stay($property_id, $date_from, $date_to);
        
        if($min_stay  === FALSE)
        {
            $this->form_validation->set_message('_check_availability', lang_check('Min. stay condition is not met'));
            return FALSE;
        }
        
        $booking_price = $this->reservations_m->calculate_price($property_id, $date_from, $date_to, $currency_code);

        if($booking_price  === FALSE)
        {
            $this->form_validation->set_message('_check_availability', lang_check('No rates defined for selected dates and currency'));
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

}