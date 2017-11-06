<?php
global $wpdb;
$show_id = 431;
//$show_id = 428;

$show = get_post( $show_id );
$show_meta = get_post_meta($show_id);
$camping_options = unserialize($show_meta['camping_options'][0]);

$args = array (
		'post_type'	=> 'entries',
		'post_status'	=> array('publish'),
		'numberposts'	=> -1,
		'post_parent' 	=> $show_id
);
// get posts
$posts = get_posts($args);
//echo count($posts)."\n";

global $post;
echo implode("\t", array("Form", "Firstname", "Surname", "Fullname", "Option", "Pitches", "Group"))."\n";
foreach( $posts as $post ) {
	//echo get_the_ID()."\n";
	setup_postdata( $post );
	$camping_data = get_field('camping-pm', false, false);
	if (count($camping_data) > 0){
		$camping_list = array();
		$user = get_user_by( 'ID', $post->post_author );
		$userMeta = get_user_meta($user->ID);
		$user_details = array($post->ID, $userMeta['first_name'][0], $userMeta['last_name'][0] ,$userMeta['first_name'][0].' '.$userMeta['last_name'][0]);
		$user_details = array_pad($user_details, 4, '');
		$camping_list = array_merge($camping_list, $user_details);
		foreach ($camping_options as $option){
			if (isset($camping_data[$option])){
				array_push($camping_list, $option);
				array_push($camping_list, $camping_data[$option]['pitches']);
			}
			$camping[$option] = array(
					'pitches' => $_POST[$option.'_pitches'],
					'nights'  => $_POST[$option.'_nights'],
			);
			$camping['camping_group'] = (isset($_POST[$option.'_group']) && $_POST[$option.'_group'] != '') ? $_POST[$option.'_group'] : $camping['camping_group'];
			$cost_per_option = $_POST[$option.'_pitches'] * $show_meta[$option.'_price'][0];
			if (count($_POST[$option.'_nights']) > 1){ $cost_per_option *= count($_POST[$option.'_nights']); }
			$total_amount += $cost_per_option;
		}
		array_push($camping_list, $camping_data['camping_group']);
		echo implode("\t", $camping_list)."\n";
	}
}

?>


