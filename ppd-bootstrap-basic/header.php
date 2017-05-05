<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php bloginfo('name'); ?> &raquo; <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' />
		<meta name="description" content="">
		<meta name="author" content="PawPrints Design">
		<link rel="shortcut icon" type="image/png" href="<?php echo get_stylesheet_directory_uri() . '/img/favicon.ico'; ?>"/>
		
		<link href="//fonts.googleapis.com/css?family=Open+Sans:400,600|Raleway:400,700" rel="stylesheet">
		<link href="<?php bloginfo('stylesheet_url');?>" rel="stylesheet">
		
		
		<?php
		//JavaScript
		wp_enqueue_script('jquery');
		wp_enqueue_script('bootstrap-js');
		wp_enqueue_script('fuelux-js');
		wp_enqueue_script('datepicker-js');
		?>
		
		<?php wp_head(); ?>
       

		<!--[if gte IE 9]>
		<style type="text/css">
			.gradient { filter: none; }
		</style>
		<![endif]-->
		<!--[if lt IE 9]>
		<style type="text/css">
			.account-bar { width:510px!important;}
		</style>
		<![endif]-->
	</head>
	<body>
		<div id="header">		
			<div class="container">
				<div class="row">
					<div class="col-lg-7 col-md-8 col-xs-12 pull-right no-print">
						<div class="account-bar">
							<a href="/basket/"><i class="fa fa-shopping-basket"></i>&nbsp;<span class="hidden-xs">Basket <?php outputBasketHeaderData(); ?></a>
							<?php if(is_user_logged_in()) { ?>
							<a href="/account/"><span class="hidden-xs">My </span>Account</a>
							<a href="<?php echo wp_logout_url(); ?>">Log Out</a>
                            <?php } else { ?>
                            <a href="<?php echo wp_login_url(); ?>">Log In</a>
                            <?php wp_register('', ''); ?>
                            <?php } ?>
                            &nbsp;<span class="contact"><a href="/contact-us/"><i class="fa fa-envelope-o"></i>Contact Us</a>&nbsp;<a href="https://www.facebook.com/agilityaid/" target="_blank"><i class="fa fa-facebook"></i></a></span>
							<div class="clearfix"></div>                        
						</div>
					</div>
					<div class="col-lg-5 col-md-4 col-xs-12">
						<a href="<?php echo site_url('/'); ?>"><img src="<?php echo get_stylesheet_directory_uri() . '/img/logo.png'; ?>" alt="" class="top-logo" /></a>
					</div>
				</div>
			</div>
		</div>
		<nav class="navbar navbar-inverse" role="navigation">
			<div class="navbar-inner">
				<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-navbar-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
					</div>

					<?php
						wp_nav_menu( array(
							'menu'              => 'main-menu',
							'theme_location'    => 'main-menu',
							'depth'             => 2,
							'container'         => 'div',
							'container_class'   => 'collapse navbar-collapse',
							'container_id'      => 'main-navbar-collapse',
							'menu_class'        => 'nav navbar-nav',
							'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
							'walker'            => new wp_bootstrap_navwalker())
						);
					?>
				</div>
			</div>
		</nav>
		
		<div class="container"