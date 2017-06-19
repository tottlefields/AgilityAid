<?php /* Template Name: Enter Show Step 2 (Main Classes) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();

global $current_user, $wpdb;
get_currentuserinfo();

$userId = $current_user->ID;
$user_meta = get_user_meta( $userId );
$handler = $user_meta['first_name'][0].' '.$user_meta['last_name'][0];

$show_id = $_POST['show'] ? $_POST['show'] : $_GET['show'];

if(!empty($show_id)) {
	$data['show_id'] = $show_id;
}

if (empty($data['dogs'])){
	$dogData = get_dogs_for_user($userId);
	if(count($dogData) == 0){
		//No dogs registered for this user...
		wp_redirect(site_url('/account/dogs/'));
		exit;
	}
	$data['dogs'] = $dogData;
}

setCustomSessionData($data);

if(!empty($data['show_id']) && isset($_POST['step-2-submitted'])) {
	$showData = $data;
	
	foreach ($_POST['form-data'][$data['show_id']] as $dog => $classes){
		foreach ($classes as $classNo => $class){
			if (isset($class['status']) && $class['status'] == 'on'){
				$class['handler'] = $handler;
				$showData[$data['show_id']][$dog][$classNo] = $class;
			}
		}
	}

	setCustomSessionData($showData);

	session_write_close();
	wp_redirect(site_url('/enter-show/teams-pairs-classes/'));
	exit;
}

if(empty($data['show_id'])) {
	wp_redirect(site_url('/enter-show/'));
	exit;
}

$show = get_post( $show_id );
$show_meta = get_post_meta($show_id);
$show_type = $show_meta['affiliation'][0];
$classes = unserialize($show_meta['classes'][0]);

if (!empty($show_type)){ $data['show_type'] = $show_type; }
if (!empty($classes)){ $data['classes'] = $classes; }

setCustomSessionData($data);


?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Select Dog/Classes</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li>Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Helpers</li>
					<li class="active">Payment</li>
				</ul>
				<?php //debug_array($data); ?>
				<form action="" method="post" class="form-horizontal" id="entryForm">
					<input type="hidden" id="show" name="show" value="<?php echo $data['show_id']; ?>" />					
                	<input type="hidden" name="step-2-submitted" value="1" />
					<div class="form-group">
						<label for="select_dog" class="control-label col-sm-3">Select Dog</label>
						<div class="col-sm-9">
							<select id="select_dog" name="select_dog" class="form-control">
								<option value="">Select Dog...</option>
								<?php foreach ($data['dogs'] as $dog){
									$level = $dog['meta_data'][$show_type.'_level'];
									$height = $dog['meta_data'][$show_type.'_height'];
									echo '<option value="'.$dog['id'].'"';
									if ($height == '' || $level == ''){ echo ' disabled="disabled"'; }
									echo '>'.$dog['pet_name'].' ('.$height.' / '.$level.')</option>';
									}
								?>
							</select>
				        </div>	
				     </div>
				     <?php foreach ($data['dogs'] as $dog){
				     	$level = $dog['meta_data'][$show_type.'_level'];
						$height = $dog['meta_data'][$show_type.'_height'];
				     echo '<div class="dogClasses" id="classes_for_'.$dog['id'].'" style="border:1px solid '.$dog['dog_color'].'; display:none; margin-bottom:10px;">';
				     if ($height == '' || $level == ''){ 
				     	echo '<p>There are no classes available for this dog to enter. Please edit your dog and set a height and/or level for them.</p>';
				     }
				     else{
				     	echo '<table style="margin-bottom:0px;"  class="table table-striped table-hover table-responsive">';
				     	foreach ($classes as $date => $class_list){
				     		$dateObj = DateTime::createFromFormat('Y-m-j', $date);
				     		echo '<tr><th colspan="5"><h3>'.$dateObj->format('l').'</h3></th></tr>';
				     		foreach ($class_list as $class){
				     			if ($class['noDogs'] > 1){ continue; }	//Teams/Trios/Pairs etc. on next page
				     			
				     			$ok_height = check_class_height($show_type, $height, $class['minHeight'], $class['maxHeight']);
				     			$ok_level = check_class_level($show_type, $level, $class['minLevel'], $class['maxLevel']);
				     			if ($ok_height + $ok_level < 2){ continue; }
				     			
				     			if ($class['minHeight'] != $class['maxHeight']){
				     				$heights = '<select name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][height]">'.get_options_for_heights_minmax($show_type, $height, $class['minHeight'], $class['maxHeight']).'</select>';
				     			}
				     			else{
				     				$heights = $height.'
									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][height]" value="'.$height.'" />';
				     			}
				     			
				     			if ($class['minLevel'] != $class['maxLevel']){
				     				$levels = '';
				     			}
				     			else{
				     				$levels = $level.'
									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][level]" value="'.$level.'" />';
				     			}
				     			
				     			echo '<tr><td>'.$class['classNo'].'. '.$class['className'].'
									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][date]" value="'.$date.'" /></td>
								<td>'.$heights.'</td>
								<td>'.$levels.'</td>
								<td>&pound;'.sprintf("%.2f", $class['price']).'<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][price]" value="'.$class['price'].'" /></td>
								<td class="col-xs-1"><input type="checkbox" name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][status]" />
								</tr>';
				     		}
				     	}
				     	echo '</table>';
				     }
				     echo '</div>';
				     } ?>
				     
                    <div class="control-group">
                        <div class="controls">
                            <input type="submit" value="Next Step &raquo;" name="submit" class="btn btn-success pull-right" />
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
<script>
$(function() {
	var select = $('#select_dog');
	select.on('change', function() {
		$('.dogClasses').hide();
		$('#classes_for_'+$(this).val()).show();
	});
});
</script>
