<?php
function my_theme_enqueue_styles() {
 
    $parent_style = 'ppd-bootstrap-basic'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'ppd-child',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );


function my_acf_google_map_api( $api ){	
	$api['key'] = 'AIzaSyAevU96cN-BphIqUa3ZgS7kJ82mRY9ohuo';
	return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

// Agility Shows Pre Get Posts
function my_pre_get_posts( $query ){
    if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'shows' ){
        $query->set('orderby', 'meta_value');
        $query->set('meta_key', 'start_date');
        $query->set('order', 'ASC');
    }

    // always return
    return $query;
}
add_action('pre_get_posts', 'my_pre_get_posts');

?>