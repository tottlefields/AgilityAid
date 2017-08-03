<?php /* Template Name: Enter Show Step 5 (Helpers) */ ?>
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

$handlers = array();
if(isset($user_meta['handlers'][0])){
	$handlers = unserialize($user_meta['handlers'][0]);
}
//array_push($handlers, 'Mark Peck');
$handler = $user_meta['first_name'][0].' '.$user_meta['last_name'][0];
array_push($handlers, $handler);
sort($handlers);

$show = get_post( $data['show_id'] );
$show_meta = get_post_meta($data['show_id']);

if (isset($_POST['step-5-submitted'])) {
	$helpers = array();
	for ($i=0; $i<count($handlers); $i++){
		if (isset($_POST['job_for_'.$i]) && $_POST['job_for_'.$i] != 'None'){
			$helpers[$handlers[$i]] = array('job' => $_POST['job_for_'.$i], 'group' => $_POST['group_for_'.$i]);
		}
	}
	$data['helpers'] = $helpers;
	setCustomSessionData($data);
	
	wp_redirect(site_url('/enter-show/confirmation/'));
	exit;
}

if (!isset($show_meta['helpers'][0]) || (isset($show_meta['helpers']) && $show_meta['helpers'][0] == 'none')){
	wp_redirect(site_url('/enter-show/confirmation/'));
	exit;
}

$helpers_info = '<h2>We need your help!</h2>';

if ($show_meta['helpers'][0] == 'yes_1'){
	$helpers_info.= '<p>By entering this show you agree to undertake a minimum of 1 hour help on a ring.</p>
		<p>Each ring will have a Ring Manager who will be responsible for allocating tasks and arranging relief so that all can enjoy running their dogs. To ensure you can arrange your time slot please make yourself known to the Ring Manager by 8am if you are helping in the morning, and midday if helping in the afternoon.</p>';
}
elseif ($show_meta['helpers'][0] == 'yes'){
	$helpers_info .= '<p>To enable the show to run as smoothly as possible and to be fair to all who attend, we kindly request that you donate an hour or more of your time each day.</p>		
		<p>All competitors who have volunteered for ring party will be allocated to a ring. Each ring will have a Ring Manager who will be responsible for allocating tasks and arranging relief so that all can enjoy running their dogs. To ensure you can arrange your time slot please make yourself known to the Ring Manager by 8am if you are helping in the morning, and midday if helping in the afternoon.</p>
<p>If everyone does their bit then no-one should need to work more than an hour or so.</p>';
}
	
$helpers_info .= '<p>No-one is expected to forgo running their dogs in the process of helping.</p><p>If you want to work on a particular ring, or do a particular job,  please indicate this below.</p>';


?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>Helpers</h1>
				<ul class="breadcrumb">
					<li class="active">Select Show</li>
					<li class="active">Individual Classes</li>
					<li class="active">Team/Pairs Classes</li>
					<li class="active">Camping</li>
					<li>Helpers</li>
					<li class="active">Payment</li>
				</ul>
				<form action="" method="post" class="form-horizontal" id="helpersForm">					
                	<input type="hidden" name="step-5-submitted" value="1" />
					<div class="well"><?php echo $helpers_info; ?></div>
					<table style="margin-bottom:0px;" class="table table-striped table-hover table-responsive">
					<?php 
					for ($i=0; $i<count($handlers); $i++){?>
						<tr>
							<td><?php echo $handlers[$i]; ?></td>
							<td>
								<select name="job_for_<?php echo $i; ?>" id="job_for_<?php echo $i; ?>" class="form-control">
									<?php echo get_options_for_jobs($data['helpers'][$handlers[$i]]['job']); ?>
								</select>
							</td>
							<td>
								<input type="text" class="form-control" id="group_for_<?php echo $i; ?>" name="group_for_<?php echo $i; ?>" placeholder="Group/Judge" value="<?php echo $data['helpers'][$handlers[$i]]['group']; ?>" />
							</td>
						</tr>
					<?php }
					?>
					</table>
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