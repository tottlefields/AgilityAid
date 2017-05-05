<?php
session_start();

//Include our widgets!
require_once('widgets/basket.php');

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
	wp_register_script('jquery', ("//code.jquery.com/jquery-2.2.4.min.js"), false, '2.2.4', false);
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
	
	//jQuery Color Picker
	wp_register_script('colorpicker-js', get_stylesheet_directory_uri().'/js/palette-color-picker.min.js', array('jquery',), '1.03', true);
	wp_enqueue_script('colorpicker-js');
}

add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');
add_action('init', 'startCustomSession', 1);
add_action('wp_logout', 'endCustomSession');

function custom_query_vars( $query ) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( is_post_type_archive( 'shows' ) ) {
        // Display all posts for a custom post type called 'shows'
        $query->set( 'posts_per_page', -1 );
#        $query->set( 'orderby', 'meta_value');
#        $query->set( 'order', 'ASC');
        return;
    }
     return;
}
add_action( 'pre_get_posts', 'custom_query_vars' );


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

function dateToSQL($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('d/m/Y', $date), 'Y-m-d');
}

function SQLToDate($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('Y-m-d', $date), 'd/m/Y');
}




function get_options_for_term($slug, $breeds, $selected){
	//echo '<pre>hello - '.$selected.'</pre>';
	$options = '';
	foreach($breeds as $id => $breed) {
		$options .= '<option value="' . $id . '" data-breed="' . $id . '" data-type="' . $slug . '"';
		if (isset($selected) && $selected > 0 && $id == $selected){
			$options .= ' selected="selected"';
		}
		$options .= '>' . $breed['name'] . '</option>';
	}
	return $options;	
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