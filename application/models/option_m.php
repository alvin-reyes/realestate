<?php

class Option_m extends MY_Model {
    
    protected $_table_name = 'option';
    protected $_order_by = 'option.order, option.id';
    
    public $rules = array(
        'parent_id' => array('field'=>'parent_id', 'label'=>'lang:parent', 'rules'=>'trim|required|xss_clean'),
        //'language_id' => array('field'=>'language_id', 'label'=>'lang:Language', 'rules'=>'trim|required|intval'),
        'type' => array('field'=>'type', 'label'=>'lang:Type', 'rules'=>'trim|required|xss_clean'),
        'visible' => array('field'=>'visible', 'label'=>'lang:Visible in table', 'rules'=>'trim|xss_clean'),
        'max_length' => array('field'=>'max_length', 'label'=>'lang:Max length', 'rules'=>'trim|is_natural|xss_clean'),
        'is_locked' => array('field'=>'is_locked', 'label'=>'lang:Locked', 'rules'=>'trim|xss_clean'),
        'is_frontend' => array('field'=>'is_frontend', 'label'=>'lang:Visible in frontend', 'rules'=>'trim|xss_clean'),
        'is_required' => array('field'=>'is_required', 'label'=>'lang:Required', 'rules'=>'trim|xss_clean'),
   );
   
   public $rules_lang = array();
   
    public $option_types = array('CATEGORY', 'CHECKBOX', 'INPUTBOX', 'TEXTAREA', 'DROPDOWN', 'TREE', 'UPLOAD', 'DECIMAL', 'INTEGER');
    public $option_type_color = array('CATEGORY'=>'danger', 'CHECKBOX'=>'success', 'INPUTBOX'=>'success', 'DROPDOWN'=>'success', 'TEXTAREA'=>'success', 'TREE'=>'warning', 'UPLOAD'=>'info', 'DECIMAL'=>'success', 'INTEGER'=>'success');
    
	public function __construct(){
		parent::__construct();
        
        $this->load->model('language_m');
        $this->languages = $this->language_m->get_form_dropdown('language', FALSE, FALSE);
        
        $this->option_types = array('CATEGORY'=>lang('CATEGORY'), 'CHECKBOX'=>lang('CHECKBOX'), 'INPUTBOX'=>lang('INPUTBOX'),
                                    'DROPDOWN'=>lang('DROPDOWN'), 'TEXTAREA'=>lang('TEXTAREA'), 'TREE'=>lang_check('TREE'), 'UPLOAD'=>lang_check('UPLOAD'), 'DECIMAL'=>lang_check('DECIMAL'), 'INTEGER'=>lang_check('INTEGER'));
        
        if(config_item('tree_field_enabled') === FALSE)
        {
            unset($this->option_types['TREE']);
        }
        
        if(config_item('enable_numeric_input') === FALSE)
        {
            unset($this->option_types['DECIMAL'], $this->option_types['INTEGER']);
        }
        
        
        //Rules for languages
        foreach($this->languages as $key=>$value)
        {
            $this->rules_lang["values_$key"] = array('field'=>"values_$key", 'label'=>'lang:Values', 'rules'=>'trim|callback_values_correction|callback_values_dropdown_check|xss_clean');
            $this->rules_lang["suffix_$key"] = array('field'=>"suffix_$key", 'label'=>'lang:Suffix', 'rules'=>'trim|xss_clean');
            $this->rules_lang["prefix_$key"] = array('field'=>"prefix_$key", 'label'=>'lang:Prefix', 'rules'=>'trim|xss_clean');
            $this->rules_lang["option_$key"] = array('field'=>"option_$key", 'label'=>'lang:Option name', 'rules'=>'trim|required|required|xss_clean');
        }
	}

    public function get_new()
	{
        $option = new stdClass();
        $option->parent_id = 0;
        $option->type = 'checkbox';
        $option->visible = false;
        $option->is_locked = 0;
        $option->is_frontend = 1;
        $option->is_required = 0;
        $option->max_length = '';
        
        //Add language parameters
        foreach($this->languages as $key=>$value)
        {
            $option->{"values_$key"} = '';
            $option->{"option_$key"} = '';
            $option->{"suffix_$key"} = '';
            $option->{"prefix_$key"} = '';
        }
        
        return $option;
	}

    public function get_no_parents($lang_id = 2)
	{
        // Fetch pages without parents
        $this->db->select($this->_table_name.'.id, option');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.option_id');
        $this->db->where('parent_id', 0);
        $this->db->where('type', 'category');
        $this->db->where('language_id', $lang_id);
        $this->db->order_by($this->_order_by);
        $query = $this->db->get();
        $options = $query->result();

        // Return key => value pair array
        $array = array(0 => lang('No parent'));
        if(count($options))
        {
            foreach($options as $option)
            {
                $array[$option->id] = $option->option;
            }
        }
        
        return $array;
	}
    
    public function get_visible($lang_id=1)
    {
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.option_id');
        $this->db->where('language_id', $lang_id);
        $this->db->where('visible', '1');
        $this->db->order_by($this->_order_by);
        
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
    public function get_options($lang_id=1, $option_id = array(), $property_id = array())
    {
        $this->db->where('language_id', $lang_id);
        
        if(count($option_id) > 0)
        {
            $this->db->where_in('option_id', $option_id);
        }
        
        if(count($property_id) > 0)
        {
            $this->db->where_in('property_id', $property_id);
        }
        
        $query = $this->db->get('property_value');
        
        $data = array();
        foreach($query->result() as $key=>$option)
        {
            $data[$option->property_id][$option->option_id] = $option->value;
        }

        return $data;
    }
    
    public function get_property_value($language_id, $property_id, $option_id)
    {
        $query = $this->db->get_where('property_value', array('language_id'=>$language_id,
                                                                        'property_id'=>$property_id,
                                                                        'option_id'=>65), 1);
        if ($query->num_rows() > 0)
        {
           $row = $query->row();
           return $row->value;
        }                                          
                                                                        
        return NULL;                                                             
    }
    
    public function get_lang($id = NULL, $single = FALSE, $lang_id=1)
    {
        if($id != NULL)
        {
            $result = $this->get($id);
            
            $this->db->select('*');
            $this->db->from($this->_table_name.'_lang');
            $this->db->where('option_id', $id);
            $lang_result = $this->db->get()->result_array();
            foreach ($lang_result as $row)
            {
                foreach ($row as $key=>$val)
                {
                    $result->{$key.'_'.$row['language_id']} = $val;
                }
            }
            
            foreach($this->languages as $key_lang=>$val_lang)
            {
                foreach($this->rules_lang as $r_key=>$r_val)
                {
                    if(!isset($result->{$r_key}))
                    {
                        $result->{$r_key} = '';
                    }
                }
            }
            
            return $result;
        }
        
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.option_id');
        $this->db->where('language_id', $lang_id);
        
        if($single == TRUE)
        {
            $method = 'row';
        }
        else
        {
            $method = 'result';
        }
        
        if(!count($this->db->ar_orderby))
        {
            $this->db->order_by($this->_order_by);
        }
        
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
    public function get_typeahead($q, $limit=8, $option_ids=array(5,7,40), $lang_id=1)
    {
        $results = array();
        
        //Generate query
        $this->db->distinct();
        $this->db->select('value');
        $this->db->from('property_value');
        $this->db->where('language_id', $lang_id);
        $this->db->where_in('option_id', $option_ids);
        $this->db->like('value', $q);
        $this->db->order_by('value');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        $q_result = $query->result();
        
        // Generate results
        foreach($q_result as $key=>$row)
        {
            $results[] = $row->value;
        }
        
        // for treefield
        if(config_db_item('tree_field_enabled') === TRUE)
        {
            //Generate query
            $this->db->distinct();
            $this->db->select('value');
            $this->db->from('treefield_lang');
            $this->db->where('language_id', $lang_id);
            $this->db->like('value', $q);
            $this->db->order_by('value');
            $this->db->limit($limit);
            
            $query = $this->db->get();
            $q_result = $query->result();
            
            // Generate results
            foreach($q_result as $key=>$row)
            {
                $results[] = $row->value;
            }
            
            $results = array_unique($results);
        }
        
        return $results;
    }
    
    public function get_lang_array($id = NULL, $single = FALSE, $lang_id=1)
    {
        if($id != NULL)
        {
            $result = $this->get($id);
            
            $this->db->select('*');
            $this->db->from($this->_table_name.'_lang');
            $this->db->where('option_id', $id);
            $lang_result = $this->db->get()->result_array();
            foreach ($lang_result as $row)
            {
                foreach ($row as $key=>$val)
                {
                    $result->{$key.'_'.$row['language_id']} = $val;
                }
            }
            
            foreach($this->languages as $key_lang=>$val_lang)
            {
                foreach($this->rules_lang as $r_key=>$r_val)
                {
                    if(!isset($result->{$r_key}))
                    {
                        $result->{$r_key} = '';
                    }
                }
            }
            
            return $result;
        }
        
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.option_id');
        $this->db->where('language_id', $lang_id);
        
        if($single == TRUE)
        {
            $method = 'row';
        }
        else
        {
            $method = 'result';
        }
        
        if(!count($this->db->ar_orderby))
        {
            $this->db->order_by($this->_order_by);
        }
        
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
    
    public function save_with_lang($data, $data_lang, $id = NULL)
    {
        // Set timestamps
        if($this->_timestamps == TRUE)
        {
            $now = date('Y-m-d H:i:s');
            $id || $data['created'] = $now;
            $data['modified'] = $now;
        }

        // Insert
        if($id === NULL)
        {
            !isset($data[$this->_primary_key]) || $data[$this->_primary_key] = NULL;
            $this->db->set($data);
            $this->db->insert($this->_table_name);
            $id = $this->db->insert_id();
        }
        // Update
        else
        {
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $id);
            $this->db->update($this->_table_name);
        }
        
        // Save lang data
        $this->db->delete($this->_table_name.'_lang', array('option_id' => $id));
        
        foreach($this->languages as $lang_key=>$lang_val)
        {
            if(is_numeric($lang_key))
            {
                $curr_data_lang = array();
                $curr_data_lang['language_id'] = $lang_key;
                $curr_data_lang['option_id'] = $id;
                
                foreach($data_lang as $data_key=>$data_val)
                {
                    $pos = strrpos($data_key, "_");
                    if(substr($data_key,$pos+1) == $lang_key)
                    {
                        $curr_data_lang[substr($data_key,0,$pos)] = $data_val;
                    }
                }
                
                $this->db->set($curr_data_lang);
                $this->db->insert($this->_table_name.'_lang');
            }
        }

        return $id;
    }
    
	public function get_nested ($lang_id = 2)
	{
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.option_id');
        $this->db->where('language_id', $lang_id);
        $this->db->order_by($this->_order_by);
		$pages = $this->db->get()->result_array();
        
        
		$array = array();
		foreach ($pages as $page) {
            if(!isset($this->option_types[$page['type']]))continue;
          
            $page['color'] = $this->option_type_color[$page['type']];
            $page['type'] = $this->option_types[$page['type']];
          
			if (! $page['parent_id']) {
				// This page has no parent
				$array[$page['id']]['parent'] = $page;
			}
			else {
				// This is a child page
				$array[$page['parent_id']]['children'][] = $page;
			}
		}
        
		return $array;
	}
    
	public function save_order ($options)
	{
		if (is_array($options)) {
			foreach ($options as $order => $option) {
				if ($option['item_id'] != '') {
					$data = array('parent_id' => (int) $option['parent_id'], 'order' => $order);
					$this->db->set($data)->where($this->_primary_key, $option['item_id'])->update($this->_table_name);
				}
			}
		}
	}
    
    public function save_repository($repository_id, $field_id, $property_id, $lang_id, $id)
    {
        $data = array();
        $data['value'] = $repository_id;
        $data['value_num'] = $repository_id;
        
        if(empty($id))
        {
            // Insert
            $data['language_id'] = $lang_id;
            $data['property_id'] = $property_id;
            $data['option_id'] = $field_id;

            $this->db->set($data);
            $this->db->insert('property_value');
            
            $id = $this->db->insert_id();
        }
        else
        {
            //Update
            $this->db->set($data);
            $this->db->where('id', $id);
            $this->db->update('property_value');
        }
        
//        $str = $this->db->last_query();
//        echo $str;
        
        return $id;
    }
    
    public function get_property_value_by($where, $single = FALSE, $limit = NULL, $order_by = NULL, $offset = "", $search = '')
    {
        $this->_table_name = 'property_value';
        $this->_order_by = 'id';
        
        $ret_parent = parent::get_by($where, $single, $limit, $order_by, $offset, $search);
        
        $this->_table_name = 'option';
        $this->_order_by = 'option.order, option.id';
        
        return $ret_parent;
    }
    
    public function check_deletable($id)
    {
        $where = "( parent_id=$id OR id=$id ) AND ( is_locked=1 OR is_hardlocked=1 )";
        $this->db->where($where);
        $this->db->from($this->_table_name);
        
        return ($this->db->count_all_results() == 0);
    }
    
    public function delete($id)
    {
        //check for deletable
        if($this->check_deletable($id))
        {
            //Get all childs
            $childs = $this->get_by(array('parent_id'=>$id));
            
            if(count($childs) > 0)
            {
                foreach($childs as $key=>$child)
                {
                    //remove all values
                    $this->db->delete('property_value', array('option_id' => $child->id));
                    
                    // remove all childs translations
                    $this->db->delete('option_lang', array('option_id' => $child->id)); 
                }
                
                //Remove childs
                $this->db->delete($this->_table_name, array('parent_id'=>$id)); 
            }
            
            //remove all values from current
            $this->db->delete('property_value', array('option_id' => $id));
            
            //Remove current option
            $this->db->delete('option_lang', array('option_id' => $id)); 
            parent::delete($id);
            
            // Delete also treefields if exists
            $this->load->model('treefield_m');
            $this->treefield_m->delete($id);
        }
    }
    
}



