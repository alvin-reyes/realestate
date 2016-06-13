<?php

class Files extends Admin_Controller {
	
    public $_current_revision_id;
    
    public function __construct(){
		parent::__construct();       
        $this->load->model('page_m');
        $this->load->model('ads_m');
        $this->load->model('file_m');
        $this->load->model('repository_m');
	}
    
    public function index($page = NULL) 
    {
        exit();
    }
    
    public function download($repository_id, $revision_id)
    {
        $this->load->library('zip');
        $this->load->helper('file');
        
        // Fetch all files by repository_id
        $files = $this->file_m->get_by(array(
            'repository_id' => $repository_id
        ));
        
        // Add all files to zip archive
        foreach($files as $file)
        {
            $name = $revision_id.'/'.$file->filename;
            $data = read_file(dirname($_SERVER['SCRIPT_FILENAME']).'/files/'.$revision_id.'/'.$file->filename);
            
            $this->zip->add_data($name, $data); 
        }
        
        $this->zip->download('file_repository_'.$repository_id.'.zip'); 
    }
    
    public function order($page_or_param, $model = 'page_m')
    {
        $this->load->model($model);
        $this->output->enable_profiler(TRUE);
        if(config_item('app_type') == 'demo')
        {
            $data = array();
            $length = strlen(json_encode($data));
            header('Content-Type: application/json; charset=utf8');
            header('Content-Length: '.$length);
            echo json_encode($data);
            exit();
        }
        
        $data = array();
        
        $page_id = NULL;
                
        if(is_numeric($page_or_param))
        {
            $page_id = $page_or_param;
            
    	    // Fetch page
    		$page = $this->$model->get($page_id, TRUE);
            
            // Fetch file repository
            $repository_id = $page->repository_id;
        }
        else if($page_or_param == 'data-files')
        {
            // Fetch all parameters
            $this->data['parameters'] = $this->parameters_m->get_parameters();
            
            $repository_id = $this->data['parameters']['additional-files'];
        }

        // Fetch all files by repository_id
        $files = $this->file_m->get_by(array(
            'repository_id' => $repository_id
        ));
        
        /* +++ Security check for USER +++ */
        if($this->session->userdata('type') == 'USER')
        {
            $user_id = $this->session->userdata('id');
            
            if(!$this->user_m->is_related_repository($user_id, $repository_id))
            {
                echo $user_id.'|'.$repository_id;
                exit();
            }
        }
        /* +++ End security check for USER +++ */
        
        // Update all files with order value
        if(isset($_POST['order']) && config_item('app_type') != 'demo')
        foreach($_POST['order'] as $order=>$filename)
        {
            foreach($files as $file)
            {
                if($filename == $file->filename)
                {
                    $this->file_m->save(array(
                        'order' => $order,
                    ), $file->id);
                    break;
                }
            }
        }

        $data['success'] = true;
        $length = strlen(json_encode($data));
        header('Content-Type: application/json; charset=utf8');
        header('Content-Length: '.$length);
        echo json_encode($data);
        exit();
    }
    
    public function upload_slideshow($slideshow_or_param = NULL) 
    {
        return $this->upload($slideshow_or_param, 'slideshow_m');
    }
    
    public function upload_estate($estate_or_param = NULL) 
    {
        return $this->upload($estate_or_param, 'estate_m');
    }
    
    public function upload_field($estate_or_param = NULL) 
    {
        return $this->upload($estate_or_param, 'option_m');
    }
    
    public function upload_user($estate_or_param = NULL) 
    {
        return $this->upload($estate_or_param, 'user_m');
    }
    
    public function upload_ads($estate_or_param = NULL) 
    {
        return $this->upload($estate_or_param, 'ads_m');
    }
    
    public function upload_showroom($estate_or_param = NULL) 
    {
        return $this->upload($estate_or_param, 'showroom_m');
    }
    
    public function upload($page_or_param = NULL, $model = 'page_m') 
    {
        //if(config_item('app_type') == 'demo')
        //    exit();
        $this->load->model($model);
        
        $page_id = NULL;
        
        $repository_id = NULL;
        
        if($model == 'option_m')
        {
            $page_or_param_split = explode('_', $page_or_param);
            $property_id = $page_or_param_split[0];
            $field_id = $page_or_param_split[1];
            $lang_id = $page_or_param_split[2];
            
            // fetch repository
            $property_value = $this->option_m->get_property_value_by(
                                                       array('option_id'=>$field_id,
                                                            'property_id'=>$property_id,
                                                            'language_id'=>$lang_id), TRUE, 1);
            
            //$str = $this->db->last_query();
            //echo $str;
            
            $property_value_id = NULL;
            if(!empty($property_value))
            {
                if(is_numeric($property_value->value_num))
                    $repository_id = $property_value->value_num;
                
                $property_value_id = $property_value->id;
            }

            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>$model));
            }
            
            // Update page with new repository_id
            $property_value_id = $this->option_m->save_repository($repository_id, $field_id, $property_id, $lang_id, $property_value_id);
        }
        
        if(is_numeric($page_or_param))
        {
            // Files for page
            $page_id = $page_or_param;
            
    	    // Fetch page
    		$page = $this->$model->get($page_id, TRUE);
            
            // Fetch file repository
            $repository_id = $page->repository_id;
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>$model));
                
                // Update page with new repository_id
                $this->$model->save(array('repository_id'=>$repository_id), $page->id);
            }
        }

        /* +++ Security check for USER +++ */
        if($this->session->userdata('type') == 'USER')
        {
            if(substr($page_or_param, 0, 4) == 'rep_')
            {
                $repository_id = substr($page_or_param, 4);
            }
            
            if($repository_id == NULL){
                exit('user type problem 1');
            }
            
            $user_id = $this->session->userdata('id');
            
            if($model == 'option_m' && isset($property_id))
            {
                $this->load->model('estate_m');
                $num_rows = $this->estate_m->check_user_permission($property_id, $user_id);
                if($num_rows == 0)
                    exit('user type problem 2, rep:'.$repository_id);
            }
            else if(!$this->user_m->is_related_repository($user_id, $repository_id) &&
               $this->user_m->get($user_id)->repository_id != $repository_id)
            {
                exit('user type problem 3, rep:'.$repository_id);
            }
        }
        /* +++ End security check for USER +++ */
        
        $watermark_disabled = FALSE;
        if($model == 'ads_m' || $model == 'slideshow_m')
            $watermark_disabled = TRUE;
        
        // count current rep files num
        $rep_images_num = $this->file_m->count_in_repository($repository_id);
        
        // check limitation in package
        $num_limitation = NULL;
        if(file_exists(APPPATH.'controllers/admin/packages.php') && ($model == 'estate_m' || $model == 'option_m') &&
           config_item('enable_num_images_listing') == TRUE )
        {
            $this->load->model('packages_m');
            
            $user = $this->user_m->get($this->session->userdata('id'));
            if($user->package_id > 0)
            {
                $package = $this->packages_m->get($user->package_id);
                if(is_numeric($package->num_images_limit))
                    $num_limitation = intval($package->num_images_limit);
            }            
        }

        $upload_options = array('script_url' => site_url('files/upload').'/',
                                'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
                                'upload_url' => base_url('files').'/', 'watermark_disabled'=>$watermark_disabled,
                                'rep_images_num' => $rep_images_num, 'max_number_of_files' => $num_limitation);
        
        if($model == 'ads_m' || $model == 'slideshow_m')
        {
            $upload_options['image_versions'] = array(
                'thumbnail' => array(
                    'max_width' => 300,
                    'max_height' => 225,
                    'jpeg_quality' => 90
                ));
        }
        
        // Upload Handler
        $this->load->library('uploadHandler', array( 'options'=>$upload_options,
                                                     'initialize'=>false
                                                     ));

        if($_SERVER['REQUEST_METHOD'] == 'DELETE' || isset($_GET['_method']))
        {
            if(isset($_GET['_method']) && $_GET['_method']== 'DELETE')
            {
                if(config_item('app_type') == 'demo')
                {
                    $data = array();
                    $length = strlen(json_encode($data));
                    header('Content-Type: application/json; charset=utf8');
                    header('Content-Length: '.$length);
                    echo json_encode($data);
                    exit();
                }
                
                $response = $this->uploadhandler->initialize(true);          
                
//                var_dump($response);
//                array(1) {
//                  ["success"]=>
//                  bool(false)
//                }
                if($response['success'] == 'true')
                if(substr($page_or_param, 0, 4) == 'rep_')
                {
                    $repository_id = substr($page_or_param, 4);
                    
                    $file = $this->file_m->get_by(array(
                        'filename' => $this->uploadhandler->get_file_name_param(),
                        'repository_id' => $repository_id
                    ), TRUE);
                    
                    $this->file_m->delete($file->id);
                }
            
            }
            exit();
        }
        else if($_SERVER['REQUEST_METHOD'] == 'GET')
        {
//            $response = $this->uploadhandler->initialize(false);
//            
//            // Get all files of page
//            $files = $this->file_m->get_by(array(
//                'repository_id' => $repository_id,
//            ));
//            
//            // Generate new list of ordered files
//            $ordered_files = array();
//            if(isset($response['files']))
//            {
//                foreach($files as $file)
//                {
//                    foreach($response['files'] as $key => $response_file)
//                    {
//                        if($file->filename == $response_file->name)
//                        {
//                            //$response_file->thumbnail_url = '';
//                            $ordered_files[] = $response_file;
//                        }
//                    }
//                }
//            }
//            $response['files'] = $ordered_files;
//            
//            // Send to output
//            $this->uploadhandler->generate_response($response);
        }
        else if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $response = $this->uploadhandler->initialize(false);
            
            if(isset($response['files']))
            {
                foreach($response['files'] as $file)
                {
    //                object(stdClass)#27 (7) {
    //                    ["name"]=>
    //                    string(9) "1 (1).gif"
    //                    ["size"]=>
    //                    int(33308)
    //                    ["type"]=>
    //                    string(9) "image/gif"
    //                    ["url"]=>
    //                    string(57) "http://localhost/BeforeConstruction/files/1%20%281%29.gif"
    //                    ["thumbnail_url"]=>
    //                    string(67) "http://localhost/BeforeConstruction/files/thumbnail/1%20%281%29.gif"
    //                    ["delete_url"]=>
    //                    string(57) "http://localhost/BeforeConstruction/?file=1%20%281%29.gif"
    //                    ["delete_type"]=>
    //                    string(6) "DELETE"
    //                }
                    
                    $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
                    $file->zoom_enabled = false;
                    $file->delete_url = site_url_q('files/upload/rep_'.$repository_id, '_method=DELETE&file='.rawurlencode($file->name));
                    if(file_exists(FCPATH.'/files/thumbnail/'.$file->name))
                    {
                        $file->thumbnail_url = base_url('files/thumbnail/'.$file->name);
                        $file->zoom_enabled = true;
                    }
                    else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->name).'.png'))
                    {
                        $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->name).'.png');
                    }
                    
                    $file->short_name = character_hard_limiter($file->name, 20);
                    
                    $this->db->reconnect(); // MySQL timeout possible
                    
                    $next_order = $this->file_m->get_max_order()+1;
                    
                    $response['orders'][$file->name] = $next_order;
                    $response['repository_id'] = $repository_id;
                    
                    if(empty($file->error))
                    {
                        // Add file to repository
                        $file_id = $this->file_m->save(array(
                            'repository_id' => $repository_id,
                            'order' => $next_order,
                            'filename' => $file->name,
                            'filetype' => $file->type
                        ));
                    }
                }
            }
            
            $this->uploadhandler->generate_response($response);
        }
        
        exit();
    }
}
