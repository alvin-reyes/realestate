<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    
    <script language="javascript">
    $(document).ready(function(){


    });
    </script>
  </head>

  <body>
  
{template_header-filter}

<div class="wrap-map" id="wrap-map-1">
    <div id="myCarousel" class="carousel slide">
    <?php if(false): ?>
    <ol class="carousel-indicators">
    {slideshow_images}
    <li data-target="#myCarousel" data-slide-to="{num}" class="{first_active}"></li>
    {/slideshow_images}
    </ol>
    <?php endif; ?>
    
    <div class="container">
    <ol class="carousel-thumbs carousel-indicators">
    {slideshow_images}
        <li data-target="#myCarousel" data-slide-to="{num}" class="{first_active}"><a href="#"><img src="{url}" /></a></li>
    {/slideshow_images}
    </ol>
    </div>
    
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

{template_search-filter}

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
    <div class="row-fluid">
        <div class="span9">
    <div class="results-properties-list with-sidebar">

        <h2>{lang_Realestates}: <?php echo $total_rows; ?></h2>
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
              <li class="span3 li-grid">
                <div class="thumbnail f_{is_featured}">
                  <h3>{option_10}&nbsp;</h3>
                  <img alt="300x200" data-src="holder.js/300x200" src="{thumbnail_url}" />
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
        <div class="span3">
        <h2>{lang_CustomFilters}</h2>
        <div class="filter-checkbox-container">
        
            <div class="row">
                <div class="span12">
                    <input style="" option_id="19" type="text" class="span12 input_am id_19" placeholder="{options_name_19}" />
                    <input style="" option_id="20" type="text" class="span12 input_am id_20" placeholder="{options_name_20}" />
                </div>
                <div class="span12">
                    <input style="" option_id="36" type="text" rel="from" class="span12 input_am_from id_36_from DECIMAL" placeholder="{lang_Fromprice} ({options_prefix_36}{options_suffix_36})" />
                </div>
                <div class="span12">
                    <input style="" option_id="36" type="text" rel="to" class="span12 input_am_to id_36_to DECIMAL" placeholder="{lang_Toprice} ({options_prefix_36}{options_suffix_36})" />
                </div>
                
                <?php if(config_db_item('search_energy_efficient_enabled') === TRUE): ?>
                <div class="span12">
                <select option_id="59" rel="to" class="span12 selectpicker nomargin input_am_to id_59_to" placeholder="{options_name_59}">
                    <option value="">{options_name_59}</option>
                    <option value="50">A</option>
                    <option value="90">B</option>
                    <option value="150">C</option>
                    <option value="230">D</option>
                    <option value="330">E</option>
                    <option value="450">F</option>
                    <option value="999999">G</option>
                </select>
                </div>
                <?php endif; ?>
                
            </div>

            <div class="row">
                <div class="span6">
                    <label class="checkbox">
                    <input option_id="11" class="checkbox_am" type="checkbox" value="true{options_name_11}" />{options_name_11}
                    </label>
                    <label class="checkbox">
                    <input option_id="22" class="checkbox_am" type="checkbox" value="true{options_name_22}" />{options_name_22}
                    </label>
                    <label class="checkbox">
                    <input option_id="25" class="checkbox_am" type="checkbox" value="true{options_name_25}" />{options_name_25}
                    </label>
                    <label class="checkbox">
                    <input option_id="27" class="checkbox_am" type="checkbox" value="true{options_name_27}" />{options_name_27}
                    </label>
                    <label class="checkbox">
                    <input option_id="28" class="checkbox_am" type="checkbox" value="true{options_name_28}" />{options_name_28}
                    </label>
                </div>
                <div class="span6">
                    <label class="checkbox">
                    <input option_id="29" class="checkbox_am" type="checkbox" value="true{options_name_29}" />{options_name_29}
                    </label>
                    <label class="checkbox">
                    <input option_id="32" class="checkbox_am" type="checkbox" value="true{options_name_32}" />{options_name_32}
                    </label>
                    <label class="checkbox">
                    <input option_id="30" class="checkbox_am" type="checkbox" value="true{options_name_30}" />{options_name_30}
                    </label>
                    <label class="checkbox">
                    <input option_id="33" class="checkbox_am" type="checkbox" value="true{options_name_33}" />{options_name_33}
                    </label>
                    <label class="checkbox">
                    <input option_id="23" class="checkbox_am" type="checkbox" value="true{options_name_23}" />{options_name_23}
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="span12">
                <button type="submit" class="btn span12 refresh_filters">{lang_RefreshResults}</button>
                </div>
            </div>
        </div>

          <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
            {has_ads_180x150px}
            <h2>{lang_Ads}</h2>
            <div class="sidebar-ads-1">
                <a href="{random_ads_180x150px_link}" target="_blank"><img src="{random_ads_180x150px_image}" /></a>
            </div>
            {/has_ads_180x150px}
          <?php endif;?>

        <h2>{lang_Agents}</h2>
        <form class="form-search agents" action="<?php echo current_url().'#content'; ?>" method="get">
        <input name="search-agent" type="text" placeholder="{lang_CityorName}" value="<?php echo $this->input->get('search-agent'); ?>" class="input-medium" />
        <button type="submit" class="btn">{lang_Search}</button>
        </form>
        {paginated_agents}
        <div class="agent">
            <div class="image"><img src="{image_url}" alt="{name_surname}" /></div>
            <div class="name"><a href="{agent_url}">{name_surname} ({total_listings_num})</a></div>
            <div class="phone">{phone}</div>
            <div class="mail"><a href="mailto:{mail}?subject={lang_Estateinqueryfor}: {page_title}">{mail}</a></div>
        </div>
        {/paginated_agents}
        <div class="pagination" style="margin-top: 10px;">
        <?php echo $agents_pagination; ?>
        </div>
        
        </div>
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
    {template_footer}
  </body>
</html>