<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang('Enquires')?>
      <!-- page meta -->
      <span class="page-meta"><?php echo lang('View all enquires')?></span>
    </h2>
    
    
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/enquire')?>"><?php echo lang('Enquires')?></a>
    </div>
    
    <div class="clearfix"></div>
</div>

<div class="matter">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <?php echo anchor('admin/enquire/edit', '<i class="icon-plus"></i>&nbsp;&nbsp;'.lang('Add a enquire'), 'class="btn btn-primary"')?>
                </div>
            </div>
          <div class="row">

            <div class="col-md-12">

                <div class="widget worange">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Enquires')?></div>
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
                        	<th><?php echo lang('Date');?></th>
                            <th data-hide="phone,tablet"><?php echo lang('Mail');?></th>
                            <th data-hide="phone,tablet"><?php echo lang('Message');?></th>
                            <th data-hide="phone,tablet"><?php echo lang('Estate');?></th>
                        	<th class="control"><?php echo lang('Edit');?></th>
                        	<th class="control"><?php echo lang('Delete');?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($enquires)): foreach($enquires as $enquire):?>
                                    <tr>
                                    	<td><?php echo anchor('admin/enquire/edit/'.$enquire->id, $enquire->date)?>&nbsp;&nbsp;<?php echo $enquire->readed == 0? '<span class="label label-warning">'.lang('Not readed').'</span>':''?></td>
                                        <td><?php echo $enquire->mail?></td>
                                        <td><?php echo word_limiter(strip_tags($enquire->message), 5);?></td>
                                        <td><?php echo $all_estates[$enquire->property_id]?></td>
                                    	<td><?php echo btn_edit('admin/enquire/edit/'.$enquire->id)?></td>
                                    	<td><?php echo btn_delete('admin/enquire/delete/'.$enquire->id)?></td>
                                    </tr>
                        <?php endforeach;?>
                        <?php else:?>
                                    <tr>
                                    	<td colspan="10"><?php echo lang('We could not find any messages')?></td>
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