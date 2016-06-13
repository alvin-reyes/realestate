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
<?php endif;?>
<a name="content" id="content"></a>
<div class="wrap-content">
    <div class="container container-property">
        <div class="row-fluid">
            <div class="span9">
            <h2 id="content">{page_title}</h2>
              <div class="property_content">
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
                            <p class="bottom-border"><strong>{address}</strong></p>
                            <p class="bottom-border">{options_name_2} <span>{option_2}</span></p>
                            <p class="bottom-border">{options_name_3} <span>{option_3}</span></p>
                            <p class="bottom-border">{options_name_19} <span>{option_19}</span></p>
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
                <br style="clear:both;" />

              </div>
            </div>
            <div class="span3">
                  {has_agent}
                  <h2>{lang_Agent}</h2>
                  <div class="agent">
                    <div class="image"><img src="{agent_image_url}" alt="{agent_name_surname}" /></div>
                    <div class="name"><a href="{agent_url}#content">{agent_name_surname}</a></div>
                    <div class="phone">{agent_phone}</div>
                    <div class="mail"><a href="mailto:{agent_mail}?subject={lang_Estateinqueryfor}: {estate_data_id}, {page_title}">{agent_mail}</a></div>
                  </div>
                  {/has_agent}

                  <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
                    {has_ads_160x600px}
                    <h2>{lang_Ads}</h2>
                    <div class="sidebar-ads-1">
                        <a href="{random_ads_160x600px_link}" target="_blank"><img src="{random_ads_160x600px_image}" /></a>
                    </div>
                    {/has_ads_160x600px}
                  <?php endif;?>

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

  </body>
</html>