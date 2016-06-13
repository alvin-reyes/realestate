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

        $("#contactMap").gmap3({
         map:{
            options:{
             center: [{settings_gps}],
             zoom: 12,
             scrollwheel: scrollWheelEnabled
            }
         },
         marker:{
            values:[
              {latLng:[{settings_gps}], options:{icon: "assets/img/marker_blue.png"}, data:"{settings_address},<br />{lang_GPS}: {settings_gps}"}
            ],
            
        options:{
          draggable: false
        },
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
          },
          mouseout: function(){
            //var infowindow = $(this).gmap3({get:{name:"infowindow"}});
            //if (infowindow){
            //  infowindow.close();
            //}
          }
        }}});
    });
    
    </script>
  </head>

  <body>
  
{template_header}

<input id="pac-input" class="controls" type="text" placeholder="{lang_Search}" />
<div class="wrap-map" id="wrap-map">
</div>

{template_search}
<a name="content" id="content"></a>
<div class="wrap-content">
    <div class="container">
        <h2>{page_title}</h2>
        <div class="property_content">
        {page_body}
        
        {has_settings_gps}
        <h2>{lang_Locationonmap}</h2>
        <div id="contactMap">
        </div>
        {/has_settings_gps}
        
        {has_settings_email}
        <h2 id="form">{lang_Contactform}</h2>
        <div id="contactForm"  class="contact-form">
        {validation_errors}
        {form_sent_message}
        <form method="post" action="{page_current_url}#form">
            
            <!-- The form name must be set so the tags identify it -->
            <input type="hidden" name="form" value="contact" />

                    <div class="row-fluid">
                    <div class="span5">
                        <div class="control-group {form_error_firstname}">
                            <div class="controls">
                                <div class="input-prepend input-block-level">
                                    <span class="add-on"><i class="icon-user"></i></span>
                                    <input class="input-block-level" id="firstname" name="firstname" type="text" placeholder="{lang_FirstLast}" value="{form_value_firstname}" />
                                </div>
                            </div>
                        </div>
                        <div class="control-group {form_error_email}">
                            <div class="controls">
                                <div class="input-prepend input-block-level">
                                    <span class="add-on"><i class="icon-envelope"></i></span>
                                    <input class="input-block-level" id="email" name="email" type="text" placeholder="{lang_Email}" value="{form_value_email}" />
                                </div>
                            </div>
                        </div>
                        <div class="control-group {form_error_phone}">
                            <div class="controls">
                                <div class="input-prepend input-block-level">
                                    <span class="add-on"><i class="icon-phone"></i></span>
                                    <input class="input-block-level" id="phone" name="phone" type="text" placeholder="{lang_Phone}" value="{form_value_phone}" />
                                </div>
                            </div>
                        </div>
                        <?php if(config_item('captcha_disabled') === FALSE): ?>
                        <div class="control-group" >
                            <?php echo $captcha['image']; ?>
                            <input class="captcha" name="captcha" type="text" placeholder="{lang_Captcha}" value="" />
                            <input class="hidden" name="captcha_hash" type="text" value="<?php echo $captcha_hash; ?>" />
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="span-mini"></div>
                    <div class="span6">
                        <div class="control-group {form_error_message}">
                            <div class="controls">
                                <textarea id="message" name="message" rows="4" class="input-block-level" type="text" placeholder="{lang_Message}">{form_value_message}</textarea>
                            </div>
                        </div>
                        <button class="btn btn-info pull-right" type="submit">{lang_Send}</button>
                    </div>
                    </div>
		</form>
        </div>
        {/has_settings_email}
        
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
        
        {has_page_documents}
        <h2>{lang_Filerepository}</h2>
        <ul>
        {page_documents}
        <li>
            <a href="{url}">{filename}</a>
        </li>
        {/page_documents}
        </ul>
        {/has_page_documents}
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