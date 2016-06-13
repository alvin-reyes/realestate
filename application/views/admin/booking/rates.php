<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang_check('Rates')?>
      <!-- page meta -->
      <span class="page-meta"><?php echo lang_check('View all rates')?></span>
    </h2>
    
    
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="" href="<?php echo site_url('admin/booking')?>"><?php echo lang_check('Booking')?></a>
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/booking/rates')?>"><?php echo lang_check('Rates')?></a>
    </div>
    
    <div class="clearfix"></div>
</div>

<div class="matter">
        <div class="container">
        
        <div class="row">
            <div class="col-md-12"> 
                <?php echo anchor('admin/booking/edit_rate', '<i class="icon-plus"></i>&nbsp;&nbsp;'.lang_check('Add rate'), 'class="btn btn-primary"')?>
            </div>
        </div>

          <div class="row">

            <div class="col-md-12">

                <div class="widget worange">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang_check('Rates')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                  <div class="widget-content">
                    <?php if($this->session->flashdata('error')):?>
                    <p class="label label-important validation"><?php echo $this->session->flashdata('error')?></p>
                    <?php endif;?>
                    <table class="table table-bordered footable">
                      <thead>
                        <tr>
                        	<th>#</th>
                            <th data-hide="phone,tablet"><?php echo lang_check('From Date');?></th>
                            <th data-hide="phone,tablet"><?php echo lang_check('To Date');?></th>
                            <th data-hide="phone,tablet"><?php echo lang_check('Property');?></th>
                        	<th class="control"><?php echo lang('Edit');?></th>
                        	<?php if(check_acl('booking/delete_rate')):?><th class="control"><?php echo lang('Delete');?></th><?php endif;?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($rates)): foreach($rates as $news_post):?>
                                    <tr>
                                    	<td><?php echo $news_post->id?></td>
                                        <td>
                                        <?php echo $news_post->date_from?>
                                        </td>
                                        <td>
                                        <?php echo $news_post->date_to?>
                                        </td>
                                        <td>
                                        <a href="<?php echo site_url('admin/booking/rates/'.$news_post->property_id); ?>" class="label label-danger"><?php echo '#'.$news_post->property_id.' - '.$properties[$news_post->property_id]?></a>
                                        </td>
                                    	<td><?php echo btn_edit('admin/booking/edit_rate/'.$news_post->id)?></td>
                                    	<?php if(check_acl('booking/delete_rate')):?><td><?php echo btn_delete('admin/booking/delete_rate/'.$news_post->id)?></td><?php endif;?>
                                    </tr>
                        <?php endforeach;?>
                        <?php else:?>
                                    <tr>
                                    	<td colspan="20"><?php echo lang('We could not find any');?></td>
                                    </tr>
                        <?php endif;?>           
                      </tbody>
                    </table>
                    
                    <div style="text-align: center;"><?php echo $pagination; ?></div>

                  </div>
                </div>
            </div>
          </div>
        </div>
</div>
    
    
    
    
    
</section>