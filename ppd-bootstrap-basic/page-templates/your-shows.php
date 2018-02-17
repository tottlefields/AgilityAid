<?php /* Template Name: Your Shows */ ?>
<?php 

global $current_user, $wpdb;
get_currentuserinfo();

if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}
if (!in_array( 'author', $current_user->roles ) && !in_array( 'administrator', $current_user->roles )){
	wp_redirect(site_url('/account/'));
	exit;
}

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-12" id="main-content">

               	<?php				
				if(isset($_GET['view']) && $_GET['view']>0) {
					
				}
				else{
					?>
	            	<h1 class="title">Your Shows <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a></span></h1>
					<?php 
					$args = array (
							'post_type'		=> 'shows',
							'post_status'	=> array('publish'),
							'numberposts'	=> -1,
							'meta_query' 	=> array(
								array(
									'key'		=> 'end_date',
									'compare'	=> '>=',
									'value'		=> date('Ymd'),
								),
							)
					);
					if (in_array( 'author', $current_user->roles )){
						$args['author'] = $current_user->ID;
					}
					
					// get posts
					$posts = get_posts($args);
					global $post;
			
					// loop
					if( $posts ) {
						echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
						foreach( $posts as $post ) {
							setup_postdata( $post );
							$show_id = get_the_ID();

							$start_date = get_field('start_date', false, false);
							$end_date = get_field('end_date', false, false);
							$close_date = get_field('close_date', false, false);
                			$online_link = get_field('online_show_entry_link', false, false);
							
	                		$start_date = new DateTime($start_date);
	                		$end_date = new DateTime($end_date);
	                		$closes = new DateTime($close_date);
	                		$show_dates = $start_date->format('jS M');
	                		
	                		if($start_date != $end_date){
	                			$show_dates .= ' to '.$end_date->format('jS M Y');
	                		}
	                		else{
	                			$show_dates .= $start_date->format(' Y');
	                		}
                		
	                		//green (success) for open shows; orange (warning) for closing in 7 days; red (danger) for closed
	                		$panel_class = 'success';
	                		$closes_text = '&nbsp;';
	                		$links = array();

	                		//array_push($links, '<a href="javascript:getCompNumbers('.$show_id.')">View Numbers</a>');
					if (in_array( 'administrator', $current_user->roles )){
                                                        if ($online_link == ''){
                                                                array_push($links, '<a href="javascript:getEntryDetails('.$show_id.')">Download Entries</a>');
                                                        }
					}
	                		
	                		if ($close_date < date('Ymd')){
	                			$panel_class = 'danger';
	                			$closes_text = 'CLOSED';
	                			if (in_array( 'administrator', $current_user->roles )){
	                				if ($online_link == ''){
	                					array_push($links, '<a href="javascript:getEntryDetails('.$show_id.')">Download Entries</a>');
	                				}	                			
	                			
		                			$ring_cards_ready = $wpdb->get_row("select count(*) as RingCardCount from wpao_posts p inner join wpao_ring_card_info rc on p.ID=rc.post_id WHERE post_parent=".$show_id);
		                			if ($ring_cards_ready->RingCardCount > 0){
	                					array_push($links, '<a href="javascript:viewShowEntries('.$show_id.')">View Show Entries</a>');
		                			}
	                			}
	                		}
	                		elseif ($close_date <= date('Ymd', strtotime("+7 day"))){
	                			$panel_class = 'warning';
	                			$closes_text = 'Closes: '.$closes->format('D jS M');
	                		}
	                		
							echo '
	                		<div class="panel panel-'.$panel_class.'">
						    	<div class="panel-heading" role="tab" id="heading'.$show_id.'">
						      		<h4 class="panel-title">
	                					<span class="pull-right">'.$closes_text.'</span>
						        		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$show_id.'" aria-expanded="false" aria-controls="collapseOne">' . get_the_title() . ' ('.$show_dates.')</a>
						      		</h4>
						    	</div>
						    	<div id="collapse'.$show_id.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading'.$show_id.'">
						      		<div class="panel-body">
						        		<div class="row"><div class="col-sm-12">'.implode($links, ' || ').'</div></div>
						      		</div>
						    	</div>
						  	</div>';
						}
						echo '</div>';
					} else {
						// no shows found
						echo '
						<div class="alert">You currently have no shows on our system. Please <a href="/contact-us">contact AgilityAid</a> to add your show to this system for online entries.</div>';
					}
				}
				?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
