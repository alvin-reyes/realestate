<?php

/**
* @author       by sanljiljan
* @web          http://codecanyon.net/item/real-estate-agency-portal/6539169
* @date         5th Yanuary, 2015
* @copyright    No Copyrights on that file, but please link back in any way
*/
 
/*
|---------------------------------------------------------------
| CASTING argc AND argv INTO LOCAL VARIABLES
|---------------------------------------------------------------
|
*/

//
// Example for seo optimization via mod rewrite for codeigniter
// removing en, id and similar parts of url, only slug.htm required
//
//.htaccess file examplecontent:
//Options +FollowSymlinks
//RewriteEngine on
//RewriteBase /property-point
//RewriteCond %{REQUEST_FILENAME} !-f
//RewriteCond %{REQUEST_FILENAME} !-d
//RewriteRule ^(.*)\.htm$ slug.php?uri=$1 [L]
//
//Then usage should be:
//map.htm will be rewrited to for example:
//index.php/en/6/map
//

$script_folder = '/property-point/';
$index_file = 'index.php';

$q_s = get_qs_from_uri($_SERVER["REQUEST_URI"]);

$_SERVER['PATH_INFO']   = '/';
$_SERVER['SCRIPT_NAME'] = $script_folder.$index_file;
$_SERVER['REQUEST_URI'] = $script_folder.$index_file;
$_SERVER["QUERY_STRING"] = $q_s;

define('BASEPATH', './system/');

if(dirname(__FILE__) == 'C:\xampp-182\htdocs\property-point')
{
    $env = 'development';
}
else
{
    $env = 'production';
}

include('./application/config/'.$env.'/database.php');
include('./application/config/config.php');

$username = $db[$active_group]['username'];
$password = $db[$active_group]['password'];
$dbname = $db[$active_group]['database'];
$servername = $db[$active_group]['hostname'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM slugs WHERE slug='".$_GET['uri']."';";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    $row = $result->fetch_assoc();
    if($row['model_name'] == 'custom')
    {
        // $row['real_url']
        // [PATH_INFO] => /treefield/en/22/winnipeg
        // [QUERY_STRING] => search={%22search_option_smart%22:%20%22zagreb%22,%22test%22:%22test2%22}
        // [REQUEST_URI] => /property-point/index.php/treefield/en/22/winnipeg?search={%22search_option_smart%22:%20%22zagreb%22,%22test%22:%22test2%22}
        
        $q_s = get_qs_from_uri($row['real_url']);
        
        // If already slug
        if(substr_count($row['real_url'], '.htm') > 0)
        {
            $before_dot = substr($row['real_url'], 0, strpos($row['real_url'], '.htm'));
            
            $last_slug = $before_dot;
            if(substr_count($before_dot, '/') > 0)
            {
                $last_slug = substr($before_dot, strrpos($before_dot, '/')+1);
            }
            
            // Fetsh slug
            $sql1 = "SELECT * FROM slugs WHERE slug='".$last_slug."';";
            $result1 = $conn->query($sql1);
            if ($result1->num_rows > 0) {
                $row1 = $result1->fetch_assoc();
                generate_server_vars($row1);
            }
            else
            {
                echo 'NO slug found!';
            }
            
            
        }
        // If not a slug
        else
        {
            $prepare_path = $row['real_url'];
            if(substr_count($row['real_url'], '?'))
            {
                $prepare_path = substr($prepare_path, 0, strpos($prepare_path, '?'));
            }
            
            if(substr_count($row['real_url'], $config['index_page']))
            {
                $prepare_path = substr($prepare_path, strpos($prepare_path, $config['index_page'])+strlen($config['index_page']));
            }
            else if(substr_count($row['real_url'], 'http'))
            {
                $prepare_path = substr($prepare_path, strpos($prepare_path, $_SERVER['SERVER_NAME'].$script_folder)+strlen($_SERVER['SERVER_NAME'].$script_folder)+1);
            }

            $_SERVER['PATH_INFO']   = $prepare_path;
            $_SERVER['REQUEST_URI'] = $script_folder.$index_file.$_SERVER['PATH_INFO'].'?'.$q_s;
            $_SERVER["QUERY_STRING"] = $q_s;
            
        }
    }
    else
    {
        generate_server_vars($row);
    }

} else {
    echo "0 results";
}
$conn->close();

function generate_server_vars($row)
{
    global $index_file, $script_folder, $q_s;

    if($row['model_name'] == 'page_m')
    {
        $_SERVER['PATH_INFO']   = '/'.$row['model_lang_code'].'/'.$row['model_id'].'/'.$row['slug'];
        $_SERVER['REQUEST_URI'] = $script_folder.$index_file.'/'.$row['model_lang_code'].'/'.$row['model_id'].'/'.$row['slug'].'?'.$q_s;;
    }
    else if($row['model_name'] == 'treefield_m')
    {
        $_SERVER['PATH_INFO']   = '/treefield/'.$row['model_lang_code'].'/'.$row['model_id'].'/'.$row['slug'];
        $_SERVER['REQUEST_URI'] = $script_folder.$index_file.'/treefield/'.$row['model_lang_code'].'/'.$row['model_id'].'/'.$row['slug'].'?'.$q_s;
    }
    else if($row['model_name'] == 'estate_m')
    {
        $_SERVER['PATH_INFO']   = '/property/'.$row['model_id'].'/'.$row['model_lang_code'].'/'.$row['slug'];
        $_SERVER['REQUEST_URI'] = $script_folder.$index_file.'/property/'.$row['model_id'].'/'.$row['model_lang_code'].'/'.$row['slug'];
    }
    
    $_SERVER["QUERY_STRING"] = $q_s;
}


function get_qs_from_uri($uri)
{
    $q_s = '';
    if(substr_count($uri, '?'))
    {
        $q_pos = strrpos($uri, '?')+1;
        $q_s = substr($uri, $q_pos);
    }
    
    return $q_s;
}
 
/*
|---------------------------------------------------------------
| PHP SCRIPT EXECUTION TIME ('0' means Unlimited)
|---------------------------------------------------------------
|
*/
set_time_limit(0);

require_once('index.php');
 
/* End of file test.php */

?>