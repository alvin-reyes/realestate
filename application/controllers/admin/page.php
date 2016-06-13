<?php

class Page extends Admin_Controller
{
	public function __construct(){
		parent::__construct();
        $this->load->model('page_m');
        $this->load->model('file_m');
        $this->load->model('repository_m');

        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
        
        $this->data['template_css'] = base_url('templates/'.$this->data['settings']['template']).'/'.config_item('default_template_css');
	
    }
    
    public function index()
	{
	    // Fetch all pages
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['pages_nested'] = $this->page_m->get_nested_tree($this->data['content_language_id']);
        
        // Load view
		$this->data['subview'] = 'admin/page/index';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function order()
    {
		$this->data['sortable'] = TRUE;
        
        // Load view
		$this->data['subview'] = 'admin/page/order';
        $this->load->view('admin/_layout_main', $this->data);
    }
    
    public function update_ajax($filename = NULL)
    {
        // Save order from ajax call
        if(isset($_POST['sortable']) && $this->config->item('app_type') != 'demo')
        {
            $this->page_m->save_order($_POST['sortable']);
        }
        
        $data = array();
        $length = strlen(json_encode($data));
        header('Content-Type: application/json; charset=utf8');
        header('Content-Length: '.$length);
        echo json_encode($data);
        
        exit();
    }
    
    public function edit($id = NULL)
	{
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['page'] = $this->page_m->get_lang($id, FALSE, $this->data['content_language_id']);
            count($this->data['page']) || $this->data['errors'][] = 'User could not be found';
            
            // Fetch file repository
            $repository_id_t = $this->data['page']->repository_id;

            if(empty($repository_id_t))
            {
                // Create repository
                $repository_id_new = $this->repository_m->save(array('name'=>'page_m'));
                // exit();
                // Update page with new repository_id
                $this->page_m->save(array('repository_id'=>$repository_id_new), $this->data['page']->id);
            }
        }
        else
        {
            $this->data['page'] = $this->page_m->get_new();
        }
        
		// Pages for dropdown
        //$this->data['pages_no_parents'] = $this->page_m->get_no_parents($this->data['content_language_id']);
        $this->data['pages_no_parents'] = $this->page_m->get_no_parents_news($this->data['content_language_id'], 'No parent', $id);
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['templates_page'] = $this->page_m->get_templates('page_');
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->zoom_enabled = false;
            $file->download_url = base_url('files/'.$file->filename);
            $file->delete_url = site_url_q('files/upload/rep_'.$file->repository_id, '_method=DELETE&amp;file='.rawurlencode($file->filename));

            if(file_exists(FCPATH.'/files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $file->zoom_enabled = true;
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
            }
            
            $this->data['files'][$file->repository_id][] = $file;
        }
        
        // Set up the form
        $rules = $this->page_m->rules;
        $this->form_validation->set_rules($this->page_m->get_all_rules());

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/page/edit/'.$id);
                exit();
            }
            
            $data = $this->page_m->array_from_post(array('type', 'template', 'parent_id', 'is_visible', 'is_private'));
            
            if($id == NULL)
            {
                //get max order in parent id and set
                $parent_id = $this->input->post('parent_id');
                $data['order'] = $this->page_m->max_order($parent_id);
            }

            $data_lang = $this->page_m->array_from_post($this->page_m->get_lang_post_fields());
            if($id == NULL)
            {
                $data['date'] = date('Y-m-d H:i:s');
                $data['date_publish'] = date('Y-m-d H:i:s');
            }

            $id = $this->page_m->save_with_lang($data, $data_lang, $id);
            
            if(config_db_item('slug_enabled') === TRUE)
            {
                // save slug
                $this->load->model('slug_m');
                $this->slug_m->save_slug('page_m', $id, $data_lang, $data);
            }
            
            $this->load->library('sitemap');
            $this->sitemap->generate_sitemap();
            
            $this->session->set_flashdata('message', 
                    '<p class="label label-success validation">'.lang_check('Changes saved').'</p>');
            
            redirect('admin/page/edit/'.$id);
        }
        
        // Load the view
		$this->data['subview'] = 'admin/page/edit';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/page');
            exit();
        }
       
		$this->page_m->delete($id);
        redirect('admin/page');
	}
    
	public function parent_check($parent_id)
	{
	    if($parent_id==0 || $this->input->post('type') == 'ARTICLE')
            return TRUE;
            
        $page_parent = $this->page_m->get($parent_id);
        if($page_parent->parent_id == 0)
        {
            return TRUE;
        }

    	$this->form_validation->set_message('parent_check', lang_check('Just 2 page levels allowed'));
    	return FALSE;
	}
    
    public function _unique_slug($str)
    {
        // Do NOT validate if slug alredy exists
        // UNLESS it's the slug for the current page
        
        $id = $this->uri->segment(4);
        $this->db->where('slug', $this->input->post('slug'));
        !$id || $this->db->where('id !=', $id);
        
        $page = $this->page_m->get();
        
        if(count($page))
        {
            $this->form_validation->set_message('_unique_slug', '%s should be unique');
            return FALSE;
        }
        
        return TRUE;
    }
    
}