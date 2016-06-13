<?php

class Treefield_m extends MY_Model {
    
    protected $_table_name = 'treefield';
    protected $_order_by = 'treefield.order, treefield.id';
    
    public $rules = array(
        'parent_id' => array('field'=>'parent_id', 'label'=>'lang:parent', 'rules'=>'trim|required|xss_clean'),
        'template' => array('field'=>'template', 'label'=>'lang:Template', 'rules'=>'trim|xss_clean')
    );
   
    public $rules_lang = array();

	public function __construct(){
		parent::__construct();
        
        $this->languages = $this->language_m->get_form_dropdown('language', FALSE, FALSE);
  
        //Rules for languages
        foreach($this->languages as $key=>$value)
        {
            $this->rules_lang["value_$key"] = array('field'=>"value_$key", 'label'=>'lang:Value', 'rules'=>'trim|required|callback_values_correction|callback_values_dropdown_check|xss_clean');
            $this->rules_lang["title_$key"] = array('field'=>"title_$key", 'label'=>'lang:Page Title', 'rules'=>'trim|xss_clean');
            $this->rules_lang["path_title_$key"] = array('field'=>"path_title_$key", 'label'=>'lang:Custom path title', 'rules'=>'trim|xss_clean');
            $this->rules_lang["body_$key"] = array('field'=>"body_$key", 'label'=>'lang:Body', 'rules'=>'trim');
            $this->rules_lang["description_$key"] = array('field'=>"description_$key", 'label'=>'lang:Description', 'rules'=>'trim');
            $this->rules_lang["keywords_$key"] = array('field'=>"keywords_$key", 'label'=>'lang:Keywords', 'rules'=>'trim');
            $this->rules_lang["slug_$key"] = array('field'=>"slug_$key", 'label'=>'lang:URI slug', 'rules'=>'trim');
            $this->rules_lang["address_$key"] = array('field'=>"address_$key", 'label'=>'lang:Address', 'rules'=>'trim');
        
            for($i=1;$i<=6;$i++)
            {
                $this->rules_lang['adcode'.$i.'_'.$key] = array('field'=>'adcode'.$i.'_'.$key, 'label'=>lang_check('Ads code').' '.$i, 'rules'=>'trim');
            }
        }
	}

    public function get_new()
	{
        $option = new stdClass();
        $option->parent_id = 0;
        $option->template = 'treefield';
        
        //Add language parameters
        foreach($this->languages as $key=>$value)
        {
            $option->{"value_$key"} = '';
            $option->{"title_$key"} = '';
            $option->{"path_title_$key"} = '';
            $option->{"address_$key"} = '';
            $option->{"body_$key"} = '';
            $option->{"keywords_$key"} = '';
            $option->{"description_$key"} = '';
            $option->{"slug_$key"} = '';
            
            for($i=1;$i<=6;$i++)
            {
                //$option->{"adcode$i_$key"} = '';
                $option->{"adcode".$i."_".$key} = '';
            }
        }
        
        return $option;
	}
    
    public function get_max_level($key_option)
    {
        $this->db->select('MAX(`level`) as `level`', FALSE);
        $this->db->where('field_id', $key_option);
        $query = $this->db->get($this->_table_name);
        
        if(is_object($query) && $query->num_rows() > 0)
        {
            $row = $query->row();
        }
        else
        {
            echo 'SQL problem in get max_order:';
            echo $this->db->last_query();
            exit();
        }
        
        return (int) $row->level;
    }
    
    public function get_level_values ($lang_id, $field_id, $parent_id=0, $level=0, $not_selected = NULL)
    {
        $this->db->select($this->_table_name.'.id, value, level, parent_id');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
        $this->db->where('field_id', $field_id);
        $this->db->where('language_id', $lang_id);
        $this->db->where('level', $level);
        $this->db->where('parent_id', $parent_id);
        $this->db->order_by($this->_table_name.'_lang.value');
        $query = $this->db->get();
        $options = $query->result();
        
        $array = array('' => $not_selected);
        if($not_selected == NULL)
        {
            $lang_not_selected = lang_check('treefield_'.$field_id.'_'.$level);
            if(empty($lang_not_selected))
                $lang_not_selected = lang_check('Not selected');
            $array = array('' => $lang_not_selected);
        }
        
        if(count($options))
        {
            foreach($options as $option)
            {
                $array[$option->id] = $option->value;
            }
        }
        
//        if(count($array) == 1)
//        {
//            $array = array('' => lang_check('No values found'));
//        }
        
        return $array;        
    }

    public function get_no_parents($lang_id = 2, $field_id=0, $current_id = NULL)
	{
        // Fetch pages without parents
        $this->db->select($this->_table_name.'.id, value, level, parent_id');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
        $this->db->where('field_id', $field_id);
        $this->db->where('language_id', $lang_id);
        if($current_id != NULL)$this->db->where($this->_table_name.'.id !=', $current_id);
        //$this->db->order_by($this->_order_by);
        $this->db->order_by($this->_table_name.'_lang.value');
        $query = $this->db->get();
        $options = $query->result();

        // Return key => value pair array
        $array = array('0' => lang('No parent'));
        
        $t_array = array();
        if(count($options))
        {
            foreach($options as $option)
            {
                $t_array[$option->parent_id][$option->id] = $option;
            }
        }
        
        $this->generate_tree_recursive(0, $t_array, $array, 0);
        return $array;
	}
    
    private function generate_tree_recursive($parent_id, $t_array, &$array, $level)
    {
        if(isset($t_array[$parent_id]))
        foreach($t_array[$parent_id] as $key=>$option)
        {
            $level_gen = str_pad('', $level*12, '&nbsp;');

            $array[$key] = $level_gen.'|-'.$option->value;
            
            if(isset($t_array[$key]))
                $this->generate_tree_recursive($key, $t_array, $array, $level+1);
        }
    }
    
    public function get_table_tree($lang_id = 2, $field_id=0, $current_id = NULL, $return_print=true)
	{
        // Fetch pages without parents
        $this->db->select($this->_table_name.'.id, value, level, parent_id, template, body');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
        $this->db->where('field_id', $field_id);
        $this->db->where('language_id', $lang_id);
        //if($current_id != NULL)$this->db->where($this->_table_name.'.id !=', $current_id);
        //$this->db->order_by($this->_order_by);
        $this->db->order_by($this->_table_name.'_lang.value');
        $query = $this->db->get();
        $options = $query->result();

        // Return key => value pair array
        $array = array();
        
        $t_array = array();
        if(count($options))
        {
            foreach($options as $option)
            {
                $t_array[$option->parent_id][$option->id] = $option;
            }
        }
        
        if(!$return_print)
        {
            return $t_array;
        }
        
        $this->_generate_table_tree_recursive(0, $t_array, $array, 0);
        return $array;
	}
    
    private function _generate_table_tree_recursive($parent_id, $t_array, &$array, $level)
    {
        if(isset($t_array[$parent_id]))
        foreach($t_array[$parent_id] as $key=>$option)
        {
            $level_gen = str_pad('', $level*12, '&nbsp;');
            
            $option->visual = $level_gen.'|-';
            $array[$key] = $option;
            
            if(isset($t_array[$key]))
                $this->_generate_table_tree_recursive($key, $t_array, $array, $level+1);
        }
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
    
    public function get_lang($id = NULL, $single = FALSE, $lang_id=1)
    {
        if($id != NULL)
        {
            $result = $this->get($id);
            
            $this->db->select('*');
            $this->db->from($this->_table_name.'_lang');
            $this->db->where('treefield_id', $id);
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
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
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
    
    public function get_typeahead($q, $limit=8, $treefield_ids=array(5,7,40), $lang_id=1)
    {
        $results = array();
        
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
    
    public function save_with_lang($data, $data_lang, $field_id, $treefield_id = NULL)
    {
        // Set timestamps
        if($this->_timestamps == TRUE)
        {
            $now = date('Y-m-d H:i:s');
            $treefield_id || $data['created'] = $now;
            $data['modified'] = $now;
        }
        
        $data['field_id'] = $field_id;
        
        if(empty($data['level']) && !empty($data['parent_id']))
        {
            $parent_treefield = $this->get($data['parent_id']);
            $data['level'] = $parent_treefield->level + 1;
        }
        
        // Insert
        if($treefield_id === NULL)
        {
            !isset($data[$this->_primary_key]) || $data[$this->_primary_key] = NULL;
            $this->db->set($data);
            $this->db->insert($this->_table_name);
            $treefield_id = $this->db->insert_id();
        }
        // Update
        else
        {
            $filter = $this->_primary_filter;
            $treefield_id = $filter($treefield_id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $treefield_id);
            $this->db->update($this->_table_name);
        }
        
        // Save lang data
        $this->db->delete($this->_table_name.'_lang', array('treefield_id' => $treefield_id));
        
        foreach($this->languages as $lang_key=>$lang_val)
        {
            if(is_numeric($lang_key))
            {
                $curr_data_lang = array();
                $curr_data_lang['language_id'] = $lang_key;
                $curr_data_lang['treefield_id'] = $treefield_id;

                foreach($data_lang as $data_key=>$data_val)
                {
                    $pos = strrpos($data_key, "_");
                    if((int)substr($data_key,$pos+1) == (int)$lang_key)
                    {
                        $curr_data_lang[substr($data_key,0,$pos)] = $data_val;
                        if(substr($data_key,0,$pos) == 'value')
                        {
                            $curr_data_lang['value_path'] = $this->get_path($field_id, $data['parent_id'], $lang_key).$data_val;
                        }
                    }
                }
                
                $this->db->set($curr_data_lang);
                $this->db->insert($this->_table_name.'_lang');
            }
        }

        return $treefield_id;
    }
    
    public function id_by_path($field_id, $lang_id, $path)
    {
        $this->db->select('treefield_id, field_id, language_id, value_path');
        $this->db->from($this->_table_name.'_lang');
        $this->db->join($this->_table_name, $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
        $this->db->where('field_id', $field_id);
        $this->db->where('value_path', $path);
        $this->db->where('language_id', $lang_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0)
        {
           $row = $query->row();
           return $row->treefield_id;
        }
        
        return NULL;
    }
    
    public function get_path($field_id, $treefield_id, $lang_id)
    {
        // Fetch pages without parents
        $this->db->select($this->_table_name.'.id, value, level, parent_id');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
        $this->db->where('field_id', $field_id);
        $this->db->where('language_id', $lang_id);
        $this->db->order_by($this->_order_by);
        $query = $this->db->get();
        $options = $query->result();

        // Return key => value pair array
        $array = array();

        $t_array = array();
        if(count($options))
        {
            foreach($options as $option)
            {
                $t_array[$option->id] = $option;
            }
        }
        
        if(!isset($t_array[$treefield_id]))
            return '';
        
        $tree_parent_id = $t_array[$treefield_id]->parent_id;
        $generated_path = $t_array[$treefield_id]->value.' - ';

        while(!empty($t_array[$tree_parent_id]))
        {
            $option = $t_array[$tree_parent_id];
            $generated_path = $option->value.' - '.$generated_path;
            $tree_parent_id = $option->parent_id;
        }

        return $generated_path;
    }
    
    public function regenerate_fields()
    {
        $this->db->select($this->_table_name.'.id, value, level, field_id, parent_id, language_id as lang_id, '.$this->_table_name.'_lang.id as tree_lang_id');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.treefield_id');
        $query = $this->db->get();
        
        echo  $this->db->last_query().'<br />';
        $fields = $query->result();
        
        $data = array();
        foreach($fields as $key=>$row)
        {
            $data_t = array();
            $data_t['value_path'] = $this->get_path($row->field_id, $row->parent_id, $row->lang_id).$row->value;
            $data_t['id'] = $row->tree_lang_id;
            $data[] = $data_t;
            
            //$this->db->where('id', $row->tree_lang_id);
            //$this->db->update($this->_table_name.'_lang', $data); 
        }
        
        $this->db->update_batch($this->_table_name.'_lang', $data, 'value_path'); 
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
    
    public function check_deletable($id)
    {
        $where = "( parent_id=$id OR id=$id )";
        $this->db->where($where);
        $this->db->from($this->_table_name);
        
        return ($this->db->count_all_results() == 0);
    }
    
    public function delete($field_id)
    {
        //Get all childs
        $childs = array();
        $this->_get_all_childs($field_id, 0, $childs);
        
        //Delete childs
        $this->db->where_in('treefield_id', $childs);
        $this->db->delete('treefield_lang'); 
        $this->db->where_in('id', $childs);
        $this->db->delete('treefield'); 
    }
    
    private function _get_all_childs($field_id, $treefield_id, &$childs)
    {
        // Fetch pages without parents
        $this->db->select($this->_table_name.'.id, level, parent_id');
        $this->db->from($this->_table_name);
        $this->db->where('field_id', $field_id);
        $this->db->order_by($this->_order_by);
        $query = $this->db->get();
        $options = $query->result();

        $t_array = array();
        if(count($options))
        {
            foreach($options as $option)
            {
                $t_array[$option->parent_id][$option->id] = $option;
            }
        }
        
        $this->_get_all_childs_recursive($treefield_id, $t_array, $childs);
    }
    
    private function _get_all_childs_recursive($parent_id, $t_array, &$array)
    {
        if(isset($t_array[$parent_id]))
        foreach($t_array[$parent_id] as $key=>$option)
        {
            $array[$key] = $key;
            
            if(isset($t_array[$key]))
                $this->_get_all_childs_recursive($key, $t_array, $array);
        }
    }
    
    public function delete_value($field_id, $treefield_id)
    {
            //Get all childs
            $childs = array();
            $this->_get_all_childs($field_id, $treefield_id, $childs);
            
            //Delete childs
            $this->db->where_in('treefield_id', $childs);
            $this->db->delete('treefield_lang'); 
            $this->db->where_in('id', $childs);
            $this->db->delete('treefield'); 
            
            //Delete current
            $this->db->delete('treefield_lang', array('treefield_id' => $treefield_id)); 
            $this->db->delete('treefield', array('id' => $treefield_id)); 
    }
    
}



