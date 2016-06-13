<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// ------------------------------------------------------------------------

/**
 * Text Input Field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_number'))
{
	function form_number($data = '', $value = '', $extra = '')
	{
		$defaults = array('type' => 'number', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Form Value
 *
 * Grabs a value from the POST array for the specified field so you can
 * re-populate an input field or textarea.  If Form Validation
 * is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @return	mixed
 */
if ( ! function_exists('set_value_GET'))
{
	function set_value_GET($field = '', $default = '', $skip_valdation = FALSE)
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			if ( ! isset($_GET[$field]))
			{
				return $default;
			}

			return form_prep($_GET[$field], $field);
		}
        
        if($skip_valdation)
        {
			if (!empty($_GET[$field]))
			{
			    $CI =& get_instance(); 
				return $CI->input->get($field);
			}
        }
        
		return form_prep($OBJ->set_value($field, $default), $field);
	}
}

if ( ! function_exists('regenerate_query_string'))
{
	function regenerate_query_string($enable_fields = array())
	{
		$CI =& get_instance();
        $check_fields = (count($enable_fields) > 0);
        $_GET_clone = $_GET;

        $gen_text = '';
        if(count($_GET_clone) > 0) foreach($_GET_clone as $field=>$value)
        {
            if($check_fields && !in_array($field, $enabled_fields))
            {
                continue;
            }
            
            $s_value = $CI->input->get($field);
            
            if(!empty($s_value))
            {
                $gen_text.=$field.'='.$s_value.'&amp;';
            }
        }
        
        if(!empty($gen_text))
            $gen_text = substr($gen_text, 0, strlen($gen_text)-5);

        return $gen_text;
	}
}

if ( ! function_exists('prepare_search_query_GET'))
{
	function prepare_search_query_GET($enabled_fields = array(), $smart_fields = array())
	{
		$CI =& get_instance();
        $check_fields = (count($enabled_fields) > 0);
        $_GET_clone = $_GET;
        
        $smart_search = '';
        if(isset($_GET_clone['smart_search']))
        $smart_search = $CI->input->get('smart_search');
        
        if(count($_GET_clone) > 0)
        {
            unset($_GET_clone['smart_search']);
            
            if(count($smart_fields) > 0 && !empty($smart_search))
            {
                $gen_q = '';
                foreach($smart_fields as $key=>$value)
                {
                    if($value == 'id' && is_numeric($smart_search))
                    {
                        $gen_q.="$value = $smart_search OR ";
                    }
                    else
                    {
                        $gen_q.="$value LIKE '%$smart_search%' OR ";
                    }
                }
                $gen_q = substr($gen_q, 0, -4);
                
                $CI->db->where("($gen_q)");
            }

            if(count($_GET_clone) > 0) foreach($_GET_clone as $field=>$value)
            {
                if($check_fields && !in_array($field, $enabled_fields))
                {
                    return;
                }
                
                $s_value = $CI->input->get($field);
                
                if(!empty($s_value))
                {
                    if($field == 'id' && is_numeric($s_value))
                    {
                        $CI->db->where($field, $s_value);
                    }
                    else
                    {
                        $CI->db->like($field, $s_value);
                    }
                }
            }
        }

	}
}


/* End of file form_helper.php */
