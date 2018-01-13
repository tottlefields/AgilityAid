<?php /* Template Name: Enter Show Step 3 (Pairs/Trios/Teams etc.) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();

if (isset($_POST['step-3-submitted'])) {
	if(isset($_POST['form-data'][$data['show_id']]['pairs_teams']) ){
		$pairs_teams = $_POST['form-data'][$data['show_id']]['pairs_teams'];
		$data['pairs_teams'] = $pairs_teams;
		setCustomSessionData($data);
	}
	wp_redirect(site_url('/enter-show/camping/'));
	exit;
}

$seen_multi_dog_class = 0;
foreach ($data['classes'] as $date => $class_list){
	foreach ($class_list as $class){
		if ($class['noDogs'] > 1){ $seen_multi_dog_class = 1; break; }
	}
}

//No Pairs/Trios/Teams etc classes at this show, skip step.
if(!$seen_multi_dog_class){
	wp_redirect(site_url('/enter-show/camping/'));
	exit;	
}

global $current_user, $wpdb;
get_currentuserinfo();

$userId = $current_user->ID;
$user_meta = get_user_meta( $userId );
if(!isset($user_meta['user_ref'])){
	$ref = sprintf('%04d', $userId);
	update_user_meta($userId, 'user_ref', 'AA'.$ref);
	$user_meta['user_ref'] = 'AA'.$ref;
}


$show = get_post( $data['show_id'] );
$show_meta = get_post_meta($data['show_id']);
$show_type = $data['show_type'];
$show_id = $data['show_id'];
$classes = $data['classes'];
$lho_options = $data['lho_options'];


$handlers = array();
if(isset($user_meta['handlers'][0])){
	$handlers = unserialize($user_meta['handlers'][0]);
}
//array_push($handlers, 'Mark Peck');
$handler = $user_meta['first_name'][0].' '.$user_meta['last_name'][0];
array_push($handlers, $handler);
asort($handlers);

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<?php //debug_array($data); ?>
				<?php if ($error == "") {?>
				<h1>Pairs/Teams Classes</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li class="active">Individual Classes</li>
					<li>Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Helpers</li>
					<li class="active">Payment</li>
				</ul>
				<div class="alert alert-info">You should see a minimum of one option to enter any pairs/trios/teams classes at this show. If you have multiple dogs eligible you will see extra rows to enter the same class multiple times. PLease leave anything blak where you don't need to use the entry space.</div>
				<form action="" method="post" class="form-horizontal" id="entryForm">
					<input type="hidden" id="show" name="show" value="<?php echo $data['show_id']; ?>" />					
                	<input type="hidden" name="step-3-submitted" value="1" />

				     <?php 
				     	foreach ($classes as $date => $class_list){
				     		$dateObj = DateTime::createFromFormat('Y-m-j', $date);
				     		echo '<h3>'.$dateObj->format('l').'</h3>';
				     		foreach ($class_list as $class){
				     			if ($class['noDogs'] == 1){ continue; }	//Individual classes on previous page
				     			
				     			$min_entries = 1;
				     			$eligible_dogs = array();
				     			foreach ($data['dogs'] as $dog){
				     				$level = $dog['meta_data'][$show_type.'_level'];
				     				$height = $dog['meta_data'][$show_type.'_height'];
					     			$ok_height = check_class_height($show_type, $height, $class['minHeight'], $class['maxHeight']);
					     			$ok_level = check_class_level($show_type, $level, $class['minLevel'], $class['maxLevel']);
					     			if ($ok_height + $ok_level >= 2){ $min_entries++; array_push($eligible_dogs, $dog); }
				     			} ?>
				     			
								<div class="panel panel-default">
									<div class="panel-heading">
										<?php echo $class['classNo'].'. '.$class['className'].' - &pound;'.sprintf("%.2f", $class['price']);
											echo '<input type="hidden" name="form-data['.$show_id.'][pairs_teams]['.$class['classNo'].'][className]" value="'.$class['className'].'" />'; 
											echo '<input type="hidden" name="form-data['.$show_id.'][pairs_teams]['.$class['classNo'].'][price]" value="'.$class['price'].'" />'; 
										?>
										<?php if (count($eligible_dogs) > 0){ 
											echo '<span class="pull-right">';
											foreach ($eligible_dogs as $d){
												echo '<i class="fa fa-square" aria-hidden="true" style="color:'.$d['dog_color'].'" title="'.$d['pet_name'].'"></i>&nbsp;';
											}
											echo '</span>'; 
										}?>
									</div>
									<div class="panel-body">
										<table style="margin-bottom:0px;"  class="table table-striped table-hover table-responsive">
				     			<?php for ($i=0; $i<$min_entries; $i++){
				     				echo '<tr>
										<td>';
				     					for ($j=0; $j<$class['noDogs']; $j++){
				     						echo '
											<div class="form-group">
												<div class="col-sm-5"><input type="text" class="form-control" name="form-data['.$show_id.'][pairs_teams]['.$class['classNo'].']['.$i.']['.$j.'][handler]" placeholder="Handler '.($j+1).'" value="'.$data['pairs_teams'][$class['classNo']][$i][$j]['handler'].'" /></div>
												<div class="col-sm-7"><input type="text" class="form-control" name="form-data['.$show_id.'][pairs_teams]['.$class['classNo'].']['.$i.']['.$j.'][dog]" placeholder="Dog '.($j+1).'" value="'.$data['pairs_teams'][$class['classNo']][$i][$j]['dog'].'" /></div>
												<!--<div class="col-sm-2"><input type="text" class="form-control" name="" placeholder="" value="" /></div>
												<div class="col-sm-2"><input type="text" class="form-control" name="" placeholder="" value="" /></div>-->
											</div>';
				     					}
				     					echo '
			                        	</div>
			                        </div>
									</tr>';
				     			} ?>
										</table>
									</div>
								</div>
				     		<?php }
				    	} ?>
				    	<div class="control-group">
                        <div class="controls">
                        	<span class="pull-right">
	                            <input type="submit" value="Next Step &raquo;" name="submit" class="btn btn-success" />
                            </span>
                       </div>
                   </div>
				</form>
				<?php }
				else{ 
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
</script>
