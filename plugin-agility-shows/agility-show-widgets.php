<?php

add_action('widgets_init', 'shows_widget_init', 10);// register widget

function shows_widget_init() {
	register_widget('FutureShows_Widget');
	register_widget('LiveShows_Widget');
}

class FutureShows_Widget extends WP_Widget {

	function FutureShows_Widget() {
		$widget_ops = array(
				'classname' => 'futureshows-widget'
		);

		$this->WP_Widget('FutureShows_Widget', 'Coming Soon', $widget_ops);
	}

	function widget($args, $instance) {
		extract($args);


		echo $before_widget;
		echo $before_title.'Coming Soon'.$after_title;
		
		// the query
		$today = date('Ymd');
		
		$args = array (
			'post_type'	=> 'shows',
			'post_status'	=> array('pending'),
			'meta_key'	=> 'start_date',
			'orderby'	=> 'meta_value',
			'order'		=> 'ASC',
			'numberposts'	=> 50,
			'meta_query' 	=> array(
				array(
					'key'		=> 'end_date',
					'compare'	=> '>=',
					'value'		=> $today,
				),
			)
		);
		
		// get posts
		$posts = get_posts($args);
		global $post;
		
		// loop
		if( $posts ) {	
			echo '<ul style="font-size:0.9em" class="agility-show-widget list-unstyled">';
			foreach( $posts as $post ) {		
				setup_postdata( $post );
                $date = new DateTime(get_field('start_date', false, false));
				echo '<li>' . get_the_title() .' ('.$date->format('jS M Y').')</li>';		
			}
			echo '</ul>';		
			wp_reset_postdata();
		
		} else {
			// no posts found
			echo 'No future shows currently on the system';
		}
		
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		return $instance;
	}
	
	function form($instance) {
	}
}

class LiveShows_Widget extends WP_Widget {

	function LiveShows_Widget() {
		$widget_ops = array(
				'classname' => 'liveshows-widget'
		);

		$this->WP_Widget('LiveShows_Widget', 'Live Shows', $widget_ops);
	}

	function widget($args, $instance) {
		extract($args);

		echo $before_widget;
		echo $before_title.'Live Shows'.$after_title;
		
		// the query
		$today = date('Ymd');
		
		$args = array (
			'post_type'	=> 'shows',
			'post_status'	=> array('publish'),
			'meta_key'	=> 'start_date',
			'orderby'	=> 'meta_value',
			'order'		=> 'ASC',
			'numberposts'	=> 20,
			'meta_query' 	=> array(
				array(
					'key'		=> 'close_date',
					'compare'	=> '>=',
					'value'		=> $today,
				),
			)
		);
		
		// get posts
		$posts = get_posts($args);
		global $post;
		
		// loop
		if( $posts ) {	
			echo '<ul style="font-size:0.9em" class="agility-show-widget list-unstyled">';	
			foreach( $posts as $post ) {		
				setup_postdata( $post );
                $date = new DateTime(get_field('start_date', false, false));
				echo '<li><a href="' . get_the_permalink() .'" rel="bookmark">' . get_the_title() .' ('.$date->format('jS M').')</a></li>';		
			}
			echo '</ul>';		
			wp_reset_postdata();
		
		} else {
			// no posts found
			echo 'No shows are currently available to enter.';
		}
		
		echo $after_widget;

	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		return $instance;
	}
	
	function form($instance) {
	}
}

?>