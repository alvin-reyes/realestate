<div class="page-head">
    <!-- Page heading -->
      <h2 class="pull-left"><?php echo lang('TreeField values')?>
          <!-- page meta -->
          <span class="page-meta"><?php echo empty($option->id) ? lang('Add a TreeField') : lang('Edit TreeField').' #' . $option->id.''?></span>
        </h2>
    
    
    <!-- Breadcrumb -->
    <div class="bread-crumb pull-right">
      <a href="<?php echo site_url('admin')?>"><i class="icon-home"></i> <?php echo lang('Home')?></a> 
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/estate')?>"><?php echo lang('Estates')?></a>
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/estate/options')?>"><?php echo lang('Fields')?></a>
      <!-- Divider -->
      <span class="divider">/</span> 
      <a class="bread-current" href="<?php echo site_url('admin/estate/edit_option/'.$option->id)?>"><?php echo lang('Field').' #'.$option->id?></a>
    </div>
    
    <div class="clearfix"></div>

</div>

<div class="matter">
        <div class="container" id="edit-form">

        <div class="row">
            <div class="col-md-12"> 
                <a href="<?php echo site_url('admin/treefield/edit/'.$option->id).'#edit-form'?>" class="btn btn-primary" type="button"><i class="icon-plus"></i>&nbsp;&nbsp;<?php echo lang('Add new')?></a>                
            </div>
        </div>

          <div class="row">

            <div class="col-md-12">


              <div class="widget wlightblue">
                
                <div class="widget-head">
                  <div class="pull-left"><?php echo lang_check('TreeField value data')?></div>
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
                                  <label class="col-lg-3 control-label"><?php echo lang('Parent')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_dropdown('parent_id', $treefield_no_parents, $this->input->post('parent_id') ? $this->input->post('parent_id') : $treefield->parent_id, 'class="form-control"')?>
                                  </div>
                                </div>
                                <?php if(!empty($treefield->id)): ?>
                                <div class="form-group">
                                  <label class="col-lg-3 control-label"><?php echo lang('Template')?></label>
                                  <div class="col-lg-9">
                                    <?php echo form_dropdown('template', 
                                                             $templates_trefield, 
                                                             $this->input->post('template') ? $this->input->post('template') : $treefield->template, 
                                                             'class="form-control"'); ?>
                                  </div>
                                </div>
                                <?php endif; ?>
                                <hr />
                                <h5><?php echo lang('Translation data')?></h5>
                                <div style="margin-bottom: 18px;" class="tabbable">
                                  <ul class="nav nav-tabs">
                                    <?php $i=0;foreach($this->option_m->languages as $key=>$val):$i++;?>
                                    <li class="<?php echo $i==1?'active':''?>"><a data-toggle="tab" href="#<?php echo $key?>"><?php echo $val?></a></li>
                                    <?php endforeach;?>
                                  </ul>
                                  <div style="padding-top: 9px; border-bottom: 1px solid #ddd;" class="tab-content">
                                    <?php $i=0;foreach($this->option_m->languages as $key=>$val):$i++;?>
                                    <div id="<?php echo $key?>" class="tab-pane <?php echo $i==1?'active':''?>">
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang_check('Value')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_input('value_'.$key, set_value('value_'.$key, $treefield->{"value_$key"}), 'class="form-control" id="inputValue_'.$key.'" placeholder="'.lang_check('Value').'"')?>
                                            <?php if(empty($option->id)): ?>
                                            <p class="help-block"><?php echo lang_check('You can also add multiple values (without spaces) "test1,test2" when adding.'); ?></p>
                                            <?php endif; ?>
                                          </div>
                                        </div>
                                    
                                    <?php if(!empty($treefield->id)): ?>
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang_check('Address')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_input('address_'.$key, set_value('address_'.$key, $treefield->{'address_'.$key}), 'class="form-control" id="inputOption_'.$key.'_0" placeholder="'.lang_check('Address').'"')?>
                                          </div>
                                        </div>
                                    
                                    <div class="form-group">
                                      <label class="col-lg-3 control-label"><?php echo lang_check('Page Title')?></label>
                                      <div class="col-lg-9">
                                        <?php echo form_input('title_'.$key, set_value('title_'.$key, $treefield->{'title_'.$key}), 'class="form-control copy_to_next" id="inputOption_'.$key.'_1" placeholder="'.lang_check('Page Title').'"')?>
                                      </div>
                                    </div>
                                    
                                    <div class="form-group">
                                      <label class="col-lg-3 control-label"><?php echo lang_check('Custom path title')?></label>
                                      <div class="col-lg-9">
                                        <?php echo form_input('path_title_'.$key, set_value('path_title_'.$key, $treefield->{'path_title_'.$key}), 'class="form-control" id="inputOption_'.$key.'_2" placeholder="'.lang_check('Custom path title').'"')?>
                                      </div>
                                    </div>
                                    
                                        <?php if(config_db_item('slug_enabled') === TRUE): ?>
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang_check('URI slug')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_input('slug_'.$key, set_value('slug_'.$key, $treefield->{'slug_'.$key}), 'class="form-control" id="inputOption_'.$key.'_slug" placeholder="'.lang_check('URI slug').'"')?>
                                          </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang('Keywords')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_input('keywords_'.$key, set_value('keywords_'.$key, $treefield->{'keywords_'.$key}), 'class="form-control" id="inputOption_'.$key.'_3" placeholder="'.lang('Keywords').'"')?>
                                          </div>
                                        </div>
                                        
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang('Description')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_textarea('description_'.$key, set_value('description_'.$key, $treefield->{'description_'.$key}), 'placeholder="'.lang('Description').'" rows=4" class="form-control" id="inputOption_'.$key.'_4"')?>
                                          </div>
                                        </div>  
                                        
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang('Body')?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_textarea('body_'.$key, set_value('body_'.$key, $treefield->{'body_'.$key}), 'placeholder="'.lang('Body').'" rows="10" class="cleditor2 form-control" id="inputOption_'.$key.'_5"')?>
                                          </div>
                                        </div> 
                                        
                                        <?php for($i=1;$i<=6;$i++): ?>
                                        <div class="form-group">
                                          <label class="col-lg-3 control-label"><?php echo lang_check('Ads code').' '.$i?></label>
                                          <div class="col-lg-9">
                                            <?php echo form_textarea('adcode'.$i.'_'.$key, set_value('adcode'.$i.'_'.$key, $treefield->{'adcode'.$i.'_'.$key}), 'placeholder="'.lang_check('Ads code').' '.$i.'" rows=4" class="form-control" id="inputOption_'.$key.'_'.($i+5).'"')?>
                                          </div>
                                        </div>  
                                        <?php endfor; ?>
                                        
                                        
                                        
                                        <?php endif; ?> 
                                    </div>
                                    <?php endforeach;?>
                                  </div>
                                </div>

                                <div class="form-group">
                                  <div class="col-lg-offset-3 col-lg-9">
                                    <?php echo form_submit('submit', lang('Save'), 'class="btn btn-primary"')?>
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
          
          <div class="row">

            <div class="col-md-12">

                <div class="widget wblue">

                <div class="widget-head">
                  <div class="pull-left"><?php echo lang_check('Tree values')?></div>
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
                            <th><?php echo lang_check('Root');?></th>
                            <th><?php echo lang_check('Parent #');?></th>
                            <th><?php echo lang_check('Level');?></th>
                        	<th class="control"><?php echo lang('Edit');?></th>
                        	<?php if(check_acl('savesearch/delete')):?><th class="control"><?php echo lang('Delete');?></th><?php endif;?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if(count($tree_listings)): foreach($tree_listings as $listing_item):?>
                                    <tr>
                                        <td><?php echo $listing_item->id; ?></td>
                                        <td><?php echo $listing_item->visual.$listing_item->value; ?></td>
                                        <td><?php echo $listing_item->parent_id; ?></td>
                                        <td><?php echo $listing_item->level; ?></td>
                                    	<td><?php echo btn_edit('admin/treefield/edit/'.$option->id.'/'.$listing_item->id)?></td>
                                    	<?php if(check_acl('treefield/delete')):?><td><?php echo btn_delete('admin/treefield/delete/'.$option->id.'/'.$listing_item->id)?></td><?php endif;?>
                                    </tr>
                        <?php endforeach;?>
                        <?php else:?>
                                    <tr>
                                    	<td colspan="20"><?php echo lang('We could not find any');?></td>
                                    </tr>
                        <?php endif;?>           
                      </tbody>
                    </table>
                    
                    <div style="text-align: center;"><?php //echo $pagination; ?></div>

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
        //controls: "undo redo | source",
        docCSSFile: "<?php echo $template_css?>",
        baseHref: '<?php echo base_url('templates/'.$settings['template'])?>/'
    });
});

</script>