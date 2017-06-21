<?php
/*
Template Name: Agility Dogs
*/

global $current_user, $wpdb;
get_currentuserinfo();
				
$userId = $current_user->ID;

if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

if(isset($_POST['submit'])) {
	
	$formData = $_POST;
	unset($formData['submit']);
	unset($formData['dogID']);
	$formData['birth_date'] = dateToSQL($formData['birth_date']);


	if ($_POST['dogID'] > 0){
		//Dog meta data.
		foreach (array('kc_level', 'kc_height', 'bs_height', 'bs_level') as $meta_key){
			if (isset($formData[$meta_key])){
				$sql = $wpdb->prepare(
						'INSERT INTO wpao_agility_dogsmeta (dog_id, meta_key, meta_value) 
						VALUES (%d, "%s", "%s") ON DUPLICATE KEY UPDATE meta_value = "%s"', 
						$_POST['dogID'], $meta_key, $formData[$meta_key], $formData[$meta_key]);
				$wpdb->query($sql);
				unset($formData[$meta_key]);
			}
		}
		$result = $wpdb->update('wpao_agility_dogs', $formData, array('id' => $_POST['dogID']));
	}
	else{
		$metaData = array();
		foreach (array('kc_level', 'kc_height', 'bs_height', 'bs_level') as $meta_key){
			if (isset($formData[$meta_key])){
				$metaData[$meta_key] = $formData[$meta_key];
				unset($formData[$meta_key]);				
			}
		}
		
		$userId = $current_user->ID;
		$formData['user_id'] = $userId;
		$wpdb->insert('wpao_agility_dogs', $formData);
		$dogID = $wpdb->insert_id;
		
		foreach ($metaData as $key => $value){
				$sql = $wpdb->prepare(
						'INSERT INTO wpao_agility_dogsmeta (dog_id, meta_key, meta_value) 
						VALUES (%d, "%s", "%s") ON DUPLICATE KEY UPDATE meta_value = "%s"', 
						$dogID, $key, $value, $value);
				$wpdb->query($sql);			
		}		
	}
	
	wp_redirect('/account/dogs/?updated=1');
	exit;
}

wp_enqueue_style('colorpicker-css', get_stylesheet_directory_uri().'/css/palette-color-picker.css');

get_header();

?>
<div id="content" class="standard">
    <div class="container">
        <div class="row">
        	<div class="col-md-9" id="main-content">

               	<?php
				
				if(isset($_GET['edit']) && isset($_GET['dogID'])) {
					
					$animal = $wpdb->get_row("SELECT * FROM wpao_agility_dogs WHERE `id` = '".$wpdb->_real_escape($_GET['dogID'])."'", 'ARRAY_A');
					
					?>
                    <h1 class="title"><i class="fa fa-paw" aria-hidden="true"></i>&nbsp;<?php echo !empty($_GET['dogID']) ? 'Edit' : 'Add'; ?> Dog</h1>
                    
                    <form class="form-horizontal" action="" method="post">
                    	
                        <input type="hidden" name="dogID" value="<?php echo strip_tags($_GET['dogID']); ?>" />
                        
                        <div class="form-group">
                        	<label for="kc_name" class="col-sm-2 control-label">KC Name</label>
                        	<div class="col-sm-10">
                        		<input type="text" class="form-control" id="kc_name" name="kc_name" placeholder="KC Registered Name" value="<?php echo strip_tags(stripslashes($animal['kc_name'])); ?>" />
                        	</div>
                        </div>
                    	
                    	<div class="form-group">
                        	<label for="pet_name" class="control-label col-sm-2">Pet Name</label>
                        	<div class="col-sm-4">
                            	<input type="text" class="form-control" id="pet_name" name="pet_name" placeholder="Pet Name" value="<?php echo strip_tags($animal['pet_name']); ?>" />
                            </div>
                            
                        	<label for="kc_number" class="control-label col-sm-2">KC Number</label>
                        	<div class="col-sm-4">
                        		<input type="text" class="form-control" id="kc_number" name="kc_number" placeholder="KC Registration Number" value="<?php echo strip_tags($animal['kc_number']); ?>">
                        	</div>
                        </div>
                        
                        <?php
                        	$breeds = get_terms('dog-breeds', array('hide_empty' => false));
                           $dogBreeds = array();
                           foreach($breeds as $b) {
                           	   $dogBreeds[$b->term_id] = array('name' => $b->name, 'slug' => $b->slug);
                           }
                         ?>
                         
                         <div class="form-group">
                        	<label for="breed" class="control-label col-sm-2">Breed</label>
                            <div class="col-sm-10">
								<select name="breed" class="form-control">
									<option value="">Select Breed...</option>
									<?php echo get_options_for_term('dog-breeds', $dogBreeds, $animal['breed']); ?>
								</select>
							</div>
                         </div>
                        
                        <div class="form-group">
                        	<label for="birth_date" class="control-label col-sm-2">Birth Date</label>
                        	<div class="col-sm-3">
                        		<input type="text" class="form-control datepicker-me" id="birth_date" name="birth_date" placeholder="Date of Birth" value="<?php echo strip_tags(SQLToDate($animal['birth_date'])); ?>" />
                        	</div>
                        	<div class="col-sm-1">
                        		<label class="checkbox-inline"><input type="checkbox" id="birth_date_unknown" <?php if($animal['birth_date'] == 'unknown') { echo 'checked="checked"'; } ?>> Unknown</label>
                        	</div>
                        	<label for="sex" class="control-label col-sm-2">Sex</label>
                        	<div class="col-sm-4">
                        		<label class="radio-inline"><input type="radio" name="sex" value="Dog" <?php if($animal['sex'] == 'Dog') { echo 'checked="checked"'; } ?>> Dog</label>
                        		<label class="radio-inline"><input type="radio" name="sex" value="Bitch" <?php if($animal['sex'] == 'Bitch') { echo 'checked="checked"'; } ?>> Bitch</label>
                        	</div>
                        </div>
                        
                        <div class="form-group">
                        	<label for="color" class="control-label col-sm-2">Color</label>
                        	<div class="col-sm-4">
                        		<input type="hidden" class="form-control" id="dog_color" name="dog_color" value="<?php echo $animal['dog_color']; ?>">
                        	</div>
                        </div>

<?php 
						$dogMeta = array();
                        $dogMetaQ = $wpdb->get_results("select meta_key,meta_value from wpao_agility_dogsmeta where dog_id= '".$wpdb->_real_escape($_GET['dogID'])."'", 'ARRAY_A');
                        foreach ($dogMetaQ as $meta){
                        	$dogMeta[$meta['meta_key']] = $meta['meta_value'];
                        }
//}
?>
                        
                    	<h3><i class="fa fa-trophy" aria-hidden="true"></i>&nbsp;Competition Details</h3>
						<div class="panel with-nav-tabs panel-default">
							<div class="panel-heading">
		                    	<ul class="nav nav-tabs nav-justified">
									<li class="active"><a data-toggle="tab" href="#tab-kc">Kennel Club</a></li>
									<li><a data-toggle="tab" href="#tab-bs">Beachside</a></li>
									<!-- li><a href="#">Menu 2</a></li>
									<li><a href="#">Menu 3</a></li> -->
								</ul>
							</div>
							<div class="panel-body">
								<div class="tab-content">
									<div id="tab-kc" class="tab-pane fade in active">
				                    	<div class="form-group">
				                        	<label for="kc_height" class="control-label col-sm-2">Height</label>
				                        	<div class="col-sm-4">				                        	
												<select name="kc_height" class="form-control">
													<option value="">Select Height...</option>
													<?php echo get_options_for_heights('kc', $dogMeta['kc_height']); ?>
												</select>
				                            </div>				                            
				                        	<label for="kc_level" class="control-label col-sm-2">KC Grade</label>
				                        	<div class="col-sm-4">				                        	
												<select name="kc_level" class="form-control">
													<option value="">Select Grade...</option>
													<?php echo get_options_for_levels('kc', $dogMeta['kc_level']); ?>
												</select>
				                        	</div>
				                        </div>
										<?php 
										if (isset($dogMeta['kc_level']) && $dogMeta['kc_level'] == 7){ ?>
											<h4>Grade 7 Wins</h4>
										<?php 
										}									
										?>
									</div>
									<div id="tab-bs" class="tab-pane fade">
				                    	<div class="form-group">
				                        	<label for="bs_height" class="control-label col-sm-2">Height</label>
				                        	<div class="col-sm-4">				                        	
												<select name="bs_height" class="form-control">
													<option value="">Select Height...</option>
													<?php echo get_options_for_heights('bs', $dogMeta['bs_height']); ?>
												</select>
				                            </div>				                            
				                        	<label for="bs_level" class="control-label col-sm-2">Level</label>
				                        	<div class="col-sm-4">				                        	
												<select name="bs_level" class="form-control">
													<option value="">Select Level...</option>
													<?php echo get_options_for_levels('bs', $dogMeta['bs_level']); ?>
												</select>
				                        	</div>
				                        </div>
									</div>
									<!-- <div id="menu2" class="tab-pane fade">
										<h4>Menu 2</h4>
										<p>Some content in menu 2.</p>
									</div> -->
								</div>
							</div>
						</div>
                        
                        <div class="form-group">
                        	<div class="controls">
                            	<input type="submit" name="submit" value="Update Details" class="btn btn-success pull-right" />
                            </div>
                        </div>    
                                         
                        
                    </form>
                    <?php
					
				} else {
					?>
                    <h1 class="title">My Dogs <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a>&nbsp;<a class="btn btn-primary" href="/account/dogs/?edit=1&dogID=0">Add New Dog</a></span></h1>
                    <?php
					
					if(isset($_GET['updated'])) {
						?>
                        <div class="alert alert-success">Your dog's data has been successfully updated.</div>
                        <?php
					}
					
					$animalData = $wpdb->get_results("SELECT * FROM wpao_agility_dogs WHERE user_id = '".$wpdb->_real_escape($userId)."' ORDER BY `pet_name`", 'ARRAY_A');
					
					if(!empty($animalData)) {
					?>
						<table class="table table-bordered table-striped table-rounded">
							<tr>
								<th>Name</th>
								<th>Registration</th>
								<th>Birth Date</th>
								<th></th>
							</tr>
							<?php
							foreach($animalData as $animal) {
								
								?>
								<tr>
									<td><span style="font-weight:bold;color:<?php echo $animal['dog_color'];?>"><?php echo $animal['pet_name']; ?></span></td>
									<td><?php echo stripslashes($animal['kc_name']) . ' (' . $animal['kc_number'] . ')'; ?></td>
									<td><?php echo SQLToDate($animal['birth_date']); ?></td>
									<td width="110"><a class="btn btn-default btn-sm" href="/account/dogs/?edit=1&dogID=<?php echo $animal['id']; ?>">Edit Details</a></td>
								</tr>
								<?php	
							}
							?>
						</table>
					<?php
					} else {
						?>
						<div class="alert">You currently have no saved dogs. To add a new dog to your account, please click the "Add New" button above.</div>
						<?php	
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


<script type="text/javascript">
	$(document).ready(function() {
			$('.datepicker-me').datepicker({ format: 'dd/mm/yyyy' });
			
			$('#dog_color').paletteColorPicker({
					colors: [
						{'Green':'#006412'},{'LimeGreen':'#32CD32'},{'Yellow':'#FBF305'},{'Orange':'#FF6403'},{'Red':'#DD0907'},
						{'Burgundy':'#800020'},{'Magenta':'#F22084'},{'Pink':'#FFC0CB'},
						{'Lilac':'#e5c8ef'},{'Purple':'#552479'},{'Blue':'#0000D3'},{'Cyan':'#02ABEA'},{'SkyBlue':'#A6CAF0'},
						{'LightGrey':'#C0C0C0'},{'DarkGrey':'#808080'},{'Black':'#000000'},{'Brown':'#562C05'},{'Beige':'#90713A'}
					],
					clear_btn: null,
					position: 'downside', // default -> 'upside'
			});
	});
	
</script>

<?php
get_footer();
?>
