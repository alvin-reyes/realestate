<?php

class Language_m extends MY_Model {
    
    protected $_table_name = 'language';
    protected $_order_by = 'is_default DESC, id';

    public $rules_admin = array(
        'code' => array('field'=>'code', 'label'=>'lang:Code', 'rules'=>'trim|required|xss_clean|alpha|max_length[4]'),
        'language' => array('field'=>'language', 'label'=>'lang:Language', 'rules'=>'trim|required|xss_clean|alpha|strtolower'),
        'is_default' => array('field'=>'is_default', 'label'=>'lang:Default', 'rules'=>'trim'),
        'is_frontend' => array('field'=>'is_frontend', 'label'=>'lang:Default', 'rules'=>'trim'),
        'is_rtl' => array('field'=>'is_rtl', 'label'=>'lang:Default', 'rules'=>'trim'),
        'currency_default' => array('field'=>'currency_default', 'label'=>'lang:Currency', 'rules'=>'trim'),
    );

    public $backend_languages = array('hr'=>'Croatian', 'en'=>'English');
    public $db_languages_code = array();
    public $db_languages_id = array();
    
	public function __construct(){
		parent::__construct();
        
        $this->backend_languages = array();
        
        $langDirectory = opendir(APPPATH.'language');
        // get each lang
        while($langName = readdir($langDirectory)) {
            if ($langName != "." && $langName != "..") {
                $this->backend_languages[$langName] = lang($langName)==''?$langName:lang($langName);
            }
        }
        
        $langs = $this->get();
        foreach($langs as $row)
        {
            $this->db_languages_id[$row->id] = $row->code;
            $this->db_languages_code[$row->code] = $row->id;
        }
	}
    
    public function get_new()
	{
        $language = new stdClass();
        $language->code = '';
        $language->language = '';
        $language->is_default = 0;
        $language->is_frontend = 1;
        $language->is_rtl = 0;
        $language->currency_default = 'USD';
        return $language;
	}
    
    public function get_content_lang()
    {
        $query = $this->db->get_where($this->_table_name, array('language' => $this->config->item('language')), 1);
        
        if ($query->num_rows() > 0)
        {
            return $query->row()->id;
        }
        else
        {
            $query = $this->db->get_where($this->_table_name, array('is_default' => 1), 1);
            if ($query->num_rows() > 0)
                return $query->row()->id;
            else 
                return NULL;
        }

        return 2;
    }
    
    public function get_default()
    {
        $query = $this->db->get_where($this->_table_name, array('is_default' => 1, 'is_frontend'=>1), 1);
        if(count($query->row()))
        {
            return $query->row()->code;
        }
        
        $query = $this->db->get_where($this->_table_name, array('is_frontend'=>1), 1);
        if(count($query->row()))
        {
            return $query->row()->code;
        }
        
        return 'en';
    }
    
    public function get_default_id()
    {
        $query = $this->db->get_where($this->_table_name, array('is_default' => 1, 'is_frontend'=>1), 1);
        if(count($query->row()))
        {
            return $query->row()->id;
        }
        
        $query = $this->db->get_where($this->_table_name, array('is_frontend'=>1), 1);
        if(count($query->row()))
        {
            return $query->row()->id;
        }
        
        $query = $this->db->get_where($this->_table_name, NULL, 1);
        if(count($query->row()))
        {
            return $query->row()->id;
        }
        
        return FALSE;
    }
    
    public function count_visible($ignore_id = FALSE)
    {
        $query = $this->db->get_where($this->_table_name, array('is_frontend' => 1, 'id !='=>$ignore_id));
        return count($query->result());
    }
    
    public function get_id($code)
    {
        $query = $this->db->get_where($this->_table_name, array('code' => $code), 1);
        if(count($query->row()))
        return $query->row()->id;
    }
    
    public function get_code($id)
    {
        $query = $this->db->get_where($this->_table_name, array('id' => $id), 1);
        return $query->row()->code;
    }
    
    public function get_name($code)
    {
        if(is_numeric($code))
        {
            $query = $this->db->get_where($this->_table_name, array('id' => $code), 1);
        }
        else
        {
            $query = $this->db->get_where($this->_table_name, array('code' => $code), 1);
        }
        
        if($query->num_rows() > 0)
            return $query->row()->language;
            
        return NULL;
    }
    
    public function save($data, $id = NULL)
    {
        if($data['is_default'] == '1')
        {
            $this->db->set(array('is_default'=>'0'));
            $this->db->update($this->_table_name);
        }
        
        return parent::save($data, $id);
    }
    
    public function delete($id)
    {
        $this->db->where('language_id', $id);
        $this->db->delete('page_lang');
        
        $this->db->where('language_id', $id);
        $this->db->delete('property_value');
        
        $this->db->where('language_id', $id);
        $this->db->delete('property_lang');
        
        $this->db->where('language_id', $id);
        $this->db->delete('option_lang');
        
        $this->db->where('language_id', $id);
        $this->db->delete('showroom_lang');
    
        $this->db->where('language_id', $id);
        $this->db->delete('qa_lang');
        
        $this->db->where('language_id', $id);
        $this->db->delete('rates_lang');
        
        return parent::delete($id);
    }

}



