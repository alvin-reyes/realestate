<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class GTranslation
{
    public $clientID; // Customer ID
    public $clientSecret; // Primary Account Key
    private $accessToken = NULL;
    private $validEmail = 'sandi@iwinter.com.hr';

    public function __construct($params = array())
    {
        $cid = '';
        $secret = '';
        
        if(is_array($params))
        {
            if(isset($params['clientID']))
                $cid = $params['clientID'];
            
            if(isset($params['clientSecret']))
                $secret = $params['clientSecret'];
        }
        
        $this->clientID = $cid;
        $this->clientSecret = $secret;
    }
    
    function curl($url,$params = array(),$is_coockie_set = false)
    {
        if(!$is_coockie_set){
            /* STEP 1. let’s create a cookie file */
            $ckfile = tempnam ("/tmp", "CURLCOOKIE");
         
            /* STEP 2. visit the homepage to set the cookie properly */
            $ch = curl_init ($url);
            curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
            $output = curl_exec ($ch);
        }
         
        $str = ''; $str_arr= array();
        foreach($params as $key => $value)
        {
            $str_arr[] = urlencode($key)."=".urlencode($value);
        }
        if(!empty($str_arr))
            $str = '?'.implode('&',$str_arr);
         
        /* STEP 3. visit cookiepage.php */
         
        $Url = $url.$str;
         
        $ch = curl_init ($Url);
        curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
         
        $output = curl_exec ($ch);
        return $output;
    }
 
    function translate_api($word, $from, $to)
    {
        $word = urlencode($word);

        $url = 'http://translate.google.com/translate_a/t?client=t&text='.$word.'&hl=en&sl='.$from.'&tl='.$to.'&multires=1&otf=2&ie=UTF-8&oe=UTF-8&pc=1&ssel=0&tsel=0&sc=1';
         
        $name_en = $this->curl($url);
         
        $name_en = explode('"',$name_en);
        return  $name_en[1];
    }
    
    public function translate($word, $from, $to)
    {
	    if(!function_exists('curl_version'))
            return '';
        
        return $this->translate_api($word, $from, $to);
    }

}

?>