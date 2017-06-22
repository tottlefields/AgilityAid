<?php /* Template Name: Enter Show Step 6 (Payment) */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

if(isset($_GET['cancel'])){
	setCustomSessionData(array());
	wp_redirect(site_url('/enter-show/'));
	exit;
}

global $current_user, $wpdb;
get_currentuserinfo();
$userId = $current_user->ID;
$user_meta = get_user_meta( $userId );

$data = getCustomSessionData();
$show = get_post( $data['show_id'] );

if(isset($_POST['submit']) && $_POST['submit'] == 'Finish'){
	$showData = $data;
	$all_classes = $data['classes'];
	$classes_entered = array();
	$nfc_dogs = array();
	
	unset($showData['classes']);
	$showData['total_cost'] = $_POST['total_cost'];
	$showData['dog_count'] = count(array_keys($showData[$data['show_id']]));
	$class_count = 0;
	foreach ($showData[$data['show_id']] as $dog_id => $classData){
		$dogData = get_dog_by_id($showData['dogs'], $dog_id);
		$dogName = ($showData['show_type'] == 'kc') ? $dogData['kc_name'] : $dogData['pet_name'];
		if ($classData == 'nfc'){
			array_push($nfc_dogs, $dogName);
		}else{
			$class_count += count($classData['classes']);
			foreach ($classData['classes'] as $classNo => $classEntry){
				$class = get_class_by_no($all_classes[$classEntry['date']], $classNo);
				array_push($classes_entered, array(
						'dog_name' => $dogName, 
						'height' => $classData['height'], 
						'handler' => $classEntry['handler'], 
						'class_title' => $class['classNo'].'. '.$class['className'])
				);
			}
		}
	}
	$showData['class_count'] = $class_count;
	$showData['nfc_count'] = count($nfc_dogs);
	//setCustomSessionData($showData);
	
	//We construct our order and insert it
	$entryPostData = array(
			'post_title'    => $show->post_name.'_'.$current_user->user_login,
			'post_content'  => '',
			'post_status'   => 'publish',
			'post_author'   => $userId,
			'post_category' => array(),
			'post_type' => 'entries'
	);
	
	// Insert the post into the database
	$insertId = wp_insert_post($entryPostData);
	
	$entryMetaData = array();

	$entryMetaData['show_id'] = $showData['show_id'];
	$entryMetaData['address'] = $user_meta['address'][0];
	$entryMetaData['town'] = $user_meta['town'][0];
	$entryMetaData['postcode'] = $user_meta['postcode'][0];
	$entryMetaData['dog_count'] = $showData['dog_count'];
	$entryMetaData['class_count'] = $showData['class_count'];
	$entryMetaData['nfc_count'] = $showData['nfc_count'];
	$entryMetaData['total_cost'] = $showData['total_cost'];
	$entryMetaData['entry_data'] = serialize($showData[$showData['show_id']]);
	$entryMetaData['show_data'] = serialize($showData);

	foreach($entryMetaData as $key => $value) {
		$key .= '-pm';
		update_post_meta($insertId, $key, $value);
	}
	
	$close_date = new DateTime(get_post_meta( $showData['show_id'] , 'close_date' , true ));
	
	$message = 'Dear '.$user_meta['first_name'][0].',<br />
<p>Thank you for entering <strong>'.$show->post_title.'</strong> via the <a href="'.get_bloginfo('url').'">AgilityAid website</a>.</p>
<p>Your total show entry fees are <strong>&pound;'.sprintf("%.2f", $showData['total_cost']).'</strong>. These fees are made up of class entry fees as per the show schedule and camping fees (where applicable). Please make sure all payments are made prior to the closing date ('.$close_date->format('jS M Y').'). You can either pay online using the bank details below, or log into your account to pay via PayPal. Failure to pay will render your entry null and void.</p>
		
	Sort Code : 20-41-15<br />
	Account No. : 40542342<br />
	Your Reference : '.$current_user->user_login.'<br />

<p>The classes that you elected to enter are shown below. If there is a problem, or an error with this entry, please contact us at your earliest convenience</p>
<table style="padding-bottom:10px;width:760px;border-collapse:collapse;" align="center">';
	foreach ($classes_entered as $class){
		$message .= '<tr><td style="border:1px solid #999999;padding-right:10px; padding-left:10px;">'.$class['dog_name'].'</td>
		<td style="border:1px solid #999999;padding-right:10px; padding-left:10px;">'.$class['height'].'</td>
		<td style="border:1px solid #999999;padding-right:10px; padding-left:10px;">'.$class['handler'].'</td>
		<td style="border:1px solid #999999;padding-right:10px; padding-left:10px;">'.$class['class_title'].'</td></tr>';
	}
	foreach ($nfc_dogs as $dog){
		$message .= '<tr><td style="border:1px solid #999999;padding-right:10px; padding-left:10px;">'.$dog.'</td>
		<td colspan="3" style="border:1px solid #999999;padding-right:10px; padding-left:10px;"><em>Not for Competition</em></td></tr>';
		
	}
	$message .= '</table>';
	
//	$TO = $current_user->user_email.','.get_bloginfo('admin_email');
	$TO = $current_user->user_email;
	$TITLE = '[AgilityAid] '.$show->post_title.' Show Entry';
	$HEADERS = array('Content-Type: text/html; charset=UTF-8');
	$sent = wp_mail( $TO, $TITLE, $message, $HEADERS );
}


?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Available Shows</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li class="active">Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li class="active">Helpers</li>
					<li>Confirmation</li>
				</ul>
				
                <?php
                if($_GET['cancel'] == '1') {
                    if(isset($_GET['error'])) {
                        $error = $_GET['error'];
                        if($error === 'mismatch'){
                            ?>
                            <div class="alert alert-error">
                                There was an error with our systems. This has been reported. Sorry for any inconvenience.
                            </div>
                            <?php
                        } else if ($error == 'REJECTED'){
                            ?>
                            <div class="alert alert-error">
                                Your card was rejected by our fraud protection systems. Please try another card or contact us.
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-error">
                                An unknown error has been encountered. This has been reported. Sorry for any inconvenience.
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="alert alert-error">
                            You have cancelled your payment. If you still wish to proceed please review the information below and click proceed to payment.
                        </div>
                        <?php
                    }   
                }
                elseif ($sent > 0){
                	?>
					<div class="alert alert-success">
						<p>Many thanks for your entry. You should receive an email confirmation of your entry shortly.</p>
						<p>Payment can be made via online bank transfer (details in your email) or by logging into your account and paying by PayPal (this is subject to extra fees).</p>
						<p>Please <a href="/account/my-entries/">click here</a> to view your entry or <a href="/enter-show/">here to enter another show</a>.</p>
					</div>
					<?php
                }else {
                ?>
                <p>Below is a summary of your entry for the <strong><?php echo $show->post_title; ?></strong> show.</p>
                
                <table class="table table-responsive">
                	<thead><tr>
                        <th>Dog's Name</th>
                        <th>Height</th>
                        <th>Handler</th>
                        <th>Classes</th>
                        <th>Cost</th>
                     </tr></thead><tbody>
                
                <?php 
                $total_cost = 0;
                foreach ($data[$data['show_id']] as $dog_id => $classData){
                	$dog = get_dog_by_id($data['dogs'], $dog_id);
                	$class_list = array();
                	$handlers = array();
                	$cost_per_dog = 0;
                	if ($classData == 'nfc'){
                	echo '<tr>
						<td><span style="color:'.$dog['dog_color'].';font-weight:bold;">'.$dog['pet_name'].'</span></td>
						<td colspan="3" align="center"><em>Not for Competition</em></td>
						<td>&nbsp;</td>';
                	}
                	else{
	                	foreach ($classData['classes'] as $classNo => $class){
	                		$handler = $class['handler'];
	                		if(!isset($class_list[$handler])){
	                			$class_list[$handler] = array();
	                		}
	                		array_push($class_list[$handler], $classNo);
	                		$cost_per_dog += $class['price'];
	                	}
                		$total_cost += $cost_per_dog;
                	echo '<tr>
						<td><span style="color:'.$dog['dog_color'].';font-weight:bold;">'.$dog['pet_name'].'</span></td>
						<td>'.$classData['height'].'</td>
						<td>'.implode("<br />", array_keys($class_list)).'</td>
						<td>';
	                	foreach( $class_list as $handler => $classes ) {
	                		sort($classes);
	                		if( has_next( $class_list ) ) {
	                			echo '<br />';
	                		}
	                		echo implode(",", $classes);
	                	}
						echo '</td>
						<td>&pound;'.sprintf("%.2f", $cost_per_dog).'</td>';
                	}
                }
                
                ?>
                </tbody>
                <tfoot>
                	<tr>
                		<th colspan="4"><span class="pull-right">Total</span></th>
                		<th><?php echo '&pound;'.sprintf("%.2f", $total_cost); ?></th>
                </tfoot>
                </table>
                <div class="alert alert-info">By clicking Finish below you are agreeing to abide by all the show rules and regulations as detailed in the schedule.</div>
				<form action="" method="post" class="form-horizontal" id="entryForm">
                    <div class="control-group">
                        <div class="controls">
                        	<span class="pull-right">
								<input type="hidden" id="total_cost" name="total_cost" value="<?php echo $total_cost; ?>" />	
	                            <input type="submit" value="Cancel" name="submit" id="cancelEntry" class="btn btn-danger" />
	                            <input type="submit" value="Finish" name="submit" class="btn btn-success" />
                            </span>
                       </div>
                   </div>
				</form>
				<?php }
				?>
				
				
				
				
				
            </div>
            <div class="col-md-3" id="sidebar">
            	<?php dynamic_sidebar('Entry Sidebar'); ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
	$('#cancelEntry').on('click', function (e) {
        e.preventDefault();
		bootbox.confirm({
		    message: "Do you really want to cancel your entry and start again?",
		    buttons: {
		        confirm: {
		            label: 'Yes',
		            className: 'btn-success'
		        },
		        cancel: {
		            label: 'No',
		            className: 'btn-danger'
		        }
		    },
		    callback: function (result) {
			    if(result)
				    window.location.href = "/enter-show/confirmation?cancel";
			    return;
		    }
		});
	});
});
</script>

<?php get_footer(); ?>



