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
        <h2>{lang_Latestnews}</h2>
        <!-- NEWS -->
        <div class="property_content_position">
        <div class="row-fluid">
        <ul class="thumbnails">
            <?php foreach($news_module_latest_5 as $key=>$row):?>
              <li class="span12 li-list">
                <div class="thumbnail span4">
                <?php if(isset($row->image_filename)):?>
                  <img alt="300x200" data-src="holder.js/300x200" style="width: 300px; height: 200px;" src="<?php echo base_url('files/thumbnail/'.$row->image_filename)?>" />
                <?php else:?>
                  <img alt="300x200" data-src="holder.js/300x200" style="width: 300px; height: 200px;" src="assets/img/no_image.jpg" />
                <?php endif;?>
                  <a href="<?php echo site_url($lang_code.'/'.$row->id); ?>" class="over-image"> </a>
                </div>
                  <div class="caption span8">
                    <p class="bottom-border"><strong><?php echo $row->title.', '.date("Y-m-d", strtotime($row->date_publish)); ?></strong></p>
                    <p class="prop-description"><?php echo $row->description; ?></p>
                    <p>
                    <a class="btn btn-info" href="<?php echo site_url($lang_code.'/'.$row->id); ?>">
                    {lang_Details}
                    </a>
                    </p>
                  </div>
              </li>
            <?php endforeach;?>
            </ul>
        </div>
        </div>
        <!-- /NEWS -->
    </div>
</div>

<div class="wrap-content2">
    <div class="container">
        {page_body}
        {has_page_images}
        <h2>{lang_Imagegallery}</h2>
        <ul data-target="#modal-gallery" data-toggle="modal-gallery" class="files files-list ui-sortable content-images">  
            {page_images}
            <li class="template-download fade in">
                <a data-gallery="gallery" href="{url}" title="{filename}" download="{url}" class="preview show-icon">
                    <img src="assets/img/preview-icon.png" class="" />
                </a>
                <div class="preview-img"><img src="{thumbnail_url}" data-src="{url}" alt="{filename}" class="" /></div>
            </li>
            {/page_images}
        </ul>
        
        
        <br style="clear: both;" />
        {/has_page_images}
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
    
  </body>
</html>