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
if(!isset($user_meta['user_ref'])){
	$ref = sprintf('%04d', $userId);
	update_user_meta($userId, 'user_ref', 'AA'.$ref);
}
$handlers = array();
if(isset($user_meta['handlers'][0])){
	$handlers = unserialize($user_meta['handlers'][0]);
}
//array_push($handlers, 'Mark Peck');
$handler = $user_meta['first_name'][0].' '.$user_meta['last_name'][0];
array_push($handlers, $handler);
asort($handlers);

$show_id = $_POST['show'] ? $_POST['show'] : $_GET['show'];
$entry_id = 0;

if(!empty($show_id)) {
	$data['show_id'] = $show_id;
}

if(empty($data['show_id'])) {
	wp_redirect(site_url('/enter-show/'));
	exit;
}
else{
	$show_id = $data['show_id'];
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
	
	if(isset($_POST['form-data']['entry_id']) && $_POST['form-data']['entry_id'] > 0){
		$showData['entry_id'] = $_POST['form-data']['entry_id'];
	}
	
	//TODO : Bug when enter dog as NFC then Edit entry, can't add classes.
	foreach ($_POST['form-data'][$data['show_id']] as $dog => $classData){
		$dog_height = '';
		if ($classData === 'nfc'){
			$showData[$data['show_id']][$dog] = 'nfc';
			continue;
		}
		else{
			if(isset($classData['height'])){
				$dog_height = $classData['height'];
			}
			if (isset($classData['classes'])){
				foreach ($classData['classes'] as $classNo => $class){
					if (isset($class['status']) && $class['status'] == 'on'){
						$class['height'] = $dog_height;
						$showData[$data['show_id']][$dog]['classes'][$classNo] = $class;
						$showData[$data['show_id']][$dog]['height'] = $dog_height;
					}
				}
				if(!isset($showData[$data['show_id']][$dog]['classes']) || count($showData[$data['show_id']][$dog]['classes']) == 0){
					$showData[$data['show_id']][$dog] = 'nfc';				
				}
			}
		}
	}
	
	foreach ($showData['dogs'] as $dog){
		if (!isset($showData[$data['show_id']][$dog['id']])){
			$showData[$data['show_id']][$dog['id']] = 'nfc';
		}
	}

	setCustomSessionData($showData);
	
	session_write_close();
	wp_redirect(site_url('/enter-show/teams-pairs-classes/'));
	exit;
}


//check if this user has already entered this show and ask re editing/cancelling
if(isset($_GET['edit']) && $_GET['edit'] === 'yes'){
	//confirmed editing of the show entry...
	$args = array (
		'post_type'	=> 'entries',
		'post_status'	=> array('publish'),
		'order'		=> 'ASC',
		'numberposts'	=> 10,
		'author'		=> $userId,
		'meta_query' 	=> array(
			array(
				'key'		=> 'show_id-pm',
				'compare'	=> '=',
				'value'		=> $show_id,
			),
		)
	);

	// get posts
	$posts = get_posts($args);
	global $post;
	foreach( $posts as $post ) {
		$data = get_field('show_data-pm', false, false);
		$entry_id = get_the_ID();
		continue;
	}
}
else{
	$args = array (
		'post_type'	=> 'entries',
		'post_status'	=> array('publish'),
		'order'		=> 'ASC',
		'numberposts'	=> 10,
		'author'		=> $userId,
		'meta_query' 	=> array(
			array(
				'key'		=> 'show_id-pm',
				'compare'	=> '=',
				'value'		=> $show_id,
			),
		)
	);
	
	// get posts
	$posts = get_posts($args);
	if(count($posts) > 0){
		$error = '<br />
		<div class="alert alert-warning">
			You appear to have already entered this show, please click below indicating how you wish to proceed:-
			<hr />
			<div class="btn-group btn-group-justified btn-group-lg" role="group">
				<a href="/enter-show/confirmation/?delete='.$show_id.'" class="btn btn-danger">Delete entry and start again.</a>
				<a href="/enter-show/individual-classes/?show='.$show_id.'&edit=yes" class="btn btn-success">Edit current entry.</a>
			</div>
		</div>';
	}
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
				<?php //debug_array($data); ?>
				<?php if ($error == "") {?>
				<h1>Select Dog/Classes</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li>Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Helpers</li>
					<li class="active">Payment</li>
				</ul>
				<form action="" method="post" class="form-horizontal" id="entryForm">
					<input type="hidden" id="show" name="show" value="<?php echo $data['show_id']; ?>" />					
                	<input type="hidden" name="step-2-submitted" value="1" />
                	<input type="hidden" name="form-data[entry_id]" value="<?php echo $entry_id; ?>" />
					<div class="form-group">
						<label for="select_dog" class="control-label col-sm-2">Select Dog</label>
						<div class="col-sm-5">
							<select id="select_dog" name="select_dog" class="form-control">
								<option value="">Select Dog...</option>
								<?php foreach ($data['dogs'] as $dog){
									$level = $dog['meta_data'][$show_type.'_level'];
									$height = $dog['meta_data'][$show_type.'_height'];
									echo '<option value="'.$dog['id'].'"';
									if ($height == '' || $level == ''){ echo ' disabled="disabled">'.$dog['pet_name'].' (NFC)</option>'; }
									else{echo '>'.$dog['pet_name'].' ('.$height.' / '.$level.')</option>'; }
									}
								?>
							</select>
				        </div>
						<label for="select_height" class="control-label col-sm-2">Height</label>
						<div class="col-sm-3">
						<?php
						foreach ($data['dogs'] as $dog){
							$dog_height = $dog['meta_data'][$show_type.'_height'];
							if(isset($data[$show_id][$dog['id']]['height'])) { $dog_height = $data[$show_id][$dog['id']]['height']; }
							echo '<select class="dogHeights form-control" style="display:none;" id="heights_for_'.$dog['id'].'" name="form-data['.$show_id.']['.$dog['id'].'][height]">'.get_options_for_heights($show_type, $dog_height).'</select>';
						}
						?>
						</div>
						
				     </div>
				     <?php foreach ($data['dogs'] as $dog){
				     	$level = $dog['meta_data'][$show_type.'_level'];
						$height = $dog['meta_data'][$show_type.'_height'];
				     echo '<div class="dogClasses" id="classes_for_'.$dog['id'].'" style="border:1px solid '.$dog['dog_color'].'; display:none; margin-bottom:10px;">';
				     if ($height == '' || $level == ''){ 
				     	echo '<p>There are no classes available for this dog to enter. Please edit your dog and set a height and/or level for them.</p>
						<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].']" value="nfc" />';
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
				     			
				     			if(count($handlers) > 1){
				     				$handlers_for_class = '<select class="form-control" name="form-data['.$show_id.']['.$dog['id'].'][classes]['.$class['classNo'].'][handler]">';
				     				foreach ($handlers as $h){
				     					$class_handler = (isset($data[$show_id][$dog['id']]['classes'][$class['classNo']]['handler'])) ? $data[$show_id][$dog['id']]['classes'][$class['classNo']]['handler'] : $handler;
				     					$handlers_for_class .= '<option value="'.$h.'"';
				     					if ($h == $class_handler){ $handlers_for_class .= ' selected="selected"'; }
				     					$handlers_for_class .= '>'.$h.'</option>';
				     				}
				     				$handlers_for_class .= '</select>';
				     				
				     			}
				     			else{
				     				$handlers_for_class = $handlers[0].'
									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].'][classes]['.$class['classNo'].'][handler]" value="'.$handlers[0].'" />';
				     			}
				     			
//				     			if ($class['minHeight'] != $class['maxHeight']){
//				     				$heights = '<select name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][height]">'.get_options_for_heights_minmax($show_type, $height, $class['minHeight'], $class['maxHeight']).'</select>';
//				     			}
//				     			else{
//				     				$heights = $height.'
//									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].']['.$class['classNo'].'][height]" value="'.$height.'" />';
//				     			}
				     			
				     			if ($class['minLevel'] != $class['maxLevel']){
				     				$levels = '';
				     				# TODO
				     			}
				     			else{
				     				$levels = $level.'
									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].'][classes]['.$class['classNo'].'][level]" value="'.$level.'" />';
				     			}
				     			
				     			echo '<tr><td>'.$class['classNo'].'. '.$class['className'].'
									<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].'][classes]['.$class['classNo'].'][date]" value="'.$date.'" /></td>
								<td>'.$handlers_for_class.'</td>
								<td>'.$levels.'</td>
								<td>&pound;'.sprintf("%.2f", $class['price']).'<input type="hidden" name="form-data['.$show_id.']['.$dog['id'].'][classes]['.$class['classNo'].'][price]" value="'.$class['price'].'" /></td>
								<td class="col-xs-1"><input type="checkbox" name="form-data['.$show_id.']['.$dog['id'].'][classes]['.$class['classNo'].'][status]"';
								if(isset($data[$show_id][$dog['id']]['classes'][$class['classNo']]['status']) && $data[$show_id][$dog['id']]['classes'][$class['classNo']]['status'] == 'on'){ echo ' checked="checked"';}
								echo '/>
								</tr>';
				     		}
				     	}
				     	echo '</table>';
				     }
				     echo '</div>';
				     } ?>
				     
				    <div class="alert alert-info">Any dog(s) not entered into a class over the duration of the show will automatically be entered Not for Competition (NFC).</div>
                    <div class="control-group">
                        <div class="controls">
                        	<span class="pull-right">
	                            <a href="javascript:selectNextDog()" id="next_dog" class="btn btn-default">Next Dog &raquo;</a>&nbsp;
	                            <input type="submit" value="Next Step &raquo;" name="submit" class="btn btn-success" />
                            </span>
                       </div>
                   </div>
				</form>
				<?php }
				else{ 
					echo '<h1>Previous Entry Found</h1>';
					echo $error;
				}?>
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
		$('.dogHeights').hide();
		$('#heights_for_'+$(this).val()).show();

		if ($(this).val() === $('#select_dog').children(':enabled:last').val()){
			console.log("last dog selected!");
			$("#next_dog").attr("disabled", "disabled");
			//$("input[type=submit]").removeAttr("disabled");
		}
		else{
			//$("input[type=submit]").attr("disabled", "disabled");
			$("#next_dog").removeAttr("disabled");
		}
	});
});

function selectNextDog(e){
	if ($("#next_dog").is(":disabled")){
		return false;
	}
	var nextDog = $('#select_dog :selected').nextAll(':not(:disabled)').first().val();
	$('#select_dog').val(nextDog).change();;
}
</script>
