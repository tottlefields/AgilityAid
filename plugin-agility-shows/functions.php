<?php
/*
 Plugin Name: Agility Show Plugin
Description: Adding agility-show specific functions for websites
*/
/* Start Adding Functions Below this Line */

//Include our widgets!
require_once('agility-show-widgets.php');

// Our custom post type function
function create_posttype() {

	$args = array(
			'label' => __('Agility Shows'),
			'labels' => array(
					'add_new_item' => 'Add Show',
					'edit_item' => 'Edit Show',
					'view_item' => 'View Shows'
			),
			'singular_label' => __('Show'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => true,
			'supports' => array('title', 'editor', 'author', 'thumbnail'),
			'exclude_from_search' => false,
			'has_archive' => true,
			'menu_icon' => 'dashicons-location-alt',
			'show_in_nav_menus' => true
	);

	register_post_type('shows', $args);

 	register_taxonomy('dog-breeds', array('shows'),
 		array(
 			'hierarchical' => false,
 			'label' => 'Dog Breeds',
 			'singular_label' => 'Dog Breed',
 			'rewrite' => true
 		)
 	);
 	

 	$args = array(
 			'label' => __('Show Entries'),
 			'labels' => array(
 					'add_new_item' => 'Add Entry',
 					'edit_item' => 'Edit Entry',
 					'view_item' => 'View Entries'
 			),
 			'singular_label' => __('Show Entry'),
 			'public' => true,
 			'show_ui' => true,
 			'capability_type' => 'post',
 			'hierarchical' => false,
 			'rewrite' => true,
 			'supports' => array('title', 'author'),
 			'exclude_from_search' => true,
 			'has_archive' => false,
 			'menu_icon' => 'dashicons-awards',
 			'show_in_nav_menus' => false
 	);
 	
 	register_post_type('entries', $args);
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

add_filter( 'manage_shows_posts_columns', 'set_custom_edit_shows_columns' );
add_action( 'manage_shows_posts_custom_column' , 'custom_shows_column', 10, 2 );
add_filter( 'manage_edit-shows_sortable_columns', 'custom_shows_sort');
add_filter( 'request', 'date_column_orderby' );

add_filter( 'manage_entries_posts_columns', 'set_custom_edit_entry_columns' );
add_action( 'manage_entries_posts_custom_column' , 'custom_entry_column', 10, 2 );

function set_custom_edit_entry_columns($columns) {
	$columns['no_classes'] = __( 'Classes');
	$columns['no_dogs'] = __( 'Dogs');
	$columns['total_cost'] = __( 'Cost');
	$columns['paid'] = __( 'Paid');

	return $columns;
}

function custom_entry_column( $column, $post_id ) {
	switch ( $column ) {

		case 'no_classes' :
			echo get_post_meta( $post_id , 'class_count-pm' , true );
			break;

		case 'no_dogs' :
			echo get_post_meta( $post_id , 'dog_count-pm' , true );
			break;

		case 'total_cost' :
			echo '&pound;'.sprintf("%.2f", get_post_meta( $post_id , 'total_cost-pm' , true ));
			break;

		case 'paid' :
			$paid = get_post_meta( $post_id , 'paid-pm' , true );
			if(isset($paid) && $paid != ''){
				$payments = array();
				foreach ($paid as $payment){
					$date = new DateTime($payment['date']);
					array_push($payments, $date->format('jS M Y').' - &pound;'.sprintf("%.2f", $payment['amount']).' ('.$payment['method'].')');
					//echo $date->format('jS M Y').' - &pound;'.sprintf("%.2f", $payment['amount']).' ('.$payment['method'].')';
				}
				echo implode("<br />", $payments);
			}
			else{
				echo 'No';
			}
			break;

	}
}

function set_custom_edit_shows_columns($columns) {
	$columns['start_date'] = __( 'Show Date');
	$columns['close_date'] = __( 'Closing Date');
	$columns['venue'] = __( 'Venue');

	return $columns;
}

function custom_shows_column( $column, $post_id ) {
	switch ( $column ) {

		case 'start_date' :
			$date = new DateTime(get_post_meta( $post_id , 'start_date' , true ));
			echo $date->format('jS M Y');
			break;

		case 'close_date' :
			$date = new DateTime(get_post_meta( $post_id , 'close_date' , true ));
			echo $date->format('jS M Y');
			break;

		case 'venue' :
			echo get_post_meta( $post_id , 'venue' , true );
			break;

	}
}

function custom_shows_sort($columns) {
	$columns['start_date'] = 'start_date';
	return $columns;
}

function date_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'start_date' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'start_date',
				'orderby' => 'meta_value'
		) );
	}

	return $vars;
}


require_once('agility-payments.php');
require_once('agility-new-payment.php');


/* Stop Adding Functions Below this Line */
?>