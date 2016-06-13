<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    
    
    <?php if(file_exists(FCPATH.'templates/'.$settings_template.'/assets/js/places.js')): ?>
    <script src="assets/js/places.js"></script>
    <?php endif; ?>
    
    <script language="javascript">
    
    var IMG_FOLDER = "assets/js/dpejes";
    
    $(document).ready(function(){
        
        <?php if(config_db_item('appId') != '' && file_exists(FCPATH.'templates/'.$settings_template.'/assets/js/like2unlock/js/jquery.op.like2unlock.min.js')): ?>
        $(".agent").toLike({
            text: "<?php echo lang_check('Like to view hidden content.');?>",
            facebook: { appId: "<?php echo config_db_item('appId'); ?>" } 
            });
        <?php endif; ?>
    
<?php if(file_exists(APPPATH.'controllers/admin/favorites.php')):?>
    // [START] Add to favorites //  
    
    $("#add_to_favorites").click(function(){
        
        var data = { property_id: {estate_data_id} };
        
        var load_indicator = $(this).find('.load-indicator');
        load_indicator.css('display', 'inline-block');
        $.post("{api_private_url}/add_to_favorites/{lang_code}", data, 
               function(data){
            
            ShowStatus.show(data.message);
                            
            load_indicator.css('display', 'none');
            
            if(data.success)
            {
                $("#add_to_favorites").css('display', 'none');
                $("#remove_from_favorites").css('display', 'inline-block');
            }
        });

        return false;
    });
    
    $("#remove_from_favorites").click(function(){
        
        var data = { property_id: {estate_data_id} };
        
        var load_indicator = $(this).find('.load-indicator');
        load_indicator.css('display', 'inline-block');
        $.post("{api_private_url}/remove_from_favorites/{lang_code}", data, 
               function(data){
            
            ShowStatus.show(data.message);
                            
            load_indicator.css('display', 'none');
            
            if(data.success)
            {
                $("#remove_from_favorites").css('display', 'none');
                $("#add_to_favorites").css('display', 'inline-block');
            }
        });

        return false;
    });
    
    // [END] Add to favorites //  
<?php endif; ?>


<?php if(file_exists(FCPATH.'templates/'.$settings_template.'/assets/js/dpejes/dpe.js')): ?>
/* start implément dpe-->   */
	
    dpe.show_numbers = true;
<?php
if(!empty($estate_data_option_59) && !is_numeric($estate_data_option_59))
{
    echo 'dpe.show_numbers = false;';
}
?>
    dpe.energy_title1 = "{options_name_59}";
	dpe.energy_title2 = "{options_suffix_59}";
	dpe.energy_title3 = "{lang_Logement}";
    dpe.gg_title2 = "{options_suffix_60}";
	dpe.gg_title1 = "{options_name_60}";
	
    if(!dpe.show_numbers)
    {
    	dpe.energy_title2 = "";
        dpe.gg_title2 = "";
    }
    
	/* adjusts the height of the large thumbnails (the width is automatically adjusted proportionally)
	 * possible values: de 180 a 312 
	 */
	dpe.canvas_height = 250;
	/*not to display the unit gas emissions greenhouse in the right column: */
	dpe.gg_unit = '';
	/*  adjusts the height of the small thumbnails
	 * possible values: 35
	 */
	dpe.sticker_height = 35;
	/* can change the attribute of div tags that indicates it is a tag */
	dpe.tag_attribute = 'attributdpe';
	dpe.tag_attribute = 'title';
	/* Launches replacing the contents of the div right by good vignettes */
	dpe.all();
/* end implement dpe-->   */
<?php endif; ?>


        /* [Carousel fix center image START] */
//        var carousel_height = $(".propertyCarousel").height();
//
//        $(".propertyCarousel .carousel-inner div.item img").one("load", function() {
//            var image_height = $(this)[0].height;
//            
//            if(image_height > carousel_height)
//            {
//                $(this).css('top', '-'+ parseInt((image_height-carousel_height)/2) +'px');
//                $(this).css('position', 'relative');
//            }
//        });
        /* [Carousel fix center image END] */

       $("#route_from_button").click(function () { 
            window.open("https://maps.google.hr/maps?saddr="+$("#route_from").val()+"&daddr={estate_data_address}@{estate_data_gps}&hl={lang_code}",'_blank');
            return false;
        });

        $('#propertyLocation').gmap3({
         map:{
            options:{
             center: [{estate_data_gps}],
             zoom: {settings_zoom}+6,
             scrollwheel: scrollWheelEnabled
            }
         },
         marker:{
            values:[
                {latLng:[{estate_data_gps}], options:{icon: "{estate_data_icon}"}, data:"{estate_data_address}<br />{lang_GPS}: {estate_data_gps}"},
            ],
         events:{
          mouseover: function(marker, event, context){
            var map = $(this).gmap3("get"),
              infowindow = $(this).gmap3({get:{name:"infowindow"}});
            if (infowindow){
              infowindow.open(map, marker);
              infowindow.setContent('<div style="width:400px;display:inline;">'+context.data+'</div>');
            } else {
              $(this).gmap3({
                infowindow:{
                  anchor:marker,
                  options:{disableAutoPan: mapDisableAutoPan, content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
                }
              });
            }
          }
        }
         }});
        
        $("#wrap-map").gmap3({
         map:{
            options:{
             center: [{estate_data_gps}],
             zoom: {settings_zoom},
             scrollwheel: scrollWheelEnabled,
             mapTypeId: c_mapTypeId,
             mapTypeControlOptions: {
               mapTypeIds: c_mapTypeIds
             }
            }
         },
        styledmaptype:{
          id: "style1",
          options:{
            name: "<?php echo lang_check('CustomMap'); ?>"
          },
          styles: mapStyle
        },
         marker:{
            values:[
            {all_estates}
                {latLng:[{gps}], adr:"{address}", options:{icon: "{icon}"}, data:"<img style=\"width: 150px; height: 100px;\" src=\"{thumbnail_url}\" /><br />{address}<br />{option_2}<br /><span class=\"label label-info\">&nbsp;&nbsp;{option_4}&nbsp;&nbsp;</span><br /><a href=\"{url}\">{lang_Details}</a>"},
            {/all_estates}
            ],
            cluster: clusterConfig,
            options: markerOptions,
        events:{
          <?php echo map_event(); ?>: function(marker, event, context){
            var map = $(this).gmap3("get"),
              infowindow = $(this).gmap3({get:{name:"infowindow"}});
            if (infowindow){
              infowindow.open(map, marker);
              infowindow.setContent('<div style="width:400px;display:inline;">'+context.data+'</div>');
            } else {
              $(this).gmap3({
                infowindow:{
                  anchor:marker,
                  options:{disableAutoPan: mapDisableAutoPan, content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
                }
              });
            }
          },
          mouseout: function(){
            //var infowindow = $(this).gmap3({get:{name:"infowindow"}});
            //if (infowindow){
            //  infowindow.close();
            //}
          }
        }}});
        init_gmap_searchbox();
        
        if (typeof init_directions == 'function')
        {
            $(".places_select a").click(function(){
                init_places($(this).attr('rel'), $(this).find('img').attr('src'));
            });
            
            var selected_place_type = 4;
            
            init_directions();
            init_places($(".places_select a:eq("+selected_place_type+")").attr('rel'), $(".places_select a:eq("+selected_place_type+") img").attr('src'));
        
        }
    }); 
    
    var map_propertyLoc;
    var markers = [];
    var generic_icon;
    
    var directionsDisplay;
    var directionsService = new google.maps.DirectionsService();
    var placesService;

    function init_places(places_types, icon) {
        var pyrmont = new google.maps.LatLng({estate_data_gps});

        setAllMap(null);
        
        generic_icon = icon;

        map_propertyLoc = $("#propertyLocation").gmap3({
            get: { name:"map" }
        });    
        
        var places_type_array = places_types.split(','); 
        
        var request = {
            location: pyrmont,
            radius: 2000,
            types: places_type_array
        };
        
        infowindow = new google.maps.InfoWindow();
        placesService = new google.maps.places.PlacesService(map_propertyLoc);
        placesService.nearbySearch(request, callback);

    }

    function callback(results, status) {
      if (status == google.maps.places.PlacesServiceStatus.OK) {
        for (var i = 0; i < results.length; i++) {
          createMarker(results[i]);
        }
      }
    }
    
    function setAllMap(map) {
      for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(map);
      }
    }

    function calcRoute(source_place, dest_place) {
      var selectedMode = 'WALKING';
      var request = {
          origin: source_place,
          destination: dest_place,
          // Note that Javascript allows us to access the constant
          // using square brackets and a string value as its
          // "property."
          travelMode: google.maps.TravelMode[selectedMode]
      };
      
      directionsService.route(request, function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
          directionsDisplay.setDirections(response);
          //console.log(response.routes[0].legs[0].distance.value);
        }
      });
    }
    
    function createMarker(place) {
      var placeLoc = place.geometry.location;
      var propertyLocation = new google.maps.LatLng({estate_data_gps});
      
        if(place.icon.indexOf("generic") > -1)
        {
            place.icon = generic_icon;
        }
      
        var image = {
          url: place.icon,
          size: new google.maps.Size(71, 71),
          origin: new google.maps.Point(0, 0),
          anchor: new google.maps.Point(17, 34),
          scaledSize: new google.maps.Size(25, 25)
        };

      var marker = new google.maps.Marker({
        map: map_propertyLoc,
        icon: image,
        position: place.geometry.location
      });
      
      markers.push(marker);
      
      var distanceKm = (calcDistance(propertyLocation, placeLoc)*1.2).toFixed(2);
      var walkingTime = parseInt((distanceKm/5)*60+0.5);

      google.maps.event.addListener(marker, 'click', function() {
        
            //drawing route
            calcRoute(propertyLocation, placeLoc);
        
        // Fetch place details
        placesService.getDetails({ placeId: place.place_id }, function(placeDetails, statusDetails){
            

            
            //open popup infowindow
            infowindow.setContent(place.name+'<br />{lang_Distance}: '+distanceKm+'{lang_Km}'+
                                  '<br />{lang_WalkingTime}: '+walkingTime+'{lang_Min}'+
                                  '<br /><a target="_blank" href="'+placeDetails.url+'">{lang_Details}</a>');
            infowindow.open(map_propertyLoc, marker);
        });

      });
    }
    
    //calculates distance between two points
    function calcDistance(p1, p2){
      return (google.maps.geometry.spherical.computeDistanceBetween(p1, p2) / 1000).toFixed(2);
    }

       
    </script>
    

  </head>

  <body>
<?php if(!empty($settings_facebook_jsdk) && (config_db_item('appId') == '' || !file_exists(FCPATH.'templates/'.$settings_template.'/assets/js/like2unlock/js/jquery.op.like2unlock.min.js'))): ?>
<?php echo $settings_facebook_jsdk; ?>
<?php endif;?>
  
{template_header}

<input id="pac-input" class="controls" type="text" placeholder="{lang_Search}" />
<div class="wrap-map" id="wrap-map">
</div>

{template_search}

<?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
{has_ads_728x90px}
<div class="wrap-content2">
    <div class="container ads">
        <a href="{random_ads_728x90px_link}" target="_blank"><img src="{random_ads_728x90px_image}" /></a>
    </div>
</div>
{/has_ads_728x90px}
<?php elseif(!empty($settings_adsense728_90)): ?>
<div class="wrap-content2">
    <div class="container ads">
        <?php echo $settings_adsense728_90; ?>
    </div>
</div>
<?php endif;?>
<a name="content" id="content"></a>
<div class="wrap-content">
    <div class="container container-property">
        <div class="row-fluid">
            <div class="span9">
            <h2>{page_title}, #{estate_data_id}</h2>
            {has_page_images}
            <div class="propertyCarousel">
                <div id="myCarousel" class="carousel slide">
                <ol class="carousel-indicators">
                {slideshow_property_images}
                <li data-target="#myCarousel" data-slide-to="{num}" class="{first_active}"></li>
                {/slideshow_property_images}
                </ol>
                <!-- Carousel items -->
                <div class="carousel-inner">
                {slideshow_property_images}
                    <div class="item {first_active}">

                    <div style="background:url('{url}') center center; background-size:cover;" class="slider-size">
                    </div>

                    </div>
                {/slideshow_property_images}
                </div>
                <!-- Carousel nav -->
                <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
                <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
                </div>
            </div>
            {/has_page_images}
              <div class="property_content">
              
            <?php if(file_exists(APPPATH.'controllers/admin/favorites.php')):?>
            <?php
                $favorite_added = false;
                if(count($not_logged) == 0)
                {
                    $CI =& get_instance();
                    $CI->load->model('favorites_m');
                    $favorite_added = $CI->favorites_m->check_if_exists($this->session->userdata('id'), 
                                                                        $estate_data_id);
                    if($favorite_added>0)$favorite_added = true;
                }
            ?>
            
            <a class="btn btn-warning" id="add_to_favorites" href="#" style="<?php echo ($favorite_added)?'display:none;':''; ?>"><i class="icon-star icon-white"></i> <?php echo lang_check('Add to favorites'); ?> <i class="load-indicator"></i></a>
            <a class="btn btn-success" id="remove_from_favorites" href="#" style="<?php echo (!$favorite_added)?'display:none;':''; ?>"><i class="icon-star icon-white"></i> <?php echo lang_check('Remove from favorites'); ?> <i class="load-indicator"></i></a>

            <?php endif; ?>
              
                <h2>{lang_Description}</h2>
                {page_body}
                <?php if(isset($category_options_21) && $category_options_count_21>0): ?>
                <h2>{options_name_21}</h2>
                <ul class="amenities">
                {category_options_21}
                {is_checkbox}
                <li>
                <img src="assets/img/checkbox_{option_value}.png" alt="{option_value}" class="check" />&nbsp;&nbsp;{option_name}&nbsp;&nbsp;{icon}
                </li>
                {/is_checkbox}
                {/category_options_21}
                </ul>
                <br style="clear: both;" />
                <?php endif; ?>
                
                <?php if(isset($category_options_52) && $category_options_count_52>0): ?>
                <h2>{options_name_52}</h2>
                <ul class="amenities">
                {category_options_52}
                {is_checkbox}
                <li>
                <img src="assets/img/checkbox_{option_value}.png" alt="{option_value}" class="check" />&nbsp;&nbsp;{option_name}&nbsp;&nbsp;{icon}
                </li>
                {/is_checkbox}
                {/category_options_52}
                </ul>
                <br style="clear: both;" />
                <?php endif; ?>
                
                <?php if(isset($category_options_43) && $category_options_count_43>0): ?>
                <h2>{options_name_43}</h2>
                <ul class="amenities">
                {category_options_43}
                {is_text}
                <li>
                {icon} {option_name}:&nbsp;&nbsp;{option_prefix}{option_value}{option_suffix}
                </li>
                {/is_text}
                {/category_options_43}
                </ul>
                <br style="clear: both;" />
                <?php endif; ?>
                
                <?php if(file_exists(FCPATH.'templates/'.$settings_template.'/assets/js/dpejes/dpe.js')): ?>
                <?php if(!empty($options_name_59) || !empty($options_name_60)): ?>
                  <!--energy version full -->
                  <h2>{options_name_59}</h2>
                  <div class="divider"></div>
                  <ul class="amenities">
                  <?php
                  
                    $values_chars = array(  'A'=>'40',
                                            'B'=>'80',
                                            'C'=>'140',
                                            'D'=>'220',
                                            'E'=>'320',
                                            'F'=>'440',
                                            'G'=>'460');
                    
                    if(!is_numeric($estate_data_option_59))
                    {
                        if(isset($values_chars[$estate_data_option_59]))
                            $estate_data_option_59 = $values_chars[$estate_data_option_59];
                    }
                    
                    $values_chars = array(  'A'=>'4',
                                            'B'=>'9',
                                            'C'=>'19',
                                            'D'=>'34',
                                            'E'=>'54',
                                            'F'=>'79',
                                            'G'=>'81');
                    
                    if(!is_numeric($estate_data_option_60))
                    {
                        if(isset($values_chars[$estate_data_option_60]))
                            $estate_data_option_60 = $values_chars[$estate_data_option_60];
                    }
                  ?>
                    <div title="energie:<?php echo $estate_data_option_59; ?>" style="float:left;width=280;margin-right:50px;">
                      <div class="alert alert-warning">{lang_No Efficiency} </div>
                      <br/>
                      <br/>
                      </a>
                    </div>
                    
                    <div title="ges:<?php echo $estate_data_option_60; ?>" style="float:left;">
                      <div class="alert alert-warning"> {lang_No Gas} </div>
                    </div>
                    
                  </ul>
                  <br style="clear: both;" />
                  
                  <!--energy --> 
                <?php endif; ?>
                <?php endif; ?>

                <?php if(file_exists(APPPATH.'controllers/admin/booking.php') && count($property_rates)>0):?>
                <h2>{lang_Rates}</h2>
                <table class="table table-striped">
                    <thead>
                    <tr>
                    <th>{lang_From}</th>
                    <th>{lang_To}</th>
                    <th>{lang_Nightly}</th>
                    <th>{lang_Weekly}</th>
                    <th>{lang_Monthly}</th>
                    <th>{lang_MinStay}</th>
                    <th>{lang_ChangeoverDay}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($property_rates as $key=>$rate): ?>
                    <tr>
                    <td><?php echo date('Y-m-d', strtotime($rate->date_from)); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($rate->date_to)); ?></td>
                    <td><?php echo $rate->rate_nightly.' '.$rate->currency_code; ?></td>
                    <td><?php echo $rate->rate_weekly.' '.$rate->currency_code; ?></td>
                    <td><?php echo $rate->rate_monthly.' '.$rate->currency_code; ?></td>
                    <td><?php echo $rate->min_stay; ?></td>
                    <td><?php echo $changeover_days[$rate->changeover_day]; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <h2>{lang_AvailabilityCalender}</h2>
                <div class="av_calender">
                <?php
                    $row_break=0;
                    
                    foreach($months_availability as $v_month)
                    {
                        echo $v_month;
                        
                        $row_break++;
                        if($row_break%3 == 0)
                        echo '<div style="clear: both;height:10px;"></div>';
                    }
                ?>
                <br style="clear: both;" />
                </div>
                <?php endif;?>

                <h2 id="hNearProperties">{lang_Propertylocation}</h2>
                <div class="places_select" style="display: none;">
                    <a class="btn btn-large" type="button" rel="hospital,health"><img src="assets/img/places_icons/hospital.png" /> {lang_Health}</a>
                    <a class="btn btn-large" type="button" rel="park"><img src="assets/img/places_icons/park.png" /> {lang_Park}</a>
                    <a class="btn btn-large" type="button" rel="atm,bank"><img src="assets/img/places_icons/atm.png" /> {lang_ATMBank}</a>
                    <a class="btn btn-large" type="button" rel="gas_station"><img src="assets/img/places_icons/petrol.png" /> {lang_PetrolPump}</a>
                    <a class="btn btn-large" type="button" rel="food,bar,cafe,restourant"><img src="assets/img/places_icons/restourant.png" /> {lang_Restourant}</a>
                    <a class="btn btn-large" type="button" rel="store"><img src="assets/img/places_icons/store.png" /> {lang_Store}</a>
                </div>
                <div id="propertyLocation">
                </div>
                <div class="route_suggestion">
                <input id="route_from" class="inputtext w360" type="text" value="" placeholder="{lang_Typeaddress}" name="route_from" />
                <a id="route_from_button" href="#" class="btn">{lang_Suggestroutes}</a>
                </div>
                
                <?php if(!empty($estate_data_option_12)): ?>
                <h2>{options_name_9}</h2>
                {estate_data_option_12}
                <br style="clear:both;" />
                <?php endif; ?>
                
                <?php if(config_item('walkscore_enabled') == TRUE && !empty($estate_data_address)): ?>
                <br />
                <script type='text/javascript'>
                var ws_wsid = '';
                <?php
                echo "var ws_address = '$estate_data_address';";
                ?>
                var ws_width = '500';
                var ws_height = '336';
                var ws_layout = 'horizontal';
                var ws_commute = 'true';
                var ws_transit_score = 'true';
                var ws_map_modules = 'all';
                </script><style type='text/css'>#ws-walkscore-tile{position:relative;text-align:left}#ws-walkscore-tile *{float:none;}#ws-footer a,#ws-footer a:link{font:11px/14px Verdana,Arial,Helvetica,sans-serif;margin-right:6px;white-space:nowrap;padding:0;color:#000;font-weight:bold;text-decoration:none}#ws-footer a:hover{color:#777;text-decoration:none}#ws-footer a:active{color:#b14900}</style><div id='ws-walkscore-tile'><div id='ws-footer' style='position:absolute;top:318px;left:8px;width:488px'><form id='ws-form'><a id='ws-a' href='http://www.walkscore.com/' target='_blank'>What's Your Walk Score?</a><input type='text' id='ws-street' style='position:absolute;top:0px;left:170px;width:286px' /><input type='image' id='ws-go' src='http://cdn2.walk.sc/2/images/tile/go-button.gif' height='15' width='22' border='0' alt='get my Walk Score' style='position:absolute;top:0px;right:0px' /></form></div></div><script type='text/javascript' src='http://www.walkscore.com/tile/show-walkscore-tile.php'></script>
                <?php endif; ?>

                <?php if(config_item('ad_gallery_enabled') == TRUE): ?>
                {has_page_images}
                <div class="ad-gallery" id="gallery">
                      <div class="ad-image-wrapper"><div class="ad-image" style="width: 600px; height: 400px; top: 0px; left: 0px;"><img width="600" height="400" src="images/5.jpg"><p class="ad-image-description" style="width: 586px; bottom: 0px;"><strong class="ad-description-title">A title for 5.jpg</strong><span>This is a nice, and incredibly descriptive, description of the image 5.jpg</span></p></div><img src="loader.gif" class="ad-loader" style="display: none;"><div class="ad-next" style="height: 400px;"><div class="ad-next-image" style="opacity: 0.7; display: none;"></div></div><div class="ad-prev" style="height: 400px;"><div class="ad-prev-image" style="opacity: 0.7; display: none;"></div></div></div>
                      <div class="ad-controls">
                      <p class="ad-info"></p><div class="ad-slideshow-controls"><span class="ad-slideshow-countdown" style="display: none;"></span></div></div>
                      <div class="ad-nav"><div class="ad-back" style="opacity: 0.6;"></div>
                        <div class="ad-thumbs">
                          <ul class="ad-thumb-list" style="width: 1353px;">
                            {page_images}
                            <li>
                              <a href="{url}" class="">
                                <img class="image0" src="{thumbnail_url}" style="opacity: 0.7;">
                              </a>
                            </li>
                            <li>
                            {/page_images}
                          </ul>
                        </div>
                      <div class="ad-forward" style="opacity: 0.6;"></div></div>
                    </div>
                {/has_page_images}
                <?php else: ?>
                {has_page_images}
                <h2>{lang_Imagegallery}</h2>
                <ul data-target="#modal-gallery" data-toggle="modal-gallery" class="files files-list ui-sortable">  
                    {page_images}
                    <li class="template-download fade in">
                        <a data-gallery="gallery" href="{url}" title="{filename}" download="{url}" class="preview show-icon">
                            <img src="assets/img/preview-icon.png" class="" />
                        </a>
                        <div class="preview-img"><img src="{thumbnail_url}" data-src="{url}" alt="{filename}" class="" /></div>
                    </li>
                    {/page_images}
                </ul>
                {/has_page_images}
                
                <?php endif; ?>
                
                <br style="clear:both;" />
                
                {has_page_documents}
                <h2>{lang_Filerepository}</h2>
                <ul class="file-repository">
                <?php if(is_array($this->session->userdata('contacted_agents'))&&in_array($agent_id, $this->session->userdata('contacted_agents'))): ?>
                <?php else: ?>
                <a class="popup-with-form hidden-file-details" href="#test-form"><?php echo lang_check('Show file details'); ?></a>
                <?php endif; ?>
                {page_documents}
                <li>
                    <a href="{url}">{filename}</a>
                </li>
                {/page_documents}
                </ul>
                <br style="clear:both;" />
                {/has_page_documents}
                
                <?php
                //Fetch repository
                $file_rep = array();
                
                if(!empty($estate_data_option_65) && is_numeric($estate_data_option_65)){
                    $rep_id = $estate_data_option_65;
                    $file_rep = $this->file_m->get_by(array('repository_id'=>$rep_id));
                }
                
                // If not defined in this language
                if(count($file_rep) == 0)
                {
                    //Fetch option for default language
                    $def_lang_id = $this->language_m->get_default_id();
                    if(!empty($def_lang_id))
                    {
                        $def_lang_rep_id = $this->option_m->get_property_value($def_lang_id, $estate_data_id, 65);
                        
                        if(!empty($def_lang_rep_id))
                        $file_rep = $this->file_m->get_by(array('repository_id'=>$def_lang_rep_id));
                    }
                }
                
                $rep_value = '';
                if(count($file_rep))
                {
                    echo '<h2>'.$options_name_65.'</h2>';
                    $rep_value.= '<ul data-target="#modal-gallery" data-toggle="modal-gallery" class="files files-list ui-sortable">';
                    foreach($file_rep as $file_r)
                    {
                    
                        $rep_value.=
                        '<li class="template-download fade in">'.
                        '    <a data-gallery="gallery" href="'.base_url('files/'.$file_r->filename).'" title="'.$file_r->filename.'" download="'.base_url('files/'.$file_r->filename).'" class="preview show-icon">'.
                        '        <img src="assets/img/preview-icon.png" class="" />'.
                        '    </a>'.
                        '    <div class="preview-img"><img src="'.base_url('files/thumbnail/'.$file_r->filename).'" data-src="'.base_url('files/'.$file_r->filename).'" alt="'.$file_r->filename.'" class="" /></div>'.
                        '</li>';
                    }
                    $rep_value.= '</ul>';

                    echo $rep_value;
                    echo '<br style="clear:both;" />';
                }
                ?>
                
                <?php if(file_exists(APPPATH.'controllers/admin/reviews.php') && $settings_reviews_enabled): ?>
                <h2 id="form_review"><?php echo lang_check('YourReview'); ?></h2>
                <?php if(count($not_logged)): ?>
                <p class="alert alert-success">
                    <?php echo lang_check('LoginToReview'); ?>
                </p>
                <?php else: ?>
                
                <?php if($reviews_submitted == 0): ?>
                <form class="form-horizontal" method="post" action="{page_current_url}#form_review">
                <div class="control-group">
                <label class="control-label" for="inputRating"><?php echo lang_check('Rating'); ?></label>
                <div class="controls">
                    <input type="number" data-max="5" data-min="1" name="stars" id="stars" class="rating" data-empty-value="5" value="5" data-active-icon="icon-star" data-inactive-icon="icon-star-empty" />
                </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="inputMessageR"><?php echo lang_check('Message'); ?></label>
                    <div class="controls">
                        <textarea id="inputMessageR" rows="3" name="message" rows="3" placeholder="{lang_Message}"></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn"><?php echo lang_check('Send'); ?></button>
                    </div>
                </div>
                </form>
                <?php else: ?>
                <p class="alert alert-info">
                    <?php echo lang_check('ThanksOnReview'); ?>
                </p>
                <?php endif; ?>
                
                <?php endif; ?>
                
                
                <?php if($settings_reviews_public_visible_enabled): ?>
                <h2><?php echo lang_check('Reviews'); ?></h2>
                <?php if(count($not_logged) && !$settings_reviews_public_visible_enabled): ?>
                <p class="alert alert-success">
                    <?php echo lang_check('LoginToReadReviews'); ?>
                </p>
                <?php else: ?>
                <?php if(count($reviews_all) > 0): ?>
                <ul class="media-list">
                <?php foreach($reviews_all as $review_data): ?>
                <?php //print_r($review_data); ?>
                <li class="media">
                <div class="pull-left">
                <?php if(isset($review_data['image_user_filename'])): ?>
                <img class="media-object" data-src="holder.js/64x64" style="width: 64px; height: 64px;" src="<?php echo base_url('files/thumbnail/'.$review_data['image_user_filename']); ?>" />
                <?php else: ?>
                <img class="media-object" data-src="holder.js/64x64" style="width: 64px; height: 64px;" src="assets/img/user-agent.png" />
                <?php endif; ?>
                </div>
                <div class="media-body">
                <h4 class="media-heading"><div class="review_stars_<?php echo $review_data['stars']; ?>"> </div></h4>
                <?php if($review_data['is_visible']): ?>
                <?php echo $review_data['message']; ?>
                <?php else: ?>
                <?php echo lang_check('HiddenByAdmin'); ?>
                <?php endif; ?>
                </div>
                </li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="alert alert-success">
                    <?php echo lang_check('SubmitFirstReview'); ?>
                </p>
                <?php endif; ?>
                <?php endif; ?>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php if(!empty($settings_facebook_comments)): ?>
                <h2><?php echo lang_check('Facebook comments'); ?></h2>
                <?php echo str_replace('http://example.com/comments', $page_current_url, $settings_facebook_comments); ?>
                <?php endif;?>
                
                <?php if(count($agent_estates) > 0): ?>
                <h2>{lang_Agentestates}</h2>
                <ul class="thumbnails agent-property">
                {agent_estates}
                      <li class="span4">
                        <div class="thumbnail f_{is_featured}">
                          <h3>{option_10}&nbsp;</h3>
                          <img alt="300x200" data-src="holder.js/300x200" style="width: 300px; height: 200px;" src="{thumbnail_url}" />
                          {has_option_38}
                          <div class="badget"><img src="assets/img/badgets/{option_38}.png" alt="{option_38}"/></div>
                          {/has_option_38}
                          {has_option_4}
                          <div class="purpose-badget fea_{is_featured}">{option_4}</div>
                          {/has_option_4}
                          {has_option_54}
                          <div class="ownership-badget fea_{is_featured}">{option_54}</div>
                          {/has_option_54}
                          <img class="featured-icon" alt="Featured" src="assets/img/featured-icon.png" />
                          <a href="{url}" class="over-image"> </a>
                          <div class="caption">
                            <p class="bottom-border"><strong class="f_{is_featured}">{address}</strong></p>
                            <p class="bottom-border">{options_name_2} <span>{option_2}</span></p>
                            <p class="bottom-border">{options_name_3} <span>{option_3}</span></p>
                            <p class="bottom-border">{options_name_19} <span>{option_19}</span></p>
                            <p class="prop-icons">
                            {icons}
                            {icon}
                            {/icons}
                            </p>
                            <p class="prop-description"><i>{option_chlimit_8}</i></p>
                            <p>
                            <a class="btn btn-info" href="{url}">
                            {lang_Details}
                            </a>
                            {has_option_36}
                            <span class="price">{options_prefix_36} {option_36} {options_suffix_36}</span>
                            {/has_option_36}
                            </p>
                          </div>
                        </div>
                      </li>
                {/agent_estates}
                </ul>
                <?php endif;?>
                <br style="clear:both;" />

              </div>
            </div>
            <div class="span3">
                  <h2>{lang_Overview}</h2>
                  <div class="property_options">
                    <p class="bottom-border"><strong>
                    {lang_Address}
                    </strong> <span>{estate_data_address}</span>
                    <br style="clear: both;" />
                    </p>
                    {category_options_1}
                    {is_text}
                    <p class="bottom-border"><strong>{option_name}:</strong> <span>{option_prefix} {option_value} {option_suffix}</span><br style="clear: both;" /></p>
                    {/is_text}
                    {is_dropdown}
                    <p class="bottom-border"><strong>{option_name}:</strong> <span class="label label-success">&nbsp;&nbsp;{option_value}&nbsp;&nbsp;</span></p>
                    {/is_dropdown}
                    {is_checkbox}
                    <img src="assets/img/checkbox_{option_value}.png" alt="{option_value}" />&nbsp;&nbsp;{option_name}
                    {/is_checkbox}
                    {/category_options_1}
                    <?php if(!empty($estate_data_option_64)): ?>
                    <p class="bottom-border">
                        <strong><?php echo $options_name_64; ?>:</strong>
                        <span>
                        <?php
                            $nice_path = $estate_data_option_64;
                            $link_defined = false;
                            // Get treefield with language data
                            $treefield_id = $this->treefield_m->id_by_path(64, $lang_id, $nice_path);
                            if(is_numeric($treefield_id))
                            {
                                $treefield_data = $this->treefield_m->get_lang($treefield_id, TRUE, $lang_id);
                                
                                // If no content defined then no link, just span
                                if(!empty($treefield_data->{"body_$lang_id"}))
                                {
                                    // If slug then define slug link
                                    $href = slug_url('treefield/'.$lang_code.'/'.$treefield_id.'/'.url_title_cro($treefield_data->{"value_$lang_id"}), 'treefield_m');
                                    echo '<a href="'.$href.'">'.$nice_path.'</a>';
                                    $link_defined=true;
                                }
                            }
                            if(!$link_defined) echo $nice_path;
                        ?>
                        </span>
                    </p>
                    <?php endif;?>
                    <?php
                        foreach($category_options_1 as $key=>$row)
                        {
                            if($row['option_type'] == 'UPLOAD')
                            {
                                if(!empty($row['option_value']) && is_numeric($row['option_value']))
                                {
                                    //Fetch repository
                                    $rep_id = $row['option_value'];
                                    $file_rep = $this->file_m->get_by(array('repository_id'=>$rep_id));
                                    $rep_value = '';
                                    if(count($file_rep))
                                    {
                                        $rep_value.= '<ul>';
                                        foreach($file_rep as $file_r)
                                        {
                                            $rep_value.= "<li><a target=\"_blank\" href=\"".base_url('files/'.$file_r->filename)."\">$file_r->filename</a></li>";
                                        }
                                        $rep_value.= '</ul>';
                                        
                                        echo '<p class="bottom-border"><strong>'.$row['option_name'].':</strong></p>';
                                        echo $rep_value;
                                    }
                                }
                            }
                            
                        }
                    ?>
                    
                    <?php if(!empty($estate_data_counter_views)): ?>
                    <p class="bottom-border">
                        <strong>{lang_ViewsCounter}:</strong>
                        <span>{estate_data_counter_views}</span>
                    </p>
                    <?php endif;?>
                    
                    <?php if(!empty($estate_data_option_56)): ?>
                    <p class="bottom-border">
                        <strong>{lang_Pro}:</strong>
                        <span class="review_stars_<?php echo $estate_data_option_56; ?>"> </span>
                    </p>
                    <?php endif;?>
                    
                    <?php if(!empty($avarage_stars) && file_exists(APPPATH.'controllers/admin/reviews.php') && $settings_reviews_enabled): ?>
                    <p class="bottom-border">
                        <strong>{lang_Users}:</strong>
                        <span class="review_stars_<?php echo $avarage_stars; ?>"> </span>
                    </p>
                    <?php endif;?>

                    <p style="text-align:right;">
                        <a target="_blank" type="button" class="btn" href="{estate_data_printurl}"><i class="icon-print"></i>&nbsp;{lang_PrintVersion}</a>
                    </p>
                  </div>
                  <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
                    {has_ads_160x600px}
                    <h2>{lang_Ads}</h2>
                    <div class="sidebar-ads-1">
                        <a href="{random_ads_160x600px_link}" target="_blank"><img src="{random_ads_160x600px_image}" /></a>
                    </div>
                    {/has_ads_160x600px}
                    <?php elseif(!empty($settings_adsense160_600)): ?>
                    <h2>{lang_Ads}</h2>
                    <div class="sidebar-ads-1">
                    <?php echo $settings_adsense160_600; ?>
                    </div>
                    <?php endif;?>
                  
                  {has_agent}
                  <h2>{lang_Agent}</h2>
                  <div class="agent">
                    <?php if($is_private_listing): ?>
                    <div class="purchase_package">
                    <a href="{front_login_url}#content">{lang_PurchaseToShow}</a>
                    </div>
                    <?php else: ?>
                    <?php if(is_array($this->session->userdata('contacted_agents'))&&in_array($agent_id, $this->session->userdata('contacted_agents'))): ?>
                    <?php else: ?>
                    <a class="popup-with-form hidden-agent-details" href="#test-form"><?php echo lang_check('Show agent contact details'); ?></a>
                    <?php endif; ?>
                    
                    <div class="image"><img src="{agent_image_url}" alt="{agent_name_surname}" /></div>
                    <div class="name"><a href="{agent_url}#content">{agent_name_surname}</a></div>
                    <div class="phone">{agent_phone}</div>
                    <div class="mail"><a href="mailto:{agent_mail}?subject={lang_Estateinqueryfor}: {estate_data_id}, {page_title}">{agent_mail}</a></div>
                    <!--<div class="address">{agent_address}</div>-->
                    <?php endif; ?>
                  </div>
                  {/has_agent}
                  
                  <?php if(file_exists(APPPATH.'controllers/admin/booking.php') && count($is_purpose_rent) && $this->session->userdata('type')=='USER' && config_item('reservations_disabled') === FALSE):?>
                  <h2>{lang_Bookingform}</h2>
                  <div id="form" class="property-form">
                    {validation_errors}
                    {form_sent_message}
                    <form method="post" action="{page_current_url}#form">
                        <label>{lang_FirstLast}</label>
                        <input class="{form_error_firstname}" name="firstname" type="text" placeholder="{lang_FirstLast}" value="{form_value_firstname}" />
                        <label>{lang_Phone}</label>
                        <input class="{form_error_phone}" name="phone" type="text" placeholder="{lang_Phone}" value="{form_value_phone}" />
                        <label>{lang_Email}</label>
                        <input class="{form_error_email}" name="email" type="text" placeholder="{lang_Email}" value="{form_value_email}" />
                        <label>{lang_Address}</label>
                        <input class="{form_error_address}" name="address" type="text" placeholder="{lang_Address}" value="{form_value_address}" />
                        {is_purpose_rent}
                        <label>{lang_FromDate}</label>
                        <input name="fromdate" type="text" id="datetimepicker1" value="{form_value_fromdate}" class="{form_error_fromdate}" placeholder="{lang_FromDate}" />
                        <label>{lang_ToDate}</label>
                        <input class="{form_error_todate}" id="datetimepicker2" name="todate" type="text" placeholder="{lang_ToDate}" value="{form_value_todate}" />
                        {/is_purpose_rent}
                        <label>{lang_Message}</label>
                        <textarea class="{form_error_message}" name="message" rows="3" placeholder="{lang_Message}">{form_value_message}</textarea>
                        
                        <?php if(config_item('captcha_disabled') === FALSE): ?>
                        <label class="captcha"><?php echo $captcha['image']; ?></label>
                        <input class="captcha {form_error_captcha}" name="captcha" type="text" placeholder="{lang_Captcha}" value="" />
                        <br style="clear: both;" />
                        <input class="hidden" name="captcha_hash" type="text" value="<?php echo $captcha_hash; ?>" />
                        <?php endif; ?>

                        <br style="clear: both;" />
                        <p style="text-align:right;">
                        <button type="submit" class="btn btn-info">{lang_CalculateBook}</button>
                        </p>
                    </form>
                  </div>
                  <?php else:?>
                  <h2>{lang_Enquireform}</h2>
                  <div id="form" class="property-form">
                    {validation_errors}
                    {form_sent_message}
                    <form method="post" action="{page_current_url}#form">
                        <label>{lang_FirstLast}</label>
                        <input class="{form_error_firstname}" name="firstname" type="text" placeholder="{lang_FirstLast}" value="{form_value_firstname}" />
                        <label>{lang_Phone}</label>
                        <input class="{form_error_phone}" name="phone" type="text" placeholder="{lang_Phone}" value="{form_value_phone}" />
                        <label>{lang_Email}</label>
                        <input class="{form_error_email}" name="email" type="text" placeholder="{lang_Email}" value="{form_value_email}" />
                        <label>{lang_Address}</label>
                        <input class="{form_error_address}" name="address" type="text" placeholder="{lang_Address}" value="{form_value_address}" />
                        
                        <?php if(config_item('reservations_disabled') === FALSE): ?>
                        {is_purpose_rent}
                        <label>{lang_FromDate}</label>
                        <input name="fromdate" type="text" id="datetimepicker1" value="{form_value_fromdate}" class="{form_error_fromdate}" placeholder="{lang_FromDate}" />
                        <label>{lang_ToDate}</label>
                        <input class="{form_error_todate}" id="datetimepicker2" name="todate" type="text" placeholder="{lang_ToDate}" value="{form_value_todate}" />
                        {/is_purpose_rent}
                        <?php endif; ?>
                        <label>{lang_Message}</label>
                        <textarea class="{form_error_message}" name="message" rows="3" placeholder="{lang_Message}">{form_value_message}</textarea>
                        
                        <?php if(config_item('captcha_disabled') === FALSE): ?>
                        <label class="captcha"><?php echo $captcha['image']; ?></label>
                        <input class="captcha {form_error_captcha}" name="captcha" type="text" placeholder="{lang_Captcha}" value="" />
                        <br style="clear: both;" />
                        <input class="hidden" name="captcha_hash" type="text" value="<?php echo $captcha_hash; ?>" />
                        <?php endif; ?>
                        
                        <br style="clear: both;" />
                        <p style="text-align:right;">
                        <button type="submit" class="btn btn-info">{lang_Send}</button>
                        </p>
                    </form>
                  </div>
                  <?php endif;?>

                  <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
                    {has_ads_180x150px}
                    <h2>{lang_Ads}</h2>
                    <div class="sidebar-ads-1">
                        <a href="{random_ads_180x150px_link}" target="_blank"><img src="{random_ads_180x150px_image}" /></a>
                    </div>
                    {/has_ads_180x150px}
                  <?php endif;?>
                  
                  
            </div>
        </div>
    </div>
</div>
    
{template_footer}

<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
<div id="blueimp-gallery" class="blueimp-gallery">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">&lsaquo;</a>
    <a class="next">&rsaquo;</a>
    <a class="close">&times;</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>

<?php if(config_item('enable_property_details_on_top') == TRUE): ?>
<div id="top_content" class="on_top_fixed">

<?php if(!empty($estate_data_option_2)): ?>
<div class="alert alert-success span2">
<?php echo $estate_data_option_2; ?>
</div>
<?php endif;?>

<?php if(!empty($estate_data_option_4)): ?>
<div class="alert alert-info span1">
<?php echo $estate_data_option_4; ?>
</div>
<?php endif;?>

<?php if(!empty($estate_data_option_36)): ?>
<div class="alert span2">
{options_prefix_36} <?php echo $estate_data_option_36; ?> {options_suffix_36}
</div>
<?php endif;?>

<?php if(!empty($estate_data_option_57)): ?>
<div class="alert span1">
{options_prefix_57} <?php echo $estate_data_option_57; ?> {options_suffix_57}
</div>
<?php endif;?>
</div>

<?php if(!empty($estate_data_option_36) && !empty($estate_data_option_57)): ?>
<div class="alert span1">
<?php echo $estate_data_option_36/$estate_data_option_57; ?> {options_prefix_36}{options_suffix_36}/{options_suffix_57}
</div>
<?php endif;?>

<script language="javascript">
$(document).ready(function(){
	
	var content = $('#content');
	var pos = content.offset();
	var top_content = $('#top_content');
	
	$(window).scroll(function(){
		if($(this).scrollTop() > pos.top){
          top_content.fadeIn('fast');
		} else if($(this).scrollTop() <= pos.top){
          top_content.fadeOut('fast');
		}
	});
    
});
</script>
<?php endif; ?>

<?php if(isset($views_last_30_min)): ?>
<link href="assets/js/pnotify/pnotify.custom.css" rel="stylesheet">
<script src="assets/js/pnotify/pnotify.custom.js"></script>

<script language="javascript">
$(document).ready(function(){
    
    PNotify.prototype.options.styling = "bootstrap2";
    var stack_bottomleft = {"dir1": "right", "dir2": "up", "push": "top"};
    
    new PNotify({
        title: false,
        text: '<?php echo lang_check('This property has been seen by other'); ?> <?php echo $views_last_30_min; ?> <?php echo lang_check('users in the last 30 minutes'); ?>',
        addclass: "stack-bottomleft",
        stack: stack_bottomleft,
        hide: false,
        icon: 'icon-user'
    });
    
});
</script>
<?php endif; ?>

  </body>
</html>