
    <!-- Page heading -->
    <div class="page-head">
    <!-- Page heading -->
        <h2 class="pull-left"><?php echo lang('Settings')?>
		  <!-- page meta -->
		  <span class="page-meta"><?php echo lang('System settings')?></span>
		</h2>


		<!-- Breadcrumb -->
		<div class="bread-crumb pull-right">
          <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
          <!-- Divider -->
          <span class="divider">/</span> 
          <a class="bread" href="<?php echo site_url('admin/settings')?>"><?php echo lang('Settings')?></a>
          <span class="divider">/</span> 
          <a class="bread-current" href="<?php echo site_url('admin/settings/contact')?>"><?php echo lang('Company contact')?></a>
		</div>

		<div class="clearfix"></div>

    </div>
    <!-- Page heading ends -->



    <!-- Matter -->

    <div class="matter-settings">
    
    <div style="margin-bottom: 8px;" class="tabbable">
      <ul class="nav nav-tabs settings-tabs">
        <li><a href="<?php echo site_url('admin/settings/contact')?>"><?php echo lang('Company contact')?></a></li>
        <li><a href="<?php echo site_url('admin/settings/language')?>"><?php echo lang('Languages')?></a></li>
        <li><a href="<?php echo site_url('admin/settings/template')?>"><?php echo lang('Template')?></a></li>
        <li class="active"><a href="<?php echo site_url('admin/settings/system')?>"><?php echo lang('System settings')?></a></li>
        <?php if(config_db_item('slug_enabled') === TRUE): ?>
        <li><a href="<?php echo site_url('admin/settings/slug')?>"><?php echo lang_check('SEO slugs')?></a></li>
        <?php endif; ?>
      </ul>
    </div>
    
    <div class="container">
          <div class="row">

            <div class="col-md-12">


              <div class="widget wlightblue">
                
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('System settings')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
                  <div class="padd">
                    <?php echo validation_errors()?>   
                    <?php if($this->session->flashdata('error')):?>
                    <p class="label label-important validation"><?php echo $this->session->flashdata('error')?></p>
                    <?php endif;?>            
                    <hr />
                    <!-- Form starts.  -->
                    <?php echo form_open(NULL, array('class' => 'form-horizontal', 'role'=>'form'))?>                              
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('No-reply email')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('noreply', set_value('noreply', isset($settings['noreply'])?$settings['noreply']:''), 'class="form-control" id="inputAddress" placeholder="'.lang('No-reply email').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Zoom index')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('zoom', set_value('zoom', isset($settings['zoom'])?$settings['zoom']:''), 'class="form-control" id="inputAddress" placeholder="'.lang('Zoom index').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('PayPal payment email')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('paypal_email', set_value('paypal_email', isset($settings['paypal_email'])?$settings['paypal_email']:''), 'class="form-control" id="inputPayPalEmail" placeholder="'.lang('PayPal payment email').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Activation price')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('activation_price', set_value('activation_price', isset($settings['activation_price'])?$settings['activation_price']:''), 'class="form-control" id="inputActivationPrice" placeholder="'.lang_check('Activation price').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Featured price')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('featured_price', set_value('featured_price', isset($settings['featured_price'])?$settings['featured_price']:''), 'class="form-control" id="inputFeaturedPrice" placeholder="'.lang_check('Activation price').'"')?>
                                  </div>
                                </div>
                                
                                <?php if(file_exists(APPPATH.'controllers/paymentconsole.php')): ?>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Authorize api login id')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('authorize_api_login_id', set_value('authorize_api_login_id', isset($settings['authorize_api_login_id'])?$settings['authorize_api_login_id']:''), 'class="form-control" id="input_authorize_api_login_id" placeholder="'.lang_check('Authorize api login id').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Authorize api hash secret')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('authorize_api_hash_secret', set_value('authorize_api_hash_secret', isset($settings['authorize_api_hash_secret'])?$settings['authorize_api_hash_secret']:''), 'class="form-control" id="input_authorize_api_hash_secret" placeholder="'.lang_check('Authorize api hash secret').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Authorize api transaction key')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('authorize_api_transaction_key', set_value('authorize_api_transaction_key', isset($settings['authorize_api_transaction_key'])?$settings['authorize_api_transaction_key']:''), 'class="form-control" id="input_authorize_api_transaction_key" placeholder="'.lang_check('Authorize api transaction key').'"')?>
                                  </div>
                                </div>
                                
                                <?php endif; ?>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Default PayPal currency code')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_dropdown('default_currency', $currencies, set_value('default_currency', isset($settings['default_currency'])?$settings['default_currency']:''), 'class="form-control"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Listing expiry days')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('listing_expiry_days', set_value('listing_expiry_days', isset($settings['listing_expiry_days'])?$settings['listing_expiry_days']:''), 'class="form-control" id="inputListingExpiry" placeholder="'.lang('Listing expiry days').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('AdSense 728x90 code')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_textarea('adsense728_90', set_value('adsense728_90', isset($settings['adsense728_90'])?$settings['adsense728_90']:''), 'placeholder="'.lang_check('AdSense 728x90 code').'" rows="3" class="form-control"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('AdSense 160x600 code')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_textarea('adsense160_600', set_value('adsense160_600', isset($settings['adsense160_600'])?$settings['adsense160_600']:''), 'placeholder="'.lang_check('AdSense 160x600 code').'" rows="3" class="form-control"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Withdrawal payment details')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_textarea('withdrawal_details', set_value('withdrawal_details', isset($settings['withdrawal_details'])?$settings['withdrawal_details']:''), 'placeholder="'.lang_check('Withdrawal payment details').'" rows="3" class="form-control"')?>
                                  </div>
                                </div>
                                
                                <?php if(false): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Enable masking')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_checkbox('agent_masking_enabled', '1', set_value('agent_masking_enabled', isset($settings['agent_masking_enabled'])?$settings['agent_masking_enabled']:'0'), 'id="inputEnableMasking"')?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(file_exists(APPPATH.'controllers/admin/reviews.php')): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Enable reviews')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_checkbox('reviews_enabled', '1', set_value('reviews_enabled', isset($settings['reviews_enabled'])?$settings['reviews_enabled']:'0'), 'id="inputEnableReviews"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Enable reviews public visible')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_checkbox('reviews_public_visible_enabled', '1', set_value('reviews_public_visible_enabled', isset($settings['reviews_public_visible_enabled'])?$settings['reviews_public_visible_enabled']:'0'), 'id="inputEnablePublicVisible"')?>
                                  </div>
                                </div>
                                <?php endif; ?>                                
                                
                                <hr />

                                <div class="form-group">
                                  <div class="col-lg-offset-2 col-lg-10">
                                    <?php echo form_submit('submit', lang('Save'), 'class="btn btn-primary"')?>
                                    <a href="<?php echo site_url('admin/settings')?>" class="btn btn-default" type="button"><?php echo lang('Cancel')?></a>
                                  </div>
                                </div>
                       <?php echo form_close()?>
                  </div>
                </div>
                  <div class="widget-foot">
                    <!-- Footer goes here -->
                  </div>
              </div>  

            </div>
          </div>
    </div>
    </div>

	<!-- Matter ends -->

   <!-- Mainbar ends -->	    	
   <div class="clearfix"></div>