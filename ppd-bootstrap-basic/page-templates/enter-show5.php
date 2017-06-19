<?php /* Template Name: Enter Show Step 5 (Helpers) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();


wp_redirect(site_url('/enter-show/confirmation/'));
exit;

debug_array($data);



?>