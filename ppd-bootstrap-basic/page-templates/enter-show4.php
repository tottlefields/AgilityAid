<?php /* Template Name: Enter Show Step 4 (Camping) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();

$show = get_post( $data['show_id'] );
$show_meta = get_post_meta($data['show_id']);


if (isset($_POST['step-4-submitted'])) {
	$total_pitches = 0;
	$camping_options = unserialize($show_meta['camping_options'][0]);
	foreach ($camping_options as $option){
		$total_pitches += $_POST[$option.'_pitches'];
	}
	$camping = array();
	if ($total_pitches > 0){
		$total_amount = 0;
		foreach ($camping_options as $option){
			$camping[$option] = array(
					'pitches' => $_POST[$option.'_pitches'],
					'nights'  => $_POST[$option.'_nights'],
			);
			$camping['camping_group'] = (isset($_POST[$option.'_group']) && $_POST[$option.'_group'] != '') ? $_POST[$option.'_group'] : $camping['camping_group'];
			$cost_per_option = $_POST[$option.'_pitches'] * $show_meta[$option.'_price'][0];
			if (count($_POST[$option.'_nights']) > 1){ $cost_per_option *= count($_POST[$option.'_nights']); }
			$total_amount += $cost_per_option;
		}
		$camping['total_pitches'] = $total_pitches;
		$camping['total_amount'] = $total_amount;
	}
	$data['camping'] = $camping;	
	setCustomSessionData($data);
	
	wp_redirect(site_url('/enter-show/helpers/'));
	exit;
}

if (!isset($show_meta['camping_avail']) || $show_meta['camping_avail'][0] != 1){
	wp_redirect(site_url('/enter-show/helpers/'));
	exit;
}

//debug_array($show_meta);

$camp_from = $show_meta['camp_from'][0];
$camp_to   = $show_meta['camp_to'][0];
$camping_options = unserialize($show_meta['camping_options'][0]);
$pitch_limit = $show_meta['pitch_limit'][0];
$camping_limit = $show_meta['camping_limit'][0];
$camping_booked = $show_meta['camping_booked'][0];


$fromDateObj = DateTime::createFromFormat('Ymd H:i', $camp_from.' 12:00');
$toDateObj   = DateTime::createFromFormat('Ymd H:i', $camp_to.' 12:00');

$limited_camping = '';
if ($pitch_limit > 0){
	$limited_camping = '<p>The maximum number of pitches that you can book with each entry is '.$pitch_limit.'.</p>';
}

$camping = (isset($data['camping'])) ? $data['camping'] : array();

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Camping</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li class="active">Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li>Camping</li>
					<li class="active">Helpers</li>
					<li class="active">Payment</li>
				</ul>
				<form action="" method="post" class="form-horizontal" id="campingForm">					
                	<input type="hidden" name="step-4-submitted" value="1" />
                	<?php 
                	if ($camping_booked >= $camping_limit){ ?>	
					<div class="alert alert-danger">
						<p>Camping is currently full. Please enter the number of camping spots you wish to book to be added to the waiting list.</p>
					</div>
                		<?php
                	}
                	else {
                	?>
                	<div class="well">Camping is available at this show from <strong><?php echo $fromDateObj->format('l jS M'); ?></strong> through to <strong><?php echo $toDateObj->format('l jS M'); ?></strong>.</p>
                	<?php echo $limited_camping; ?>
                	<p>Please fill in details below to book your pitch(es).</p></div>
                	<?php 
                		$opt_count = 1;
	                	foreach ($camping_options as $option){
	                		$camp_group = ($camping[$option]['pitches'] > 0) ? $camping['camping_group'] : '';
	                		echo '
	                		<div class="panel panel-default">
						    	<div class="panel-heading">';
	                		if ($option == 'camp_whole'){
	                			echo '<h4 class="panel-title">Option #'.$opt_count.'. Pitches for the Whole Show</h4></div>
								<div class="panel-body">
			                        <div class="form-group">
			                        	<label for="'.$option.'_pitches" class="col-sm-4 control-label">No of Pitches @ &pound;'.sprintf("%.2f", $show_meta[$option.'_price'][0]).'</label>
			                        	<div class="col-sm-2">
			                        		<input type="text" class="form-control" id="'.$option.'_pitches" name="'.$option.'_pitches" placeholder="" value="'.$camping[$option]['pitches'].'" />
			                        	</div>
			                        </div>
			                        <div class="form-group">
			                        	<label for="'.$option.'_group" class="col-sm-4 control-label">Camping Group</label>
			                        	<div class="col-sm-8">
			                        		<input type="text" class="form-control" id="'.$option.'_group" name="'.$option.'_group" placeholder="Group to camp with" value="'.$camp_group.'" />
			                        	</div>
			                        </div>';	
	                		}
	                		elseif ($option == 'camp_night'){
	                			echo '<h4 class="panel-title">Option #'.$opt_count.'. Pitches per Night</h4>
						    	</div>
								<div class="panel-body">
			                        <div class="form-group">
			                        	<label for="'.$option.'_pitches" class="col-sm-4 control-label">No of Pitches @ &pound;'.sprintf("%.2f", $show_meta[$option.'_price'][0]).'/night</label>
			                        	<div class="col-sm-2">
			                        		<input type="text" class="form-control" id="'.$option.'_pitches" name="'.$option.'_pitches" placeholder="" value="'.$camping[$option]['pitches'].'" />
			                        	</div>
			                        </div>
			                        <div class="form-group">
			                        	<label for="'.$option.'_nights[]" class="col-sm-4 control-label">Select Night(s)</label>
			                        	<div class="col-sm-8">';
	                					for ($i = $fromDateObj->format('U'); $i<$toDateObj->format('U'); $i += 60*60*24){
	                						$dateObj = DateTime::createFromFormat('U', $i);
			                        		echo '<label class="checkbox-inline"><input type="checkbox" name="'.$option.'_nights['.$i.']"';
			                        		if (isset($camping[$option]['nights'][$i]) ){ //&& $camping[$option]['nights'][$i] == 'on'){
			                        			echo ' checked="checked"';
			                        		}
			                        		echo  '> '.$dateObj->format('l').'</label>';
	                					}
			                        	echo '
                						</div>
			                        </div>
			                        <div class="form-group">
			                        	<label for="'.$option.'_group" class="col-sm-4 control-label">Camping Group</label>
			                        	<div class="col-sm-8">
			                        		<input type="text" class="form-control" id="'.$option.'_group" name="'.$option.'_group" placeholder="Group to camp with" value="'.$camp_group.'" />
			                        	</div>
			                        </div>';		                			
	                		}
						     echo ' 		
								</div>
	                		</div>';	                		
	                		$opt_count++;
	                	}
                	}
                	?>
                	
					<div class="control-group">
                        <div class="controls">
                        	<span class="pull-right">
	                            <input type="submit" value="Next Step &raquo;" name="submit" class="btn btn-success" />
                            </span>
                       </div>
                   </div>
				</form>
            </div>
            <div class="col-md-3" id="sidebar">
            	<?php dynamic_sidebar('Entry Sidebar'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>