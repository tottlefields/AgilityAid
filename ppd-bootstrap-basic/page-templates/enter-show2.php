<?php /* Template Name: Enter Show Step 2 */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();

global $current_user, $wpdb;
get_currentuserinfo();
				
$userId = $current_user->ID;

$dogData = get_dogs_for_user($userId);
if(count($dogData) == 0){
	//No dogs registered for this user...
	wp_redirect(site_url('/account/dogs/'));
	exit;	
}
$data['dogs'] = $dogData;

$show_id = $_POST['show'] ? $_POST['show'] : $_GET['show'];

if(!empty($show_id)) {
	$data['show_id'] = $show_id;
}

setCustomSessionData($data);

if(empty($data['show_id'])) {
	wp_redirect(site_url('/enter-show/'));
	exit;
}

$show = get_post( $show_id );
$show_meta = get_post_meta($show_id);
$show_type = $show_meta['affiliation'][0];

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Select Dog/Classes</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li>Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Payment</li>
				</ul>
				<?php debug_array($data); ?>
				<form action="/enter-show/team-pairs-classes/" method="post" class="form-horizontal" id="entryForm">
					<input type="hidden" id="show" name="show" value="<?php echo $data['show_id']; ?>" />
					<div class="form-group">
						<label for="select_dog" class="control-label col-sm-3">Select Dog</label>
						<div class="col-sm-9">
							<select name="select_dog" class="form-control">
								<option value="">Select Dog...</option>
								<?php foreach ($data['dogs'] as $dog){
									echo '<option value="'.$dog['id'].'">'.$dog['pet_name'].' ('.$dog['meta_data'][$show_type.'_height'].' / '.$dog['meta_data'][$show_type.'_level'].')</option>';
									}
								?>
							</select>
				        </div>	
				     </div>
				</form>
            </div>
            <div class="col-md-3" id="sidebar">
            	<?php dynamic_sidebar('Entry Sidebar'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>