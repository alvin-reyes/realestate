<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    <script language="javascript">
    $(document).ready(function(){
        
        $("#search_expert").keyup( function() {
            if($(this).val().length > 2 || $(this).val().length == 0)
            {
                $.post('<?php echo $ajax_expert_load_url; ?>', {search: $('#search_expert').val()}, function(data){
                    $('.property_content_position').html(data.print);
                    
                    reloadElements();
                }, "json");
            }
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
<a name="content" id="content"></a>
<div class="wrap-content">
    <div class="container">
    
        <h2>{page_title}</h2>
        <div class="property_content">
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
        <?php if(file_exists(APPPATH.'controllers/admin/expert.php')):?>
        <!-- SHOWROOM -->
        <div id="expert" class="news_content">
        <div class="row-fluid">
        <div class="span9">
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
                        <?php if(!empty($row->answer_user_id) && isset($all_experts[$row->answer_user_id])): ?>
                        <a class="image_expert" href="<?php echo site_url('expert/'.$row->answer_user_id.'/'.$lang_code); ?>#content-position">
                            <img src="<?php echo $all_experts[$row->answer_user_id]['image_url']?>" />
                        </a>
                        <?php else:?>
                        <span class="image_expert"> </span>
                        <?php endif;?>
                        <?php echo $row->answer; ?>
                        <br style="clear:both" />
                    </p>
                  </div>
              </li>
            <?php endforeach;?>
            </ul>
            <div class="pagination news">
            <?php echo $expert_pagination; ?>
            </div>
        </div>
        </div>
        </div>
        <div class="span3">
        
            <input type="text" placeholder="{lang_Search}" id="search_expert" autocomplete="off"/>
        
            <ul class="nav nav-tabs nav-stacked">
            <?php foreach($categories_expert as $id=>$category_name):?>
            <?php if($id != 0): ?>
                <li><a href="{page_current_url}?cat=<?php echo $id; ?>#expert"><?php echo $category_name; ?></a></li>
            <?php endif;?>
            <?php endforeach;?>
            </ul>
            
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
        <!-- /SHOWROOM -->
        <?php endif;?>
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