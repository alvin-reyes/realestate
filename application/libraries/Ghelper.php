<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ghelper {

    public function __construct($params = array())
    {
        $this->CI = &get_instance();
    }
    
    /**
    * Reads an URL to a string
    * @param string $url The URL to read from
    * @return string The URL content
    */
    private function getURL($url){
     	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_URL, $url);
    	$tmp = curl_exec($ch);
    	curl_close($ch);
    	if ($tmp != false){
    	 	return $tmp;
    	}
    }
    
	/**
	* Get Latitude/Longitude/Altitude based on an address
	* @param string $address The address for converting into coordinates
	* @return array An array containing Latitude/Longitude/Altitude data
	*/
	public function getCoordinates($address){
		$address = str_replace(' ','+',$address);
        $address = strtolower($address);
        
        // http://maps.google.com/maps/api/geocode/json?sensor=false&address=Zagreb
	 	$url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&address=' . $address;
        
        $this->CI->load->model('cacher_m');
        $loaded_value = $this->CI->cacher_m->load($address);
        
        if($loaded_value === FALSE)
        {
            $data = $this->getURL($url);
        }
        else
        {
            $data = $loaded_value;
        }

		if ($data){
			$resp = json_decode($data, true);
            
            if(isset($resp['status']))
			if($resp['status'] == 'OK'){
                if($loaded_value === FALSE)
                {
                    $this->CI->cacher_m->cache($address, $data);
                }
             
			 	//all is ok
			 	$lat = $resp['results'][0]['geometry']['location']['lat'];
                $lng = $resp['results'][0]['geometry']['location']['lng'];
			 	if (!empty($lat) && !empty($lng)){
			 	   return array('lat' => $lat, 'lng' => $lng, 'alt' => 0);
				}
			}
		}
		//return default data
		return array('lat' => 0, 'lng' => 0, 'alt' => 0);
	}
    
    // Modified from:
    // http://www.sitepoint.com/forums/showthread.php?656315-adding-distance-gps-coordinates-get-bounding-box
    /**
    * bearing is 0 = north, 180 = south, 90 = east, 270 = west
    *
    */
    function getDueCoords($latitude, $longitude, $bearing, $distance, $distance_unit = "km", $return_as_array = FALSE) {
    
        if ($distance_unit == "m") {
          // Distance is in miles.
        	  $radius = 3963.1676;
        }
        else {
          // distance is in km.
          $radius = 6378.1;
        }
        
        //	New latitude in degrees.
        $new_latitude = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / $radius) + cos(deg2rad($latitude)) * sin($distance / $radius) * cos(deg2rad($bearing))));
        		
        //	New longitude in degrees.
        $new_longitude = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad($bearing)) * sin($distance / $radius) * cos(deg2rad($latitude)), cos($distance / $radius) - sin(deg2rad($latitude)) * sin(deg2rad($new_latitude))));
        
        if ($return_as_array) {
          //  Assign new latitude and longitude to an array to be returned to the caller.
          $coord = array();
          $coord['lat'] = $new_latitude;
          $coord['lng'] = $new_longitude;
        }
        else {
          $coord = $new_latitude . ", " . $new_longitude;
        }
        
        return $coord;
    
    }	
    
}

?>