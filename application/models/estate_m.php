<?php

class Estate_m extends MY_Model {
    
    protected $_table_name = 'property';
    protected $_order_by = 'is_featured DESC, id DESC';
    public $rules = array(
        'gps' => array('field'=>'gps', 'label'=>'lang:Gps', 'rules'=>'trim|required|xss_clean|callback_gps_check'),
        'date' => array('field'=>'date', 'label'=>'lang:DateTime', 'rules'=>'trim|required|xss_clean'),
        'address' => array('field'=>'address', 'label'=>'lang:Address', 'rules'=>'trim|required|xss_clean|quote_fix'),
        'is_featured' => array('field'=>'is_featured', 'label'=>'lang:Featured', 'rules'=>'trim'),
        'is_activated' => array('field'=>'is_activated', 'label'=>'lang:Activated', 'rules'=>'trim'),
        'agent' => array('field'=>'agent', 'label'=>'lang:Agent', 'rules'=>'trim')
   );
   
	public function __construct(){
		parent::__construct();
        
        $this->load->model('language_m');
        $this->languages = $this->language_m->get_form_dropdown('language', FALSE, FALSE);
                                  
        //Rules for languages
        foreach($this->languages as $key=>$value)
        {
            $this->rules["slug_$key"] = array('field'=>"slug_$key", 'label'=>'lang:URI slug', 'rules'=>'trim');
        }
	}

    public function get_new()
	{
        $estate = new stdClass();
        $estate->gps = '';
        $estate->address = '';
        $estate->date = date('Y-m-d H:i:s');
        $estate->agent = NULL;
        $estate->is_featured = '0';
        $estate->is_activated = '0';
        $estate->counter_views = 0;
        
        //Add language parameters
        foreach($this->languages as $key=>$value)
        {
            $estate->{"slug_$key"} = '';
        }        
        
        return $estate;
	}
    
    public function get_new_array()
	{
        $estate = array();
        $estate['gps'] = '';
        $estate['address'] = '';
        $estate['date'] = date('Y-m-d H:i:s');
        $estate['agent'] = NULL;
        $estate['is_featured'] = '0';
        $estate['is_activated'] = '0';
        $estate['counter_views'] = 0;
        
        //Add language parameters
        foreach($this->languages as $key=>$value)
        {
            $estate["slug_$key"] = '';
        }  
        
        return $estate;
	}
    
    public function update_counter($property_id)
    {
        $this->db->set('counter_views', 'counter_views+1', FALSE);
        $this->db->where('id', $property_id);
        $this->db->update($this->_table_name); 
    }
    
    public function get_array($id = NULL, $single = FALSE, $where = NULL)
    {
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.property_id');
        
        if(!empty($where))
            $this->db->where($where);
        
        return parent::get_array($id, $single);
    }
    
    public function count_get_by($where, $single = FALSE, $limit = NULL, $order_by = NULL, $offset = NULL, $search = array(), $where_in = NULL)
    {
        $this->filter_results($where, $search, $where_in);
        if($order_by !== NULL)
        {
            $this->db->order_by($order_by);
        }
        else
        {
            $this->db->order_by($this->_order_by);
        }
        if($limit !== NULL) $this->db->limit($limit, $offset);
        
        return $this->db->count_all_results();
    }
    
    public function get_by($where, $single = FALSE, $limit = NULL, $order_by = NULL, $offset = NULL, $search = array(), $where_in = NULL)
    {
        $this->filter_results($where, $search, $where_in);
        if($order_by !== NULL)
        {
            $this->db->order_by($order_by);
        }
        else
        {
            $this->db->order_by($this->_order_by);
        }
        if($limit !== NULL) $this->db->limit($limit, $offset);

        $query = $this->db->get();  
        
//        $str = $this->db->last_query();
//        echo $str;
//        exit();
        
        $results = $query->result();

        return $results;
    }
    
    private function filter_results($where, $search_array = array(), $where_in = NULL)
    {
        $rectangle_ne = $this->input->get_post('v_rectangle_ne');
        $rectangle_sw = $this->input->get_post('v_rectangle_sw');
        $search_radius = $this->input->get_post('v_search_radius');

        if(!is_array($search_array))
            $search_array = (array) $search_array;
          
        if(isset($search_array['v_rectangle_ne']))
            $rectangle_ne = $search_array['v_rectangle_ne'];
            
        if(isset($search_array['v_rectangle_sw']))
            $rectangle_sw = $search_array['v_rectangle_sw'];
            
        if(isset($search_array['v_search_radius']))
            $search_radius = $search_array['v_search_radius'];

        // [START] Radius search
        if(isset($search_radius) && isset($search_array['v_search_option_smart']) && $search_radius > 0)
        {
            $this->load->library('ghelper');
            $coordinates_center = $this->ghelper->getCoordinates($search_array['v_search_option_smart']);
            
            if(count($coordinates_center) >= 2 && $coordinates_center['lat'] != 0)
            {
                // calculate rectangle
                $rectangle_ne = $this->ghelper->getDueCoords($coordinates_center['lat'], $coordinates_center['lng'], 45, $search_radius);
                $rectangle_sw = $this->ghelper->getDueCoords($coordinates_center['lat'], $coordinates_center['lng'], 225, $search_radius);
                $search_array['v_search_option_smart'] = '';
            }
        }
        // [END] Radius search
        
        //var_dump($search_array, $rectangle_ne, $rectangle_sw);

        $fields = $this->db->list_fields('property_lang');
        $fields = array_flip($fields);
        
        $this->db->distinct();
        $this->db->select($this->_table_name.'.*, '.$this->_table_name.'_lang.*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.property_id');
        if($where !== NULL) $this->db->where($where);

        // [RECTANGLE SEARCH]
        if(!empty($rectangle_ne) && !empty($rectangle_sw))
        {
            $gps_ne = explode(', ', $rectangle_ne);
            $gps_sw = explode(', ', $rectangle_sw);
            $this->db->where("(property.lat < '$gps_ne[0]' AND property.lat > '$gps_sw[0]' AND 
                               property.lng < '$gps_ne[1]' AND property.lng > '$gps_sw[1]')");
        }
        // [/RECTANGLE SEARCH]
        
        if($where_in != NULL)
            $this->db->where_in('property.id', $where_in);
        
        unset($search_array['v_rectangle_ne'], 
              $search_array['v_rectangle_sw']);

        if(count($search_array) > 0)
        {
            foreach($search_array as $key=>$val)
            {
                $parts = explode('_', $key);

                if(isset($parts[3]) && is_numeric($parts[3]))
                    $option_id = $parts[3];
                else $parts[3] = 'NULL';
                
                if($key == 'search_option_smart' ||
                   $key == 'v_search_option_smart')
                {
                    if(is_numeric($val))
                    {
                        $this->db->where('property.id', $val);
                    }
                    else
                    {
                        
                        $this->db->where("(property.id = '$val' OR address LIKE '%$val%' OR search_values LIKE '%$val%')");
                    }
                }
                else if(strrpos($key, 'from') > 0 && isset($fields['field_'.$option_id.'_int']))
                {
                    if(isset($fields['field_'.$option_id.'_int']))
                    {
                        $val = intval($val);
                        $this->db->where("(".'field_'.$option_id.'_int'." >= $val)");
                    }
                    
                }
                else if(strrpos($key, 'to') > 0 && isset($fields['field_'.$option_id.'_int']))
                {
                    if(isset($fields['field_'.$option_id.'_int']))
                    {
                        $val = intval($val);
                        $this->db->where("(".'field_'.$option_id.'_int'." <= $val)");
                    }
                }
                else if(strrpos($key, 'search_option') > 0 && isset($fields['field_'.$option_id]))
                {
                    if(isset($fields['field_'.$option_id]))
                    {
                        $this->db->where('field_'.$option_id, $val);
                    }
                }
                else if(strrpos($key, 'rectangle') > 0)
                {
                    
                }
                else if(is_numeric($val))
                {
                    //$this->db->where("(search_values LIKE '% $val %')");
                    $this->db->where("(json_object LIKE '%\"field_$option_id\":\"$val\"%')");
                }
                else
                {
                    $this->db->where("(search_values LIKE '%$val%')");
                }
            }
        }
    }
    
    public function get_search($search_tag)
    {
        // Fetch pages without parents
        $this->db->distinct();
        $this->db->select($this->_table_name.'.id, gps, address, is_featured, is_activated');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_value', $this->_table_name.'.id = '.$this->_table_name.'_value.property_id');
        
        $this->db->where("(property.id = '$search_tag' OR address LIKE '%$search_tag%' OR value LIKE '%$search_tag%')");
        
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            $this->db->join('property_user', $this->_table_name.'.id = property_user.property_id', 'right');
            $this->db->where('user_id', $this->session->userdata('id'));
        }
        
        $query = $this->db->get();
        $results = $query->result();
        
        return $results;
    }
    
    public function get_last($n = 5)
    {
        $this->db->select('property.*');
        $this->db->limit($n);
        $this->db->from($this->_table_name);
        
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            $this->db->select('property.*, property_user.user_id');
            $this->db->join('property_user', $this->_table_name.'.id = property_user.property_id', 'left');
            $this->db->where('user_id', $this->session->userdata('id'));
        }
        
        $this->db->order_by($this->_table_name.'.id DESC');

        $query = $this->db->get();
        return $query->result();
    }
    
    public function get_dynamic($id)
    {
        $data = parent::get($id);
        
        if($data == NULL) return NULL;
        
        $this->db->where('property_id', $id);
        $query = $this->db->get('property_value');
        
        foreach ($query->result() as $row)
        {
            $data->{'option'.$row->option_id.'_'.$row->language_id} = $row->value;
        }
        
        // Get agent
        $data->agent = null;
        $this->db->where('property_id', $id);
        $this->db->limit(1);
        $query = $this->db->get('property_user');
        foreach ($query->result() as $row)
        {
            $data->agent = $row->user_id;
        }
        
        // Get slug
        $this->load->model('slug_m');
        foreach($this->languages as $key=>$value)
        {
            $slug_data = $this->slug_m->get_slug('estate_m_'.$id.'_'.$this->language_m->db_languages_id[$key]);
            
            $data->{"slug_$key"} = '';
            if($slug_data !== FALSE)
                $data->{"slug_$key"} = $slug_data->slug;
        }
        
        return $data;
    }
    
    public function get_dynamic_array($id)
    {
        $data = parent::get_array($id);
        
        if($data == NULL) return NULL;
        
        $this->db->where('property_id', $id);
        $query = $this->db->get('property_value');
        
        foreach ($query->result() as $row)
        {
            $data['option'.$row->option_id.'_'.$row->language_id] = $row->value;
        }
        
        // Get agent
        $data['agent'] = null;
        $this->db->where('property_id', $id);
        $this->db->limit(1);
        $query = $this->db->get('property_user');
        foreach ($query->result() as $row)
        {
            $data['agent'] = $row->user_id;
        }
        
        // Get slug
        $this->load->model('slug_m');
        foreach($this->languages as $key=>$value)
        {
            $slug_data = $this->slug_m->get_slug('estate_m_'.$id.'_'.$this->language_m->db_languages_id[$key]);

            $data["slug_$key"] = '';
            if($slug_data !== FALSE)
                $data["slug_$key"] = $slug_data->slug;
        }      
        
        return $data;
    }
    
    public function get_join($limit = null, $offset = "")
    {
        $this->db->select('property.*, property_user.user_id as agent');
        $this->db->from($this->_table_name);
        $this->db->join('property_user', $this->_table_name.'.id = property_user.property_id', 'left');
        
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            $this->db->where('user_id', $this->session->userdata('id'));
        }
        
        $this->db->order_by('id DESC');
        
        if($limit != null)
            $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        
        return $query->result();
    }
    
    public function get_form_dropdown($column, $where = FALSE, $empty=TRUE, $show_id=FALSE, $check_user=true)
    {
        $this->db->select('property.*, property_user.user_id as agent');
        $this->db->from($this->_table_name);
        $this->db->join('property_user', $this->_table_name.'.id = property_user.property_id', 'left');

        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN' && $check_user)
        {
            $this->db->where('user_id', $this->session->userdata('id'));
        }
        
        $this->db->order_by('id DESC');

        $filter = $this->_primary_filter;
        
        if(!count($this->db->ar_orderby))
        {
            $this->db->order_by($this->_order_by);
        }
        
        if($where)
            $this->db->where($where); 
        
        $dbdata = $this->db->get()->result_array();
        
        $results = array();
        if($empty)$results[''] = '';
        foreach($dbdata as $key=>$row){
            if(isset($row[$column]))
            {
                if(lang($row[$column]) != '')$row[$column] = lang($row[$column]);
                $results[$row[$this->_primary_key]] = $row[$column];
                
                if($show_id)
                {
                    $results[$row[$this->_primary_key]] = $row['id'].', '.$results[$row[$this->_primary_key]];
                }
                
            }
            
        }
        return $results;
    }
    
    public function save($data, $id)
    {
        // Save lat lng in decimal for radius/rectangle search
        if(!empty($data['gps']))
        {
            $gps = explode(', ', $data['gps']);
            $data['lat'] = floatval($gps[0]);
            $data['lng'] = floatval($gps[1]);
        }
        
        // [Save first image in repository]
        $curr_estate = $this->get($id);
        $repository_id = NULL;
        if(is_object($curr_estate))
        {
            $repository_id = $curr_estate->repository_id;
        }
        
        $data['image_repository'] = NULL;
        if(!empty($repository_id))
        {
            $files = $this->file_m->get_by(array('repository_id'=>$repository_id));
            
            $image_repository = array();
            foreach($files as $key_f=>$file_row)
            {
                if(is_object($file_row))
                if(file_exists(FCPATH.'files/thumbnail/'.$file_row->filename))
                {
                    if(empty($data['image_filename']))
                        $data['image_filename'] = $file_row->filename;
                        
                    $image_repository[] = $file_row->filename;
                }
            }
            
            $data['image_repository'] = json_encode($image_repository);
        }
        // [/Save first image in repository]
        
        return parent::save($data, $id);
    }
    
    public function save_dynamic($data, $id)
    {
        // Delete all
        $this->db->where('property_id', $id);
        $this->db->where('value !=', 'SKIP_ON_EMPTY');
        $this->db->delete('property_value'); 
        
        if(config_db_item('slug_enabled') === TRUE)
        {
            // save slug
            $this->load->model('slug_m');
            $this->slug_m->save_slug('estate_m', $id, $data);
        }
        
        $fields = $this->db->list_fields('property_lang');
        $fields = array_flip($fields);
        
        // Insert all
        $insert_batch = array();
        $data_property_lang = array();
        foreach($data as $key=>$value)
        {
            if(substr($key, 0, 6) == 'option')
            {
                $pos = strpos($key, '_');
                $option_id = substr($key, 6, $pos-6);
                $language_id = substr($key, $pos+1);
                
                $val_numeric = NULL;
                $value_n = trim($value);
                $value_n = str_replace("'", '', $value_n);
                $value_n = str_replace("’", '', $value_n);
                $value_n = str_replace(",", '', $value_n);
                if( is_numeric($value_n) )
                {
                    $val_numeric = floatval($value_n);
                }
                
                $insert_arr = array('language_id' => $language_id,
                                    'property_id' => $id,
                                    'option_id' => $option_id,
                                    'value' => $value,
                                    'value_num' => $val_numeric);
                
                /* [property_lang] */
                $data_property_lang[$language_id]['language_id']=$language_id;
                $data_property_lang[$language_id]['property_id']=$id;
                $data_property_lang[$language_id]['json_object']['field_'.$option_id] = $value;
                
                if (isset($fields['field_'.$option_id]))
                {
                    $data_property_lang[$language_id]['field_'.$option_id]=$value;
                } 
                
                if(is_numeric($val_numeric) && isset($fields['field_'.$option_id.'_int']))
                {
                    $data_property_lang[$language_id]['field_'.$option_id.'_int'] = floatval($val_numeric);
                }
                /* [/property_lang] */
                
                if($value != 'SKIP_ON_EMPTY')
                    $insert_batch[] = $insert_arr;
            }
        }
        
        if(count($insert_batch) > 0)
            $this->db->insert_batch('property_value', $insert_batch); 

        // Delete all users
        if(!empty($data['agent']))
        {
            $this->db->where('property_id', $id);
            $this->db->delete('property_user'); 
            $this->db->set(array('property_id'=>$id,
                                 'user_id'=>$data['agent']));
            $this->db->insert('property_user');
        }
        /* [property_lang] */
        foreach($data_property_lang as $lang_id =>$property_data)
        {
            foreach($fields as $key_field=>$val_field)
            {
                if(!isset($data_property_lang[$lang_id][$key_field]))
                    $data_property_lang[$lang_id][$key_field] = NULL;
            }
            
            $data_property_lang[$lang_id]['json_object'] = 
                json_encode($data_property_lang[$lang_id]['json_object']);
        }
        
        if(count($data_property_lang) > 0)
        {
            $this->db->delete('property_lang', array('property_id' => $id)); 
            $this->db->insert_batch('property_lang', $data_property_lang); 
        }
        /* [/property_lang] */
    }
    
    public function delete($id)
    {
        // Delete all options
        $this->db->where('property_id', $id);
        $this->db->delete('property_value'); 
        
        $this->db->where('property_id', $id);
        $this->db->delete('property_lang'); 
        
        $this->db->where('property_id', $id);
        $this->db->delete('enquire'); 
        
        $this->db->where('property_id', $id);
        $this->db->delete('property_user'); 
        
        $this->db->where('property_id', $id);
        $this->db->delete('reservaions');
        
        $this->db->where('property_id', $id);
        $this->db->delete('favorites');
        
        // [START] remove rates
        $query = $this->db->get_where('rates', array('property_id' => $id));
        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            $this->db->where('rates_id', $row->id);
            $this->db->delete('rates_lang'); 
        } 
        $this->db->where('property_id', $id);
        $this->db->delete('rates'); 
        // [END] remove rates
        
        // Remove repository
        $estate_data = $this->get($id, TRUE);
        if(count($estate_data))
        {
            $this->repository_m->delete($estate_data->repository_id);
        }
        
        parent::delete($id);
    }
    
    public function get_sitemap()
	{
        // Fetch pages without parents
        $this->db->select('*');
        //$this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.page_id');
        $estates = parent::get_by(array('is_activated'=>1));
                
        return $estates;
	}
    
    public function check_user_permission($property_id, $user_id)
    {
        $this->db->where('property_id', $property_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('property_user');
        return $query->num_rows();
    }
    
    public function get_user_properties($user_id)
    {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('property_user');
        
        $properties = array();
        foreach ($query->result() as $row)
        {
          $properties[] = $row->property_id;
        }
        
        return $properties;
    }
    
    public function get_user_id($property_id)
    {
        $this->db->where('property_id', $property_id);
        $query = $this->db->get('property_user', 1);
        
        if ($query->num_rows() > 0)
        {
           $row = $query->row();
           return $row->user_id;
        } 
        
        return NULL;
    }
    
    public function change_activated_properties($property_ids = array(), $is_activated)
    {
        $data = array(
                       'is_activated' => $is_activated
                    );
        
        $this->db->where_in('id', $property_ids);
        $this->db->update($this->_table_name, $data); 
    }

}



