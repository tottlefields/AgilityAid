<?php
session_start();

// Register Custom Navigation Walker
require_once('wp_bootstrap_navwalker.php');

register_nav_menu(
	'main-menu',
	'Main Menu'
);


function mytheme_enqueue_scripts() {
	wp_deregister_script('jquery');
	wp_register_script('jquery', ("//code.jquery.com/jquery-2.2.4.min.js"), false, '2.2.4', true);
	wp_enqueue_script('jquery');

	// Bootstrap
	wp_register_script('bootstrap-js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), '3.3.7', true);
	wp_enqueue_script('bootstrap-js');

	//Fuel UX
	wp_register_script('fuelux-js', '//www.fuelcdn.com/fuelux/3.13.0/js/fuelux.min.js', array('bootstrap-js'), '3.13.0', true);
	wp_enqueue_script('fuelux-js');
	
	//BS DatePicker
	wp_register_script('datepicker-js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', array('jquery', 'bootstrap-js'), '1.6.4', true);
	wp_enqueue_script('datepicker-js');
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');


?>