<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    <script language="javascript">
    $(document).ready(function(){
        
        $('.menu-onmap li a').click(function () {
            var tab_index = $('ul.menu-onmap li').index($(this).parent()[0]);
            
            if(tab_index == 0)
            {
                // fields manipulation for tab 0
                $('#search_option_19').show();
                $('#search_option_20').show();
            }
            else if(tab_index == 1)
            {
                // fields manipulation for tab 1
                $('#search_option_19').show();
                $('#search_option_20').show();
            }
            else if(tab_index == 2)
            {
                // fields manipulation for tab 2
                $('#search_option_19').hide();
                $('#search_option_20').hide();
            }
            
            //Auto search when click on property purpose
            manualSearch(0);
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
        options:{
          draggable: false
        },
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
                  options:{disableAutoPan: true, content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
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

<div class="wrap-search">
    <div class="container">
        <ul id="search_option_4" class="menu-onmap tabbed-selector">
            {options_values_li_4}
            <li class="list-property-button"><a href="{myproperties_url}">{lang_Listproperty}</a></li>
        </ul>
        <div class="search-form">
            <form class="form-inline">
                <input id="search_option_smart" type="text" class="span6" placeholder="{lang_CityorCounty}" />
                <select id="search_option_2" class="span3 selectpicker" placeholder="{options_name_2}">
                    {options_values_2}
                </select>
                <select id="search_option_3" class="span3 selectpicker nomargin" placeholder="{options_name_3}">
                    {options_values_3}
                </select>
                <div class="form-row-space"></div>
                <input id="search_option_36_from" type="text" class="span3 mPrice" placeholder="{lang_Fromprice} ({options_prefix_36}{options_suffix_36})" />
                <input id="search_option_36_to" type="text" class="span3 xPrice" placeholder="{lang_Toprice} ({options_prefix_36}{options_suffix_36})" />
                <input id="search_option_19" type="text" class="span3 Bathrooms" placeholder="{options_name_19}" />
                <input id="search_option_20" type="text" class="span3" placeholder="{options_name_20}" />
                <div class="form-row-space"></div>
                
                <select id="search_category_21" class="span7 selectpicker" title="{options_name_21}" multiple>
                    <option value="true{options_name_11}">{options_name_11}</option>
                    <option value="true{options_name_22}">{options_name_22}</option>
                    <option value="true{options_name_25}">{options_name_25}</option>
                    <option value="true{options_name_27}">{options_name_27}</option>
                    <option value="true{options_name_28}">{options_name_28}</option>
                    <option value="true{options_name_29}">{options_name_29}</option>
                    <option value="true{options_name_32}">{options_name_32}</option>
                    <option value="true{options_name_30}">{options_name_30}</option>
                    <option value="true{options_name_33}">{options_name_33}</option>
                </select>

                <br style="clear:both;" />
                <button id="search-start" type="submit" class="btn btn-info btn-large">&nbsp;&nbsp;{lang_Search}&nbsp;&nbsp;</button>
            </form>
        </div>
    </div>
</div>
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
        
        <br style="clear:both;" />
        
        
        <h2>{lang_Lastaddedproperties}</h2>
        <div class="row-fluid">
            <ul>
            {last_estates}
              <li class="span3">
                <div class="thumbnail">
                  <h3>{option_10}&nbsp;</h3>
                  <img alt="300x200" data-src="holder.js/300x200" style="width: 300px; height: 200px;" src="{thumbnail_url}" />
                  {has_option_38}
                  <div class="badget"><img src="assets/img/badgets/{option_38}.png" alt="{option_38}"/></div>
                  {/has_option_38}
                  {has_option_4}
                  <div class="purpose-badget fea_{is_featured}">{option_4}</div>
                  {/has_option_4}
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
              
                
                    {has_option_37}
                    <span class="price">{options_prefix_37} {option_37} {options_suffix_37}</span>
                    {/has_option_37}

                    </p>
                  </div>
                </div>
              </li>
            {/last_estates}
            </ul>
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