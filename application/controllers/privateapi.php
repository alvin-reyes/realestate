<?php

class Privateapi extends CI_Controller
{
   
   private $user_id = NULL;
   
   private $data = array();
   
   private $settings = array();
    
   public function __construct()
   {
        parent::__construct();
        
        header('Content-Type: application/json');
        
        //load settings
        $this->load->model('settings_m');
        $this->settings = $this->settings_m->get_fields();
        
        //load language files
        $this->load->model('language_m');
        $lang_code_uri = $this->uri->segment(3);
        $lang_name = $this->language_m->get_name($lang_code_uri);
        if($lang_name != NULL)
            $this->lang->load('frontend_template', $lang_name, FALSE, TRUE, FCPATH.'templates/'.$this->settings['template'].'/');
        
        // Check login and fetch user id
        $this->load->library('session');
        $this->load->model('user_m');
        if($this->user_m->loggedin() == TRUE)
        {
            $this->user_id = $this->session->userdata('id');
        }
        else
        {
            $this->data['message'] = lang_check('Login required!');
            echo json_encode($this->data);
            exit();
        }
   }

	public function index()
	{
		$this->data['message'] = lang_check('Hello, Private API here!');
        echo json_encode($this->data);
        exit();
	}
    
    public function add_to_favorites($lang_code='')
    {
        $this->data['message'] = lang_check('No message returned!');
        $this->data['parameters'] = $_POST;
        $property_id = $this->input->post('property_id');
        // To fetch user_id use: $this->user_id

        $this->load->model('favorites_m');
        
        $this->data['success'] = false;
        // Check if property_id already saved, stop and write message
        if($this->favorites_m->check_if_exists($this->user_id, $property_id)>0)
        {
            $this->data['message'] = lang_check('Favorite already exists!');
            $this->data['success'] = true;
        }
        // Save favorites to database
        else
        {
            $data = $this->favorites_m->get_new_array();
            $data['user_id'] = $this->user_id;
            $data['property_id'] = $property_id;
            $data['lang_code'] = $lang_code;
            $data['date_last_informed'] = date('Y-m-d H:i:s');
            
            $this->favorites_m->save($data);
            
            $this->data['message'] = lang_check('Favorite added!');
            $this->data['success'] = true;
        }
        
        echo json_encode($this->data);
        exit();
    }
    
    public function remove_from_favorites($lang_code='')
    {
        $this->data['message'] = lang_check('No message returned!');
        $this->data['parameters'] = $_POST;
        $property_id = $this->input->post('property_id');
        // To fetch user_id use: $this->user_id

        $this->load->model('favorites_m');
        
        $this->data['success'] = false;
        // Check if property_id already saved, stop and write message
        if($this->favorites_m->check_if_exists($this->user_id, $property_id)>0)
        {
            $favorite_selected = $this->favorites_m->get_by(array('property_id'=>$property_id, 'user_id'=>$this->user_id), TRUE);
            $this->favorites_m->delete($favorite_selected->id);
            
            $this->data['message'] = lang_check('Favorite removed!');
            $this->data['success'] = true;
        }
        // Save favorites to database
        else
        {
            $this->data['message'] = lang_check('Favorite doesnt exists!');
            $this->data['success'] = true;
        }
        
        echo json_encode($this->data);
        exit();
    }

    public function save_search($lang_code='')
    {
        $this->data['message'] = lang_check('No message returned!');
        
        if(count($_POST > 0))
        {
            // [START] Radius search
            $search_radius = $_POST['v_search_radius'];
            if(isset($search_radius) && isset($_POST['v_search_option_smart']) && $search_radius > 0)
            {
                $this->load->library('ghelper');
                $coordinates_center = $this->ghelper->getCoordinates($search_array['v_search_option_smart']);
                
                if(count($coordinates_center) >= 2 && $coordinates_center['lat'] != 0)
                {
                    // calculate rectangle
                    $rectangle_ne = $this->ghelper->getDueCoords($coordinates_center['lat'], $coordinates_center['lng'], 315, $search_radius);
                    $rectangle_sw = $this->ghelper->getDueCoords($coordinates_center['lat'], $coordinates_center['lng'], 135, $search_radius);
                    
                    $_POST['v_rectangle_ne'] = $rectangle_ne;
                    $_POST['v_rectangle_sw'] = $rectangle_sw;
                    unset($_POST['v_search_option_smart'], $_POST['v_undefined'], $_POST['v_search_radius']);
                }
            }
            // [END] Radius search
        }

        $this->data['parameters'] = $_POST;
        $parameters = json_encode($_POST);
        // To fetch user_id use: $this->user_id
        
        $this->load->model('savedsearch_m');
        
        // Check if parameters already saved, stop and write message
        if($this->savedsearch_m->check_if_exists($this->user_id, $parameters, $lang_code)>0)
        {
            $this->data['message'] = lang_check('Search already exists!');
        }
        // Save parameters to database
        else
        {
            $data = $this->savedsearch_m->get_new_array();
            $data['user_id'] = $this->user_id;
            $data['parameters'] = $parameters;
            $data['lang_code'] = $lang_code;
            
            // Check if there is some parameters
            $values_exists = false;
            foreach($this->data['parameters'] as $key=>$value){
                if(!empty($value) && $key != 'view' && $key != 'order' && 
                    $key != 'page_num' && $key != 'v_search-start')
                $values_exists = true;
            }
            
            if(!$values_exists)
            {
                $this->data['message'] = lang_check('No values selected!');
                echo json_encode($this->data);
                exit();
            }
            
            $this->savedsearch_m->save($data);
            
            $this->data['message'] = lang_check('Search saved!');
        }
        
        echo json_encode($this->data);
        exit();
    }
    
    public function get_level_values_select($lang_id, $field_id, $parent_id=0, $level=0)
    {
        $this->data['message'] = lang_check('No message returned!');
        $this->data['parameters'] = $_POST;
        $parameters = json_encode($_POST);
        // To fetch user_id use: $this->user_id
        
        $this->load->model('language_m');
        $this->load->model('treefield_m');

        $lang_name = $this->session->userdata('lang');
        if(!empty($lang_id))
            $lang_name = $this->language_m->get_name($lang_id);
            
        $this->lang->load('backend_base', $lang_name);
        
        $values_arr = $this->treefield_m->get_level_values ($lang_id, $field_id, $parent_id, $level);
        
        $generate_select = '';
        foreach($values_arr as $key=>$value)
        {
            $generate_select.= "<option value=\"$key\">$value</option>\n";
        }
        
        $this->data['generate_select'] = $generate_select;
        $this->data['values_arr'] = $values_arr;
        
        echo json_encode($this->data);
        exit();
    }
    

}