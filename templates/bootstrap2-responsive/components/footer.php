<div class="wrap-bottom">
    <div class="container">
      <div class="row-fluid">
        <div class="span3">
            <div class="logo-transparent">
                <img src="assets/img/logo.png" alt="Logo footer" />
            </div>
            <div class="sketch-bottom visible-desktop">
            </div>
        </div>
        <div class="span6">
            <br />
            <table>
                <tr>
                    <td><i class="icon-map-marker icon-white"></i></td>
                    <td>
                        {settings_address_footer}
                    </td>
                </tr>
                <tr>
                    <td><i class="icon-phone icon-white"></i></td>
                    <td>{settings_phone}</td>
                </tr>
                <tr>
                    <td><i class="icon-print icon-white"></i></td>
                    <td>{settings_fax}</td>
                </tr>
                <tr>
                    <td><i class="icon-envelope icon-white"></i></td>
                    <td><a href="mailto:{settings_email}">{settings_email}</a></td>
                </tr>
            </table>
        </div>
        <div class="span3">
            <a class="to-top" href="#top-page">{lang_ToTop}</a>
        
            <a class="developed_by" href="http://iwinter.com.hr" target="_blank"><img src="assets/img/partners/winter.png" alt="winter logo" /></a>
            
            <div class="share">
                {settings_facebook}
            </div>
            
            
        </div>
      </div>
    </div>
    <!-- Generate time: <?php echo (microtime(true) - $time_start)?>, version: <?php echo APP_VERSION_REAL_ESTATE; ?> -->
</div>

<?php if(config_db_item('agent_masking_enabled') == TRUE): ?>
<!-- form itself -->
<form id="test-form" class="form-horizontal mfp-hide white-popup-block">
    <div id="popup-form-validation">
    <p class="hidden alert alert-error"><?php echo lang_check('Submit failed, please populate all fields!'); ?></p>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputAre"><?php echo lang_check('YouAre'); ?></label>
        <div class="controls">
            <label class="radio inline">
            <input type="radio" name="visitor_type" id="optionsRadios1" value="Individual" <?php echo $this->session->userdata('visitor_type')=='Individual'?'checked':''; ?>>
            <?php echo lang_check('Individual'); ?>
            </label>
            <label class="radio inline">
            <input type="radio" name="visitor_type" id="optionsRadios2" value="Dealer" <?php echo $this->session->userdata('visitor_type')=='Dealer'?'checked':''; ?>>
            <?php echo lang_check('Dealer'); ?>
            </label>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputName"><?php echo lang_check('YourName'); ?></label>
        <div class="controls">
            <input type="text" name="name" id="inputName" value="<?php echo $this->session->userdata('name'); ?>" placeholder="<?php echo lang_check('YourName'); ?>">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputPhone"><?php echo lang_check('Phone'); ?></label>
        <div class="controls">
            <input type="text" name="phone" id="inputPhone" value="<?php echo $this->session->userdata('phone'); ?>" placeholder="<?php echo lang_check('Phone'); ?>">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputEmail"><?php echo lang_check('Email'); ?></label>
        <div class="controls">
            <input type="text" name="email" id="inputEmail" value="<?php echo $this->session->userdata('email'); ?>" placeholder="<?php echo lang_check('Email'); ?>">
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <label class="checkbox">
                <input name="allow_contact" value="1" type="checkbox"> <?php echo lang_check('I allow agent and affilities to contact me'); ?>
            </label>
            <button id="unhide-agent-mask" type="button" class="btn"><?php echo lang_check('Submit'); ?></button>
            <img id="ajax-indicator-masking" src="assets/img/ajax-loader.gif" style="display: none;" />
        </div>
    </div>
</form>
<?php endif; ?>

{settings_tracking}

<?php if(file_exists(FCPATH.'templates/'.$settings_template.'/assets/js/jquery-contact-tabs/js/jquery.contact.tabs.1.0.js')): ?>
<div id="contact-tabs"></div>
<?php endif; ?>

<?php if(config_item('enable_search_details_on_top') == TRUE): ?>

<script language="javascript">
$(document).ready(function(){
	if($('.top_content').length == 0)
    {
    	var content = $('.wrap-search');
    	var pos = content.offset();
    	
    	$(window).scroll(function(){
    		if($(this).scrollTop() > pos.top){
              content.addClass('search_on_top');
    		} else if($(this).scrollTop() <= pos.top){
              content.removeClass('search_on_top');
    		}
    	});
    }
});
</script>
<?php endif; ?>