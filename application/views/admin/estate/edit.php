<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang('Estate')?>
          <!-- page meta -->
          <span class="page-meta"><?php echo empty($estate->id) ? lang('Add a estate') : lang('Edit estate').' "' . $estate->id.'"'?></span>
        </h2>
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/estate')?>"><?php echo lang('Estates')?></a>
    </div>
    
    <div class="clearfix"></div>

</div>

<div class="matter">
        <div class="container">
        
            <div class="row">
                <div class="col-md-12" style="text-align:right;"> 
                <?php if(!empty($estate->id) && file_exists(APPPATH.'controllers/admin/reviews.php') && check_acl('reviews')): ?>
                    <?php echo anchor('admin/reviews/index/'.$estate->id, '<i class="icon-star"></i>&nbsp;&nbsp;'.lang('Reviews'), 'class="btn btn-primary"')?>
                <?php endif; ?>
                </div>
            </div>

          <div class="row">

            <div class="col-md-8">
              <div class="widget wlightblue">
                
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Estate data')?></div>
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
                    <?php echo form_open(NULL, array('class' => 'form-horizontal form-estate', 'role'=>'form'))?>                              
                                
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Address')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_input('address', set_value('address', $estate->address), 'class="form-control" id="inputAddress" placeholder="'.lang('Address').'"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Gps')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_input('gps', set_value('gps', $estate->gps), 'class="form-control" id="inputGps" placeholder="'.lang('Gps').'" readonly')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('DateTime')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_input('date', set_value('date', $estate->date), 'class="form-control" id="inputDate" placeholder="'.lang('DateTime').'"')?>
                                  </div>
                                </div>
                                
                                <?php if($this->session->userdata('type') == 'ADMIN' || $this->session->userdata('type') == 'AGENT_ADMIN'):?>
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Agent')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_dropdown('agent', $available_agent, set_value('agent', $estate->agent), 'class="form-control" id="inputAgent" placeholder="'.lang('Agent').'"')?>
                                  </div>
                                </div>
                                <?php endif;?>
                                
                                <?php if($this->session->userdata('type') == 'AGENT_LIMITED'):?>
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Featured')?></label>
                                  <div class="col-lg-9">
                                  <?php
                                  $status = '<i class="icon-remove"></i>';
                                  if(set_value('is_featured', $estate->is_featured) == '1')
                                  {
                                       $status = '<i class="icon-ok"></i>';
                                  }
                                  echo $status;
                                  ?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Activated')?></label>
                                  <div class="col-lg-9">
                                  <?php
                                  $status = '<i class="icon-remove"></i>';
                                  if(set_value('is_activated', $estate->is_activated) == '1')
                                  {
                                       $status = '<i class="icon-ok"></i>';
                                  }
                                  echo $status;
                                  ?>
                                  </div>
                                </div>
                                <?php else:?>
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Featured')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_checkbox('is_featured', '1', set_value('is_featured', $estate->is_featured), 'id="inputFeatured"')?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Activated')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_checkbox('is_activated', '1', set_value('is_activated', $estate->is_activated), 'id="inputActivated"')?>
                                  </div>
                                </div>
                                <?php endif;?>
                                
                                <hr />
                                <h5><?php echo lang('Translation data')?></h5>
                                <div style="margin-bottom: 0px;" class="tabbable">
                                  <ul class="nav nav-tabs language_tabs">
                                    <?php $i=0;foreach($this->option_m->languages as $key=>$val):$i++;?>
                                    <li class="<?php echo $i==1?'active':''?> lang"><a data-toggle="tab" href="#<?php echo $key?>"><?php echo $val?></a></li>
                                    <?php endforeach;?>
                                    
                                    <?php if(count($this->option_m->languages) > 1): ?>
                                    <li class="pull-right"><a href="#" id="copy-lang" class="btn btn-default" type="button"><?php echo lang_check('Copy to other languages')?></a></li>
                                    <li class="pull-right"><a href="#" id="translate-lang" rel="<?php echo site_url('api/translate/');  ?>" class="btn btn-default" type="button"><?php echo lang_check('Translate to other languages')?></a></li>
                                    <?php endif; ?>
                                  </ul>
                                  <div style="padding-top: 9px; border-bottom: 1px solid #ddd;" class="tab-content">
                                    <?php $i=0;foreach($this->option_m->languages as $key=>$val):$i++;?>
                                    <div id="<?php echo $key?>" class="tab-pane <?php echo $i==1?'active':''?>">
                                    
                                        <?php if(config_db_item('slug_enabled') === TRUE): ?>
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang_check('URI slug')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_input('slug_'.$key, set_value('slug_'.$key, $estate->{'slug_'.$key}), 'class="form-control" id="inputOption_'.$key.'_slug" placeholder="'.lang_check('URI slug').'"')?>
                                          </div>
                                        </div>
                                        <?php endif; ?>
                                    
                                        <?php foreach($options as $key_option=>$val_option):?>
                                        
                                        <?php
                                        $required_text = '';
                                        $required_notice = '';
                                        if($val_option->is_required == 1)
                                        {
                                            $required_text = 'required';
                                            $required_notice = '*';
                                        }
                                        
                                        $max_length_text = '';
                                        if($val_option->max_length > 0)
                                        {
                                            $max_length_text = 'maxlength="'.$val_option->max_length.'"';
                                        }
                                        
                                        ?>
                                        
                                        <?php if($val_option->type == 'CATEGORY'):?>
                                        <hr />
                                        <h5><?php echo $val_option->option?> <span class="checkbox-visible"><?php echo form_checkbox('option'.$val_option->id.'_'.$key, 'true', set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:''), 'id="inputOption_'.$key.'_'.$val_option->id.'"')?> <?php echo lang_check('Hidden on preview page'); ?></span></h5>
                                        <hr />
                                        <?php elseif($val_option->type == 'INPUTBOX' || $val_option->type == 'DECIMAL' || $val_option->type == 'INTEGER'):?>
                                            <div class="form-group <?php echo (!$val_option->is_frontend && $this->session->userdata('type') == 'AGENT_LIMITED'?' hidden':'') ?>">
                                              <label class="col-lg-3 control-label"><?php echo $required_notice.$val_option->option?></label>
                                              <div class="<?php echo empty($options_lang[$key][$key_option]->prefix)&&empty($options_lang[$key][$key_option]->suffix)?'col-lg-9':'col-lg-6'; ?>">
                                                <?php echo form_input('option'.$val_option->id.'_'.$key, set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:''), 'class="form-control '.$val_option->type.'" id="inputOption_'.$key.'_'.$val_option->id.'" placeholder="'.$val_option->option.'" '.$required_text.' '.$max_length_text)?>
                                              </div>
                                              <?php if(!empty($options_lang[$key][$key_option]->prefix) || !empty($options_lang[$key][$key_option]->suffix)): ?>
                                              <div class="col-lg-3">
                                                <?php echo $options_lang[$key][$key_option]->prefix.$options_lang[$key][$key_option]->suffix?>
                                              </div>
                                              <?php endif; ?>
                                            </div>
                                        <?php elseif($val_option->type == 'DROPDOWN'):?>
                                            <div class="form-group <?php echo (!$val_option->is_frontend && $this->session->userdata('type') == 'AGENT_LIMITED'?' hidden':'') ?>">
                                              <label class="col-lg-3 control-label"><?php echo $required_notice.$val_option->option?></label>
                                              <div class="col-lg-9">
                                                <?php
                                                if(isset($options_lang[$key][$key_option]))
                                                    $drop_options = array_combine(explode(',',check_combine_set(isset($options_lang[$key])?$options_lang[$key][$key_option]->values:'', $val_option->values, '')),explode(',',check_combine_set($val_option->values, isset($options_lang[$key])?$options_lang[$key][$key_option]->values:'', '')));
                                                else
                                                    $drop_options = array();

                                                $drop_selected = set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:'');
                                                
                                                echo form_dropdown('option'.$val_option->id.'_'.$key, $drop_options, $drop_selected, 'class="form-control" id="inputOption_'.$key.'_'.$val_option->id.'" placeholder="'.$val_option->option.'" '.$required_text)
                                                
                                                ?>
                                                <?php //=form_dropdown('option'.$val_option->id.'_'.$key, explode(',', $options_lang[$key][$key_option]->values), set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:''), 'class="form-control" id="inputOption_'.$val_option->id.'" placeholder="'.$val_option->option.'"')?>
                                              </div>
                                            </div>
                                        <?php elseif($val_option->type == 'TEXTAREA'):?>
                                            <div class="form-group <?php echo (!$val_option->is_frontend && $this->session->userdata('type') == 'AGENT_LIMITED'?' hidden':'') ?>">
                                              <label class="col-lg-3 control-label"><?php echo $required_notice.$val_option->option?></label>
                                              <div class="col-lg-9">
                                                <?php echo form_textarea('option'.$val_option->id.'_'.$key, set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:''), 'class="cleditor form-control" id="inputOption_'.$key.'_'.$val_option->id.'" placeholder="'.$val_option->option.'" '.$required_text)?>
                                              </div>
                                            </div>
                                        <?php elseif($val_option->type == 'TREE' && config_item('tree_field_enabled') === TRUE):?>
                                            <div class="form-group TREE-GENERATOR">
                                              <label class="col-lg-3 control-label">
                                              <?php echo $val_option->option?>
                                              <div class="ajax_loading"> </div>
                                              </label>
                                              <div class="col-lg-9">
                                                <?php
                                                $drop_options = $this->treefield_m->get_level_values($key, $key_option);
                                                $drop_selected = array();
                                                
                                                echo '<div class="field-row">';
                                                echo form_dropdown('option'.$val_option->id.'_'.$key.'_level_0', $drop_options, $drop_selected, 'class="form-control" id="inputOption_'.$key.'_'.$val_option->id.'_level_0'.'" placeholder="'.$val_option->option.'"');
                                                echo '</div>';
                                                
                                                
                                                $levels_num = $this->treefield_m->get_max_level($key_option);
                                                
                                                if($levels_num>0)
                                                for($ti=1;$ti<=$levels_num;$ti++)
                                                {
                                                    echo '<div class="field-row">';
                                                    echo form_dropdown('option'.$val_option->id.'_'.$key.'_level_'.$ti, array(''=>lang_check('Please select parent')), array(), 'class="form-control" id="inputOption_'.$key.'_'.$val_option->id.'_level_'.$ti.'" placeholder="'.$val_option->option.'"');
                                                    echo '</div>';
                                                }

                                                ?>
                                                <div class="field-row hidden">
                                                <?php echo form_input('option'.$val_option->id.'_'.$key, set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:''), 'class="form-control tree-input-value" id="inputOption_'.$key.'_'.$val_option->id.'" placeholder="'.$val_option->option.'"')?>
                                                </div>
                                              </div>
                                            </div>
                                        <?php elseif($val_option->type == 'UPLOAD'):?>
                                            <div class="form-group UPLOAD-FIELD-TYPE">
                                              <label class="col-lg-3 control-label">
                                              <?php echo $val_option->option?>
                                              <div class="ajax_loading"> </div>
                                              </label>
                                              <div class="col-lg-9">
<div class="field-row hidden">
<?php echo form_input('option'.$val_option->id.'_'.$key, set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:'SKIP_ON_EMPTY'), 'class="form-control skip-input" id="inputOption_'.$key.'_'.$val_option->id.'" placeholder="'.$val_option->option.'"')?>
</div>
<?php //if(empty($estate->id) || !isset($estate->{'option'.$val_option->id.'_'.$key})): ?>
<?php if( empty($estate->id) ): ?>
<span class="label label-danger"><?php echo lang('After saving, you can add files and images');?></span>
<?php else: ?>
<!-- Button to select & upload files -->
<span class="btn btn-success fileinput-button">
    <span>Select files...</span>
    <!-- The file input field used as target for the file upload widget -->
    <input id="fileupload_<?php echo $val_option->id.'_'.$key; ?>" class="FILE_UPLOAD file_<?php echo $val_option->id.'_'.$key; ?>" type="file" name="files[]" multiple>
</span><br style="clear: both;" />
<!-- The global progress bar -->
<p>Upload progress</p>
<div id="progress_<?php echo $val_option->id.'_'.$key; ?>" class="progress progress-success progress-striped">
    <div class="bar"></div>
</div>
<!-- The list of files uploaded -->
<p>Files uploaded:</p>
<ul id="files_<?php echo $val_option->id.'_'.$key; ?>">
<?php 
if(isset($estate->{'option'.$val_option->id.'_'.$key})){
    $rep_id = $estate->{'option'.$val_option->id.'_'.$key};
    
    //Fetch repository
    $file_rep = $this->file_m->get_by(array('repository_id'=>$rep_id));
    if(count($file_rep)) foreach($file_rep as $file_r)
    {
        $delete_url = site_url_q('files/upload/rep_'.$file_r->repository_id, '_method=DELETE&amp;file='.rawurlencode($file_r->filename));
        
        echo "<li><a target=\"_blank\" href=\"".base_url('files/'.$file_r->filename)."\">$file_r->filename</a>".
             '&nbsp;&nbsp;<button class="btn btn-xs btn-danger" data-type="POST" data-url='.$delete_url.'><i class="icon-trash icon-white"></i></button></li>';
    }
}
?>
</ul>

<!-- JavaScript used to call the fileupload widget to upload files -->
<script language="javascript">
// When the server is ready...
$( document ).ready(function() {
    
    // Define the url to send the image data to
    var url_<?php echo $val_option->id.'_'.$key; ?> = '<?php echo site_url('files/upload_field/'.$estate->id.'_'.$val_option->id.'_'.$key);?>';
    
    // Call the fileupload widget and set some parameters
    $('#fileupload_<?php echo $val_option->id.'_'.$key; ?>').fileupload({
        url: url_<?php echo $val_option->id.'_'.$key; ?>,
        autoUpload: true,
        dropZone: $('#fileupload_<?php echo $val_option->id.'_'.$key; ?>'),
        dataType: 'json',
        done: function (e, data) {
            // Add each uploaded file name to the #files list
            $.each(data.result.files, function (index, file) {
                if(!file.hasOwnProperty("error"))
                {
                    $('#files_<?php echo $val_option->id.'_'.$key; ?>').append('<li><a href="'+file.url+'" target="_blank">'+file.name+'</a>&nbsp;&nbsp;<button class="btn btn-xs btn-danger" data-type="POST" data-url='+file.delete_url+'><i class="icon-trash icon-white"></i></button></li>');
                    added=true;
                }
                else
                {
                    ShowStatus.show(file.error);
                }

            });
            
            //console.log(data.result.repository_id);
            //console.log('<?php echo '#inputOption_'.$key.'_'.$val_option->id; ?>');
            $('<?php echo '#inputOption_'.$key.'_'.$val_option->id; ?>').attr('value', data.result.repository_id);
            
            reset_events_<?php echo $val_option->id.'_'.$key; ?>();
        },
        progressall: function (e, data) {
            // Update the progress bar while files are being uploaded
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress_<?php echo $val_option->id.'_'.$key; ?> .bar').css(
                'width',
                progress + '%'
            );
        }
    });
    
    reset_events_<?php echo $val_option->id.'_'.$key; ?>();
});

function reset_events_<?php echo $val_option->id.'_'.$key; ?>(){
    $("#files_<?php echo $val_option->id.'_'.$key; ?> li button").unbind();
    $("#files_<?php echo $val_option->id.'_'.$key; ?> li button.btn-danger").click(function(){
        var image_el = $(this);
        
        $.post($(this).attr('data-url'), function( data ) {
            var obj = jQuery.parseJSON(data);
            
            if(obj.success)
            {
                image_el.parent().remove();
            }
            else
            {
                ShowStatus.show('<?php echo lang_check('Unsuccessful, possible permission problems or file not exists'); ?>');
            }
            //console.log("Data Loaded: " + obj.success );
        });
        
        return false;
    });
}

</script>
<?php endif; ?>
                                              </div>
                                            </div>
                                        <?php elseif($val_option->type == 'CHECKBOX'):?>
                                            <div class="form-group type_checkbox <?php echo (!$val_option->is_frontend && $this->session->userdata('type') == 'AGENT_LIMITED'?' hidden':'') ?>">
                                              <label class="col-lg-3 control-label"><?php echo $required_notice.$val_option->option?></label>
                                              <div class="col-lg-9">
                                                <?php echo form_checkbox('option'.$val_option->id.'_'.$key, 'true', set_value('option'.$val_option->id.'_'.$key, isset($estate->{'option'.$val_option->id.'_'.$key})?$estate->{'option'.$val_option->id.'_'.$key}:''), 'id="inputOption_'.$key.'_'.$val_option->id.'" class="valid_parent" '.$required_text)?>
                                                <?php
                                                    if(file_exists(FCPATH.'templates/'.$settings['template'].'/assets/img/icons/option_id/'.$val_option->id.'.png'))
                                                    {
                                                        echo '<img class="results-icon" src="'.base_url('templates/'.$settings['template'].'/assets/img/icons/option_id/'.$val_option->id.'.png').'" alt="'.$val_option->option.'"/>';
                                                    }
                                                ?>
                                              </div>
                                            </div>
                                        <?php endif;?>
                                        <?php endforeach;?>
                                    </div>
                                    <?php endforeach;?>
                                  </div>
                                </div>
                                
                                <div class="form-group">
                                  <div class="col-lg-offset-3 col-lg-9">
                                    <?php echo form_submit('submit', lang('Save'), 'class="btn btn-primary"')?>
                                    <a href="<?php echo site_url('admin/estate')?>" class="btn btn-default" type="button"><?php echo lang('Cancel')?></a>
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
            
            
            <div class="col-md-4">
              <div class="widget wblue">
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Location')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
                  <div class="gmap" id="mapsAddress">

                  </div>
                </div>


              </div> 
            </div>

          </div>
          
          <div class="row">
        <div class="col-md-12">

              <div class="widget worange">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang('Files')?></div>
                  <div class="widget-icons pull-right">
                    <a class="wminimize" href="#"><i class="icon-chevron-up"></i></a> 
                  </div>
                  <div class="clearfix"></div>
                </div>

                <div class="widget-content">
                  <div class="padd">

<?php if(!isset($estate->id)):?>
<span class="label label-danger"><?php echo lang('After saving, you can add files and images');?></span>
<?php else:?>
<div id="page-files-<?php echo $estate->id?>" rel="estate_m">
    <!-- The file upload form used as target for the file upload widget -->
    <form class="fileupload" action="<?php echo site_url('files/upload_estate/'.$estate->id);?>" method="POST" enctype="multipart/form-data">
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript><input type="hidden" name="redirect" value="<?php echo site_url('admin/estate/edit/'.$estate->id);?>"></noscript>
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
<?php if(isset($files[$estate->repository_id]))foreach($files[$estate->repository_id] as $file ):?>
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
          
<script language="javascript">
    
    /* [START] TreeField */
    
    $(function() {
        $(".TREE-GENERATOR select").change(function(){
            var s_value = $(this).val();
            var s_name_splited = $(this).attr('name').split("_"); 
            var s_level = parseInt(s_name_splited[3]);
            var s_lang_id = s_name_splited[1];
            var s_field_id = s_name_splited[0].substr(6);
            // console.log(s_value); console.log(s_level); console.log(s_field_id);
            
            load_by_field($(this));
            
            // Reset child selection and value generator
            var generated_val = '';
            $(this).parent().parent()
            .find('select').each(function(index){
                // console.log($(this).attr('name'));
                if(index > s_level)
                {
                    $(this).html('<option value=""><?php echo lang_check('No values found'); ?></option>');
                    $(this).val('');
                }
                else
                    generated_val+=$(this).find("option:selected").text()+" - ";
            });
            //console.log(generated_val);
            $("#inputOption_"+s_lang_id+"_"+s_field_id).val(generated_val);

        });
        
        // Autoload selects
        $(".TREE-GENERATOR input.tree-input-value").each(function(index_1){
            var s_values_splited = ($(this).val()+" ").split(" - "); 
//            $.each(s_values_splited, function( index, value ) {
//                alert( index + ": " + value );
//            });
            if(s_values_splited[0] != '')
            {
                var first_select = $(this).parent().parent().find('select:first');
                first_select.find('option').filter(function () { return $(this).html() == s_values_splited[0]; }).attr('selected', 'selected');

                load_by_field(first_select, true, s_values_splited);
            }
            
            //console.log('value: '+s_values_splited[0]);
        });

    });
    
    function load_by_field(field_element, autoselect_next, s_values_splited)
    {
        if (typeof autoselect_next === 'undefined') autoselect_next = false;
        if (typeof s_values_splited === 'undefined') s_values_splited = [];

        var s_value = field_element.val();
        var s_name_splited = field_element.attr('name').split("_"); 
        var s_level = parseInt(s_name_splited[3]);
        var s_lang_id = s_name_splited[1];
        var s_field_id = s_name_splited[0].substr(6);
        // console.log(s_value); console.log(s_level); console.log(s_field_id);
        
        // Load values for next select
        var ajax_indicator = field_element.parent().parent().parent().find('.ajax_loading');
        var select_element = $("select[name=option"+s_field_id+"_"+s_lang_id+"_level_"+parseInt(s_level+1)+"]");
        if(select_element.length > 0 && s_value != '')
        {
            ajax_indicator.css('display', 'block');
            $.getJSON( "<?php echo site_url('privateapi/get_level_values_select'); ?>/"+s_lang_id+"/"+s_field_id+"/"+s_value+"/"+parseInt(s_level+1), function( data ) {
                //console.log(data.generate_select);
                //console.log("select[name=option"+s_field_id+"_"+s_lang_id+"_level_"+parseInt(s_level+1)+"]");
                ajax_indicator.css('display', 'none');
                
                select_element.html(data.generate_select);
                
                if(autoselect_next)
                {
                    if(s_values_splited[s_level+1] != '')
                    {
                        select_element.find('option').filter(function () { return $(this).html() == s_values_splited[s_level+1]; }).attr('selected', 'selected');
                        load_by_field(select_element, true, s_values_splited);
                    }
                }
            });
        }
    }
    
    function load_and_select_index(field_element, field_select_id, field_parent_select_id)
    {
        var s_name_splited = field_element.attr('name').split("_"); 
        var s_level = parseInt(s_name_splited[3]);
        var s_lang_id = s_name_splited[1];
        var s_field_id = s_name_splited[0].substr(6);
        
        // Load values for current select
        var ajax_indicator = field_element.parent().parent().parent().find('.ajax_loading');
        if(s_level == 0)$("#inputOption_"+s_lang_id+"_"+s_field_id).attr('value', '');

        ajax_indicator.css('display', 'block');
        $.getJSON( "<?php echo site_url('privateapi/get_level_values_select'); ?>/"+s_lang_id+"/"+s_field_id+"/"+field_parent_select_id+"/"+parseInt(s_level), function( data ) {
            ajax_indicator.css('display', 'none');
            
            field_element.html(data.generate_select);
            //console.log(field_select_id);
            if(isNumber(field_select_id))
                field_element.val(field_select_id);
            else
                field_element.val('');
            
            var generated_val = '';
            field_element.parent().parent()
            .find('select').each(function(index){
                if($(this).val() != '' && $(this).val() != null)
                    generated_val+=$(this).find("option:selected").text()+" - ";
            });

            if(generated_val.length > $("#inputOption_"+s_lang_id+"_"+s_field_id).val().length)
                $("#inputOption_"+s_lang_id+"_"+s_field_id).val(generated_val);
        });

    }
    
    function isNumber(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    }
    
    /* [END] TreeField */
    
    /* [START] NumericFields */
    
    $(function() {
        $('input.DECIMAL').number( true, 2 );
        $('input.INTEGER').number( true, 0 );
    });

    /* [END] NumericFields */
    
    /* [START] ValidateFields */
    
    $(function() {
        $('form.form-estate').h5Validate();
    });
    
    /* [END] ValidateFields */
    
    <?php if(isset($package_num_amenities_limit)): ?>
    $(document).ready(function(){
        console.log('loaded');
        $('.form-group input[type=checkbox]').change(function(event){
            var selected_checkboxes = $('.tab-pane.active .form-group input[type=checkbox]:checked').length;
            console.log('changed');
            console.log(selected_checkboxes);
            if(selected_checkboxes > <?php echo $package_num_amenities_limit; ?>)
            {
                $(this).prop('checked', false);
                ShowStatus.show('<?php echo lang_check('Limitation by package'); ?>: '+'<?php echo $package_num_amenities_limit; ?>');
            }
        });
    
    });
    <?php endif; ?>

</script>

