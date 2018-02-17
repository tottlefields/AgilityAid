<?php
global $wpdb;
$show_id = 887;

$show = get_post( $show_id );
$show_meta = get_post_meta($show_id);

$args = array (
		'post_type'	=> 'entries',
		'post_status'	=> array('publish'),
		'numberposts'	=> -1,
		'post_parent' 	=> $show_id
);
// get posts
$posts = get_posts($args);
echo count($posts)."\n";

global $post;
$nfc_count = 0;
$class_counts = array();
foreach( $posts as $post ) {
	//echo get_the_ID()."\n";
	setup_postdata( $post );
	$entry_data = get_field('entry_data-pm', false, false);
	foreach ($entry_data as $dog_id => $dogEntry){
		if($dogEntry == 'nfc'){ $nfc_count++; continue; }
		foreach ($dogEntry['classes'] as $classNo => $classDetails){
			if (!isset($class_counts[$classNo])){
				$class_counts[$classNo] = array();
				$class_counts[$classNo]['title'] = $classDetails['class_title'];
			}
			if ($classDetails['lho']){
				$class_counts[$classNo]['lho']++;
			}
			else{
				$class_counts[$classNo]['fho']++;
			}
		}
	}
}
ksort($class_counts);
echo implode("\t", array('ClassNo', 'ClassTitle', 'FHO', 'LHO'))."\n";
foreach ($class_counts as $classNo => $classDetails){
	echo implode("\t", array($classNo, $classDetails['title'], $classDetails['fho'], $classDetails['lho']))."\n";
}
echo implode("\t", array("NFC", "Not for competition",$nfc_count))."\n";
?>


