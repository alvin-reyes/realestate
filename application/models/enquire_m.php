<?php

class Enquire_m extends MY_Model {
    
    protected $_table_name = 'enquire';
    protected $_order_by = 'id DESC';
    public $rules = array(
    );
    public $rules_admin = array(
        'property_id' => array('field'=>'property_id', 'label'=>'lang:Estate', 'rules'=>'trim|required|xss_clean'),
        'name_surname' => array('field'=>'name_surname', 'label'=>'lang:Name and surname', 'rules'=>'trim|required|xss_clean'),
        'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|required|xss_clean'),
        'mail' => array('field'=>'mail', 'label'=>'lang:Mail', 'rules'=>'trim|required|xss_clean'),
        'message' => array('field'=>'message', 'label'=>'lang:Message', 'rules'=>'trim|required|xss_clean'),
        'address' => array('field'=>'address', 'label'=>'lang:Address', 'rules'=>'trim|xss_clean'),
        'readed' => array('field'=>'readed', 'label'=>'lang:Readed', 'rules'=>'trim'),
        'fromdate' => array('field'=>'fromdate', 'label'=>'lang:FromDate', 'rules'=>'trim|xss_clean'),
        'todate' => array('field'=>'todate', 'label'=>'lang:ToDate', 'rules'=>'trim|xss_clean')
    );

	public function __construct(){
		parent::__construct();
	}
    
    public function get_new()
	{
        $enquire = new stdClass();
        $enquire->name_surname = '';
        $enquire->address = '';
        $enquire->message = '';
        $enquire->phone = '';
        $enquire->mail = '';
        $enquire->date = date('Y-m-d H:i:s');
        $enquire->readed = 0;
        $enquire->fromdate = '';
        $enquire->todate = '';
        $enquire->property_id = NULL;
        return $enquire;
	}
    
    public function get($id = NULL, $single = FALSE)
    {
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            $this->db->select($this->_table_name.'.*, property_user.user_id');
            $this->db->join('property_user', $this->_table_name.'.property_id = property_user.property_id', 'left');
            $this->db->where('user_id', $this->session->userdata('id'));
        }
        
        return parent::get($id, $single);
    }
    
    public function total_unreaded()
    {
        $this->db->where('readed', '0');
        
        if($this->session->userdata('type') != 'ADMIN' && $this->session->userdata('type') != 'AGENT_ADMIN')
        {
            $this->db->select($this->_table_name.'.*, property_user.user_id');
            $this->db->join('property_user', $this->_table_name.'.property_id = property_user.property_id', 'left');
            $this->db->where('user_id', $this->session->userdata('id'));
        }
        
        $query = $this->db->get($this->_table_name);
        return $query->num_rows();
    }

}



