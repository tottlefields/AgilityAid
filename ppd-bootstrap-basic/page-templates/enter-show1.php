<?php /* Template Name: Enter Show Step 1 */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

startCustomSession();

global $current_user, $wpdb;
get_currentuserinfo();

$userId = $current_user->ID;

$dogData = get_dogs_for_user($userId);
if(count($dogData) == 0){
	//No dogs registered for this user...
	wp_redirect(site_url('/account/dogs/'));
	exit;
}
$data['dogs'] = $dogData;

setCustomSessionData($data);

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Available Shows</h1>
				<ul class="breadcrumb">
					<li>Select Show</li>
					<li class="active">Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Helpers</li>
					<li class="active">Payment</li>
				</ul>
				<?php 
				// the query
				$today = date('Ymd');
				
				$args = array (
						'post_type'	=> 'shows',
						'post_status'	=> array('publish'),
						'meta_key'	=> 'start_date',
						'orderby'	=> 'meta_value',
						'order'		=> 'ASC',
						'numberposts'	=> -1,
						'meta_query' 	=> array(
								array(
										'key'		=> 'close_date',
										'compare'	=> '>=',
										'value'		=> $today,
								),
								array(
										'key'		=> 'online_show_entry_link',
										'compare'	=> '=',
										'value'		=> '',
								),
						)
				);
				
				// get posts
				$posts = get_posts($args);
				global $post;
				
				// loop
				if( $posts ) {
					echo '<form action="/enter-show/individual-classes/" method="post" class="form-inline" id="entryForm">
					<input type="hidden" id="show" name="show" value="#" />';	
					foreach( $posts as $post ) {		
						setup_postdata( $post );
                		$post_id = get_the_ID();
                		$start_date = new DateTime(get_field('start_date', false, false));
                		$end_date = new DateTime(get_field('end_date', false, false));
                		$close_date = get_field('close_date', false, false);
                		$closes = new DateTime($close_date);
                		
                		$show_dates = $start_date->format('jS M');
                		if($start_date != $end_date){
                			$show_dates .= ' to '.$end_date->format('jS M Y');
                		}
                		else{
                			$show_dates .= $start_date->format(' Y');
                		}
		                
                		//green (success) for open shows; orange (warning) for closing in 7 days
                		$panel_class = 'success';
                		$closes_text = '&nbsp;';
                		if ($close_date <= date('Ymd', strtotime("+7 day"))){
                			$panel_class = 'warning';
                			$closes_text = 'Closes: '.$closes->format('D jS M');
                		}
                		echo '
                		<div class="panel panel-'.$panel_class.'">
					    	<div class="panel-heading" role="tab" id="heading'.$post_id.'">
					      		<h4 class="panel-title">
                					<span class="pull-right">'.$closes_text.'</span>
					        		<a href="javascript:{}" onclick="document.getElementById(\'show\').value='.$post_id.'; document.getElementById(\'entryForm\').submit(); return false;">'.get_the_title().' ('.$show_dates.')</a>
					      		</h4>
					    	</div>
                		</div>';
					}
					echo '</form>';		
					wp_reset_postdata();
				
				} else {
					// no posts found
					echo 'No shows are currently available to enter.';
				}
				
				
				?>
            </div>
            <div class="col-md-3" id="sidebar">
            	<?php dynamic_sidebar('Entry Sidebar'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>