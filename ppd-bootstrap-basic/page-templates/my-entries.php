<?php /* Template Name: My Entries */ ?>
<?php 


global $current_user, $wpdb;
get_currentuserinfo();

if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$userId = $current_user->ID;

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">

               	<?php
				
				if(isset($_GET['view']) && $_GET['view']>0) {
					$show = get_post( $_GET['view'] );
					$title = $show->post_title;
					?>					
	            	<h1 class="title">ENTRY: <?php echo $title; ?> <span class="pull-right"><a class="btn btn-info" href="/account/my-entries/">My Entries</a>
	            	&nbsp;<a href="/enter-show/individual-classes/?edit=yes&show=<?php echo $_GET['view']; ?>" class="btn btn-primary">Edit Entry</a></span></h1>
					<?php 

					$dogData = get_dogs_for_user($userId);
					
					$args = array (
						'post_type'	=> 'entries',
						'post_status'	=> array('publish'),
						'order'		=> 'ASC',
						'numberposts'	=> -1,
						'author'		=> $userId,
						'meta_query' 	=> array(
							array(
								'key'		=> 'show_id-pm',
								'compare'	=> '=',
								'value'		=> $_GET['view'],
							),
						)
					);
					
					// get posts
					$posts = get_posts($args);
					global $post;
					
					echo '<table style="margin-bottom:0px;"  class="table table-striped table-hover table-responsive">';
					foreach( $posts as $post ) {	
						setup_postdata( $post );
						$entry_data = get_field('entry_data-pm', false, false);
						foreach ($entry_data as $dog_id => $dogEntry){
							$dog = get_dog_by_id($dogData, $dog_id);
							$height = ($dogEntry == 'nfc') ? 'NFC' : $dogEntry['height'];
							echo '<tr><th colspan="3"><h3><span style="color:'.$dog['dog_color'].'">'.$dog['pet_name'].'</span><span class="pull-right"><small>'.$height.'</small></span></h3></th></tr>';
							if($dogEntry == 'nfc'){ continue; }
							foreach ($dogEntry['classes'] as $classNo => $classDetails){
								echo '<tr>
									<td>'.DateTime::createFromFormat('Y-m-j', $classDetails['date'])->format('l').'</td>
									<td>'.$classNo.'. '.$classDetails['class_title'].'</td>
									<td>'.$classDetails['handler'].'</td>
								</tr>';
							}
						}
					}
					echo '</table>';
				}
				else{
					?>
	            	<h1 class="title">My Entries <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a>
	            	&nbsp;<a class="btn btn-primary" href="/enter-show/">Enter Show</a></span></h1>
					<?php 
					$args = array (
							'post_type'		=> 'entries',
							'post_status'	=> array('publish'),
							'numberposts'	=> -1,
							'author'		=> $userId
					);
					
					// get posts
					$posts = get_posts($args);
					global $post;
					$shows = array();
			
					// loop
					if( $posts ) {	
						foreach( $posts as $post ) {	
							setup_postdata( $post );
							$show_id = get_field('show_id-pm', false, false);
							array_push($shows, $show_id);	
						}
						wp_reset_postdata();
						
						$show_args = array(
							'post_type'		=> 'shows',
							'post_status'	=> array('publish'),
							'meta_key'	=> 'start_date',
							'orderby'	=> 'meta_value',
							'order'		=> 'ASC',
							'numberposts'	=> -1,
							'include'		=> $shows
						);
						// get posts
						$show_posts = get_posts($show_args);
						if( $show_posts ) {	
							?>						
							<table class="table table-bordered table-striped table-rounded">
								<tr>
									<th>Date(s)</th>
									<th>Show</th>
									<th>Closes</th>
									<th></th>
								</tr>
							<?php 
							
							foreach( $show_posts as $post ) {	
								setup_postdata( $post );
	
								$start_date = new DateTime(get_field('start_date', false, false));
								$end_date 	= new DateTime(get_field('end_date', false, false));
								$close_date = new DateTime(get_field('close_date', false, false));
								$show_dates = $start_date->format('jS M');
								if($start_date != $end_date){
									$show_dates .= ' to '.$end_date->format('jS M Y');
								}
								else{
									$show_dates .= $start_date->format(' Y');
								}
								?>
								<tr>
									<td><?php echo $show_dates; ?></td>
									<td><?php echo get_the_title(); ?></td>
									<td><?php echo $close_date->format('jS M'); ?></td>
									<td width="195px">
										<a class="btn btn-default btn-sm" href="/account/my-entries/?view=<?php echo the_ID(); ?>">View Entry</a>
										<a class="btn btn-default btn-sm" href="/enter-show/individual-classes/?show=<?php echo the_ID(); ?>">Edit Entry</a>
									</td>
								</tr>
								<?php
							}
							echo '</table>';
							wp_reset_postdata();
						}
					
					} else {
						// no entries found
						echo '
						<div class="alert">You currently have no shows entered on our system. To enter a show, please go to the <a href="/enter-show">Enter Show</a> page.</div>';
					}
				}
				?>
            </div>
            <div class="col-md-3" id="sidebar">
            <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>