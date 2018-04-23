<?php
global $wpdb;

if (count($args) != 1){
        echo "ERROR : you must provide this script with the following (in this order):- <Show ID>\n\n";
        exit(1);
}

$SHOW_ID   = $args[0];

$show = get_post( $SHOW_ID );
$show_meta = get_post_meta( $SHOW_ID );

$args = array (
		'post_type'	=> 'entries',
		'post_status'	=> array('publish'),
		'numberposts'	=> -1,
		'post_parent' 	=> $SHOW_ID
);
// get posts
$posts = get_posts($args);
//echo count($posts)."\n";

global $post;
echo implode("\t", array("Form", "Firstname", "Surname", "Fullname", "Job", "Group", "Session"))."\n";
foreach( $posts as $post ) {
	//echo get_the_ID()."\n";
	setup_postdata( $post );
	$helpers_data = get_field('helpers-pm', false, false);
	if (count($helpers_data) > 0){
		$user = get_user_by( 'ID', $post->post_author );
		$userMeta = get_user_meta($user->ID);
		$user_details = array($post->ID, $userMeta['first_name'][0], $userMeta['last_name'][0] ,$userMeta['first_name'][0].' '.$userMeta['last_name'][0]);
		$user_details = array_pad($user_details, 4, '');
		
		foreach ($helpers_data as $helper => $job_info){
			$helpers_list = array();
			if ($helper !== '' && $helper !== ' '){
				$handler_details = explode(' ', $helper);
				$user_details[1] = array_shift($handler_details);
				$user_details[2] = implode(' ', $handler_details);
				$user_details[3] = $handler;
			}

//			print_r($job_info);
			
			$helpers_list = array_merge($helpers_list, $user_details);
			array_push($helpers_list, $job_info['job']);
			array_push($helpers_list, $job_info['group']);
			array_push($helpers_list, $job_info['session']);
			
			echo implode("\t", $helpers_list)."\n";
		}
	}
}
?>


