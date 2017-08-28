<?php /* Template Name: My Entries */ ?>
<?php 


global $current_user, $wpdb;
get_currentuserinfo();

if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$userId = $current_user->ID;
$user_ref = get_user_meta($userId, 'user_ref', true);
if(!isset($user_ref)){
	$ref = sprintf('%04d', $userId);
	update_user_meta($userId, 'user_ref', 'AA'.$ref);
	$user_ref = 'AA'.$ref;
}

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-12" id="main-content">

               	<?php
				
				if(isset($_GET['view']) && $_GET['view']>0) {
					$show = get_post( $_GET['view'] );
					$show_meta = get_post_meta($_GET['view']);
					$title = $show->post_title;
                	$close_date = $show_meta['close_date'][0];
                	$today = date('Ymd');
					?>					
	            	<h1 class="title">ENTRY: <?php echo $title; ?> <span class="pull-right"><a class="btn btn-info" href="/account/my-entries/">My Entries</a>
	            	<?php if ($close_date >= $today){ ?>
	            	&nbsp;<a href="/enter-show/individual-classes/?edit=yes&show=<?php echo $_GET['view']; ?>" class="btn btn-primary">Edit Entry</a>
					<?php }?>
					</span></h1>
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
					
					foreach( $posts as $post ) {
						echo '<table class="table table-striped table-hover table-responsive">';	
						setup_postdata( $post );
						$entry_data = get_field('entry_data-pm', false, false);
						foreach ($entry_data as $dog_id => $dogEntry){
							$dog = get_dog_by_id($dogData, $dog_id);
							$height = ($dogEntry == 'nfc') ? 'NFC' : $dogEntry['height'];
							echo '<tr><th colspan="3"><h3><span style="color:'.$dog['dog_color'].'">'.$dog['pet_name'].'</span><span class="pull-right"><small>'.$height.'</small></span></h3></th></tr>';
							if($dogEntry == 'nfc'){ continue; }
							foreach ($dogEntry['classes'] as $classNo => $classDetails){
								$class_title = $classNo.'. '.$classDetails['class_title'];
								if(isset($classDetails['lho']) && $classDetails['lho']){ $class_title .= '&nbsp;(LHO)'; }
								echo '<tr>
									<td>'.DateTime::createFromFormat('Y-m-j', $classDetails['date'])->format('l').'</td>
									<td>'.$class_title.'</td>
									<td>'.$classDetails['handler'].'</td>
								</tr>';
							}
						}
						echo '</table>';
						
						echo '<div class="row">';
						$camping = get_field('camping-pm', false, false);
						if (isset($camping) && count($camping)>0){
								echo '<div class="col-sm-12 col-md-6">
								<div class="panel panel-default">
							    	<div class="panel-heading"><h4 class="panel-title">Camping</h4></div>
									<div class="panel-body">';
								$camping_options = unserialize($show_meta['camping_options'][0]);
								foreach ($camping_options as $option){
									if ($camping[$option]['pitches'] > 0){
										$pitches = ($camping[$option]['pitches'] == 1) ? ' Pitch' : 'Pitches';
										echo '<p>You have requested to book <strong>'.$camping[$option]['pitches'].$pitches.'</strong>';										
										if (isset($camping['camping_group']) && $camping['camping_group'] != ''){ echo ' and will be camping with the <strong>'.$camping['camping_group'].'</strong> group'; }
										echo '.<br />';
								
										if ($option == 'camp_night'){
											$nights = array();
											foreach ($camping[$option]['nights'] as $night => $status){
												$dateObj = DateTime::createFromFormat('U', $night);
												array_push($nights, $dateObj->format('l'));
											}
											echo ' You have booked for the following night(s): <strong>'.implode(' / ', $nights).'</strong>.<br />';
										}
										echo ' The total amount owed for camping is <strong>&pound;'.sprintf('%.2f', $camping['total_amount']).'</strong>.</p>';
									}
								}
								echo '
									</div>
								</div>							
							</div>';
						}
						$helpers = get_field('helpers-pm', false, false);
						if (isset($helpers) && count($camping)>0){
								echo '<div class="col-sm-12 col-md-6">
								<div class="panel panel-default">
							    	<div class="panel-heading"><h4 class="panel-title">Helpers</h4></div>
									<div class="panel-body">
										<p>Many thanks for offers to help at this show. Your original request is shown below:-</br >
										<ul>';
										foreach ($helpers as $h => $offer){
											echo '<li>'.$h.' - <strong>'.$offer['job'].'</strong> with <strong>'.$offer['group'].'</strong></li>';
										}
										echo '</ul>
									</div>
								</div>							
							</div>';
						}
						echo '</div>';
					}
					
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
					$money = array();
			
					// loop
					if( $posts ) {
						foreach( $posts as $post ) {
							$total_money = 0;
							$outstanding = 0;
							setup_postdata( $post );
							$show_id = get_field('show_id-pm', false, false);
							$total_cost = get_field('total_cost-pm');
							$total_money += $total_cost;
							$outstanding += $total_cost;
							$paid_details = get_field('paid-pm');
							if (isset($paid_details) && $paid_details != ''){
								foreach ($paid_details as $payment){
									$outstanding -= $payment['amount'];
								}
							}
							if (!isset($money[$show_id])){
								$money[$show_id] = array('total_money' => $total_money, 'outstanding' => $outstanding);
							}
							else{
								$money[$show_id]['total_money'] += $total_money;
								$money[$show_id]['outstanding'] += $outstanding;
							}
								
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
									<th class="text-center">Date(s)</th>
									<th class="text-center">Show</th>
									<th class="text-center">Closes</th>
									<th class="text-center">Total</th>
									<th class="text-center">Outstanding</th>
									<th class="text-center" nowrap="nowrap"></th>
								</tr>
							<?php 
							
							foreach( $show_posts as $post ) {	
								setup_postdata( $post );
								$show_id = get_the_ID();
								$title= get_the_title();								
	
								$start_date = new DateTime(get_field('start_date', false, false));
								$end_date 	= new DateTime(get_field('end_date', false, false));
								$close_date = new DateTime(get_field('close_date', false, false));
								$ringplan_file = get_field('ring_plan');

								$show_dates = $start_date->format('jS M');
								if($start_date != $end_date){
									$show_dates .= ' to '.$end_date->format('jS M Y');
								}
								else{
									$show_dates .= $start_date->format(' Y');
								}
								
								$buttons = '
										<a class="btn btn-default btn-sm" href="/account/my-entries/?view='.$show_id.'">View Entry</a>';
								$ring_cards = get_ring_card_info($show_id, $userId);
								
								if ($close_date->format('Ymd') >= date('Ymd')){
									$buttons .= '
										<a class="btn btn-default btn-sm" href="/enter-show/individual-classes/?show='.$show_id.'">Edit Entry</a>';
								}
								elseif (count($ring_cards) > 0){
									$filename = $post->post_name."__".$current_user->user_firstname."-".$current_user->user_lastname.".pdf";
									$buttons = '
										<a class="btn btn-default btn-sm" href=\'javascript:pdf_ring_cards('.json_encode($ring_cards).', "'.$title.'", "'.$show_dates.'", "'.$filename.'");\'><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Ring Cards</a>';
								}
								if (isset($ringplan_file) && is_array($ringplan_file)){
									$buttons .= '
	            						<a class="btn btn-default btn-sm" href="'.$ringplan_file['url'].'" target="_blank"><i class="fa fa-map-o" aria-hidden="true"></i> Ring Plan</a>';
								}
									
								
								$show_dates = $start_date->format('jS M');
								if($start_date != $end_date){
									$show_dates .= ' to '.$end_date->format('jS M Y');
								}
								else{
									$show_dates .= $start_date->format(' Y');
								}
								
								?>
								<tr>
									<td class="text-center"><?php echo $show_dates; ?></td>
									<td class="text-center"><?php echo get_the_title(); ?></td>
									<td class="text-center"><?php echo $close_date->format('jS M'); ?></td>
									<td class="text-center">&pound;<?php echo sprintf("%.2f", $money[$show_id]['total_money']); ?></td>
									<td class="text-center">
									<?php if ($money[$show_id]['outstanding'] > 0){ 
										$paypal_money = $money[$show_id]['outstanding']+($money[$show_id]['outstanding']*0.035)+0.3;
										?>
										&pound;<?php echo sprintf("%.2f", $money[$show_id]['outstanding']); ?>
										<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	                                        <input type="hidden" name="cmd" value="_xclick">
                                       	 	<input type="hidden" name="business" value="agilityaid@outlook.com">
	                                        <input type="hidden" name="amount" value="<?php echo $paypal_money ?>">
                                        	<input type="hidden" name="item_name" value="<?php echo get_the_title(); ?> Entry Fees">
	                                        <INPUT TYPE="hidden" NAME="currency_code" value="GBP">
	                                        <INPUT TYPE="hidden" NAME="return" value="<?php echo get_site_url(); ?>/process-paypal/?result=done&entry=<?php echo the_ID(); ?>&amount=<?php echo $total_money; ?>&user=<?php echo $userId; ?>">
	                                        <input type="hidden" name="first_name" value="<?php echo $current_user->user_firstname; ?>">
	                                        <input type="hidden" name="last_name" value="<?php echo $current_user->user_lastname; ?>">
	                                        <input type="hidden" name="email" value="<?php echo $current_user->user_email; ?>">
	                                        <input type="image" name="submit" border="0"
	                                        src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif"
	                                        alt="PayPal - The safer, easier way to pay online">
	                                	</form>
	                                <?php }
	                                else{ ?>
	                                <span class="label label-success">PAID</span>
	                                <?php } ?>
                                	</td>
									<td class="text-center" nowrap>
										<?php echo $buttons; ?>
									</td>
								</tr>
								<?php
							}
							echo '</table>
							<div class="alert alert-info"><i class="fa fa-bank" aria-hidden="true"></i>&nbsp;Payments can be made directly to the AgilityAid bank account with the following details:-<br />
<div style="margin-left:30px;">			
	Sort Code : 20-41-15<br />
	Account No. : 40542342<br />
	Your Reference : <strong>'.$user_ref.'</strong><br /></div>
		</div>
							<div class="alert alert-warning"><i class="fa fa-paypal" aria-hidden="true"></i>&nbsp;When paying by PayPal, please note that there is a handling fee of 3.5% + 30p added to your transaction.</div>';
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
        </div>
    </div>
</div>

<?php get_footer(); ?>
