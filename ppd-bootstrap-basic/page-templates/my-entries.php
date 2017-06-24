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
            	<h1 class="title">My Entries <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a>
            	&nbsp;<a class="btn btn-primary" href="/enter-show/">Enter Show</a></span></h1>
				<?php 
				$allEntries = $wpdb->get_results("select * from wpao_posts where post_type ='entries' and post_author= '".$wpdb->_real_escape($userId)."'", 'ARRAY_A');
					
				if(!empty($allEntries)) {
					$entryData = array();
					foreach($allEntries as $e) {
						$entry = get_post( $e['ID'] );
						$entry_meta = get_post_meta($e['ID']);
						$show_id = $entry_meta['show_id-pm'][0];
						$show = get_post($show_id);
						$show_meta = get_post_meta($show_id);
						
						$start_date = $show_meta['start_date'][0];
						$end_date = $show_meta['end_date'][0];
						$close_date = $show_meta['close_date'][0];

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
						
						if(isset($entryData[$show_meta['start_date'][0]])){
							
						}
						else{
							$entryData[$show_meta['start_date'][0]] = array(
									'show_id' => $show_id,
									'dates' => $show_dates,
									'show_title' => $show->post_title,
									'closes' => $closes->format('jS M')
							);
						}
					}
					sort($entryData);
					?>
						<table class="table table-bordered table-striped table-rounded">
							<tr>
								<th>Date(s)</th>
								<th>Show</th>
								<th>Closes</th>
								<th></th>
							</tr>
					<?php
					foreach ($entryData as $entry){
						?>
						<tr>
							<td><?php echo $entry['dates']; ?></td>
							<td><?php echo $entry['show_title']; ?></td>
							<td><?php echo $entry['closes']; ?></td>
							<td width="195">
								<a class="btn btn-default btn-sm" href="">View Entry</a>
								<a class="btn btn-default btn-sm" href="/enter-show/individual-classes/?show=<?php echo $entry['show_id']; ?>">Edit Entry</a>
							</td>
						</tr>
						<?php	
						} ?>
						</table>
						<?php 
					} else {
						?>
						<div class="alert">You currently have no shows entered on our system. To enter a show, please go to the <a href="/enter-show">Enter Show</a> page..</div>
						<?php	
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