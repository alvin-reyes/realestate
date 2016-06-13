<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    
    <script language="javascript">
    $(document).ready(function(){

        $("#wrap-map").gmap3({
         map:{
            options:{
             <?php if(config_item('custom_map_center') === FALSE): ?>
             center: [{all_estates_center}],
             <?php else: ?>
             center: [<?php echo config_item('custom_map_center'); ?>],
             <?php endif; ?>
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
                {latLng:[{gps}], adr:"{address}",  options:{ icon: "{icon}"}, data:"<img style=\"width: 150px; height: 100px;\" src=\"{thumbnail_url}\" /><br />{address}<br />{option_2}<br /><span class=\"label label-info\">&nbsp;&nbsp;{option_4}&nbsp;&nbsp;</span><br /><a href=\"{url}\">{lang_Details}</a>"},
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
                  infowindow.setContent('<div style="width:100px;display:inline;">'+context.data+'</div>');
                } else {
                  $(this).gmap3({
                    infowindow:{
                      anchor:marker,
                      options:{disableAutoPan: mapDisableAutoPan, content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
                    }
                  });
                }
              }
//        ,mouseout: function(){
//            var infowindow = $(this).gmap3({get:{name:"infowindow"}});
//            if (infowindow){
//              infowindow.close();
//            }
//          }
        }}});
        
        init_gmap_searchbox();
    });
    
    </script>
  </head>

  <body>
  
{template_header-slideshow}

<div class="wrap-map">
    <div id="myCarousel" class="carousel slide">
    <ol class="carousel-indicators">
    {slideshow_images}
    <li data-target="#myCarousel" data-slide-to="{num}" class="{first_active}"></li>
    {/slideshow_images}
    </ol>
    <!-- Carousel items -->
    <div class="carousel-inner">
    {slideshow_images}
        <div class="item {first_active}">
        <img alt="" src="{url}" />
        </div>
    {/slideshow_images}
    </div>
    <!-- Carousel nav -->
    <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
    <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
    </div>
</div>

{template_search-slideshow}

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
    <div class="container">

        <h2>{lang_Realestates}</h2>
        <div class="options">
            <a class="view-type active hidden-phone" ref="grid" href="#"><img src="assets/img/glyphicons/glyphicons_156_show_thumbnails.png" /></a>
            <a class="view-type hidden-phone" ref="list" href="#"><img src="assets/img/glyphicons/glyphicons_157_show_thumbnails_with_lines.png" /></a>
            
            <select class="span3 selectpicker-small pull-right" placeholder="{lang_OrderBy}">
                <option value="id ASC" {order_dateASC_selected}>{lang_DateASC}</option>
                <option value="id DESC" {order_dateDESC_selected}>{lang_DateDESC}</option>
                <option value="price ASC" {order_priceASC_selected}>{lang_PriceASC}</option>
                <option value="price DESC" {order_priceDESC_selected}>{lang_PriceDESC}</option>
            </select>
            <span class="pull-right" style="padding-top: 5px;">{lang_OrderBy}&nbsp;&nbsp;&nbsp;</span>
        </div>

        <br style="clear:both;" />

        <div class="row-fluid">
            <ul class="thumbnails">
            {has_no_results}
            <li class="span12">
            <div class="alert alert-success">
            {lang_Noestates}
            </div>
            </li>
            {/has_no_results}
            {results}
              <li class="span3">
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

                    {has_option_37}
                    <span class="price">{options_prefix_37} {option_37} {options_suffix_37}</span>
                    {/has_option_37}

                    <span class="res_counter">{lang_ViewsCounter}: {counter_views}</span>
                    </p>
                  </div>
                </div>
              </li>
            {/results}
            </ul>
          </div>
          <div class="pagination properties">
          {pagination_links}
          </div>
    </div>
    </div>
    <div class="wrap-content2">
        <div class="container">
            {page_body}
        </div>
    </div>
    
    <div class="wrap-content2">
        <div class="container">
            <h2>{lang_Agencies}</h2>
            <!-- AGENCIES -->
            <div class="property_content_position">
            <div class="row-fluid">
            <?php foreach($all_agents as $agent): ?>
            <?php if(isset($agent['image_sec_url'])): ?>
              <div class="span2"><a href="<?php echo $agent['agent_url']; ?>"><img src="<?php echo $agent['image_sec_url']; ?>" /></a></div>
            <?php endif; ?>
            <?php endforeach; ?>
            </div>
            <br />
            </div>
            <!-- AGENCIES -->
        </div>
    </div>
    
    <div class="wrap-map" id="wrap-map">
    </div>
    
    {template_footer}
  </body>
</html>