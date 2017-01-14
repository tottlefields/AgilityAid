<?php
session_start();

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ppd_widgets_init() {
	register_sidebar( array(
			'name'          => esc_html__( 'Sidebar', 'ppd-bootstrap-basic' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'ppd-bootstrap-basic' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'ppd_widgets_init' );

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
add_action('init', 'startCustomSession', 1);
add_action('wp_logout', 'endCustomSession');


// Functions for the ppd-bootstrap-basic theme //

function startCustomSession() {
	if(!session_id()) {
		session_start();

		if(empty($_SESSION['data'])) {
			$_SESSION['data'] = serialize(array());
		}
	}
}

function endCustomSession() {
	session_destroy();
}

function getCustomSessionData() {
	return unserialize($_SESSION['data']);
}

function setCustomSessionData($data) {
	$_SESSION['data'] = serialize($data);
}





function outputBasketHeaderData(){
	$data = getCustomSessionData();
	$cost = 0;
	$items = 0;
	
	//cost - needs to be worked out
	//items = number of dogs entered?
	//NB only one show in basket at a given time
	
//	foreach($data as $type => $typeData) {
//	}
	
	echo '<strong>' . $items . ($items == 1 ? ' Item' : ' Items') . ' (&pound;' . number_format($cost, 2) . ')</strong>';
}
?>