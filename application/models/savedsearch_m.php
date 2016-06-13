<?php

class Savedsearch_m extends MY_Model {
    
    protected $_table_name = 'saved_search';
    protected $_order_by = 'id';
    public $rules_admin = array(
        'activated' => array('field'=>'activated', 'label'=>'lang:Activated', 'rules'=>'trim'),
        'user_id' => array('field'=>'user_id', 'label'=>'lang:User', 'rules'=>'trim|required'),
    );
    
    public $rules_lang = array();
   
	public function __construct(){
		parent::__construct();
	}

    public function get_new()
	{
        $item = new stdClass();
        $item->date_last_informed = date('Y-m-d H:i:s');
        $item->date_created = date('Y-m-d H:i:s');
        $item->activated = 0;
        $item->user_id = 'NULL';
        $item->parameters = '';
        $item->lang_code = '';
        
        return $item;
	}
    
    public function get_new_array()
	{
        $item = array();
        $item['date_last_informed'] = date('Y-m-d H:i:s');
        $item['date_created'] = date('Y-m-d H:i:s');
        $item['activated'] = 1;
        $item['user_id'] = 'NULL';
        $item['parameters'] = '';
        $item['lang_code'] = '';
        
        return $item;
	}
    
    public function delete($id)
    {      
        parent::delete($id);
    }
    
    public function check_if_exists($user_id, $parameters, $lang_code='')
    {
        $query = $this->db->get_where($this->_table_name, array('user_id'    => $user_id, 
                                                                'parameters' => $parameters,
                                                                'lang_code'  => $lang_code  ));
        
        return $query->num_rows();
    }

}


