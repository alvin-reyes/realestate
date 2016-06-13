<?php $this->load->view('admin/components/page_head_main')?>
<body>
<div class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
  
    <div class="containerk">
      <!-- Menu button for smallar screens -->
		<div class="navbar-header">
		  <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		  <a href="<?php echo site_url('admin/dashboard')?>" class="navbar-brand"><img src="<?php echo base_url('admin-assets/img/custom/logo-system-mini.png');?>" />Real estate <span class="bold">point</span></a>
		</div>
      <!-- Site name for smallar screens -->

      <!-- Navigation starts -->
      <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">     

        <!-- Links -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">            
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
              <?php if($this->session->userdata('profile_image') != ''):?><img src="<?php echo base_url($this->session->userdata('profile_image'));?>" alt="" class="nav-user-pic img-responsive" /> <?php endif;?><?php echo $this->session->userdata('name_surname')?> <b class="caret"></b>              
            </a>
            
            <!-- Dropdown menu -->
            <ul class="dropdown-menu">
              <li><a href="<?php echo site_url('admin/user/edit/'.$this->session->userdata('id'))?>"><i class="icon-user"></i> <?php echo lang_check('Profile');?></a></li>
              <?php if(check_acl('settings')):?><li><a href="<?php echo site_url('admin/settings')?>"><i class="icon-cogs"></i> <?php echo lang_check('Settings');?></a></li><?php endif;?>
              <li><a target="_blank" href="<?php echo site_url()?>"><i class="icon-globe"></i> <?php echo lang_check('Website link');?></a></li>
              <li><a href="<?php echo site_url('admin/user/logout')?>"><i class="icon-off"></i> <?php echo lang_check('Logout');?></a></li>
            </ul>
          </li>
          
        </ul>

        <!-- Notifications -->
        <ul class="nav navbar-nav navbar-right">
            
            <?php if(check_acl('enquire')):?>
            <!-- Message button with number of latest messages count-->
            <li class="dropdown dropdown-big">
              <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="icon-envelope-alt"></i> <?php echo lang_check('Enquires');?> <span class="badge badge-important"><?php echo $this->enquire_m->total_unreaded();?></span> 
              </a>

                <ul class="dropdown-menu">
                  <li>
                    <!-- Heading - h5 -->
                    <h5><i class="icon-envelope-alt"></i> <?php echo lang_check('Enquires');?></h5>
                    <!-- Use hr tag to add border -->
                    <hr />
                  </li>
                    <?php foreach($enquire_3 as $enquire):?>
                  <li>
                    <!-- List item heading h6 -->
                    <a href="<?php echo site_url('admin/enquire/edit/'.$enquire->id)?>"><?php echo $enquire->name_surname?></a>
                    <!-- List item para -->
                    <p><?php echo word_limiter(strip_tags($enquire->message), 9);?></p>
                    <hr />
                  </li>
                    <?php endforeach;?>    
                  <li>
                    <div class="drop-foot">
                      <a href="<?php echo site_url('admin/enquire')?>"><?php echo lang_check('View All');?></a>
                    </div>
                  </li>                                    
                </ul>
            </li>
            <?php endif;?>
            
            <?php if(check_acl('user')):?>
            <!-- Members button with number of latest members count -->
            <li class="dropdown dropdown-big">
              <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="icon-user"></i> <?php echo lang_check('Users');?> <span   class="badge badge-warning"><?php echo $this->user_m->total_unactivated();?></span> 
              </a>

                <ul class="dropdown-menu">
                  <li>
                    <!-- Heading - h5 -->
                    <h5><i class="icon-user"></i> <?php echo lang_check('Users');?></h5>
                    <!-- Use hr tag to add border -->
                    <hr />
                  </li>
                    <?php foreach($users_3 as $user):?>
                  <li>
                    <!-- List item heading h6-->
                    <a href="<?php echo site_url('admin/user/edit/'.$user->id)?>"><?php echo $user->name_surname?></a> 
                    <span class="label label-<?php echo $this->user_m->user_type_color[$user->type]?> pull-right"><?php echo $this->user_m->user_types[$user->type]?></span>
                    <div class="clearfix"></div>
                    <hr />
                  </li>
                    <?php endforeach;?>               
                  <li>
                    <div class="drop-foot">
                      <a href="<?php echo site_url('admin/user')?>"><?php echo lang_check('View All');?></a>
                    </div>
                  </li>                                    
                </ul>
            </li>
            <?php endif;?>

        </ul>
		</nav>
      </div>

    </div>
  



<!-- Main content starts -->

<div class="content">

  	<!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-dropdown"><a href="#">Navigation</a></div>

        <div class="sidebar-inner">

          <!-- Search form -->
          <div class="sidebar-widget">
             <?php echo form_open('admin/dashboard/search');?>
              	<input type="text" class="form-control" name="search" placeholder="<?php echo lang_check('Search')?>" />
            <?php echo form_close();?>
          </div>

          <!--- Sidebar navigation -->
          <!-- If the main navigation has sub navigation, then add the class "has_submenu" to "li" of main navigation. -->
          <ul class="navi">

            <!-- Use the class nred, ngreen, nblue, nlightblue, nviolet or norange to add background color. You need to use this in <li> tag. -->

            <li class="nred<?php echo (strpos($this->uri->uri_string(),'dashboard')!==FALSE || $this->uri->uri_string() == 'admin')?' current':'';?>"><a href="<?php echo site_url('admin/dashboard')?>"><i class="icon-desktop"></i> <?php echo lang_check('Dashboard');?></a></li>
            
            <?php if(check_acl('page')):?>
            <li class="ngreen<?php echo (strpos($this->uri->uri_string(),'page')!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/page')?>"><i class="icon-sitemap"></i> <?php echo lang_check('Pages & menu');?></a></li>
            <?php endif;?>
            
            <!-- Menu with sub menu -->
            <li class="has_submenu nlightblue<?php echo (strpos($this->uri->uri_string(),'estate')!==FALSE)?' current open':'';?>">
              <a href="#">
                <!-- Menu name with icon -->
                <i class="icon-map-marker"></i> <?php echo lang_check('Real estates');?> 
                <!-- Icon for dropdown -->
                <span class="pull-right"><i class="icon-angle-right"></i></span>
              </a>

              <ul>
                <li><a href="<?php echo site_url('admin/estate')?>"><?php echo lang_check('Manage');?></a></li>
                <?php if(check_acl('estate/options')):?>
                <li><a href="<?php echo site_url('admin/estate/options')?>"><?php echo lang_check('Options');?></a></li>
                <?php endif;?>
              </ul>
            </li>
            
            <?php if(config_item('admin_beginner_enabled') === TRUE):?>
                <?php if(check_acl('user')):?>
                <li class="norange<?php echo (strpos($this->uri->uri_string(),'user')!==FALSE && strpos($this->uri->uri_string(),'user/edit/'.$this->session->userdata('id'))===FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/user')?>"><i class="icon-list-alt"></i> <?php echo lang_check('Agents & Users');?></a></li>
                <?php endif;?>
                
                
                <?php if(check_acl('enquire')):?>
                <li class="nviolet<?php echo (strpos($this->uri->uri_string(),'enquire')!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/enquire')?>"><i class="icon-envelope-alt"></i> <?php echo lang_check('Enquires');?></a></li>
                <?php endif;?>
                
                <li class="nblue<?php echo (strpos($this->uri->uri_string(),'user/edit/'.$this->session->userdata('id'))!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/user/edit/'.$this->session->userdata('id'))?>"><i class="icon-user"></i> <?php echo lang_check('Profile');?></a></li>
                
                <?php if(check_acl('settings')):?>
                    <li class="has_submenu nred<?php echo (strpos($this->uri->uri_string(),'settings')!==FALSE)?' current open':'';?>">
                      <a href="#">
                        <!-- Menu name with icon -->
                        <i class="icon-cogs"></i> <?php echo lang_check('Settings');?> 
                        <!-- Icon for dropdown -->
                        <span class="pull-right"><i class="icon-angle-right"></i></span>
                      </a>
                    
                      <ul>
                        <li><a href="<?php echo site_url('admin/settings')?>"><?php echo lang_check('Company details');?></a></li>
                        <li><a href="<?php echo site_url('admin/settings/language')?>"><?php echo lang_check('Languages');?></a></li>
                        <li><a href="<?php echo site_url('admin/settings/template')?>"><?php echo lang_check('Template');?></a></li>
                        <li><a href="<?php echo site_url('admin/settings/system')?>"><?php echo lang_check('System');?></a></li>
                      </ul>
                    </li>
                <?php endif;?>

            <?php endif;?>

            <?php if(check_acl('slideshow')):?>
            <li class="ngreen<?php echo (strpos($this->uri->uri_string(),'slideshow')!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/slideshow')?>"><i class="icon-picture"></i> <?php echo lang_check('Slideshow')?></a></li>
            <li class="nlightblue<?php echo (strpos($this->uri->uri_string(),'statistics')!==FALSE)?' current':'';?>"><a target="_blank" href="https://www.google.com/analytics/web"><i class="icon-bar-chart"></i> <?php echo lang_check('Statistics');?></a></li>
            <?php endif;?>
            
            <?php if(check_acl('backup')):?>
            <li class="norange<?php echo (strpos($this->uri->uri_string(),'backup')!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/backup')?>"><i class="icon-hdd"></i> <?php echo lang_check('Backup')?></a></li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/news.php') && check_acl('news')):?>
            <li class="has_submenu nblue<?php echo (strpos($this->uri->uri_string(),'news')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-book"></i> <?php echo lang_check('News');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/news')?>"><?php echo lang_check('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/news/categories')?>"><?php echo lang_check('Categories');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/ads.php') && check_acl('ads')):?>
            <li class="nred<?php echo (strpos($this->uri->uri_string(),'ads')!==FALSE)?' current open':'';?>">
                <a href="<?php echo site_url('admin/ads')?>">
                    <!-- Menu name with icon -->
                    <i class="icon-globe"></i> <?php echo lang_check('Ads');?>
                </a>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/showroom.php') && check_acl('showroom')):?>
            <li class="has_submenu ngreen<?php echo (strpos($this->uri->uri_string(),'showroom')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-briefcase"></i> <?php echo lang_check('Showroom');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/showroom')?>"><?php echo lang_check('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/showroom/categories')?>"><?php echo lang_check('Categories');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/expert.php') && check_acl('expert')):?>
            <li class="has_submenu nlightblue<?php echo (strpos($this->uri->uri_string(),'expert')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-comment"></i> <?php echo lang_check('Q&A');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/expert')?>"><?php echo lang_check('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/expert/categories')?>"><?php echo lang_check('Categories');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/booking.php') && check_acl('booking')):?>
            <li class="has_submenu norange<?php echo (strpos($this->uri->uri_string(),'booking')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-shopping-cart"></i> <?php echo lang_check('Booking');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/booking')?>"><?php echo lang_check('Reservations');?></a></li>
                <li><a href="<?php echo site_url('admin/booking/rates')?>"><?php echo lang_check('Rates');?></a></li>
                <li><a href="<?php echo site_url('admin/booking/payments')?>"><?php echo lang_check('Payments');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/packages.php') && check_acl('packages')):?>
            <li class="has_submenu nviolet<?php echo (strpos($this->uri->uri_string(),'packages')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-gift"></i> <?php echo lang_check('Packages');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/packages')?>"><?php echo lang_check('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/packages/users')?>"><?php echo lang_check('Users');?></a></li>
                <li><a href="<?php echo site_url('admin/packages/payments')?>"><?php echo lang_check('Payments');?></a></li>
              </ul>
            </li>
            <?php elseif(file_exists(APPPATH.'controllers/admin/packages.php') && check_acl('packages/mypackage')): ?>
            <li class="nviolet<?php echo (strpos($this->uri->uri_string(),'packages')!==FALSE)?' current open':'';?>">
                <a href="<?php echo site_url('admin/packages/mypackage')?>">
                    <!-- Menu name with icon -->
                    <i class="icon-gift"></i> <?php echo lang_check('My package');?>
                </a>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/reviews.php') && check_acl('reviews')): ?>
            <li class="nblue<?php echo (strpos($this->uri->uri_string(),'reviews')!==FALSE)?' current open':'';?>">
                <a href="<?php echo site_url('admin/reviews')?>">
                    <!-- Menu name with icon -->
                    <i class="icon-tags"></i> <?php echo lang_check('Reviews');?>
                </a>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/favorites.php') && check_acl('favorites')): ?>
            <li class="nblue<?php echo (strpos($this->uri->uri_string(),'favorites')!==FALSE)?' current open':'';?>">
                <a href="<?php echo site_url('admin/favorites')?>">
                    <!-- Menu name with icon -->
                    <i class="icon-star"></i> <?php echo lang_check('Favorites');?>
                </a>
            </li>
            <?php endif;?>
            
            <?php if(check_acl('monetize')):?>
            <li class="has_submenu nred<?php echo (strpos($this->uri->uri_string(),'monetize')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-usd"></i> <?php echo lang_check('Payments');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/monetize/payments')?>"><?php echo lang_check('Activations');?></a></li>
                <li><a href="<?php echo site_url('admin/monetize/payments_featured')?>"><?php echo lang_check('Featured');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/savesearch.php') && check_acl('savesearch')): ?>
            <li class="ngreen<?php echo (strpos($this->uri->uri_string(),'savesearch')!==FALSE)?' current open':'';?>">
                <a href="<?php echo site_url('admin/savesearch')?>">
                    <!-- Menu name with icon -->
                    <i class="icon-filter"></i> <?php echo lang_check('Research');?>
                </a>
            </li>
            <?php endif; ?>
            
          </ul>
  
          <?php if(false):?>
          <!-- Date -->
          <div class="sidebar-widget">
            <div id="todaydate"></div>
          </div>
          <?php endif;?>

        </div>

    </div>

    <!-- Sidebar ends -->

  	<!-- Main bar -->
  	<div class="mainbar">
    <?php $this->load->view($subview)?>
    </div>
</div>
<!-- Content ends -->

<?php $this->load->view('admin/components/page_tail_main')?>