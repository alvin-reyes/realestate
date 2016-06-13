<div class="wrap-search">
    <div class="container">

        <ul id="search_option_4" class="menu-onmap tabbed-selector">
            <li class="all-button"><a href="#"><?php echo lang_check('All'); ?></a></li>
            {options_values_li_4}
            <li class="list-property-button"><a href="{myproperties_url}">{lang_Listproperty}</a></li>
        </ul>
        
        <div class="search-form">
            <form class="form-inline">
            
                <input id="rectangle_ne" type="text" class="hide" />
                <input id="rectangle_sw" type="text" class="hide" />
            
                <input id="search_option_smart" type="text" class="span6" value="{search_query}" placeholder="{lang_CityorCounty}" autocomplete="off" />
                <select id="search_option_2" class="span3 selectpicker" placeholder="{options_name_2}">
                    {options_values_2}
                </select>
                <select id="search_option_3" class="span3 selectpicker nomargin" placeholder="{options_name_3}">
                    {options_values_3}
                </select>
                
                <div class="advanced-form-part">
                <div class="form-row-space"></div>
                <input id="search_option_36_from" type="text" class="span3 mPrice DECIMAL" placeholder="{lang_Fromprice} ({options_prefix_36}{options_suffix_36})" value="<?php echo search_value('36_from'); ?> />
                <input id="search_option_36_to" type="text" class="span3 xPrice DECIMAL" placeholder="{lang_Toprice} ({options_prefix_36}{options_suffix_36})" value="<?php echo search_value('36_to'); ?> />
                <input id="search_option_19" type="text" class="span3 Bathrooms INTEGER" placeholder="{options_name_19}" value="<?php echo search_value(19); ?>" />
                <input id="search_option_20" type="text" class="span3 INTEGER" placeholder="{options_name_20}" value="<?php echo search_value(20); ?>" />
                <div class="form-row-space"></div>
                <?php if(file_exists(APPPATH.'controllers/admin/booking.php')):?>
                <input id="booking_date_from" type="text" class="span3 mPrice" placeholder="{lang_Fromdate}" value="<?php echo search_value('date_from'); ?>" />
                <input id="booking_date_to" type="text" class="span3 xPrice" placeholder="{lang_Todate}" value="<?php echo search_value('date_to'); ?>" />
                <div class="form-row-space"></div>
                <?php endif; ?>
                <label class="checkbox">
                <input id="search_option_11" type="checkbox" class="span1" value="true{options_name_11}" <?php echo search_value('11', 'checked'); ?>/>{options_name_11}
                </label>
                <label class="checkbox">
                <input id="search_option_22" type="checkbox" class="span1" value="true{options_name_22}" <?php echo search_value('22', 'checked'); ?>/>{options_name_22}
                </label>
                <label class="checkbox">
                <input id="search_option_25" type="checkbox" class="span1" value="true{options_name_25}" <?php echo search_value('25', 'checked'); ?>/>{options_name_25}
                </label>
                <label class="checkbox">
                <input id="search_option_27" type="checkbox" class="span1" value="true{options_name_27}" <?php echo search_value('27', 'checked'); ?>/>{options_name_27}
                </label>
                <label class="checkbox">
                <input id="search_option_28" type="checkbox" class="span1" value="true{options_name_28}" <?php echo search_value('28', 'checked'); ?>/>{options_name_28}
                </label>
                <label class="checkbox">
                <input id="search_option_29" type="checkbox" class="span1" value="true{options_name_29}" <?php echo search_value('29', 'checked'); ?>/>{options_name_29}
                </label>
                <label class="checkbox">
                <input id="search_option_32" type="checkbox" class="span1" value="true{options_name_32}" <?php echo search_value('32', 'checked'); ?>/>{options_name_32}
                </label>
                <label class="checkbox">
                <input id="search_option_30" type="checkbox" class="span1" value="true{options_name_30}" <?php echo search_value('30', 'checked'); ?>/>{options_name_30}
                </label>
                <label class="checkbox">
                <input id="search_option_33" type="checkbox" class="span1" value="true{options_name_33}" <?php echo search_value('33', 'checked'); ?>/>{options_name_33}
                </label>
                </div>
                <br style="clear:both;" />
                <button id="search-start" type="submit" class="btn btn-info btn-large">&nbsp;&nbsp;{lang_Search}&nbsp;&nbsp;</button>
                <a id="search-start-map" href="#wrap-map" class="scroll"><button type="button" class="btn btn-success btn-large">{lang_ShowOnMap}</button></a>
                
                <img id="ajax-indicator-1" src="assets/img/ajax-loader.gif" />
            </form>
        </div>
    </div>
</div>



