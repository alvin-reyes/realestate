<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    <script language="javascript">
    $(document).ready(function(){
        
       $("#route_from_button").click(function () { 
            window.open("https://maps.google.hr/maps?saddr="+$("#route_from").val()+"&daddr={showroom_data_address}@{showroom_data_gps}&hl={lang_code}",'_blank');
            return false;
        });
        
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

<div class="wrap-content" id="content-position">
    <div class="container container-property">
    <div class="row-fluid">
    <div class="span9">
        <h2>{page_title}</h2>
        <div class="property_content_position">
            <div class="row-fluid"
            <ul class="thumbnails">
                <?php foreach($expert_module_all as $key=>$row):?>
                  <li class="span12 li-list">
                      <div class="caption span12">
                        <p class="bottom-border">
                            <i class="qmark">?</i>
                            <strong><?php echo $row->question; ?></strong>
                            <br style="clear:both" />
                        </p>
                        <p class="prop-description">
                            <span class="image_expert"> </span>
                            <?php echo $row->answer; ?>
                            <br style="clear:both" />
                        </p>
                      </div>
                  </li>
                <?php endforeach;?>
                </ul>
            </div>
        </div>
    </div>
    <div class="span3">
          <h2>{lang_Expert}</h2>
          <div class="agent">
            <div class="image"><img src="{showroom_image_url}" alt="<?php echo $expert_data['name_surname']; ?>" /></div>
            <div class="name"><?php echo $expert_data['name_surname']; ?></div>
            <div class="phone"><?php echo $expert_data['phone']; ?></div>
            <div class="mail"><a href="mailto:<?php echo $expert_data['mail']; ?>?subject={lang_AskExpert}"><?php echo $expert_data['mail']; ?></a></div>
          </div>
          <h2>{lang_Overview}</h2>
          <div class="property_options">
            <p class="bottom-border"><strong>
            {lang_FirstLast}
            </strong> <span>{page_title}</span>
            <br style="clear: both;" />
            </p>
            <p class="bottom-border"><strong>
            {lang_Address}
            </strong> <span>{showroom_data_address}</span>
            <br style="clear: both;" />
            </p>
            <?php if(isset(${'documents_'.$expert_data['repository_id']}[0])):?>
            <p class="bottom-border"><strong>
            {lang_CV}
            </strong> <a href="<?php echo ${'documents_'.$expert_data['repository_id']}[0]->url?>">{lang_Download}</a>
            <br style="clear: both;" />
            </p>
            <?php endif;?>
          </div>

          <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
            {has_ads_180x150px}
            <h2>{lang_Ads}</h2>
            <div class="sidebar-ads-1">
                <a href="{random_ads_180x150px_link}" target="_blank"><img src="{random_ads_180x150px_image}" /></a>
            </div>
            {/has_ads_180x150px}
          <?php endif;?>

          <h2>{lang_AskExpert}</h2>
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

                <label>{lang_Question}</label>
                <textarea class="{form_error_question}" name="question" rows="3" placeholder="{lang_Question}">{form_value_question}</textarea>
                
                <br style="clear: both;" />
                <p style="text-align:right;">
                <button type="submit" class="btn btn-info">{lang_Send}</button>
                </p>
            </form>
          </div>
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