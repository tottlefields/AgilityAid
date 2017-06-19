<?php /* Template Name: Enter Show Step 4 (Camping) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();

$show = get_post( $data['show_id'] );
$show_meta = get_post_meta($data['show_id']);

if (isset($show_meta['camping_avail']) && $show_meta['camping_avail'][0] == 1){
	
}
else{
	wp_redirect(site_url('/enter-show/helpers/'));
	exit;	
}

debug_array($show_meta);



?>