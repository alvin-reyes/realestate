<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang_check('Payments')?>
      <!-- page meta -->
      <span class="page-meta"><?php echo lang_check('View all payments')?></span>
    </h2>
    
    
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="" href="<?php echo site_url('admin/booking')?>"><?php echo lang_check('Booking')?></a>
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/booking/payments')?>"><?php echo lang_check('Payments')?></a>
    </div>
    
    <div class="clearfix"></div>
</div>

<div class="matter">
        <div class="container">
        
          <div class="row">

            <div class="col-md-12">

                <div class="widget worange">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang_check('Payments')?></div>
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
                            <th data-hide="phone,tablet"><?php echo lang_check('Payer email');?></th>
                            <th data-hide="phone,tablet"><?php echo lang_check('Date paid');?></th>
                            <th data-hide="phone,tablet"><?php echo lang_check('Paid');?></th>
                            <th data-hide="phone,tablet"><?php echo lang_check('Reservation id');?></th>
                            <th data-hide="phone,tablet"><?php echo lang_check('Transaction id');?></th>
                        	<th class="control"><?php echo lang_check('Details');?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($payments)): foreach($payments as $item):?>
                                    <tr>
                                    	<td><?php echo $item->id?></td>
                                        <td>
                                        <?php echo $item->payer_email?>
                                        </td>
                                        <td>
                                        <?php echo $item->date_paid?>
                                        </td>
                                        <td>
                                        <?php echo $item->paid.' '.$item->currency_code; ?>
                                        </td>
                                        <td>
                                        <?php 
                                            $inv_ex = explode('_', $item->invoice_num);
                                            $reservation_id = $inv_ex[0];
                                            if(!empty($reservation_id))
                                            echo '<a href="'.site_url('admin/booking/payments/'.$reservation_id).'" class="label label-danger">#'.$reservation_id.'</a>';
                                        ?>
                                        </td>
                                        <td>
                                        <?php echo $item->txn_id; ?>
                                        </td>
                                    	<td><?php echo btn_view('admin/booking/view_payment/'.$item->id)?></td>
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