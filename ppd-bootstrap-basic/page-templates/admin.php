<?php /* Template Name: Admin */ ?>
<?php 

global $current_user, $wpdb;
get_currentuserinfo();

if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}
if (!in_array( 'administrator', $current_user->roles )){
	wp_redirect(site_url('/account/'));
	exit;
}

if(isset($_REQUEST['payment_add'])){
	$_REQUEST['user_id'] = ltrim($_REQUEST['user_id'], '0'); 
	$_REQUEST['payment_date'] = dateToSQL($_REQUEST['payment_date']);
	unset($_REQUEST['payment_add']);
	$wpdb->insert('wpao_agility_payments', $_REQUEST);
	wp_redirect($_SERVER['HTTP_REFERER']);
}

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                if(have_posts()):
                    while(have_posts()):
                        the_post();

                        echo '<h1 class="title">' . get_the_title() . ' <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a></span></h1>';

                        //the_content();
                    endwhile;
                endif;
                ?>
             </div>
          </div>
          
		<div class="row">
			<div class="col-md-4">
	            <div class="well">
	            	<h3 style="margin-top:0px;"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;Add Payment</h3>
	            	<form class="form" method="post">
	            		<div class="form-group">
	            			<label for="method" class="control-label">Payment type...</label>
							<select name="method" class="form-control">
								<option value="">Select Payment Type...</option>
								<option value="BACS">Bank Transfer</option>
								<option value="PAYPAL">PayPal</option>
							</select>
	            		</div>
	            		<div class="form-group">
	            			<label for="payment_date" class="control-label">Date of payment...</label>
	            			<input type="text" class="form-control text-right datepicker-me" id="payment_date" name="payment_date">
	            		</div>
	            		<div class="form-group">
	            			<label for="user_id" class="control-label">User ID...</label>
	            			<div class="input-group">
	            				<span class="input-group-addon">AA</span>
	            				<input type="text" class="form-control text-right" id="user_id" name="user_id">
	            			</div>
	            		</div>
	            		<div class="form-group">
	            			<label for="amount" class="control-label">Amount paid...</label>
	            			<div class="input-group">
	            				<span class="input-group-addon">&pound;</span>
	            				<input type="text" class="form-control text-right" id="amount" name="amount">
	            			</div>
	            		</div>
	            		<button type="submit" class="btn btn-primary btn-block" id="payment_add" name="payment_add">Add Payment</button>
	            	</form>
	            </div>
	        </div>
	            
          	<div class="col-md-8" id="main-content">
          	
          	</div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
			$('.datepicker-me').datepicker({ format: 'dd/mm/yyyy', todayHighlight: true, weekStart: 1, autoclose: true });
	});
	
</script>
<?php get_footer(); ?>
