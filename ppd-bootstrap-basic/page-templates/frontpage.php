<?php /* Template Name: Frontpage Template */ ?>
<?php get_header(); ?>

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="jumbotron">
        <h1>Welcome to Agility Aid!</h1>
	<p>The original Show Processing Service for dog agility shows in the UK, now with online entries and with electronic scoring/results coming for the 2018 season.</p>
	<p>If you would like to discuss how Agility Aid can help your show, please <a href="/contact/">get in touch here</a>.</p>
        <!-- <p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p> -->
      </div>

<!--       <div class="alert alert-danger">
	<p><strong>Scheduled Website Maintenance - 25th May</strong></p>
<p>Our website will be unavailable for a short period of time on Friday 25th May, from approx 9am (BST) to 12 noon (BST) for scheduled maintenance.<br /></p>
<p>We apologise for any inconvenience this may cause.<br />
      </div> -->
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-4">
          <h2>Grade Changes</h2>
          <p>Please complete the online form to submit your grade changes, you will need to submit a separate form for each show needing grade changes.<br />All wins up to and including 25 days before the start of the competition shall be counted.<br />Grade change requests must be received no later than 14 days before the show, but please send as soon as possible.</p>
          <p><a class="btn" href="/grade-changes/">Submit Grade Change &raquo;</a></p>
        </div>
        <div class="col-md-4">
          <h2>Ring Cards/Show Information</h2>
          <p>You can view and download ring cards and other show information for each show in the <a href="/shows/">shows</a> page.<br />
If you enter a show by post and choose to download your ring cards please ensure you print them before the show as they will not be posted to you.</p>
       </div>
        <div class="col-md-4">
          <h2>Live Shows</h2>
          <p>
<?php		
	$args = array (
		'post_type'		=> 'shows',
		'post_status'	=> array('publish'),
		'meta_key'		=> 'start_date',
		'orderby'		=> 'meta_value',
		'order'			=> 'ASC',
		'numberposts'	=> 20,
		'meta_query' 	=> array(
			array(
				'key'		=> 'close_date',
				'compare'	=> '>=',
				'value'		=> date('Ymd'),
			),
		)
	);
	$shows = new WP_Query ( $args );
	if ( $shows->have_posts() ) {
		while ( $shows->have_posts() ) {
			$shows->the_post();
			$label = 'success';
			if (get_field('close_date', false, false) < date('Ymd')){ $label = 'danger'; }
			elseif (get_field('close_date', false, false) <= date('Ymd', strtotime("+7 day"))){ $label = 'warning'; }
			echo '<a href="/shows/#'.$post->post_name.'" class="btn btn-success btn-xs">' . get_the_title() . '</a> ';
			//echo '<a href="'.get_the_permalink().'" style="text-decoration:none;"><span class="label label-'.$label.'" style="line-height:2;">' . get_the_title() . '</span></a> ';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	}
	else{
		echo '<small>There are currently no shows live on our system.</small>';
	}
?>
			</p>
          <h2>Coming Soon</h2>
          <p>
<?php
	$args = array (
		'post_type'	=> 'shows',
		'post_status'	=> array('pending'),
		'meta_key'	=> 'start_date',
		'orderby'	=> 'meta_value',
		'order'		=> 'ASC',
		'numberposts'	=> 20,
		'meta_query' 	=> array(
			array(
				'key'		=> 'end_date',
				'compare'	=> '>=',
				'value'		=> $today,
			),
		)
	);
	$shows = new WP_Query ( $args );
	if ( $shows->have_posts() ) {
		while ( $shows->have_posts() ) {
			$shows->the_post();
			echo '<span class="label label-default" style="line-height:2">' . get_the_title() . '</span> ';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	}
	else{
		echo 'here';
	}
?>
			</p>
          <p><a class="btn" href="/shows/">View Shows &raquo;</a></p>
        </div>
      </div>

<?php get_footer(); ?>
