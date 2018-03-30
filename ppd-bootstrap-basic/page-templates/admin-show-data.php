<?php /* Template Name: Show Data */ ?>
<?php 

global $current_user, $wpdb;
get_currentuserinfo();

if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}
if (!in_array( 'author', $current_user->roles ) && !in_array( 'administrator', $current_user->roles )){
	wp_redirect(site_url('/account/'));
	exit;
}

$show_id = isset($_POST['show']) ? $_POST['show'] : $_GET['show'];
$show = get_post($show_id);

if (!in_array( 'administrator', $current_user->roles ) && $show->post_author != $current_user->ID){
	wp_redirect(site_url('/account/'));
	exit;
}

$classes = get_post_meta($show->ID, 'classes', true);
//debug_array($classes);

$args = array (
		'post_type'	=> 'entries',
		'post_status'	=> array('publish'),
		'numberposts'	=> -1,
		'post_parent'	=> $show_id
);
$entries = get_posts($args);

$class_counts = array();
foreach ($entries as $entry){
	$data = unserialize(get_post_meta($entry->ID, 'show_data-pm', true));
	//debug_array($entry);
	//debug_array($data[$show_id]);
	foreach ($data[$show_id] as $dog_id => $dog_entry){
		if ($dog_entry == 'nfc'){ $class_counts['nfc']++; continue; }
		foreach ($dog_entry['classes'] as $class_no => $class_details){
			if (isset($class_details['lho']) && $class_details['lho']){$class_counts[$class_no.':lho']++;}
			else {$class_counts[$class_no]++;}
		}
	}
	if ( get_post_meta($entry->ID, 'pairs_teams-pm', true) != 'none'){
	    $pairs_teams = unserialize(get_post_meta($entry->ID, 'pairs_teams-pm', true));
	    debug_array($pairs_teams);
	}
}

/*if (get_post_meta($show_id, 'camping_avail', true)){
	echo "CAMPERS....";	
}
if (get_post_meta($show_id, 'helpers', true) && get_post_meta($show_id, 'helpers', true) != 'none'){
	echo "HELPERS....";
}*/
?>
<?php get_header(); ?>

<div id="content" class="standard">
	<div class="container">
		<div class="row">
			<div class="col-md-12" id="main-content">
				<h1 class="title">Show Data for <?php echo $show->post_title; ?> <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a>&nbsp;<a href="/account/your-shows/" class="btn btn-primary">Your Shows</a></span></h1>
          		<div class="row">
          			<div class="col-md-7">
		            	<h3 style="margin-top:0px;">Class Counts</h3>
		            	<table style="margin-bottom:0px;"  class="table table-striped table-hover table-responsive">
<?php 
          			foreach ($classes as $date => $class_list){
          				$dateObj = DateTime::createFromFormat('Y-m-j', $date);
          				echo '<tr><th colspan="2"><h4>'.$dateObj->format('l').'</h4></th></tr>';
          				foreach ($class_list as $class){
          					echo '<tr><td>'.$class['classNo'].'. '.$class['className'].'</td>';
          					
          					if (isset($class_counts[$class['classNo']])){
          						$lho = '';
          						if (isset($class_counts[$class['classNo'].':lho'])){ $lho = ' ('.$class_counts[$class['classNo'].':lho'].')'; }
          						echo '<td class="text-center">'.$class_counts[$class['classNo']].$lho.'</td>';
          					}
          					else{ echo '<td></td>'; }
          					echo '</tr>';
          				}
          			} 			
?>
						</table>
          			</div>
          			
          			<div class="col-md-5">
          			
          			</div>
          		</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>

















