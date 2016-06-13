<?php

class Updater extends MY_Controller
{

	public function __construct ()
	{
		parent::__construct();
        
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        
        $this->load->library('form_validation');
        $this->lang->load('configurator', 'english');
        $this->form_validation->set_error_delimiters('<p class="alert alert-error">', '</p>');
        $this->load->model('user_m');
        
        $CI =& get_instance();
        $CI->form_languages = array();
        
	}
    
    public function installed()
    {
        $this->load->view('configurator/installed');
    }
    
	public function index( $update_version = NULL )
	{
        $this->data['custom_errors'] = '';
        $this->data['update_to_version'] = '';
        $this->data['update_output'] = '';
        $this->data['update_alert'] = true;
        
        if(config_item('installed') != true)
        {
            $this->data['custom_errors'] .= lang_check('Looks like your script is not installed, please install it first');
        }

        $this->check_writing_permissions();
        
        // Get script version
        $this->load->database();
        
        $this->data['script_version_db'] = '< 1.5.1';
        if ($this->db->table_exists('update_debug'))
        {
           $this->data['script_version_db'] = '1.5.1';
        } 
        if ($this->db->table_exists('favorites'))
        {
           $this->data['script_version_db'] = '1.5.2';
        } 
        if ($this->db->table_exists('property_lang'))
        {
           $this->data['script_version_db'] = '1.5.3';
        } 
        
        if($this->data['script_version_db'] == '1.5.2' && !is_numeric($update_version))
        {
            $this->data['update_to_version'] = '1.5.3';
        }
        
        if($update_version == 'backup_sql')
        {
            $this->data['update_output'] = $this->backup_sql();
        }
        
        if($update_version == 'backup_files')
        {
            $this->data['update_output'] = $this->backup_files();
        }
        
        $function_name ='update_'.$update_version;
        if($update_version != NULL && is_numeric($update_version))
            $this->data['update_output'] = $this->{$function_name}();
        
        
		// Load the view
		$this->load->view('configurator/update_index', $this->data);
	}
    
    private function update_153()
    {
        $version = '1.5.3';
        
        $update_output = '';
        
        if($this->data['script_version_db'] != '1.5.2')
        {
            $update_output.= lang_check('Wrong script version or already updated!');
            return $update_output;
        }
        
        // Run sql import file
        if(!file_exists(FCPATH.'update-'.$version.'.sql'))
        {
            $update_output.= '<br />Missing file: update-'.$version.'.sql';
            return $update_output;
        }
        
        $db_error = '';
        $sql=file_get_contents(FCPATH.'update-'.$version.'.sql');
          foreach (explode(";", $sql) as $sql) 
           {
             $sql = trim($sql);
              //echo  $sql.'<br/>============<br/>';
                if($sql) 
              {
                if(empty($db_error))
                {
                    $this->db->query($sql);
                    if($this->db->_error_message() != '')
                        $db_error.= '<br />'.$this->db->_error_message();
                }
                else
                {
                    break;
                }
               } 
          }
        
        if(!empty($db_error))
            $update_output.=$db_error.'<br />';
          
        // Execute db_structure modifications
        $this->fix_gps($update_output);
        $this->fix_image_filename($update_output);
        $this->fix_data_structure($update_output);

        if(empty($update_output))
            $update_output.=lang_check('Completed successfully to db version: ').'1.5.3';
        
        return $update_output;
    }
    
    private function backup_files()
    {
        $this->load->helper('file');
        $zip = new ZipArchive;
        
        $filename_zip = APP_VERSION_REAL_ESTATE.'-'.date('Y-m-d-H-i-s-').$this->user_m->hash(date('Y-m-d H:i:s')).rand(1,1000).'.zip';
        $zip->open(APPPATH.'../backups/'.$filename_zip, ZipArchive::CREATE);
        
        $remove_chars = strlen(FCPATH);
        $directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FCPATH));
        foreach($directory_iterator as $filename => $path_object)
        {
            if(is_file($filename))
            {
                $zip_filename = substr($filename, $remove_chars);
                $zip->addFile($filename, $zip_filename);
            }
        }

        $ret = $zip->close();
        
        if($ret == true)
            return lang_check('ZIP file backup created in folder backups/');
        
        return lang_check('ZIP file backup FAILED!');
    }
    
    private function backup_sql()
    {
        // Load the DB utility class
        $this->load->dbutil();
        
        $tables = $this->db->list_tables();
        
        $prefs = array(
            'tables'      => $tables,  
            'ignore'      => array(),           // List of tables to omit from the backup
            'format'      => 'txt',             // gzip, zip, txt
            'filename'    => '',    // File name - NEEDED ONLY WITH ZIP FILES
            'add_drop'    => TRUE,              // Whether to add DROP TABLE statements to backup file
            'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
            'newline'     => "\n"               // Newline character used in backup file
        );
        
        // Backup your entire database and assign it to a variable
        $backup = &$this->dbutil->backup($prefs);
        
        $filename_sql = APP_VERSION_REAL_ESTATE.'-'.date('Y-m-d-H-i-s-').$this->user_m->hash(date('Y-m-d H:i:s')).rand(1,1000).'.sql';
        
        // Load the file helper and write the file to your server
        $this->load->helper('file');
        $ret = write_file(APPPATH.'../backups/'.$filename_sql, 
                    $backup);
                    
        if($ret == true && !empty($backup))
            return lang_check('SQL backup created in folder backups/');
        
        return lang_check('SQL backup FAILED!');
    }
    
    private function check_writing_permissions()
    {
        $write_error = '';
        
        if(!is_writable(APPPATH.'config/cms_config.php'))
        {
            $write_error.='File application/config/cms_config.php is not writable<br />';
        }
        
        if(!is_writable(APPPATH.'config/production/database.php'))
        {
            $write_error.='File application/config/production/database.php is not writable<br />';
        }
        
        if(!is_writable(FCPATH.'files/'))
        {
            $write_error.='Folder files/ is not writable<br />';
        }
        
        if(!is_writable(FCPATH.'files/captcha/'))
        {
            $write_error.='Folder files/captcha/ is not writable<br />';
        }
        
        if(!is_writable(FCPATH.'templates/bootstrap2-responsive/language/'))
        {
            $write_error.='Folder templates/bootstrap2-responsive/language/ is not writable<br />';
        }
        
        if(!is_writable(FCPATH.'application/language/'))
        {
            $write_error.='Folder application/language/ is not writable<br />';
        }
        
        if(!is_writable(FCPATH.'system/language/'))
        {
            $write_error.='Folder system/language/ is not writable<br />';
        }
        
        if(!is_writable(FCPATH.'sitemap.xml'))
        {
            $write_error.='File sitemap.xml is not writable<br />';
        }
        
        $this->data['custom_errors'] .= $write_error;
    }
    
    // fix gps to lat, lng convert
    public function fix_gps(&$update_output)
	{
        $update_output .= 'FIX GPS START'.'<br />';
        
        $data_batch = array();
        $query = $this->db->query("SELECT * FROM property;");
        if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
                if(!empty($row->gps) && empty($row->lat))
                {
                    $gps = explode(', ', $row->gps);
                    
                    if(count($gps)>=2)
                    $data_batch[] = array(
                        'id' => $row->id,
                        'lat' => floatval($gps[0]),
                        'lng' => floatval($gps[1])
                    );
                }
           }
        }
        
        $update_output .= 'FOR UPDATE: '.count($data_batch).'<br />';
        
        if(count($data_batch) > 0)
            $this->db->update_batch('property', $data_batch, 'id'); 
        
        $update_output .= 'FIX GPS END'.'<br />';
	}
    
    // fix image_filename column in property convert
    public function fix_image_filename(&$update_output)
	{
        $update_output .= 'FIX image_filename START'.'<br />';
        
        $this->load->model('file_m');
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        $rep_file_count = array();
        foreach($files as $key=>$file)
        {
            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $this->data['images_'.$file->repository_id][] = $file;
            }
        }
        
        /* [PROPERTY] */
        $data_batch = array();
        $query = $this->db->query("SELECT * FROM property;");
        if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
                $image_repository = NULL;
                if(isset($this->data['images_'.$row->repository_id]))
                if(count($this->data['images_'.$row->repository_id]>0))
                {
                    foreach($this->data['images_'.$row->repository_id] as $img_file)
                    {
                        $image_repository[] = $img_file->filename;
                    }
                }
                
                if(isset($this->data['images_'.$row->repository_id][0]))
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_filename' => $this->data['images_'.$row->repository_id][0]->filename,
                        'image_repository' => json_encode($image_repository)
                    );
                }
                else
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_filename' => NULL,
                        'image_repository' => NULL
                    );
                }
           }
        } 
        
        $update_output .= 'FOR UPDATE PROPERTY: '.count($data_batch).'<br />';
        
        if(count($data_batch) > 0)
            $this->db->update_batch('property', $data_batch, 'id'); 
        /* [/PROPERTY] */
        
        /* [USER] */
        $data_batch = array();
        $query = $this->db->query("SELECT * FROM user;");
        if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
                $image_repository = NULL;
                if(isset($this->data['images_'.$row->repository_id]))
                if(count($this->data['images_'.$row->repository_id]>0))
                {
                    foreach($this->data['images_'.$row->repository_id] as $img_file)
                    {
                        $image_repository[] = $img_file->filename;
                    }
                }
                
                if(isset($this->data['images_'.$row->repository_id][0]))
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_user_filename' => $this->data['images_'.$row->repository_id][0]->filename,
                        'image_agency_filename' => (isset($this->data['images_'.$row->repository_id][1])?
                                                    $this->data['images_'.$row->repository_id][1]->filename:
                                                    NULL)
                    );
                }
                else
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_user_filename' => NULL,
                        'image_agency_filename' => NULL
                    );
                }
           }
        } 
        
        $update_output .= 'FOR UPDATE USERS: '.count($data_batch).'<br />';
        
        if(count($data_batch) > 0)
            $this->db->update_batch('user', $data_batch, 'id'); 
        /* [/USER] */
        
        /* [PAGE] */
        $data_batch = array();
        $query = $this->db->query("SELECT * FROM page;");
        if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
                $image_repository = NULL;
                if(isset($this->data['images_'.$row->repository_id]))
                if(count($this->data['images_'.$row->repository_id]>0))
                {
                    foreach($this->data['images_'.$row->repository_id] as $img_file)
                    {
                        $image_repository[] = $img_file->filename;
                    }
                }
                
                if(isset($this->data['images_'.$row->repository_id][0]))
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_filename' => $this->data['images_'.$row->repository_id][0]->filename
                    );
                }
                else
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_filename' => NULL
                    );
                }
           }
        } 
        
        $update_output .= 'FOR UPDATE PAGE: '.count($data_batch).'<br />';
        
        if(count($data_batch) > 0)
            $this->db->update_batch('page', $data_batch, 'id'); 
        /* [/PAGE] */
        
        /* [SHOWROOM] */
        $data_batch = array();
        $query = $this->db->query("SELECT * FROM showroom;");
        if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
                $image_repository = NULL;
                if(isset($this->data['images_'.$row->repository_id]))
                if(count($this->data['images_'.$row->repository_id]>0))
                {
                    foreach($this->data['images_'.$row->repository_id] as $img_file)
                    {
                        $image_repository[] = $img_file->filename;
                    }
                }
                
                if(isset($this->data['images_'.$row->repository_id][0]))
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_filename' => $this->data['images_'.$row->repository_id][0]->filename
                    );
                }
                else
                {
                    $data_batch[] = array(
                        'id' => $row->id,
                        'image_filename' => NULL
                    );
                }
           }
        } 
        
        $update_output .= 'FOR UPDATE SHOWROOM: '.count($data_batch).'<br />';
        
        if(count($data_batch) > 0)
            $this->db->update_batch('showroom', $data_batch, 'id'); 
        /* [/SHOWROOM] */
        
        $update_output .= 'FIX image_filename END'.'<br />';
	}
    
    // fix data_structure changed
    public function fix_data_structure(&$update_output)
	{
	   //$this->output->enable_profiler(TRUE);
       
        $update_output .= 'FIX DATA property_lang START'.'<br />';
        
        $this->load->model('option_m');
        
        $langs = $this->language_m->get();
        $options_name = $this->option_m->get();
        $data_batch = array();
        
        $fields = $this->db->list_fields('property_lang');
        $fields = array_flip($fields);
        
        foreach($langs as $row_lang)
        {
            $options = $this->option_m->get_options($row_lang->id);
            
            $query = $this->db->query("SELECT * FROM property;");
            if ($query->num_rows() > 0)
            {
               foreach ($query->result() as $row_property)
               {
                    $row_property_id = $row_property->id;
                    
                    $data_property_lang = array();
                    $data_property_lang['property_id'] = intval($row_property->id);
                    $data_property_lang['language_id'] = intval($row_lang->id);
                    $json_obj = array();
                    foreach($options_name as $option_name)
                    {
                        $option_id = $option_name->id;

                        if(isset($options[$row_property_id][$option_id]))
                        {
                            $option_val = $options[$row_property_id][$option_id];
                            $json_obj['field_'.$option_id] = $option_val;
                            
                            if(!empty($option_val))
                            {
                                if (isset($fields['field_'.$option_id]))
                                {
                                    $data_property_lang['field_'.$option_id] = $option_val;
                                } 
                                
                                $value_n = trim($option_val);
                                $value_n = str_replace("'", '', $value_n);
                                $value_n = str_replace("’", '', $value_n);
                                $value_n = str_replace(",", '', $value_n);
                                
                                if(is_numeric($value_n) && isset($fields['field_'.$option_id.'_int']))
                                {
                                    $data_property_lang['field_'.$option_id.'_int'] = intval($value_n);
                                }
                            }
                        }
                    }
                    
                    // check fields consistent
                    foreach($fields as $key_c=>$val_c)
                    {
                        if(!isset($data_property_lang[$key_c]))
                        {
                            $data_property_lang[$key_c] = NULL;
                        }
                    }
                    
                    $data_property_lang['json_object'] = json_encode($json_obj);
                    
                    if(count($data_property_lang) > 3)
                        $data_batch[] = $data_property_lang;
               }
            } 
        }
        
        $update_output .= 'FOR INSERT: '.count($data_batch).'<br />';

        if(count($data_batch) > 0)
        {
            $this->db->truncate('property_lang');
            $this->db->insert_batch('property_lang', $data_batch); 
        }
        
        $update_output .= 'FIX DATA property_lang END'.'<br />';
	}

}