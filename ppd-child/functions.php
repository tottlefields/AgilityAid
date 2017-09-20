<?php
add_action('manage_users_columns','agilityaid_manage_users_columns');
function agilityaid_manage_users_columns($column_headers) {
    unset($column_headers['posts']);
    $column_headers['custom_posts'] = 'Entries';
    $column_headers['user_balance'] = 'Balance';
    return $column_headers;
}

add_action('manage_users_custom_column','agilityaid_manage_users_custom_column',10,3);
function agilityaid_manage_users_custom_column($custom_column,$column_name,$user_id) {
    if ($column_name=='custom_posts') {
        $counts = _agilityaid_get_author_post_type_counts();
        $custom_column = array();
        if (isset($counts[$user_id]) && is_array($counts[$user_id]))
            foreach($counts[$user_id] as $count) {
                $link = admin_url() . "edit.php?post_type=" . $count['type']. "&author=".$user_id;
                // admin_url() . "edit.php?author=" . $user->ID;
                $custom_column[] = "\t<tr><th><a href={$link}>{$count['label']}</a></th><td>{$count['count']}</td></tr>";
            }
        $custom_column = implode("\n",$custom_column);
        if (empty($custom_column))
            $custom_column = "<th>[none]</th>";
        $custom_column = "<table>\n{$custom_column}\n</table>";
    }
    if ($column_name=='user_balance') {
    	$custom_column = "";
    }
    return $custom_column;
}

function _agilityaid_get_author_post_type_counts() {
    static $counts;
    if (!isset($counts)) {
        global $wpdb;
        global $wp_post_types;
        $sql = <<<SQL
        SELECT
        post_type,
        post_author,
        COUNT(*) AS post_count
        FROM
        {$wpdb->posts}
        WHERE 1=1
        AND post_type IN ('entries','shows')
        AND post_status IN ('publish','pending', 'draft')
        GROUP BY
        post_type,
        post_author
SQL;
        $posts = $wpdb->get_results($sql);
        foreach($posts as $post) {
            $post_type_object = $wp_post_types[$post_type = $post->post_type];
            if (!empty($post_type_object->label))
                $label = $post_type_object->label;
            else if (!empty($post_type_object->labels->name))
                $label = $post_type_object->labels->name;
            else
                $label = ucfirst(str_replace(array('-','_'),' ',$post_type));
            if (!isset($counts[$post_author = $post->post_author]))
                $counts[$post_author] = array();
            $counts[$post_author][] = array(
                'label' => $label,
                'count' => $post->post_count,
                'type' => $post->post_type,
                );
        }
    }
    return $counts;
}

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
