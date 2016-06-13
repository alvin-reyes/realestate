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
<a name="content" id="content"></a>
<div class="wrap-content">
    <div class="container">
        <div class="row-fluid">
            <div class="span12">
            <a name="content" id="content"></a>
            <h2>{lang_Myproperties}</h2>
            <div class="property_content">
                <div class="widget-controls"> 
                    <?php echo anchor('frontend/editproperty/'.$lang_code.'#content', '<i class="icon-plus"></i>&nbsp;&nbsp;'.lang_check('Addproperty'), 'class="btn btn-info"')?>
                </div>
                <div class="widget-content">
                    <?php if($this->session->flashdata('error')):?>
                    <p class="alert alert-error"><?php echo $this->session->flashdata('error')?></p>
                    <?php endif;?>
                    <table class="table table-striped">
                      <thead>
                        <tr>
                        	<th>#</th>
                            <th><?php echo lang('Address');?></th>
                            <!-- Dynamic generated -->
                            <?php foreach($this->option_m->get_visible($content_language_id) as $row):?>
                            <th><?php echo $row->option?></th>
                            <?php endforeach;?>
                            <!-- End dynamic generated -->
                            <th></th>
                        	<th class="control"><?php echo lang('Edit');?></th>
                        	<th class="control"><?php echo lang('Delete');?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($estates)): foreach($estates as $estate):?>
                                    <tr>
                                    	<td><?php echo $estate->id?></td>
                                        <td>
                                        <?php echo anchor('frontend/editproperty/'.$lang_code.'/'.$estate->id, $estate->address)?>
                                        <?php if($estate->is_activated == 0):?>
                                        &nbsp;<span class="label label-important"><?php echo lang_check('Not activated')?></span>
                                        <?php endif;?>
                                        
                                        <?php if( isset($settings_listing_expiry_days) && $settings_listing_expiry_days > 0 && strtotime($estate->date_modified) <= time()-$settings_listing_expiry_days*86400): ?>
                                        &nbsp;<span class="label label-warning"><?php echo lang_check('Expired')?></span>
                                        <?php endif; ?>
                                        </td>
                                        <!-- Dynamic generated -->
                                        <?php foreach($this->option_m->get_visible($content_language_id) as $row):?>
                                        <td><?php echo isset($options[$estate->id][$row->option_id])?$options[$estate->id][$row->option_id]:''?></td>
                                        <?php endforeach;?>
                                        <!-- End dynamic generated -->
                                        <td>
                                        <?php if($estate->is_activated == 0 && $settings_activation_price > 0):?>
                                        <?php 
                                        echo anchor('frontend/do_purchase_activation/'.$lang_code.'/'.$estate->id.'/'.$settings_activation_price, '<i class="icon-shopping-cart"></i> '.lang_check('Pay for activation'), array('class'=>'btn btn-warning'));
                                        ?>
                                        <?php endif;?>
                                        
                                        <?php if($estate->is_featured == 0 && $estate->is_activated == 1 && $settings_featured_price > 0):?>
                                        <?php 
                                        echo anchor('frontend/do_purchase_featured/'.$lang_code.'/'.$estate->id.'/'.$settings_featured_price, '<i class="icon-shopping-cart"></i> '.lang_check('Pay for featured'), array('class'=>'btn btn-primary'));
                                        ?>
                                        <?php endif;?>
                                        
                                        </td>
                                    	<td><?php echo anchor('frontend/editproperty/'.$lang_code.'/'.$estate->id, '<i class="icon-edit"></i> '.lang('Edit'), array('class'=>'btn btn-info'))?></td>
                                    	<td><?php echo anchor('frontend/deleteproperty/'.$lang_code.'/'.$estate->id, '<i class="icon-remove"></i> '.lang('Delete'), array('onclick' => 'return confirm(\''.lang_check('Are you sure?').'\')', 'class'=>'btn btn-danger'))?></td>
                                    </tr>
                        <?php endforeach;?>
                        <?php else:?>
                                    <tr>
                                    	<td colspan="20"><?php echo lang_check('You can add your first property');?></td>
                                    </tr>
                        <?php endif;?>           
                      </tbody>
                    </table>

                  </div>
            </div>
            </div>
        </div>
        
        <?php if(file_exists(APPPATH.'controllers/admin/packages.php')): ?>
        
        <div class="row-fluid">
            <div class="span12">
            <h2>{lang_Mypackage}</h2>
            <div class="property_content">
                <div class="widget-content">
                    <?php if($this->session->flashdata('error_package')):?>
                    <p class="alert alert-error"><?php echo $this->session->flashdata('error_package')?></p>
                    <?php endif;?>
                    <table class="table table-striped">
                      <thead>
                        <tr>
                        	<th>#</th>
                            <th><?php echo lang_check('Package name');?></th>
                            <th><?php echo lang_check('Price');?></th>
                            <th><?php echo lang_check('Free property activation');?></th>
                            <th><?php echo lang_check('Days limit');?></th>
                            <th><?php echo lang_check('Listings limit');?></th>
                        	<th class="control" style="width: 120px;"><?php echo lang('Buy/Extend');?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if(count($packages)): foreach($packages as $package):
                        
                        if(!empty($user['package_id']) && 
                           $user['package_id'] != $package->id &&
                           strtotime($user['package_last_payment']) >= time() &&
                           $packages_days[$package->id] > 0 &&
                           $packages_price[$user['package_id']] > 0)
                        {
                            continue;
                        }
                        else if(!empty($package->user_type) && $package->user_type != 'USER' && $user['package_id'] != $package->id)
                        {
                            continue;
                        }
                        
                        ?>
                                    <tr>
                                    	<td><?php echo $package->id; ?></td>
                                        <td>
                                        <?php echo $package->package_name; ?>
                                        <?php echo $package->show_private_listings==1?'&nbsp;<i class="icon-eye-open"></i>':'&nbsp;<i class="icon-eye-close"></i>'; ?>
                                        <?php if($user['package_id'] == $package->id):?>
                                        &nbsp;<span class="label label-success"><?php echo lang_check('Activated'); ?></span>
                                        <?php else: ?>
                                        &nbsp;<span class="label label-important"><?php echo lang_check('Not activated'); ?></span>
                                        <?php endif;?>
                                        
                                        <?php if($package->package_price > 0 && $user['package_id'] == $package->id && strtotime($user['package_last_payment']) < time() && $packages_days[$package->id] > 0): ?>
                                        &nbsp;<span class="label label-warning"><?php echo lang_check('Expired'); ?></span>
                                        <?php endif; ?>
                                        </td>
                                        <td>
                                        <?php echo $package->package_price.' '.$package->currency_code; ?>
                                        </td>
                                        <td><?php echo $package->auto_activation?'<i class="icon-ok"></i>':''; ?></td>
                                        <td>
                                        <?php 
                                            echo $package->package_days;
                                        
                                            if($user['package_id'] == $package->id && $package->package_price > 0 &&
                                               strtotime($user['package_last_payment']) >= time() && $packages_days[$package->id] > 0 )
                                            {
                                                echo ', '.$user['package_last_payment'];
                                            }
                                        
                                        ?>
                                        </td>
                                        <td>
                                        <?php echo $package->num_listing_limit?>
                                        </td>
                                    	<td><?php 
                                        
                                        if($package->package_price > 0)
                                        {
                                            echo anchor('frontend/do_purchase_package/'.$lang_code.'/'.$package->id.'/'.$package->package_price, '<i class="icon-shopping-cart"></i> '.lang('Buy/Extend'), array('class'=>'btn btn-info'));
                                        }

                                        ?></td>
                                    </tr>
                        <?php endforeach;?>
                        <?php else:?>
                                    <tr>
                                    	<td colspan="20"><?php echo lang_check('Not available');?></td>
                                    </tr>
                        <?php endif;?>           
                      </tbody>
                    </table>

                  </div>
            </div>
            </div>
        </div>
        
        <?php endif; ?>
        
        <?php if(!empty($settings_withdrawal_details)):?>
        <div class="row-fluid">
            <div class="span12">
            <h2>{lang_WithdrawalDetails}</h2>
            <div class="property_content">
            <?php echo $settings_withdrawal_details; ?><br />
            {lang_WithdrawalDetailsNotice}
            </div>
            </div>
        </div>
        <?php endif;?>
        
        <?php if(isset($settings_activation_price) && isset($settings_featured_price) &&
                 $settings_activation_price > 0 || $settings_featured_price > 0): ?>
        <div class="row-fluid">
            <div class="span12">
            <div class="property_content">
                <div class="widget-content">
                <?php if($settings_activation_price > 0): ?>
                    <?php echo lang_check('* Property activation price:').' '.$settings_activation_price.' '.$settings_default_currency; ?><br />
                 <?php endif;?>
                 <?php if($settings_featured_price > 0): ?>
                    <?php echo lang_check('* Property featured price:').' '.$settings_featured_price.' '.$settings_default_currency; ?>
                 <?php endif;?>
                 </div>
            </div>
            </div>
        </div>
        <?php endif;?>
        
        <?php if(false):?>
        <br />
        <div class="property_content">
        {page_body}
        </div>
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