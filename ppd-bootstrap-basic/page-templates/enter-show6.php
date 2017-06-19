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
$userMeta = get_user_meta( $userId );

$data = getCustomSessionData();
$show = get_post( $data['show_id'] );

if(isset($_POST['submit']) && $_POST['submit'] == 'Finish'){
	
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
	debug_string($insertId);
	
	$entryMetaData = array();

	$entryMetaData['show_id'] = $data['show_id'];

	foreach($postMetaData as $key => $value) {
		$key .= '-pm';
		update_post_meta($insertId, $key, $value);
	}
	
	
	exit;
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
                ?>
                <p>Below is a summary of your entry for the <strong><?php echo $show->post_title; ?></strong> show.</p>
                
                <table class="table table-responsive">
                	<thead><tr>
                        <th>Dog's Name</th>
                        <th>Handler</th>
                        <th>Classes</th>
                        <th>Cost</th>
                     </tr></thead><tbody>
                
                <?php 
                $total_cost = 0;
                foreach ($data[$data['show_id']] as $dog_id => $classes){
                	$dog = get_dog_by_id($data['dogs'], $dog_id);
                	$class_list = array();
                	$cost_per_dog = 0;
                	foreach ($classes as $classNo => $class){
                		array_push($class_list, $classNo);
                		$cost_per_dog += $class['price'];
                	}
                	$total_cost += $cost_per_dog;
                	$handler = $userMeta['first_name'][0].' '.$userMeta['last_name'][0];
                	echo '<tr>
						<td><span style="color:'.$dog['dog_color'].';font-weight:bold;">'.$dog['pet_name'].'</span></td>
						<td>'.$handler.'</td>
						<td>'.implode(",", $class_list).'</td>
						<td>&pound;'.sprintf("%.2f", $cost_per_dog).'</td>';
                }
                
                ?>
                </tbody>
                <tfoot>
                	<tr>
                		<th colspan="3"><span class="pull-right">Total</span></th>
                		<th><?php echo '&pound;'.sprintf("%.2f", $total_cost); ?></th>
                </tfoot>
                </table>
                <div class="alert alert-info">By clicking Finish below you are agreeing to abide by all the show rules and regulations as detailed in the schedule.</div>
				<form action="" method="post" class="form-horizontal" id="entryForm">
                    <div class="control-group">
                        <div class="controls">
                        	<span class="pull-right">
	                            <input type="submit" value="Cancel" name="submit" id="cancelEntry" class="btn btn-danger" />
	                            <input type="submit" value="Finish" name="submit" class="btn btn-success" />
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



