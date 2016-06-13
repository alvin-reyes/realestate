<?php

class Packages_m extends MY_Model {
    
    protected $_table_name = 'packages';
    protected $_order_by = 'id';
    public $rules_admin = array(
        'package_name' => array('field'=>'package_name', 'label'=>'lang:Package name', 'rules'=>'trim|required'),
        'num_listing_limit' => array('field'=>'num_listing_limit', 'label'=>'lang:Num listing limit', 'rules'=>'trim|required|is_natural'),
        'num_images_limit' => array('field'=>'num_images_limit', 'label'=>'lang:Num images limit', 'rules'=>'trim|is_natural'),
        'num_amenities_limit' => array('field'=>'num_amenities_limit', 'label'=>'lang:Num amenities limit', 'rules'=>'trim|is_natural'),
        'package_price' => array('field'=>'package_price', 'label'=>'lang:Package price', 'rules'=>'trim|numeric|xss_clean'),
        'package_days' => array('field'=>'package_days', 'label'=>'lang:Days limit', 'rules'=>'trim|is_natural'),
        'currency_code' => array('field'=>"currency_code", 'label'=>'lang:Currency code', 'rules'=>'trim|required|xss_clean'),
        'show_private_listings' => array('field'=>"show_private_listings", 'label'=>'lang:Show private listings', 'rules'=>'trim|xss_clean'),
        'user_type' => array('field'=>"user_type", 'label'=>'lang:User type', 'rules'=>'trim|xss_clean'),
        'auto_activation' => array('field'=>"auto_activation", 'label'=>'lang:Auto activate properties', 'rules'=>'trim|xss_clean')
    );
    
    public $rules_lang = array();
   
	public function __construct(){
		parent::__construct();
	}

    public function get_new()
	{
        $item = new stdClass();
        $item->date_created = date('Y-m-d H:i:s');
        $item->date_modified = NULL;
        $item->package_name = '';
        $item->num_listing_limit = 0;
        $item->package_price = '0.00';
        $item->package_days = 0;
        $item->currency_code = 'NULL';
        $item->show_private_listings = 1;
        $item->user_type = '';
        $item->auto_activation = 0;
        $item->num_images_limit = 1000;
        $item->num_amenities_limit = 1000;
        
        return $item;
	}
    
    public function get_curr_listings($where = NULL)
    {
        $listings_count = array();
        
        $this->db->select('user_id, COUNT(*) as properties_count');
        
        if($where != NULL)$this->db->where($where);
        
        $this->db->group_by(array('user_id'));

        $query = $this->db->get('property_user');
        if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
                $listings_count[$row->user_id] = $row->properties_count;
           }
        } 
        
        return $listings_count;
    }
    
    public function delete($id)
    {      
        parent::delete($id);
    }

}


