<?php

class Settings extends Admin_Controller {
	
    public function __construct(){
		parent::__construct();
        $this->load->model('settings_m');
        $this->data['settings'] = $this->settings_m->get_fields();
	}
    
    public function contact()
    {
        $this->index();
    }
    
    public function index() 
    {
        // Set up the form
        $rules = $this->settings_m->rules_contact;
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings');
                exit();
            }
            
            $data = $this->settings_m->array_from_post($this->settings_m->get_post_from_rules($rules));
            $this->settings_m->save_settings($data);
            
            redirect('admin/settings');
        }
        
    	$this->data['subview'] = 'admin/settings/contact';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function system() 
    {
        // Set up the form
        $rules = $this->settings_m->rules_system;
        $this->form_validation->set_rules($rules);
        
        $this->load->model('payments_m');
        $this->data['currencies'] = $this->payments_m->currencies;
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/system');
                exit();
            }
            
            $data = $this->settings_m->array_from_post($this->settings_m->get_post_from_rules($rules));
            $this->settings_m->save_settings($data);
            
            redirect('admin/settings/system');
        }
        
    	$this->data['subview'] = 'admin/settings/system';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function template() 
    {
        // Set up the form
        $rules = $this->settings_m->rules_template;
        $this->form_validation->set_rules($rules);
        
        $this->data['templates_available'] = array();
        $langDirectory = opendir(FCPATH.'templates/');
        // get each template
        while($templateName = readdir($langDirectory)) {
            if ($templateName != "." && $templateName != "..") {
                $this->data['templates_available'][$templateName] = $templateName;
            }
        }
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/template');
                exit();
            }
            
            $data = $this->settings_m->array_from_post($this->settings_m->get_post_from_rules($rules));

            $this->settings_m->save_settings($data);
            
            redirect('admin/settings/template');
        }
        
    	$this->data['subview'] = 'admin/settings/template';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language() 
    {
        $this->data['languages'] = $this->language_m->get();
        
    	$this->data['subview'] = 'admin/settings/language';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language_files($id) 
    {
        $this->data['language_files'] = array();
        $this->data['language_id'] = $id;
        
        // template files
        $directory = opendir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && (strpos($templateName, '.php')>0) ) {
                $file = array('path'=> FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/'.$templateName, 
                              'filename'=>$templateName, 'important_for'=>'Frontend', 'folder'=>'template');

                $this->data['language_files'][] = $file;
            }
        }
        
        // application files
        $directory = opendir(APPPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && (strpos($templateName, '.php')>0)) {
                $file = array('path'=> APPPATH.'language/english/'.$templateName, 
                              'filename'=>$templateName, 'important_for'=>'Backend', 'folder'=>'application');
                
                if(strpos($templateName, 'vali')>0)
                {
                    $file = array('path'=> APPPATH.'language/english/'.$templateName, 
                                  'filename'=>$templateName, 'important_for'=>'Frontend & Backend', 'folder'=>'application');
                }
                
                if(strpos($templateName, 'ontent')>0)
                {
                    $file = array('path'=> APPPATH.'language/english/'.$templateName, 
                                  'filename'=>$templateName, 'important_for'=>'Frontend', 'folder'=>'application');
                }

                $this->data['language_files'][] = $file;
            }
        }
        
        // system files
        $directory = opendir(BASEPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && (strpos($templateName, '.php')>0)) {
                $file = array('path'=> BASEPATH.'language/english/'.$templateName, 
                              'filename'=>$templateName, 'important_for'=>'System', 'folder'=>'system');

                $this->data['language_files'][] = $file;
            }
        }
        
    	$this->data['subview'] = 'admin/settings/language_files';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language_edit_file($lang_id, $file_name)
    {
        $this->load->helper('security');
        
        $language_current = $this->language_m->get_name($lang_id);
        $this->data['message'] = '';
        
        $folder = substr($file_name, 0, stripos($file_name, '-'));
        $file   = substr($file_name, stripos($file_name, '-')+1);
        $path_english   = '';
        $path_current   = '';
        
        if($folder == 'system')
        {
            $path_english = BASEPATH.'language/english/'.$file;
            $path_current = BASEPATH.'language/'.$language_current.'/'.$file;
        }
        else if($folder == 'application')
        {
            $path_english = APPPATH.'language/english/'.$file;
            $path_current = APPPATH.'language/'.$language_current.'/'.$file;
        }
        else if($folder == 'template')
        {
            $path_english = FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/'.$file;
            $path_current = FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_current.'/'.$file;
        }
        
        $this->data['file'] = $file;
        $this->data['lang_id'] = $lang_id;
        $this->data['lang_name'] = $language_current;
        $this->data['lang_code'] = $this->language_m->get_code($lang_id);
        
        $lang=array();
        include $path_english;
        $this->data['language_translations_english'] = $lang;
        
        $lang=array();
        if(file_exists($path_current)){
            include $path_current;
            $this->data['language_translations_current'] = $lang;
        }
        
// TODO: Buggy Bing service...
//        $this->load->library('bingTranslation', array(
//                             'clientID'=>'4aeabe14-2f55-423d-a986-f122319c638b', 
//                             'clientSecret'=>'aJu5fxFAuyezxdMUHZneB+NGpyYyKYVVE43zZDVCxzQ'));
//        
//        echo $this->bingtranslation->translate('Nice day', 'en', 'de');
//        exit();
        
        $this->load->library('gTranslation', array());
        $this->load->library('mymemoryTranslation', array());
        //echo $this->mymemorytranslation->translate('Booking form', 'en', 'fr');
        //exit();
        
//        $not_translated = array();
//        foreach($this->data['language_translations_english'] as $key=>$value)
//        {
//            if( empty($this->data['language_translations_current'][$key]) &&
//               !empty($value))
//            {
//                $not_translated[] = $value;
//            }
//        }
//        
//        $join_nt = implode('._', $not_translated);
//        
//        $sector_joins = '';
//        
//        if(strlen($join_nt)>400)
//        {
//            for($i=0; $i <= strlen($join_nt); $i+=400)
//            {
//                $sector = substr($join_nt, $i, 400);
//                $sector_joins .= $this->mymemorytranslation->translate('\''.$sector.'\'', 'en', 'hr');
//            }
//        }
//        else
//        {
//            $sector_joins = $this->mymemorytranslation->translate('\''.$join_nt.'\'', 'en', 'hr');
//        }
//
//        echo $join_nt.'<br />'."\r\n";
//        echo $sector_joins.'<br />';
//        exit();
        
        if(count($_POST) > 0)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/language_edit_file/'.$lang_id.'/'.$file_name);
                exit();
            }
            
            if(!is_writable($path_current))
            {
                $this->session->set_flashdata('error', 
                        'File '.$path_current.' is not writable<br />');
                redirect('admin/settings/language_edit_file/'.$lang_id.'/'.$file_name);
                exit();
            }
            
            // Save file
            $file_content = '<?php '."\n\n";
            
            $previous = 't';
            foreach($this->data['language_translations_english'] as $key=>$val)
            {
                $lang_val = $val;
                if(isset($_POST[md5($key)]))
                    $lang_val = $_POST[md5($key)];
                
                $lang_val = xss_clean($lang_val);
                $lang_val = str_replace('"', '\"', $lang_val);
                $lang_val = str_replace('$', '\\$', $lang_val);
                
                $key = str_replace('\'', '\\\'', $key);
                $key = str_replace('$', '\\$', $key);
                
                if(empty($previous) && !empty($lang_val))
                    $file_content.= "\n";
                
                $file_content.= '$lang[\''.$key.'\'] = "'.$lang_val.'";'."\n";
                $previous = $lang_val;
            }
            $file_content.= "\n".'?>';
            
            $message = '';
            if (!$handle = fopen($path_current, 'w')) {
                 $message = lang('cannot_open_file')." ($path_current)";
                 exit;
            }
        
            // Write $somecontent to our opened file.
            if (fwrite($handle, $file_content) === FALSE) {
                $message = lang('cannot_write_file')." ($path_current)";
                exit;
            }
    
            fclose($handle);
            
            if($message == '')
            {
                $this->session->set_flashdata('message', 
                        '<p class="label label-success validation">Changes saved</p>');
                redirect('admin/settings/language_edit_file/'.$lang_id.'/'.$file_name);
            }
            else
            {
                $this->data['message'] = '<p class="label label-important validation">'.$message.'</p>';
            }
            
        }
        
    	$this->data['subview'] = 'admin/settings/language_edit_file';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language_edit($id = NULL) 
    {
	    // Fetch a record or set a new one
	    if($id)
        {
            $this->data['language'] = $this->language_m->get($id);
            count($this->data['language']) || $this->data['errors'][] = 'Could not be found';
        }
        else
        {
            $this->data['language'] = $this->language_m->get_new();
        }
        
        $this->data['available_langs'] = lang_check('Alredy translated').':&nbsp;&nbsp;';
        // language directory files
        $directory = opendir(FCPATH.'templates/'.$this->data['settings']['template'].'/language');
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != "..") {
                // Check if is dir
                if(is_dir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$templateName))
                {
                    $this->data['available_langs'].='<span class="available-langs-sel badge badge-info">'.$templateName.'</span>&nbsp;&nbsp;';
                }
            }
        }

        // Set up the form
        $rules = $this->language_m->rules_admin;
        if(!$id || (isset($_POST['language']) && $this->data['language']->language != $_POST['language'])){
            $rules['language']['rules'].='|is_unique[language.language]';
        }
        
        $this->load->model('payments_m');
        $this->data['currencies'] = $this->payments_m->currencies;

        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/language');
                exit();
            }

            $data = $this->settings_m->array_from_post($this->language_m->get_post_from_rules($rules));
            
            if($data['is_frontend'] == '0' && !empty($id))
            {
                //Check if there is one more visible language
                if($this->language_m->count_visible($id) == 0)
                {
                    $data['is_frontend'] = '1';
                }
            }
            
            $message = $this->_check_language_files($data['language']);
            
            if(!empty($message))
            {
                $this->session->set_flashdata('error', $message);
            }
            
            $lang_id = $this->language_m->save($data, $id);
            
            if(empty($id)) // If adding new language
                $this->_check_content($lang_id);
            
            redirect('admin/settings/language');
        }
        
    	$this->data['subview'] = 'admin/settings/language_edit';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    private function _check_content($lang_id)
    {
        $default_language_id = $this->language_m->get_default_id();
        if($default_language_id === FALSE)return 'ERROR: No default language id';
        $language_name = $this->language_m->get_name($default_language_id);
        $language_name_new = $this->language_m->get_name($lang_id);
        
        // First remove if some trash vaues found
        $this->db->delete('page_lang', array('language_id' => $lang_id));
        $this->db->delete('property_value', array('language_id' => $lang_id)); 
        $this->db->delete('option_lang', array('language_id' => $lang_id)); 
        $this->db->delete('showroom_lang', array('language_id' => $lang_id)); 
        $this->db->delete('qa_lang', array('language_id' => $lang_id)); 
        $this->db->delete('rates_lang', array('language_id' => $lang_id));
        
        $generate_content_lang = array();
        
        if(file_exists(APPPATH.'language/'.$language_name_new.'/content_lang.php'))
        {
            $lang=array();
            include APPPATH.'language/'.$language_name_new.'/content_lang.php';
            $content_lang = $lang;
        }
        
        // Copy page_lang from default
        $query = $this->db->get_where('page_lang', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                if(!empty($row['title']))$generate_content_lang[$row['title']] = $row['title'];
                if(!empty($row['navigation_title']))$generate_content_lang[$row['navigation_title']] = $row['navigation_title'];
                unset($row['id_page_lang']);
                $row['language_id'] = $lang_id;
                if(!empty($content_lang[$row['title']]))$row['title'] = $content_lang[$row['title']];
                if(!empty($content_lang[$row['navigation_title']]))$row['navigation_title'] = $content_lang[$row['navigation_title']];
                
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('page_lang', $insert_batch); 
        
        // Copy option_lang from default
        $query = $this->db->get_where('option_lang', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                if(!empty($row['option']))$generate_content_lang[$row['option']] = $row['option'];
                if(!empty($row['values']))$generate_content_lang[$row['values']] = $row['values'];
                unset($row['id_option_lang']);
                $row['language_id'] = $lang_id;
                if(!empty($content_lang[$row['option']]))$row['option'] = $content_lang[$row['option']];
                if(!empty($content_lang[$row['values']]))$row['values'] = $content_lang[$row['values']];
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('option_lang', $insert_batch); 

        // Copy property_value from default
        $query = $this->db->get_where('property_value', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                unset($row['id']);
                $row['language_id'] = $lang_id;
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('property_value', $insert_batch); 

        // Copy showroom_lang from default
        $query = $this->db->get_where('showroom_lang', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                unset($row['id_showroom_lang']);
                $row['language_id'] = $lang_id;
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('showroom_lang', $insert_batch); 

        // Copy qa_lang from default
        $query = $this->db->get_where('qa_lang', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                unset($row['id_qa_lang']);
                $row['language_id'] = $lang_id;
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('qa_lang', $insert_batch); 
        
        // Copy qa_lang from default
        $query = $this->db->get_where('rates_lang', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                unset($row['id']);
                $row['language_id'] = $lang_id;
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('rates_lang', $insert_batch); 
            
        // Copy treefield_lang from default
        $query = $this->db->get_where('treefield_lang', array('language_id'=>$default_language_id));
        $insert_batch = array();
        if ($query->num_rows() > 0)
            foreach ($query->result_array() as $row)
            {
                unset($row['id']);
                $row['language_id'] = $lang_id;
                $insert_batch[] = $row;
            }
        if(count($insert_batch) > 0)
            $this->db->insert_batch('treefield_lang', $insert_batch); 
            
        // Save $generate_content_lang
        if(file_exists(APPPATH.'language/'.$language_name.'/'))
        {
            $path_current = APPPATH.'language/'.$language_name.'/content_lang.gen';
            
            // Save file
            $file_content = '<?php '."\n\n";
            foreach($generate_content_lang as $key=>$val)
            {
                $file_content.= '$lang[\''.$key.'\'] = "'.$val.'";'."\n";
            }
            $file_content.= "\n".'?>';
            
            $message = '';
            if (!$handle = fopen($path_current, 'w')) {
                 $message = lang('cannot_open_file')." ($path_current)";
                 exit;
            }
        
            // Write $somecontent to our opened file.
            if (fwrite($handle, $file_content) === FALSE) {
                $message = lang('cannot_write_file')." ($path_current)";
                exit;
            }
    
            fclose($handle);
        }
            
        return 'Content copied';
    }
    
    private function _check_language_files($language_name)
    {
        $res = true;
        $message = '';
        
        if(!file_exists(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'))
            $res = mkdir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/');
        
        if(!$res)
        {
            $message = 'Failed to make dir: '.FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/';
            return $message;
        }
        
        // template files
        $directory = opendir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && (strpos($templateName, '.php')>0 || strpos($templateName, '.html')>0)) {
                // Check if file not exists, copy it from english
                if(!file_exists(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'.$templateName))
                    $res = copy(FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/'.$templateName,
                                FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'.$templateName);
                
                if(!$res)
                {
                    $message = 'Failed to create file: '.FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'.$templateName;
                    return $message;
                }
            }
        }
        
        if(!file_exists(APPPATH.'language/'.$language_name.'/'))
            $res = mkdir(APPPATH.'language/'.$language_name.'/');
            
        if(!$res)
        {
            $message = 'Failed to make dir: '.APPPATH.'language/'.$language_name.'/';
            return $message;
        }
        
        // application files
        $directory = opendir(APPPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && (strpos($templateName, '.php')>0 || strpos($templateName, '.html')>0) ) {
                // Check if file not exists, copy it from english
                if(!file_exists(APPPATH.'language/'.$language_name.'/'.$templateName))
                    $res = copy(APPPATH.'language/english/'.$templateName,
                                APPPATH.'language/'.$language_name.'/'.$templateName);
                
                if(!$res)
                {
                    $message = 'Failed to create file: '.APPPATH.'language/'.$language_name.'/'.$templateName;
                    return $message;
                }
            }
        }
        
        if(!file_exists(BASEPATH.'language/'.$language_name.'/'))
            $res = mkdir(BASEPATH.'language/'.$language_name.'/');
        
        if(!$res)
        {
            $message = 'Failed to make dir: '.BASEPATH.'language/'.$language_name.'/';
            return $message;
        }
        
        // system files
        $directory = opendir(BASEPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && (strpos($templateName, '.php')>0 || strpos($templateName, '.html')>0)) {
                // Check if file not exists, copy it from english
                if(!file_exists(BASEPATH.'language/'.$language_name.'/'.$templateName))
                    $res = copy(BASEPATH.'language/english/'.$templateName,
                                BASEPATH.'language/'.$language_name.'/'.$templateName);
                
                if(!$res)
                {
                    $message = 'Failed to create file: '.BASEPATH.'language/'.$language_name.'/'.$templateName;
                    return $message;
                }
            }
        }
        
        return $message;
    }
    
    public function language_delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/settings/language');
            exit();
        }
        
        $language = $this->language_m->get($id);
        if( $language->is_locked )
        {
            $this->session->set_flashdata('error', 
                    lang_check('Language locked, can\'t be deleted but you can change it!'));
            redirect('admin/settings/language');
            exit();
        }
       
		$this->language_m->delete($id);
        redirect('admin/settings/language');
	}
    
    public function slug() 
    {
        $this->load->model('slug_m');
        
        $this->data['listings'] = $this->slug_m->get_by(array('model_name'=>'custom'));
        
    	$this->data['subview'] = 'admin/settings/slug';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function slug_edit($id = NULL) 
    {
        $this->load->model('slug_m');
        
	    // Fetch a record or set a new one
	    if($id)
        {
            $this->data['item'] = $this->slug_m->get($id);
            count($this->data['item']) || $this->data['errors'][] = 'Could not be found';
        }
        else
        {
            $this->data['item'] = $this->slug_m->get_new();
        }

        // Set up the form
        $rules = $this->slug_m->rules_admin;
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/slug');
                exit();
            }

            $data = $this->slug_m->array_from_post($this->slug_m->get_post_from_rules($rules));
            
            // customize data
            $data['model_name'] = 'custom';
            
            $slug_id = $this->slug_m->save($data, $id);
            
            redirect('admin/settings/slug');
        }
        
    	$this->data['subview'] = 'admin/settings/slug_edit';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function slug_delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/settings/slug');
            exit();
        }
       
        $this->load->model('slug_m');
		$this->slug_m->delete($id);
        redirect('admin/settings/slug');
	}
    
    public function _unique_slug($str)
    {
        // Do NOT validate if slug alredy exists
        // UNLESS it's the slug for the current
        
        $id = $this->uri->segment(4);
        $this->db->where('slug', $this->input->post('slug'));
        !$id || $this->db->where('id !=', $id);
        
        $listings = $this->slug_m->get();
        
        if(count($listings))
        {
            $this->form_validation->set_message('_unique_slug', '%s '.lang('should be unique'));
            return FALSE;
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
    
}