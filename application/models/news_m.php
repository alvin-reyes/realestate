<?php

class News_m extends MY_Model {
    
    protected $_table_name = 'news';
    protected $_order_by = 'parent_id, order, id';
    public $rules = array(
        'parent_id' => array('field'=>'parent_id', 'label'=>'lang:Parent', 'rules'=>'trim|intval'),
        'language_id' => array('field'=>'language_id', 'label'=>'lang:Language', 'rules'=>'trim|intval'),
        'template' => array('field'=>'template', 'label'=>'lang:Template', 'rules'=>'trim|required|xss_clean'),
        //'title' => array('field'=>'title', 'label'=>'lang:Title', 'rules'=>'trim|required|max_length[100]|xss_clean'),
        //'navigation_title' => array('field'=>'navigation_title', 'label'=>'lang:Navigation title', 'rules'=>'trim|required|max_length[100]|xss_clean'),
        //'slug' => array('field'=>'slug', 'label'=>'lang:Slug', 'rules'=>'trim|required|max_length[100]|url_title|callback__unique_slug|xss_clean'),
        //'body' => array('field'=>'body', 'label'=>'lang:Body', 'rules'=>'trim|required'),
   );
   
   public $rules_lang = array();
   
	public function __construct(){
		parent::__construct();
        
        $this->languages = $this->language_m->get_form_dropdown('language', FALSE, FALSE);
                                  
        //Rules for languages
        foreach($this->languages as $key=>$value)
        {
            $this->rules_lang["title_$key"] = array('field'=>"title_$key", 'label'=>'lang:Title', 'rules'=>'trim|required|xss_clean');
            $this->rules_lang["navigation_title_$key"] = array('field'=>"navigation_title_$key", 'label'=>'lang:Navigation title', 'rules'=>'trim|required|xss_clean');
            $this->rules_lang["body_$key"] = array('field'=>"body_$key", 'label'=>'lang:Body', 'rules'=>'trim');
            $this->rules_lang["description_$key"] = array('field'=>"description_$key", 'label'=>'lang:Description', 'rules'=>'trim');
            $this->rules_lang["keywords_$key"] = array('field'=>"keywords_$key", 'label'=>'lang:Keywords', 'rules'=>'trim');
        }
	}

    public function get_new()
	{
        $page = new stdClass();
        $page->parent_id = 0;
        $page->language_id = 0;
        $page->template = 'page';
        $page->date = date('Y-m-d H:i:s');
        
        //Add language parameters
        foreach($this->languages as $key=>$value)
        {
            $page->{"title_$key"} = '';
            $page->{"navigation_title_$key"} = '';
            //$page->{"slug_$key"} = '';
            $page->{"body_$key"} = '';
            $page->{"keywords_$key"} = '';
            $page->{"description_$key"} = '';
        }
        
        return $page;
	}
    
	public function save_order ($pages)
	{
		if (count($pages)) {
			foreach ($pages as $order => $page) {
				if ($page['item_id'] != '') {
					$data = array('parent_id' => (int) $page['parent_id'], 'order' => $order);
					$this->db->set($data)->where($this->_primary_key, $page['item_id'])->update($this->_table_name);
				}
			}
		}
	}
    
	public function get_with_parent ($id = NULL, $single = FALSE)
	{
		$this->db->select('page.*, p.slug as parent_slug, p.title as parent_title');
		$this->db->join('page as p', 'page.parent_id=p.id', 'left');
		return parent::get($id, $single);
	}
    
    public function get_templates($template_prefix)
    {
        $CI =& get_instance();
        
        $templates = array();

//        $templates = array('page' => lang('Page'), 
//                           'homepage' => lang('Homepage'), 
//                           'homepage-slideshow' => lang('Homepage Slideshow'), 
//                           'sale' => lang('Sale'), 
//                           'rent' => lang('Rent'), 
//                           'contact' => lang('Contact page'));
        
        $templatesDirectory = opendir(FCPATH.'templates/'.$CI->app_settings['template']);
        // get each template
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != "..") {
                if(substr_count($tempFile, $template_prefix) > 0)
                {
                    $templates[substr($tempFile,0,-4)] = lang_check(ucfirst(substr($tempFile, strlen($template_prefix), -4)));
                }

            }
        }
        
        return $templates;
    }
    
    public function get_lang($id = NULL, $single = FALSE, $lang_id=1)
    {
        if($id != NULL)
        {
            $result = $this->get($id);
            
            $this->db->select('*');
            $this->db->from($this->_table_name.'_lang');
            $this->db->where('page_id', $id);
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
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.page_id');
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
        $this->db->delete($this->_table_name.'_lang', array('page_id' => $id));
        
        foreach($this->languages as $lang_key=>$lang_val)
        {
            if(is_numeric($lang_key))
            {
                $curr_data_lang = array();
                $curr_data_lang['language_id'] = $lang_key;
                $curr_data_lang['page_id'] = $id;
                
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
    
    public function get_no_parents($lang_id=1)
	{
        // Fetch pages without parents
        $this->db->select('*');
        $this->db->where('parent_id', 0);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.page_id');
        $this->db->where('language_id', $lang_id);
        $pages = parent::get();
        
        // Return key => value pair array
        $array = array(0 => lang('No parent'));
        if(count($pages))
        {
            foreach($pages as $page)
            {
                $array[$page->id] = $page->title;
            }
        }
        
        return $array;
	}
    
    public function get_sitemap()
	{
        // Fetch pages without parents
        $this->db->select('*');
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.page_id');
        $pages = parent::get();
                
        return $pages;
	}
    
    public function get_first ()
    {
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->where('template !=', '');
        $this->db->order_by($this->_order_by);
        $this->db->limit(1);
        
		$pages = $this->db->get()->result();
        
        if(count($pages) > 0)
        {
            return $pages[0];
        }
        
        return '';
    }
    
    public function get_id_by_name ($page_id)
    {
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.page_id');
        $this->db->order_by($this->_order_by);
		$pages = $this->db->get()->result_array();
        
		foreach ($pages as $page) {
		  if(url_title_cro($page['title'], '-', TRUE) == $page_id)  
          {
            return $page['id'];
          }
		}
        
        return $page_id;
    }    
    
	public function get_nested ($lang_id=2)
	{
        $this->db->select('*');
        $this->db->from($this->_table_name);
        $this->db->join($this->_table_name.'_lang', $this->_table_name.'.id = '.$this->_table_name.'_lang.page_id');
        $this->db->where('language_id', $lang_id);
        $this->db->order_by($this->_order_by);
		$pages = $this->db->get()->result_array();
		
		$array = array();
		foreach ($pages as $page) {         
			if (! $page['parent_id']) {
				// This page has no parent
				$array[$page['id']] = $page;
			}
			else {
				// This is a child page
				$array[$page['parent_id']]['children'][] = $page;
			}
		}
		return $array;
	}
    
    public function delete($id)
    {
        $this->db->delete('page_lang', array('page_id' => $id)); 
        
        // Remove repository
        $page_data = $this->get($id, TRUE);
        if(count($page_data))
        {
            $this->repository_m->delete($page_data->repository_id);
        }
        
        parent::delete($id);
        
		// Reset parent ID for its children
		$this->db->set(array(
			'parent_id' => 0
		))->where('parent_id', $id)->update($this->_table_name);
    }

}


