<?php /* Template Name: Enter Show Step 2 */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/log-in/'));
	exit;
}


$show = $_POST['show'] ? $_POST['show'] : $_GET['show'];

$data = getCustomSessionData();

if(!empty($show)) {
	$data['show'] = $show;
}

setCustomSessionData($data);

if(empty($data['show'])) {
	wp_redirect(site_url('/enter-show/'));
	exit;
}

print_r($data);
exit;
?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Available Shows</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li>Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Payment</li>
				</ul>
            </div>
            <div class="col-md-3" id="sidebar">
            	<?php dynamic_sidebar('Entry Sidebar'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>