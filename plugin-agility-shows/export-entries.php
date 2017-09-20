<?php
global $wpdb;

$SHOW = 68;

$args = array(
		'post_type'		=> 'entries',
		'post_parent'	=> $SHOW,
		'post_status'	=> array('publish'),
		'order'			=> 'ASC',
		'numberposts'	=> -1
);

$orders = array();
$animals = array();
$clients = array();

// get posts
$posts = get_posts($args);
global $post;
foreach( $posts as $post ) {
	setup_postdata($post);
	$postMeta = get_post_custom($post->ID);
	$user = get_user_by( 'ID', $post->post_author );
	$userMeta = get_user_meta($user->ID);
/*	$row = array(
		'id' =>  $post->ID,
		'firstName' => $userMeta['first_name'][0],
		'lastName' => $userMeta['last_name'][0],
		'handler' => $userMeta['first_name'][0].' '.$userMeta['last_name'][0],
		'address1' => $userMeta['address'][0],
		'address2' => $userMeta['town'][0],
		'address4' => $userMeta['county'][0],
		'address4' => $userMeta['country'][0],
		'address5' => '',
		'postcode' => $userMeta['postcode'][0],
		'tel' => (isset($userMeta['mobile'][0])) ? $userMeta['mobile'][0] : $userMeta['landline'][0],
		'email' => $user->user_email
	);*/
	
	$user_details = array($post->ID, $userMeta['first_name'][0], $userMeta['last_name'][0] ,$userMeta['first_name'][0].' '.$userMeta['last_name'][0]);
	$user_details = array_pad($user_details, 4, '');
	array_push($user_details, rtrim(preg_replace('/\s+/', ' ',$userMeta['address'][0])), rtrim($userMeta['town'][0]),rtrim($userMeta['county'][0]),rtrim($userMeta['country'][0]));
	$user_details = array_pad($user_details, 9, '');
	array_push($user_details, rtrim($userMeta['postcode'][0]));
	array_push($user_details, (isset($userMeta['mobile'][0])) ? $userMeta['mobile'][0] : $userMeta['landline'][0]);
	array_push($user_details, $user->user_email);
	
	$ro_postal = get_field('show_data-pm', false, false)['ro_postal'];

	$entry_data = get_field('entry_data-pm', false, false);
	if (isset(get_field('show_data-pm', false, false)['dogs'])){
		$dogs = get_field('show_data-pm', false, false)['dogs'];
		foreach ($dogs as $dog){
			$dog_breed = get_term_by('id', $dog['breed'], 'dog-breeds');
			$dog['breedName'] = $dog_breed->name;
			$dogs[$dog['id']] = $dog;
		}
	}
	else{
		$dogs = array();
	}
	
	$classes = array();
	foreach ($entry_data as $dog_id => $dogEntry){
		if(isset($dogEntry['classes'])){
			$classes[$dog_id] = array();
			foreach ($dogEntry['classes'] as $class_no => $class){
				array_push($classes[$dog_id], $class_no);
				if (!isset($dogs[$dog_id])){ $dogs[$dog_id] = array(); }
				$dogs[$dog_id]['classHeight'] = $class['height'];
				$dogs[$dog_id]['classLevel'] = $class['level'];
			}
		}
		else{
			//print_r($dogEntry);
		}
	}
	
	foreach ($classes as $dog_id => $classes){
		$dog = $dogs[$dog_id];
		if (!isset($dog['id'])){
			// Get dog details when not saved with show data/entry
			$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}agility_dogs WHERE ID = %d", $dog_id );
			$row = $wpdb->get_row( $sql );
			$dog['id'] = $dog_id;
			$dog['pet_name'] = $row->pet_name;
			$dog['kc_name'] = $row->kc_name;
			$dog['kc_number'] = $row->kc_number;
			$dog['birth_date'] = $row->birth_date;
		}
		
		$name = (isset($dog['kc_name'])) ? $dog['kc_name'] : $dog['pet_name'];
		echo implode("\t", array_slice($user_details, 0, 4));
		echo "\t".$name."\t".$dog['classHeight']."\t".$dog['classLevel']."\t".$dog['breedName']."\t".$dog['sex']."\t".$dog['birth_date']."\t".$dog['kc_number']."\t".$dog_id."\t";
		echo implode("\t", array_slice($user_details, 4));
		echo "\t".''."\t".''."\t".''."\t".rtrim($ro_postal)."\t".implode(',', $classes);
		echo "\n";
	}
}

?>
