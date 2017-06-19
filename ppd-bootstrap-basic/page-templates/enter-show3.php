<?php /* Template Name: Enter Show Step 3 (Pairs/Trios/Teams etc.) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();

$seen_multi_dog_class = 0;
foreach ($data['classes'] as $class){
	if ($class['noDogs'] > 1){ $seen_multi_dog_class = 1; break; }
}

//No Pairs/Trios/Teams etc classes at this show, skip step.
if(!$seen_multi_dog_class){
	wp_redirect(site_url('/enter-show/camping/'));
	exit;	
}


?>