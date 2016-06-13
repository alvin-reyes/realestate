<?php

class Treefield extends Frontend_Controller
{

	public function __construct ()
	{
		parent::__construct();
        
        $this->load->model('treefield_m');
	}
    
    public function _remap($method)
    {
        $this->index();
    }
    
    private function _get_purpose()
    {
        if(config_item('all_results_default') === TRUE)
        {
            $this->data['purpose_defined'] = '';
            return '';
        }
        
        if(isset($this->data['is_purpose_sale'][0]['count']))
        {
            return lang('Sale');
        }
        
        if(isset($this->data['is_purpose_rent'][0]['count']))
        {
            return lang('Rent');
        }
        
        return lang('Sale');
    }
    
    private function _custom_search_filtering(&$res_array, $options, $post_option)
    {
        foreach($res_array as $key=>$row)
        {
            foreach($post_option as $key1=>$val1)
            {
                if(is_numeric($val1) && $key1 != 'smart')
                {
                    $option_num = $key1;

                    if(strrpos($option_num, 'from') > 0)
                    {
                        $option_num = substr($option_num,0,-5);
                        
                        // For rentable
                        if($option_num == 36 && isset($this->data['is_purpose_rent'][0]['count']))
                            $option_num++;
                        
                        if(!isset($this->data['is_purpose_rent'][0]['count']) &&
                                !isset($this->data['is_purpose_sale'][0]['count']))
                        {
                            if( ($options[$row['id']][$option_num] < $val1 || empty($options[$row['id']][$option_num])) && 
                                ($options[$row['id']][$option_num+1] < $val1 || empty($options[$row['id']][$option_num+1]))  )
                            {
                                unset($res_array[$key]);
                            }
                        }
                        else if(!isset($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] < $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                    else if(strrpos($option_num, 'to') > 0)
                    {
                        $option_num = substr($option_num,0,-3);
                        
                        // For rentable
                        if($option_num == 36 && isset($this->data['is_purpose_rent'][0]['count']))
                            $option_num++;
                        
                        
                        if(!isset($this->data['is_purpose_rent'][0]['count']) &&
                                !isset($this->data['is_purpose_sale'][0]['count']))
                        {
                            if(!isset($options[$row['id']][$option_num]))
                            {
                                unset($res_array[$key]);
                            }
                            else
                            {
//                                echo $val1."\r\n";
//                                echo $options[$row['id']][$option_num]."\r\n";
//                                echo $options[$row['id']][$option_num+1]."\r\n";
                                
                                if( ($options[$row['id']][$option_num] > $val1 || empty($options[$row['id']][$option_num])) && 
                                    ($options[$row['id']][$option_num+1] > $val1 || empty($options[$row['id']][$option_num+1]) || $row['id'] != 36 )  )
                                {
                                    unset($res_array[$key]);
//                                    echo "unset\r\n";
                                }
                            }
                        }
                        else if(!isset($options[$row['id']][$option_num]) || empty($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] > $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                    else
                    {
                        if(!isset($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] != $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                }
            }
        }
    }

    public function index()
    {
        $model_s = (string) $this->uri->segment(1);
        $lang_code = (string) $this->uri->segment(2);
        $treefield_id = (string) $this->uri->segment(3);

        $lang_id = $this->data['lang_id'];

        $date['treefield_data'] = array();
        
        if(!empty($treefield_id) && is_numeric($treefield_id)){
            $this->data['treefield_data'] = $this->treefield_m->get_lang($treefield_id, TRUE, $lang_id);

            $this->data['page_navigation_title'] = $this->data['treefield_data']->{'value_'.$lang_id};
            $this->data['value_path'] = $this->data['treefield_data']->{'value_path_'.$lang_id};
            $this->data['page_title'] = $this->data['treefield_data']->{'title_'.$lang_id};
            $this->data['page_body']  = $this->data['treefield_data']->{'body_'.$lang_id};
            $this->data['page_address']  = $this->data['treefield_data']->{'address_'.$lang_id};
            $this->data['page_description'] = character_limiter(strip_tags($this->data['treefield_data']->{'description_'.$lang_id}), 160);
            $this->data['keywords'] = character_limiter(strip_tags($this->data['treefield_data']->{'keywords_'.$lang_id}), 160);
            $this->data['page_keywords'] = $this->data['keywords'];
        }
        else
        {
            show_404(current_url());
        }
        
        $field_id = $this->data['treefield_data']->field_id;

        /* Fetch options names */
        $this->data['options'] = $options = $this->option_m->get_options($lang_id);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);
        $option_categories = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_prefix_'.$row->option_id] = $row->prefix;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_name'] = $row->option;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_type'] = $row->type;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_suffix'] = $row->suffix;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_prefix'] = $row->prefix;
            
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_checkbox'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_dropdown'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_text'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_upload'] = array();
            
            $this->data['category_options_count_'.$row->parent_id] = 0;
            
            $option_categories[$row->option_id] = $row->parent_id;
        }
        /* End fetch options names */
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);
            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($this->data['treefield_data']->repository_id == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
                $this->data['documents_'.$file->repository_id][] = $file;
                if($this->data['treefield_data']->repository_id == $file->repository_id)
                {
                    $this->data['page_documents'][] = $file;
                }
            }
        }
        
        // Has attributes
        $this->data['has_page_documents'] = array();
        if(count($this->data['page_documents']))
            $this->data['has_page_documents'][] = array('count'=>count($this->data['page_documents']));
        
        $this->data['has_page_images'] = array();
        if(count($this->data['page_images']))
            $this->data['has_page_images'][] = array('count'=>count($this->data['page_images']));
        
        /* Fetch treefield data end */
        
        /* Get last n properties */
        $last_n = 4;
        if(config_item('last_estates_limit'))
            $last_n = config_item('last_estates_limit');
        
        $last_n_estates = $this->estate_m->get_array_by(array('is_activated' => 1), FALSE, $last_n, 'id DESC');
        
        $this->data['last_estates_num'] = $last_n;
        $this->data['last_estates'] = array();
        foreach($last_n_estates as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr['id'];
            $estate['gps'] = $estate_arr['gps'];
            $estate['address'] = $estate_arr['address'];
            $estate['date'] = $estate_arr['date'];
            $estate['repository_id'] = $estate_arr['repository_id'];
            $estate['is_featured'] = $estate_arr['is_featured'];
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_arr['id']][$row2->option_id]))
                {
                    $row1 = $options[$estate_arr['id']][$row2->option_id];
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                }
            }
            
            // [START] custom price field
//            $estate['custom_price'] = '';
//            if(!empty($estate['option_36']))
//                $estate['custom_price'].=$this->data['options_prefix_36'].$estate['option_36'].$this->data['options_suffix_36'];
//            if(!empty($estate['option_37']))
//                $estate['custom_price'].=$this->data['options_prefix_37'].$estate['option_37'].$this->data['options_suffix_37'];
//            if(empty($estate['option_37']) && !empty($estate['option_56']))
//                $estate['custom_price'].=$this->data['options_prefix_56'].$estate['option_56'].$this->data['options_suffix_56'];
            // [END] custom price field
            
            // Url to preview
            if(isset($options[$estate_arr['id']][10]))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_arr['id']][10]));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_arr['repository_id']]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['last_estates'][] = $estate;
        }
        
        /* END Get last n properties */

        // Get slideshow
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['slideshow_property_images'] = array();
        $num=0;
//        foreach($files as $key=>$file)
//        {
//            if($estate_data['repository_id'] == $file->repository_id)
//            {
//                $slideshow_image = array();
//                $slideshow_image['num'] = $num;
//                $slideshow_image['url'] = base_url('files/'.$file->filename);
//                $slideshow_image['first_active'] = '';
//                if($num==0)$slideshow_image['first_active'] = 'active';
//                
//                $this->data['slideshow_property_images'][] = $slideshow_image;
//                $num++;
//            }
//        }

        foreach($this->data['page_images']  as $key=>$file)
        {
            if($estate_data['repository_id'] == $file->repository_id)
            {
                $slideshow_image = array();
                $slideshow_image['num'] = $num;
                $slideshow_image['url'] = str_replace(' ', '%20', base_url('files/'.$file->filename));
                $slideshow_image['first_active'] = '';
                if($num==0)$slideshow_image['first_active'] = 'active';
                
                $this->data['slideshow_property_images'][] = $slideshow_image;
                $num++;
            }
        }
        // End Get slideshow
        
        /* Helpers */
        $this->data['year'] = date('Y');
        /* End helpers */
        
        /* Widgets functions */
        $this->data['print_menu'] = get_menu($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_menu_realia'] = get_menu_realia($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_lang_menu'] = get_lang_menu($this->language_m->get_array_by(array('is_frontend'=>1)), $this->data['lang_code']);
        $this->data['page_template'] = $this->temp_data['page']->template;
        /* End widget functions */

        /* Fetch options data */
        $options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        // Fetch post values
        $address = $this->input->post('address');
        $order = $this->input->post('order');
        $view = $this->input->post('view');
        
        $post_option = array();
        $post_option_sum = ' ';
        foreach($_POST as $key=>$val)
        {
            $tmp_post = $this->input->post($key);
            if(!empty($tmp_post) && strrpos($key, 'tion_') > 0){
                $post_option[substr($key, strrpos($key, 'tion_')+5)] = $tmp_post;
                $post_option_sum.=$tmp_post.' ';
            }
            
            if(is_array($tmp_post))
            {
                $category_num = substr($key, strrpos($key, 'gory_')+5);
                
                foreach($tmp_post as $key=>$val)
                {
                    $post_option['0'.$category_num.'9999'.$key] = $val;
                    $post_option_sum.=$val.' ';
                }
            }
        }
        
        // add treefield value
        $post_option[64] = $this->data['value_path'].' - ';
        $post_option[88] = $this->data['value_path'].' - ';

        // [JSON_SEARCH]
        // Example: ?search={"search_option_smart": "zagreb"}
        $search_json = NULL;
        if(isset($_GET['search']))$search_json = json_decode($_GET['search']);
        
        if($search_json !== FALSE && $search_json !== NULL)
        {
            $post_option = array();
            $post_option_sum = ' ';
            
            foreach($search_json as $key=>$val)
            {
                $tmp_post = $val;
                if(!empty($tmp_post) && strrpos($key, 'tion_') > 0){
                    $post_option[substr($key, strrpos($key, 'tion_')+5)] = $tmp_post;
                    $post_option_sum.=$tmp_post.' ';
                }
                
                if(is_array($tmp_post))
                {
                    $category_num = substr($key, strrpos($key, 'gory_')+5);
                    
                    foreach($tmp_post as $key=>$val)
                    {
                        $post_option['0'.$category_num.'9999'.$key] = $val;
                        $post_option_sum.=$val.' ';
                    }
                }
            }

            $this->data['search_query'] = '';
            if(!empty($post_option['smart']))
                $this->data['search_query'] = $post_option['smart'];
        }
        
        $this->g_post_option = &$post_option;
        //print_r($this->g_post_option);

        // [/JSON_SEARCH]
        
        // End fetch post values  
        
        $this->data['options_name'] = array();
        $this->data['options_suffix'] = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_prefix_'.$row->option_id] = $row->prefix;
            $this->data['options_values_'.$row->option_id] = '';
            $this->data['options_values_li_'.$row->option_id] = '';
            $this->data['options_values_arr_'.$row->option_id] = array();
            $this->data['options_values_radio_'.$row->option_id] = '';
            
            if(count(explode(',', $row->values)) > 0)
            {
                $options_o = '<option value="">'.$row->option.'</option>';
                $options_li = '';
                $radio_li = '';
                $_s_value = strtolower(search_value($row->option_id));
                foreach(explode(',', $row->values) as $key2 => $val)
                {
                    $o_selected = '';
                    if(!empty($_s_value) && $_s_value == strtolower($val))
                    {
                        $o_selected = 'selected="selected"';
                    }
                    
                    $options_o.='<option value="'.$val.'" '.$o_selected.'>'.$val.'</option>';
                    $this->data['options_values_arr_'.$row->option_id][] = $val;
                    
                    $active = '';
                    if($this->_get_purpose() == strtolower($val))$active = 'active';
                    $options_li.= '<li class="'.$active.' cat_'.$key2.'"><a href="#">'.$val.'</a></li>';
                    
                    $radio_li.='<label class="checkbox" for="inputRent">
                                <input type="radio" rel="'.$val.'" name="search_option_'.$row->option_id.'" value="'.$key2.'" checked> '.$val.'
                                </label>';
                }
                $this->data['options_values_'.$row->option_id] = $options_o;
                $this->data['options_values_li_'.$row->option_id] = $options_li;
                $this->data['options_values_radio_'.$row->option_id] = $radio_li;
            }
        }

        /* Define order */
        if(empty($order))$order='id DESC';
        
        $this->data['order_dateASC_selected'] = '';
        if($order=='id ASC')
            $this->data['order_dateASC_selected'] = 'selected';
            
        $this->data['order_dateDESC_selected'] = '';
        if($order=='id DESC')
            $this->data['order_dateDESC_selected'] = 'selected';
            
        $this->data['order_priceASC_selected'] = '';
        if($order=='price ASC')
            $this->data['order_priceASC_selected'] = 'selected';
            
        $this->data['order_priceDESC_selected'] = '';
        if($order=='price DESC')
            $this->data['order_priceDESC_selected'] = 'selected';

        $this->data['order_livingarea_selected'] = '';
        if($order=='livingArea')
            $this->data['order_livingarea_selected'] = 'selected';
        /* End define order */

        /* Search */
        $this->db->distinct();
        //$this->db->select('property.id as id, property.gps, property.is_featured, property.address, property.date, property.repository_id');
        //$this->db->from('property');
        //$this->db->where('property.is_activated', 1);
        $this->db->select('property.id, property.gps, property.is_featured, property.counter_views, property.address, property.date, property.repository_id, property_user.user_id as agent_id, user.repository_id as agent_rep_id');
        $this->db->from('property');
        $this->db->where('property.is_activated', 1);
        $this->db->join('property_user', 'property.id = property_user.property_id', 'left');
        $this->db->join('user', 'property_user.user_id = user.id', 'left');
        
        if(strpos($order, 'price') !== FALSE)
        {
            /* ORDER_BY_PRICE */
            $this->db->join('property_value', 'property.id = property_value.property_id', 'inner');
            $this->db->where('property_value.option_id', 36);
            /* /ORDER_BY_PRICE */
        }
        
        if(isset($this->data['settings_listing_expiry_days']))
        {
            if(is_numeric($this->data['settings_listing_expiry_days']) && $this->data['settings_listing_expiry_days'] > 0)
            {
                $this->db->where('property.date_modified >', date("Y-m-d H:i:s" , time()-$this->data['settings_listing_expiry_days']*86400));
            }
        }
        
        if(!empty($address)){
            $this->db->like('property.address', $address);
        }
        foreach($post_option as $key=>$val)
        {
            if(is_numeric($key) || $key == 'smart')
                $this->db->like('property.search_values', $val);
        }

        if(isset($available_properties))
            $this->db->where_in('property.id', $available_properties);
        
        //$this->db->order_by('property.'.$order);
        
        if(strpos($order, 'price') === FALSE)
        {
            $this->db->order_by('property.is_featured DESC, property.'.$order);
        }
        else
        {
            $this->db->order_by('property.is_featured DESC, value_num '.substr($order, 6));
        }

        //$this->db->limit($config['per_page'], $this->data['pagination_offset']);
        $query = $this->db->get();

        $res_array = array();
        if ($query->num_rows() > 0)
        {
            $res_array = $query->result_array();
            
            $this->_custom_search_filtering($res_array, $options, $post_option);
        }
        
        /* Pagination in query */
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '<ul>';
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        $config['total_rows'] = count($res_array);
        $config['per_page'] = config_item('per_page');
        $config['uri_segment'] = 0;
        $this->data['total_rows'] = $config['total_rows'];
        $res_array_all = $res_array;
        
        // Pagination filtering
        $i=0;
        foreach($res_array as $key=>$row)
        {
            if($this->data['pagination_offset'] > $i)
                unset($res_array[$key]);
            
            if($this->data['pagination_offset']+$config['per_page'] <= $i)
                unset($res_array[$key]);
            
            $i++;
        }
        
        /* End Pagination */

        /* Get all estates data */
        $this->data['results'] = array();
        foreach($res_array as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr['id'];
            $estate['gps'] = $estate_arr['gps'];
            $estate['address'] = $estate_arr['address'];
            $estate['date'] = $estate_arr['date'];
            $estate['repository_id'] = $estate_arr['repository_id'];
            $estate['is_featured'] = $estate_arr['is_featured'];
            $estate['counter_views'] = $estate_arr['counter_views'];
            $estate['icons'] = array();

            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_arr['id']][$row2->option_id]))
                {
                    $row1 = $options[$estate_arr['id']][$row2->option_id];
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    $estate['option_icon_'.$key1] = '';
                    
                    if(!empty($row1))
                    {
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                        
                        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                            '/assets/img/icons/option_id/'.$key1.'.png'))
                        {
                            $estate['option_icon_'.$key1] = '<img class="results-icon" src="assets/img/icons/option_id/'.$key1.'.png" alt="'.$row1.'"/>';;
                            $estate['icons'][]['icon']= $estate['option_icon_'.$key1];
                        }
                    }
                }
            }
            
            $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png';
                }
            }
            
            // [START] custom price field
            $estate['custom_price'] = '';
            if(!empty($estate['option_36']))
                $estate['custom_price'].=$this->data['options_prefix_36'].$estate['option_36'].$this->data['options_suffix_36'];
            if(!empty($estate['option_37']))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_37'].$estate['option_37'].$this->data['options_suffix_37'];
            }
                
            if(empty($estate['option_37']) && !empty($estate['option_56']))
            {
                if(!empty($estate['custom_price']))
                    $estate['custom_price'].=' / ';
                $estate['custom_price'].=$this->data['options_prefix_56'].$estate['option_56'].$this->data['options_suffix_56'];
            }
            // [END] custom price field
            
            // Url to preview
            if(isset($options[$estate_arr['id']][10]))
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_arr['id']][10]));
            }
            else
            {
                $estate['url'] = slug_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_arr['repository_id']]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
                $estate['thumbnail_url_json'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
                $estate['thumbnail_url_json'] = base_url('templates/'.$this->data['settings_template']).'/assets/img/no_image.jpg';
            }
            
            // [agent second image]
            if(isset($estate_arr['agent_rep_id']))
            if(isset($this->data['images_'.$estate_arr['agent_rep_id']]))
            {
                if(isset($this->data['images_'.$estate_arr['agent_rep_id']][1]))
                $estate['agent_sec_img_url'] = $this->data['images_'.$estate_arr['agent_rep_id']][1]->thumbnail_url;
            }
            
            $estate['has_agent_sec_img'] = array();
            if(isset($estate['agent_sec_img_url']))
                $estate['has_agent_sec_img'][] = array('count'=>'1');
            // [/agent second image]

            $this->data['results'][] = $estate;
        }
        
        $this->data['all_estates'] = $this->data['results'];
        $this->data['all_estates_center'] = calculateCenter($this->data['all_estates']);
        
        /* Pagination load */ 
        $this->pagination->initialize($config);
        $this->data['pagination_links'] =  $this->pagination->create_links();
        /* End Pagination */

        /* {MOULE_ADS} */
        $this->load->model('ads_m');
        $this->data['ads'] = array();
        
        foreach($this->ads_m->ads_types as $type_key=>$type_name)
        {
            $ads_by_type = $this->ads_m->get_by(array('type'=>$type_key, 'is_activated'=>1));
            
            $num_ads = count($ads_by_type);

            $this->data['has_ads_'.$type_name] = array();
            if(isset($ads_by_type[0]))
            if($num_ads > 0)
            {
                $rand_ad_key = rand(0, $num_ads-1);
                
                if(isset($ads_by_type[$rand_ad_key]))
                {
                    $rand_image=0;
                    if($ads_by_type[$rand_ad_key]->is_random)
                        $rand_image = rand(0, count($this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id])-1);
                    
                    $this->data['random_ads_'.$type_name.'_link'] = $ads_by_type[$rand_ad_key]->link;
                    $this->data['random_ads_'.$type_name.'_repository'] = $ads_by_type[$rand_ad_key]->repository_id;
                    $this->data['random_ads_'.$type_name.'_image'] = $this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id][$rand_image]->url;
                    $this->data['has_ads_'.$type_name][] = array('count' => $num_ads);
                }
            }
        }
        /* {/MOULE_ADS} */

        // Get templates
        $templatesDirectory = opendir(FCPATH.'templates/'.$this->data['settings_template'].'/components');
        // get each template
        $template_prefix = 'page_';
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != ".." && strpos($tempFile, '.php') !== FALSE) {
                if(substr_count($tempFile, $template_prefix) == 0)
                {
                    $template_output = $this->parser->parse($this->data['settings_template'].'/components/'.$tempFile, $this->data, TRUE);
                    //$template_output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $template_output);
                    $this->data['template_'.substr($tempFile, 0, -4)] = $template_output;
                }
            }
        }

        $output = $this->parser->parse($this->data['settings_template'].'/'.$this->data['treefield_data']->template.'.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }

}