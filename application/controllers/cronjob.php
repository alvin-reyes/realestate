<?php

class Cronjob extends CI_Controller
{

    private $enable_output= false;
    private $enable_debug = false;
    private $default_language = 'english';

    public function __construct()
    {
        parent::__construct();
        
        $this->config->set_item('language', $this->default_language);
        $this->lang->load('backend_base');
    }
    
	public function index()
	{
		echo 'Hello, cronjob here!';
        exit();
	}
    
    public function research($output = NULL)
    {
        error_reporting(E_ERROR);
        
        $this->load->model('savedsearch_m');
        $this->load->model('estate_m');
        $this->load->model('language_m');
        $this->load->model('option_m');
        $this->load->model('settings_m');
        $this->load->model('user_m');
        $settings = $this->settings_m->get_fields();
        $emails_stack = array();
        
        if($output == 'output')
            $this->enable_output = true;
        
        if($this->enable_output) echo 'Research started!'."\n";
        
        // Fetch last date from research database
        $research_from_date = date('Y-m-d H:i:s');
        $row = $this->savedsearch_m->get_by(array('date_last_informed !='=>'NULL'), true, 1, 'date_last_informed');
        if(!empty($row))
        {
            if($row->date_last_informed != NULL)
                $research_from_date = $row->date_last_informed;
            
            if($this->enable_output) echo 'Research from date: '.$research_from_date.''."\n";
        }
        else
        {
            exit('No researches found!');
        }
        
        // For all properties
        $options_c = array();
        foreach($this->language_m->db_languages_id as $id=>$code)
        {
            $options_c[$code] = $this->option_m->get_options($id);
        }
        
        $estates_to_research = $this->estate_m->get_by(array('date_modified >'=>$research_from_date));
        //print_r($estates_to_research);
        if($this->enable_output) echo 'Total estates for research: '.count($estates_to_research).''."\n";
        $count_emails_try = 0;
        $count_emails_success = 0;
        foreach($estates_to_research as $key_e=>$row_e){
            
            // For all researches
            $researches_all = $this->savedsearch_m->get();
            foreach($researches_all as $key_r=>$row_r){
                
            if(strtotime($row_r->date_last_informed) > strtotime($row_e->date_modified))
                continue;
                
            $parameters = json_decode($row_r->parameters);
            $acceptable_research = true;
            //print_r($parameters);
            
            // Check if research $parameters include that property
            $options = $options_c[$row_r->lang_code];
            //print_r($options);
            $parameters_array =  (array) $parameters;
            $post_option = array();
            $post_option_sum = ' ';
            foreach($parameters_array as $key=>$val)
            {
                $tmp_post = $parameters_array[$key];
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
                
                if($key == 'v_rectangle_ne' || $key == 'v_rectangle_sw')
                {
                    $post_option[$key] = $parameters_array[$key];
                }
                
            }
            
            /* Define purpose */
            $this->data['is_purpose_rent'] = array();
            $this->data['is_purpose_sale'] = array();
            //$this->data['is_purpose_sale'][] = array('count'=>'1');
            
            if(strpos($post_option_sum, lang_check('Rent')) !== FALSE)
            {
                $this->data['is_purpose_rent'][] = array('count'=>'1');
            }
            if(strpos($post_option_sum, lang_check('Sale')) !== FALSE)
            {
                $this->data['is_purpose_sale'][] = array('count'=>'1');
            }
            
            // print_r($post_option);echo ''; Before check
            // End fetch post values
            
            foreach($post_option as $key=>$val)
            {
                if(is_numeric($key) || $key == 'smart')
                {
                    if(strpos($row_e->search_values, $val) === FALSE)
                    {
                        // acceptable rule
                        $acceptable_research = false;
                    }
                }
                else if($key == 'v_rectangle_ne')
                {
                    if(!empty($post_option['v_rectangle_sw']))
                    {
                        $gps_ne = explode(', ', $post_option['v_rectangle_ne']);
                        $gps_sw = explode(', ', $post_option['v_rectangle_sw']);
            
                        if($row_e->lat < $gps_ne[0] && $row_e->lat > $gps_sw[0] &&
                           $row_e->lng < $gps_ne[1] && $row_e->lng > $gps_sw[1] )
                        {
                            
                        }
                        else
                        {
                            $acceptable_research = false;
                        }
                    }
                }
                else if(is_numeric($val))
                {
                    $option_num = $key;
                    $row = (array) $row_e; // row from estate, convert to array
                    $val1 = $val;
                    
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
                                $acceptable_research = false;
                            }
                        }
                        else if(!isset($options[$row['id']][$option_num]))
                        {
                            $acceptable_research = false;
                        }
                        else if($options[$row['id']][$option_num] < $val1)
                        {
                            $acceptable_research = false;
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
                                $acceptable_research = false;
                            }
                            else
                            {
//                                echo $val1."\r\n";
//                                echo $options[$row['id']][$option_num]."\r\n";
//                                echo $options[$row['id']][$option_num+1]."\r\n";
                                
                                if( ($options[$row['id']][$option_num] > $val1 || empty($options[$row['id']][$option_num])) && 
                                    ($options[$row['id']][$option_num+1] > $val1 || empty($options[$row['id']][$option_num+1]) || $row['id'] != 36 )  )
                                {
                                    $acceptable_research = false;
//                                    echo "unset\r\n";
                                }
                            }
                        }
                        else if(!isset($options[$row['id']][$option_num]) || empty($options[$row['id']][$option_num]))
                        {
                            $acceptable_research = false;
                        }
                        else if($options[$row['id']][$option_num] > $val1)
                        {
                            $acceptable_research = false;
                        }
                    }
                    else
                    {
                        if(!isset($options[$row['id']][$option_num]))
                        {
                            $acceptable_research = false;
                        }
                        else if($options[$row['id']][$option_num] != $val1)
                        {
                            $acceptable_research = false;
                        }
                    }
                }
            }
            
            if($acceptable_research)
            {
                // Send message to user
                if($this->enable_debug) echo 'Property: '.$row_e->id.'';
                if($this->enable_debug) { print_r($post_option); echo ''; }
                
                // Add email to sending stack
                $email_data = array();
                $email_data['property_id'] = $row_e->id;
                $email_data['lang_code'] = $row_r->lang_code;
                $email_data['research_id'] = $row_r->id;
                $emails_stack[$row_r->user_id][$row_e->id.'_'.$row_r->id] = $email_data;
            }
            }
        }

        // Send emails
        $count_emails_try = count($emails_stack);
        //print_r($emails_stack);
        foreach($emails_stack as $user_id_k=>$emails_data){
            
            //print_r($emails_data);
            
            // Send email
            $user = $this->user_m->get($user_id_k);
            
            if(!empty($user->mail))
            {
                $user_email = $user->mail;
                if($this->enable_debug) echo 'Email to: '.$user_email.''."\n";;

                $this->load->library('email');
                $config_mail['mailtype'] = 'html';
                $this->email->initialize($config_mail);
                $this->email->from($settings['noreply'], 'Web page');
                $this->email->to($user_email);
                
                $this->email->subject(lang_check('New listing from your saved research!'));
                
                $data = array();
                $data['message'] = lang_check('New listings from your saved research!');
                $researches_to_reset = array();
                foreach($emails_data as $email_data_v){
                    $data['property_links'][$email_data_v['property_id']] = '<a href="'.site_url('property/'.$email_data_v['property_id'].'/'.$email_data_v['lang_code']).'#content">'.lang_check('Check out property').' #'.$email_data_v['property_id'].'</a>';
                    $researches_to_reset[$email_data_v['research_id']] = $email_data_v['research_id'];
                }

                $data['research_link'] = '<a href="'.site_url('frontend/login/').'#content">'.lang_check('Manage your saved researches').'</a>';
            
                $message = $this->load->view('email/research_new_listing', $data, TRUE);
                
                $this->email->message($message);
                if ( ! $this->email->send() )
                {
                    if($this->enable_debug) echo 'Email sanding failed to: '.$user_email.''."\n";;
                }
                else
                {
                    // update research date_last_informed
                    foreach($researches_to_reset as $res_id)
                    {
                        $this->savedsearch_m->save(array('date_last_informed'=>date('Y-m-d H:i:s')), $res_id);
                    }
                    
                    $count_emails_success++;
                }
            }
            
        }
        
        if($this->enable_output) echo 'Email try: '.$count_emails_try.''."\n";;
        if($this->enable_output) echo 'Email sent: '.$count_emails_success.''."\n";;
        
        if($this->enable_output) echo 'Research completed!'."\n";
        exit();
    }
    
    public function favorites($output = NULL)
    {
        error_reporting(E_ERROR);
        
        $this->load->model('favorites_m');
        $this->load->model('estate_m');
        $this->load->model('language_m');
        $this->load->model('option_m');
        $this->load->model('settings_m');
        $this->load->model('user_m');
        $settings = $this->settings_m->get_fields();
        $emails_stack = array();
        
        if($output == 'output')
            $this->enable_output = true;
        
        if($this->enable_output) echo 'Favorites alert started!'."\n";
        
        // Fetch last date from favorites database
        $research_from_date = date('Y-m-d H:i:s');
        $row = $this->favorites_m->get_by(array('date_last_informed !='=>'NULL'), true, 1, 'date_last_informed');
        if(!empty($row))
        {
            if($row->date_last_informed != NULL)
                $research_from_date = $row->date_last_informed;

            if($this->enable_output) echo 'Changes from date: '.$research_from_date.''."\n";
        }
        else
        {
            exit('No favorites found!');
        }
        
        // For all properties
        $options_c = array();
        foreach($this->language_m->db_languages_id as $id=>$code)
        {
            $options_c[$code] = $this->option_m->get_options($id);
        }
        
        $estates_to_research = $this->estate_m->get_by(array('date_modified >'=>$research_from_date));
        //print_r($estates_to_research);
        if($this->enable_output) echo 'Total estates for check: '.count($estates_to_research).''."\n";
        $count_emails_try = 0;
        $count_emails_success = 0;
        foreach($estates_to_research as $key_e=>$row_e){
            
            // For all favorites
            $researches_all = $this->favorites_m->get();
            foreach($researches_all as $key_r=>$row_r){ 
                if(strtotime($row_r->date_last_informed) > strtotime($row_e->date_modified))
                    continue;

                // Send message to user
                if($this->enable_debug) echo 'Property: '.$row_e->id.'';
                
                // Add email to sending stack
                $email_data = array();
                $email_data['property_id'] = $row_e->id;
                $email_data['lang_code'] = $row_r->lang_code;
                $email_data['research_id'] = $row_r->id;
                $emails_stack[$row_r->user_id][$row_e->id] = $email_data;
            }
        }

        // Send emails
        $count_emails_try = count($emails_stack);
        //print_r($emails_stack);
        foreach($emails_stack as $user_id_k=>$emails_data){
            // Send email
            $user = $this->user_m->get($user_id_k);
            
            if(!empty($user->mail))
            {
                $user_email = $user->mail;
                if($this->enable_debug) echo 'Email to: '.$user_email.''."\n";;

                $this->load->library('email');
                $config_mail['mailtype'] = 'html';
                $this->email->initialize($config_mail);
                $this->email->from($settings['noreply'], 'Web page');
                $this->email->to($user_email);
                
                $this->email->subject(lang_check('Changes on your property favorites!'));
                
                $data = array();
                $data['message'] = lang_check('Changes on your property favorites!');
                foreach($emails_data as $email_data_v){
                    $data['property_links'][] = '<a href="'.site_url('property/'.$email_data_v['property_id'].'/'.$email_data_v['lang_code']).'#content">'.lang_check('Check out property').' #'.$email_data_v['property_id'].'</a>';

                }

                $data['research_link'] = '<a href="'.site_url('frontend/login/').'#content">'.lang_check('Manage your favorites').'</a>';
            
                $message = $this->load->view('email/favorites_changed_listing', $data, TRUE);
                
                $this->email->message($message);
                if ( ! $this->email->send() )
                {
                    if($this->enable_debug) echo 'Email sanding failed to: '.$user_email.''."\n";;
                }
                else
                {
                    // update research date_last_informed
                    $this->favorites_m->save(array('date_last_informed'=>date('Y-m-d H:i:s')), $email_data_v['research_id']);
                    $count_emails_success++;
                }
            }
            
        }
        
        if($this->enable_output) echo 'Email try: '.$count_emails_try.''."\n";;
        if($this->enable_output) echo 'Email sent: '.$count_emails_success.''."\n";;
        
        if($this->enable_output) echo 'Favorites alerts completed!'."\n";
        exit();
    }

}