<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang('Category')?>
          <!-- page meta -->
          <span class="page-meta"><?php echo empty($showroom->id) ? lang('Add a category') : lang('Edit category').' "' . $showroom->id.'"'?></span>
        </h2>
    
    
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a href="<?php echo site_url('admin/showroom')?>"><?php echo lang_check('Showrooms')?></a>
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/showroom/categories')?>"><?php echo lang_check('Categories')?></a>
    </div>
    
    <div class="clearfix"></div>

</div>

<div class="matter">
        <div class="container">

          <div class="row">

            <div class="col-md-12">

              <div class="widget wgreen">
                
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang_check('Category data')?></div>
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
                                  <label class="col-lg-2 control-label"><?php echo lang_check('Parent')?></label>
                                  <div class="col-lg-10">
                                    <?php echo form_dropdown('parent_id', $showrooms_no_parents, $this->input->post('parent_id') ? $this->input->post('parent_id') : $showroom->parent_id, 'class="form-control"')?>
                                  </div>
                                </div>
                                
                                <hr />
                                <h5><?php echo lang('Translation data')?></h5>
                               <div style="margin-bottom: 0px;" class="tabbable">
                                  <ul class="nav nav-tabs">
                                    <?php $i=0;foreach($this->showroom_m->languages as $key_lang=>$val_lang):$i++;?>
                                    <li class="<?php echo $i==1?'active':''?>"><a data-toggle="tab" href="#<?php echo $key_lang?>"><?php echo $val_lang?></a></li>
                                    <?php endforeach;?>
                                  </ul>
                                  <div style="padding-top: 9px; border-bottom: 1px solid #ddd;" class="tab-content">
                                    <?php $i=0;foreach($this->showroom_m->languages as $key_lang=>$val_lang):$i++;?>
                                    <div id="<?php echo $key_lang?>" class="tab-pane <?php echo $i==1?'active':''?>">
                                        <div class="form-group">
                                          <label class="col-lg-2 control-label"><?php echo lang('Title')?></label>
                                          <div class="col-lg-10">
                                            <?php echo form_input('title_'.$key_lang, set_value('title_'.$key_lang, $showroom->{'title_'.$key_lang}), 'class="form-control copy_to_next" id="inputTitle'.$key_lang.'" placeholder="'.lang('Title').'"')?>
                                          </div>
                                        </div>
                                        <div class="form-group">
                                          <label class="col-lg-2 control-label"><?php echo lang_check('Window Title')?></label>
                                          <div class="col-lg-10">
                                            <?php echo form_input('window_title_'.$key_lang, set_value('window_title_'.$key_lang, $showroom->{'window_title_'.$key_lang}), 'class="form-control" id="inputWindowTitle'.$key_lang.'" placeholder="'.lang_check('Window Title').'"')?>
                                          </div>
                                        </div>
                                        
                                         <div class="form-group">
                                          <label class="col-lg-2 control-label"><?php echo lang('Keywords')?></label>
                                          <div class="col-lg-10">
                                            <?php echo form_input('keywords_'.$key_lang, set_value('keywords_'.$key_lang, $showroom->{'keywords_'.$key_lang}), 'class="form-control" id="inputKeywords'.$key_lang.'" placeholder="'.lang('Keywords').'"')?>
                                          </div>
                                        </div>
                                        
                                        <div class="form-group">
                                          <label class="col-lg-2 control-label"><?php echo lang('Description')?></label>
                                          <div class="col-lg-10">
                                            <?php echo form_textarea('description_'.$key_lang, set_value('description_'.$key_lang, $showroom->{'description_'.$key_lang}), 'placeholder="'.lang('Description').'" rows=4" class="form-control"')?>
                                          </div>
                                        </div>  
                                        
                                        <div class="form-group">
                                          <label class="col-lg-2 control-label"><?php echo lang('Body')?></label>
                                          <div class="col-lg-10">
                                            <?php echo form_textarea('body_'.$key_lang, set_value('body_'.$key_lang, $showroom->{'body_'.$key_lang}), 'placeholder="'.lang('Body').'" rows="10" class="cleditor2 form-control"')?>
                                          </div>
                                        </div>  

                                    </div>
                                    <?php endforeach;?>
                                  </div>
                                </div>
                                <div class="form-group">
                                  <div class="col-lg-offset-2 col-lg-10">
                                    <?php echo form_submit('submit', lang('Save'), 'class="btn btn-primary"')?>
                                    <a href="<?php echo site_url('admin/page')?>" class="btn btn-default" type="button"><?php echo lang('Cancel')?></a>
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

<script>

/* CL Editor */
$(document).ready(function(){
    $(".cleditor2").cleditor({
        width: "auto",
        height: 250,
        docCSSFile: "<?php echo $template_css?>",
        baseHref: '<?php echo base_url('templates/'.$settings['template'])?>/'
    });
});

</script>