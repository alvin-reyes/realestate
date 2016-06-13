<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang('User')?>
          <!-- page meta -->
          <span class="page-meta"><?php echo empty($user->id) ? lang('Add a new user') : lang('Edit user').' "' . $user->name_surname.'"'?></span>
        </h2>
    
    
    <!-- Breadcrumb -->
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/user')?>"><?php echo lang('Users')?></a>
    </div>
    
    <div class="clearfix"></div>

</div>

<div class="matter">
        <div class="container">
        
        <div class="row">
            <div class="col-md-12"> 
                <?php if(!empty($user->id) && file_exists(APPPATH.'controllers/admin/reviews.php') && check_acl('reviews')): ?>
                    <?php echo anchor('admin/reviews/index/0/'.$user->id, '<i class="icon-star"></i>&nbsp;&nbsp;'.lang('Reviews'), 'class="btn btn-primary pull-right" style="margin-left:5px;"')?>
                <?php endif; ?>
            
                <?php if(check_acl('user/all_deactivate') && !empty($user->id)):?>
                <?php echo anchor('admin/user/all_deactivate/'.$user->id, '<i class="icon-remove"></i>&nbsp;&nbsp;'.lang_check('Deactivate all estates'), 'class="btn btn-danger pull-right"')?>
                <?php echo anchor('admin/user/all_activate/'.$user->id, '<i class="icon-ok"></i>&nbsp;&nbsp;'.lang_check('Activate all estates'), 'class="btn btn-success pull-right"')?>
                <?php endif;?>                
            </div>
        </div>
        
          <div class="row">

            <div class="col-md-12">


              <div class="widget wgreen">
                
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('User data')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
                  <div class="padd">
                    <?php echo validation_errors()?>
                    <?php if($this->session->flashdata('message')):?>
                    <?php echo $this->session->flashdata('message')?>
                    <?php endif;?>
                    <?php if($this->session->flashdata('error')):?>
                    <p class="label label-important validation"><?php echo $this->session->flashdata('error')?></p>
                    <?php endif;?>     
                    <hr />
                    <!-- Form starts.  -->
                    <?php echo form_open(NULL, array('class' => 'form-horizontal', 'role'=>'form'))?>                              
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Name and surname')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('name_surname', set_value('name_surname', $user->name_surname), 'class="form-control" id="inputNameSurname" placeholder="'.lang('Name and surname').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Username')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('username', set_value('username', $user->username), 'class="form-control" id="inputUsername" placeholder="'.lang('Username').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Password')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_password('password', set_value('password', ''), 'class="form-control" id="inputPassword" placeholder="'.lang('Password').'" autocomplete="off"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('PasswordConfirm')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_password('password_confirm', set_value('password_confirm', ''), 'class="form-control" id="inputPasswordConfirm" placeholder="'.lang('PasswordConfirm').'" autocomplete="off"')?>
                                  </div>
                                </div>
                                
                                <?php if($this->session->userdata('type') == 'ADMIN'): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Type')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_dropdown('type', $this->user_m->user_types, set_value('type', $user->type), 'class="form-control"');?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($this->session->userdata('type') == 'ADMIN' && file_exists(APPPATH.'controllers/admin/expert.php')): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Expert category')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_dropdown('qa_id', $expert_categories, set_value('qa_id', $user->qa_id), 'class="form-control"');?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($this->session->userdata('type') == 'ADMIN' && file_exists(APPPATH.'controllers/admin/packages.php')): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Package')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_dropdown('package_id', $packages, set_value('package_id', $user->package_id), 'class="form-control"');?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Package expire date')?></label>
                                  <div class="col-lg-10">
                                  <div class="input-append" id="datetimepicker1">
                                    <?php echo form_input('package_last_payment', $this->input->post('package_last_payment') ? $this->input->post('package_last_payment') : $user->package_last_payment, 'class="picker" data-format="yyyy-MM-dd hh:mm:ss"'); ?>
                                    <span class="add-on">
                                      &nbsp;<i data-date-icon="icon-calendar" data-time-icon="icon-time" class="icon-calendar">
                                      </i>
                                    </span>
                                  </div>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($this->session->userdata('type') != 'AGENT_LIMITED'): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Address')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_textarea('address', set_value('address', $user->address), 'placeholder="'.lang('Address').'" rows="3" class="form-control"')?>
                                  </div>
                                </div>       
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Description')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_textarea('description', set_value('description', $user->description), 'placeholder="'.lang('Description').'" rows="3" class="form-control"')?>
                                  </div>
                                </div>      
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Phone')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('phone', set_value('phone', $user->phone), 'class="form-control" id="inputPhone" placeholder="'.lang('Phone').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Mail')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('mail', set_value('mail', $user->mail), 'class="form-control" id="inputMail" placeholder="'.lang('Mail').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Language')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_dropdown('language', $this->language_m->backend_languages, set_value('language', $user->language), 'class="form-control"');?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($this->session->userdata('type') == 'ADMIN'): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang('Activated')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_checkbox('activated', '1', set_value('activated', $user->activated), 'id="inputActivated"')?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($this->session->userdata('type') != 'AGENT_LIMITED'): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Facebook ID')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_input('facebook_id', set_value('facebook_id', $user->facebook_id), 'class="form-control" id="inputMail" placeholder="'.lang_check('Facebook ID').'"')?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($this->session->userdata('type') == 'ADMIN' && config_db_item('phone_verification_enabled') === TRUE && file_exists(APPPATH.'libraries/Clickatellapi.php')): ?>
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Phone verified')?></label>
                                  <div class="col-lg-10">
                                  <?php echo form_checkbox('phone_verified', '1', set_value('phone_verified', $user->phone_verified), 'id="inputPhoneVerified"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Mail verified')?></label>
                                  <div class="col-lg-10">
                                  <?php echo form_checkbox('mail_verified', '1', set_value('mail_verified', $user->mail_verified), 'id="inputMailVerified"')?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                
                                    <hr />
                                <div class="form-group">
                                  <div class="col-lg-offset-2 col-lg-10">
                                    <?php echo form_submit('submit', lang('Save'), 'class="btn btn-primary"')?>
                                    <a href="<?php echo site_url('admin/user')?>" class="btn btn-default" type="button"><?php echo lang('Cancel')?></a>
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
            
        <div class="col-md-12">

              <div class="widget worange">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Images')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
                  <div class="padd">

<?php if(!isset($user->id)):?>
<span class="label label-danger"><?php echo lang('After saving, you can add files and images');?></span>
<?php else:?>
<div id="page-files-<?php echo $user->id?>" rel="user_m">
    <!-- The file upload form used as target for the file upload widget -->
    <form class="fileupload" action="<?php echo site_url('files/upload_user/'.$user->id);?>" method="POST" enctype="multipart/form-data">
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript><input type="hidden" name="redirect" value="<?php echo site_url('admin/user/edit/'.$user->id);?>"></noscript>
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="fileupload-buttonbar">
            <div class="span7 col-md-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="icon-plus icon-white"></i>
                    <span><?php echo lang('add_files...')?></span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="icon-ban-circle icon-white"></i>
                    <span><?php echo lang('cancel_upload')?></span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <i class="icon-trash icon-white"></i>
                    <span><?php echo lang('delete_selected')?></span>
                </button>
                <input type="checkbox" class="toggle" />
            </div>
            <!-- The global progress information -->
            <div class="span5 col-md-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="bar" style="width:0%;"></div>
                </div>
                <!-- The extended global progress information -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The loading indicator is shown during file processing -->
        <div class="fileupload-loading"></div>
        <br />
        <!-- The table listing the files available for upload/download -->
        <!--<table role="presentation" class="table table-striped">
        <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">-->

          <div role="presentation" class="fieldset-content">
            <ul class="files files-list" data-toggle="modal-gallery" data-target="#modal-gallery">      
<?php if(isset($files[$user->repository_id]))foreach($files[$user->repository_id] as $file ):?>
            <li class="img-rounded template-download fade in">
                <div class="preview">
                    <img class="img-rounded" alt="<?php echo $file->filename?>" data-src="<?php echo $file->thumbnail_url?>" src="<?php echo $file->thumbnail_url?>">
                </div>
                <div class="filename">
                    <code><?php echo character_hard_limiter($file->filename, 20)?></code>
                </div>
                <div class="options-container">
                    <?php if($file->zoom_enabled):?>
                    <a data-gallery="gallery" href="<?php echo $file->download_url?>" title="<?php echo $file->filename?>" download="<?php echo $file->filename?>" class="zoom-button btn btn-xs btn-success"><i class="icon-search icon-white"></i></a>                  
                    <?php else:?>
                    <a target="_blank" href="<?php echo $file->download_url?>" title="<?php echo $file->filename?>" download="<?php echo $file->filename?>" class="btn btn-xs btn-success"><i class="icon-search icon-white"></i></a>
                    <?php endif;?>
                    <span class="delete">
                        <button class="btn btn-xs btn-danger" data-type="POST" data-url="<?php echo $file->delete_url?>"><i class="icon-trash icon-white"></i></button>
                        <input type="checkbox" value="1" name="delete">
                    </span>
                </div>
            </li>
<?php endforeach;?>
            </ul>
            <br style="clear:both;"/>
          </div>
    </form>

</div>
<?php endif;?>

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