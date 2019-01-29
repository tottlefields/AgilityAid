<?php
session_start();

//Include our widgets!
require_once('widgets/basket.php');
require_once('widgets/agility_constants.php');

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


register_sidebar(
		array(
				'name'          => 'Entry Sidebar',
				'id'            => 'entry-sidebar',
				'description'   => '',
				'class'         => '',
				'before_widget' => '<div id="%1$s" class="widget %2$s gradient">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widgettitle">',
				'after_title'   => '</h4>'
		)
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
	
	//BootBox
	wp_register_script('bootbox-js', '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js', array('jquery', 'bootstrap-js'), '4.4.0', true);
	wp_enqueue_script('bootbox-js');
	
	//Bootstrap-toggle
	wp_register_script('bstoggle-js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', array('jquery', 'bootstrap-js'), '2.2.2', true);
	wp_enqueue_script('bstoggle-js');
	
	//PDF Make
	wp_register_script('pdfmake-js', get_template_directory_uri().'/js/pdfmake.min.js', false, '0.1.31', true);
	wp_register_script('pdfmake-fonts-js', get_template_directory_uri().'/js/vfs_fonts.js', array('pdfmake-js'), '0.1.31', true);
	wp_enqueue_script('pdfmake-js');
	wp_enqueue_script('pdfmake-fonts-js');

	// Main functions js file
	wp_register_script ( 'js-functions', get_template_directory_uri () . '/js/functions.js', array ('jquery'), '0.2.1', true );
	wp_enqueue_script ( 'js-functions' );
	
    // register template-specific scripts
    wp_register_script('js-ring_cards', get_template_directory_uri().'/js/ring_cards.js', array('jquery', 'pdfmake-js'), '0.2.1', true); 
    
    // conditional load
    if (is_page(array('my-entries')) || is_post_type_archive('shows')){
    	wp_enqueue_script('js-ring_cards');
    	//wp_localize_script('js-orders', 'DennisAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }
}

add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');
add_action('init', 'startCustomSession', 1);
add_action('wp_logout', 'endCustomSession');

function rewrite_shows_url() {
	//add_rewrite_rule( '^shows/([0-9]+)/?','index.php?post_type=shows&year=$matches[1]', 'top' );
	add_rewrite_rule( '^shows/([0-9]+)/?','index.php?post_type=shows&show_year=$matches[1]', 'top' );
}
function register_custom_query_vars( $vars ) {
	array_push( $vars, 'show_year' );
	return $vars;
}

add_action( 'init', 'rewrite_shows_url');
add_filter( 'query_vars', 'register_custom_query_vars', 1 );

function custom_query_vars( $query ) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( is_post_type_archive( 'shows' ) ) {
	    // Display all posts for a custom post type called 'shows'
    	if (get_query_var('show_year') > 0){
    		$year = get_query_var('show_year');
	        $query->set( 'posts_per_page', -1 );
	        $query->set(
				'meta_query', array(
	        				array(
	        						'key'		=> 'end_date',
	        						'compare'	=> '>=',
	        						'value'		=> $year.'0101',
	        				),
	        				array(
	        						'key'		=> 'end_date',
	        						'compare'	=> '<=',
	        						'value'		=> $year.'1231',
	        				),
	        		)
			);
	        return;
    	}
    	else{
			$today = date('Ymd');
	        $query->set( 'posts_per_page', -1 );
	        $query->set(
				'meta_query', array(
	        				array(
	        						'key'		=> 'end_date',
	        						'compare'	=> '>=',
	        						'value'		=> $today,
	        				),
	        		)
	        );
	#        $query->set( 'orderby', 'meta_value');
	#        $query->set( 'order', 'ASC');
	        return;
    	}
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

function clearCustomSessionData() {
	$_SESSION['data'] = serialize(array());
}

function getCustomSessionData() {
	return unserialize($_SESSION['data']);
}

function setCustomSessionData($data) {
	$_SESSION['data'] = serialize($data);
}

function get_dog_by_id($dogs, $id){
	foreach ($dogs as $dog){
		if ($dog['id'] == $id) { return $dog; }
	}
	return;
}

function get_class_by_no($classes, $classNo){
	foreach ($classes as $class){
		if ($class['classNo'] == $classNo) { return $class; }
	}
	return;
}

function dateToSQL($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('d/m/Y', $date), 'Y-m-d');
}

function SQLToDate($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('Y-m-d', $date), 'd/m/Y');
}

function debug_array($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function debug_string($string){
	echo '<pre>';
	echo ($string);
	echo '</pre>';	
}


function get_ring_card_info($show_id, $user_id){
	global $wpdb;
	$ring_cards = array();
	
	$sql = "select p.ID, rc.*, DATE_FORMAT(class_date, '%a %D') as class_day from wpao_posts p inner join wpao_ring_card_info rc on p.ID=rc.post_id 
			where post_author=".$user_id." and post_parent=".$show_id;
	$rows = $wpdb->get_results($sql);
	if (count($rows) == 0){
		return $ring_cards;
	}
	
	foreach ($rows as $row){
		if(!isset($ring_cards[$row->ring_no])){
			$ring_cards[$row->ring_no] = array(
					'dog_name' => $row->dog,
					'handler' => $row->handler,
					'dog_level' => $row->level,
					'dog_height' => $row->height,
					'misc2' => $row->misc2,
					'misc1' => $row->misc1,
					'lho' => $row->lho,
					'camping' => $row->camping,
					'classes' => array()
			);
		}
		if(!isset($ring_cards[$row->ring_no]['classes'][$row->class_day])){
			$ring_cards[$row->ring_no]['classes'][$row->class_day] = array();
		}
		$class = array(
				'class_no'		=> $row->class_no,
				'class_title'	=> $row->class_title,
				'class_day'		=> date_format(DateTime::createFromFormat('Y-m-d', $row->class_date), 'j'),
				'running_order'	=> $row->running_order,
				'which_ring'	=> $row->which_ring,
				'onfirst'		=> $row->onfirst,
				'total_entry'	=> $row->total_entry,
				'equalto'		=> $row->equalto,
				'part_grade'	=> $row->part_grade				
		);
		array_push($ring_cards[$row->ring_no]['classes'][$row->class_day], $class);
	}
	return $ring_cards;
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

//function has_next(array $a){
//	return next($a) !== false ?: each($a) !== false;
//}
function has_next($array) {
	if (is_array($array)) {
		if (next($array) === false) {
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

require_once 'ajax.php';
?>
