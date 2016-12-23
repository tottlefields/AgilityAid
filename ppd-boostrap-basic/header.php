<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php wp_title(''); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' />
		<meta name="description" content="">
		<meta name="author" content="PawPrints Design">
		
		<link href="//fonts.googleapis.com/css?family=Open+Sans:400,600|Raleway:400,700" rel="stylesheet">
		<link href="/css/main.css" rel="stylesheet">
       

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
							<div class="clearfix"></div>                        
						</div>
					</div>
					<div class="col-lg-5 col-md-4 col-xs-12">
						<a href="<?php echo site_url('/'); ?>"><img src="<?php echo get_template_directory_uri() . '/img/logo.png'; ?>" alt="" class="top-logo" /></a>
					</div>
				</div>
			</div>
		</div>
		<nav class="navbar" role="navigation">
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
		
		<div class="container">