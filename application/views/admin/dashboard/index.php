
    <!-- Page heading -->
    <div class="page-head">
    <!-- Page heading -->
        <h2 class="pull-left"><?php echo lang('Dashboard')?>
		  <!-- page meta -->
		  <span class="page-meta"><?php echo lang('Short, basic informations')?></span>
		</h2>

		<!-- Breadcrumb -->
		<div class="bread-crumb pull-right">
		  <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
		</div>

		<div class="clearfix"></div>

    </div>
    <!-- Page heading ends -->

    <!-- Matter -->

    <div class="matter">
    <div class="container">
    

    
        <div class="row">
            <div class="col-md-12"> 
                <?php if(check_acl('page')):?><?php echo anchor('admin/page/edit', '<i class="icon-sitemap"></i>&nbsp;&nbsp;'.lang('Add a page'), 'class="btn btn-success"')?><?php endif;?>
                <?php echo anchor('admin/estate/edit', '<i class="icon-map-marker"></i>&nbsp;&nbsp;'.lang('Add a estate'), 'class="btn btn-info"')?>
            </div>
        </div>
          <div class="row">
            <div class="col-md-12">
              <div class="widget worange">
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('View properties')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
                  <div class="gmap" id="mapsProperties">

                  </div>
                </div>
              </div> 
            </div>
            </div>
            <div class="row">
            <?php if(check_acl('page')):?>
            <div class="col-md-6">
                <div class="widget wgreen">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Pages')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                  <div class="widget-content">
                    <!-- Nested Sortable -->
                    <div id="orderResult">
                    <?php echo get_ol_pages($pages_nested)?>
                    </div>
                  </div>
                </div>
            </div>
            <?php endif;?>
            
            
            
            
            
            <div class="col-md-6">

                <div class="widget wlightblue">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Last added estates')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                  <div class="widget-content">

                    <table class="table table-bordered footable">
                      <thead>
                        <tr>
                        	<th>#</th>
                            <th><?php echo lang('Address');?></th>
                            <!-- Dynamic generated -->
                            <?php foreach($this->option_m->get_visible($content_language_id) as $row):?>
                            <th data-hide="phone,tablet"><?php echo $row->option?></th>
                            <?php endforeach;?>
                            <!-- End dynamic generated -->
                        	<th class="control"><?php echo lang('Edit');?></th>
                        	<?php if(check_acl('estate/delete')):?><th class="control"><?php echo lang('Delete');?></th><?php endif;?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($estates)): foreach($estates as $estate):?>
                                    <tr>
                                        <?php if($estate->is_activated == 0):?>
                                        <td><span class="label label-danger"><?php echo $estate->id?></span></td>
                                        <?php else:?>
                                        <td><?php echo $estate->id?></td>
                                        <?php endif;?>
                                        <td><?php echo anchor('admin/estate/edit/'.$estate->id, $estate->address)?>
                                        </td>
                                        <!-- Dynamic generated -->
                                        <?php foreach($this->option_m->get_visible($content_language_id) as $row):?>
                                        <td><?php echo isset($options[$estate->id][$row->option_id])?$options[$estate->id][$row->option_id]:''?></td>
                                        <?php endforeach;?>
                                        <!-- End dynamic generated -->
                                    	<td><?php echo btn_edit('admin/estate/edit/'.$estate->id)?></td>
                                    	<?php if(check_acl('estate/delete')):?><td><?php echo btn_delete('admin/estate/delete/'.$estate->id)?></td><?php endif;?>
                                    </tr>
                        <?php endforeach;?>
                        <?php else:?>
                                    <tr>
                                    	<td colspan="5"><?php echo lang('We could not find any');?></td>
                                    </tr>
                        <?php endif;?>           
                      </tbody>
                    </table>

                  </div>
                </div>
            </div>
          </div>

    </div>
    </div>

	<!-- Matter ends -->

   <!-- Mainbar ends -->	    	
   <div class="clearfix"></div>
   
       <!-- Script for this page -->
    <script type="text/javascript">
    
        $(function () {
            $("#mapsProperties").gmap3({
             map:{
                options:{
                 center: [<?php echo calculateCenter($estates)?>],
                 zoom: 8,
                 scrollwheel: false
                }
             },
             marker:{
                values:[
                <?php if(count($estates_all)): foreach($estates_all as $estate):
                
                    $icon_url = base_url('admin-assets/img/markers/marker_blue.png');
                    if(isset($options[$estate->id][6]))
                    {
                        if($options[$estate->id][6] != '' && $options[$estate->id][6] != 'empty')
                        {
                            if(file_exists(FCPATH.'admin-assets/img/markers/'.$options[$estate->id][6].'.png'))
                            $icon_url = base_url('admin-assets/img/markers/'.$options[$estate->id][6].'.png');
                        }
                    }
                
                    echo '{latLng:['.$estate->gps.'], options:{ icon: "'.$icon_url.'"}, data:"'.strip_tags($estate->address);
                    foreach($this->option_m->get_visible($content_language_id) as $row):
                        if($row->type == 'DROPDOWN')
                        {
                            echo isset($options[$estate->id][$row->option_id])?'<br /><span class=\\"label label-warning\\">'.htmlentities(strip_tags($options[$estate->id][$row->option_id])).'</span>':'';
                        }
                        else
                        {
                            echo isset($options[$estate->id][$row->option_id])?'<br />'.htmlentities(strip_tags($options[$estate->id][$row->option_id])):'';
                        }
                    endforeach;
                    echo '<br /><a style=\\"font-weight:bold;\\" href=\\"'.site_url('admin/estate/edit/'.$estate->id).'\\">'.lang('Edit').'</a>"},';
                endforeach;
                endif;?> 
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
                  infowindow.setContent(context.data);
                } else {
                  $(this).gmap3({
                    infowindow:{
                      anchor:marker,
                      options:{content: context.data}
                    }
                  });
                }
              },
              mouseout: function(){
                var infowindow = $(this).gmap3({get:{name:"infowindow"}});
                if (infowindow){
                  //infowindow.close();
                }
              }
            }
             }
            });
        });
    
    </script>