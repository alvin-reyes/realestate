        
        
        
        <h2>{lang_Results}: <?php echo $total_rows; ?></h2>
        <div class="options">
            <a class="view-type {view_grid_selected}" ref="grid" href="#"><img src="assets/img/glyphicons/glyphicons_156_show_thumbnails.png" /></a>
            <a class="view-type {view_list_selected}" ref="list" href="#"><img src="assets/img/glyphicons/glyphicons_157_show_thumbnails_with_lines.png" /></a>
            
            <select class="span3 selectpicker-small pull-right" placeholder="{lang_Sort}">
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
            {has_view_grid}
              <li class="span3 li-grid">
                <div class="thumbnail f_{is_featured}">
                  <h3>{option_10}&nbsp;</h3>
                  <img alt="300x200" data-src="holder.js/300x200" style="" src="{thumbnail_url}" />
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
                    <span class="price price-rent">{options_prefix_37} {option_37} {options_suffix_37}</span>
                    {/has_option_37}
                
                    <span class="res_counter">{lang_ViewsCounter}: {counter_views}</span>
                    </p>
                  </div>
                </div>
              </li>
            {/has_view_grid}
            {has_view_list}
              <li class="span12 li-list">
                <div class="thumbnail span4 f_{is_featured}">
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
                </div>
                  <div class="caption span8">
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
                    <p class="prop-button-container">
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
              </li>
            {/has_view_list}
            {/results}
            </ul>
          </div>
          <div class="pagination properties">
          {pagination_links}
          </div>
          
          
          
