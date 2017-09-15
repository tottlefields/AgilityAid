<?php /* Template Name: My Payments */ ?>
<?php 

global $current_user, $wpdb;
get_currentuserinfo();

$user_ref = get_user_meta($current_user->ID, 'user_ref', true);
$userId = $current_user->ID;
if(!isset($user_ref)){
	$ref = sprintf('%04d', $current_user->ID);
	$user_ref = 'AA'.$ref;
	update_user_meta($userId, 'user_ref', $user_ref);
}

$payments = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."agility_payments WHERE user_id=".$current_user->ID." ORDER BY payment_date DESC");

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
          	<div class="col-md-9" id="main-content">
                <?php if (count($payments) > 0){ 
                $table = '<table id="payments" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
					<thead>
						<th class="text-center">Transaction Date</th>
						<th class="text-center">Method</th>
						<th>Description</th>
						<th class="text-center">Amount</th>
						<th class="text-center">Total</th>
					</thead>
					<tbody>';
					foreach ($payments as $payment){
						if ($payment->method == 'INVOICE'){
							$total_amount += $payment->amount;
							$method_string = '<span class="label label-danger">&pound;'.sprintf('%.2f', $payment->amount).'</span>';
						}
						else {
							$total_amount -= $payment->amount;
							$method_string = '<span class="label label-success">&pound;'.sprintf('%.2f', $payment->amount).'</span>';
						}
						
						if ($total_amount < 0){
							$a = $total_amount * -1;
							$amount_string = '<span class="label label-success">&pound;'.sprintf('%.2f', $a).'</span>';
						}
						else{ $amount_string = '<span class="label label-danger">&pound;'.sprintf('%.2f', $total_amount).'</span>'; }

						$table .= '
						<tr>
							<td class="text-center">'.DateTime::createFromFormat('Y-m-j', $payment->payment_date)->format('d/m/Y').'</td>
							<td class="text-center">'.$payment->method.'</a></td>
							<td>'.$payment->description.'</td>
							<td class="text-center">'.$method_string.'</td>
							<td class="text-center">'.$amount_string.'</td>
						</tr>';
						
					}
					$table .= '
					</tbody>
                </table>';
					if ($total_amount < 0){
						$total_amount *= -1;
						echo '<div class="alert alert-info"><i class="fa fa-smile-o" aria-hidden="true"></i>&nbsp;Congratulations, your account is currently <strong>&pound;'.sprintf('%.2f', $total_amount).'</strong> in credit.</div>';
					}
					else{
						echo '<div class="alert alert-warning"><i class="fa fa-warning" aria-hidden="true"></i>&nbsp;Warning, your account is currently <strong>&pound;'.sprintf('%.2f', $total_amount).'</strong> in arrears, please make sure payments are made prior to the closing date(s) of shows.</div>';			
					}
                	echo $table;
                } else {
                }
                ?>
                
                <div class="alert alert-info"><i class="fa fa-bank" aria-hidden="true"></i>&nbsp;Payments can be made directly to the AgilityAid bank account with the following details:-<br>
				<div style="margin-left:30px;">			
					Sort Code : 20-41-15<br>
					Account No. : 40542342<br>
					Your Reference : <strong><?php echo $user_ref; ?></strong><br></div>
				</div>
                <!-- <div class="alert alert-warning"><i class="fa fa-paypal" aria-hidden="true"></i>&nbsp;Payments can also be made through <a href="https://www.paypal.me/agilityaid" target="_blank">PayPal.me</a> using the AgilityAid account - <strong>agilityaid@outlook.com</strong>.<br />
                When paying by PayPal, please note that your payment is subject to a handling fee of 3.5% + 30p, which will be included in your transaction.</div> -->
            </div>
            <div class="col-md-3">
	            <div class="well">
	            	<h3 style="margin-top:0px;"><i class="fa fa-paypal" aria-hidden="true"></i>&nbsp;PayPal Tool</h3>
	            	<small>Payments can be made through <a href="https://www.paypal.com" target="_blank">PayPal</a> using our simple tool below. Just enter the amount of money you wish to pay and click the "Buy Now" button.</small>
	            	<hr>
	            	<form class="form">
	            		<div class="form-group">
	            			<label for="paypal_amount" class="control-label">Amount to send...</label>
	            			<div class="input-group">
	            				<span class="input-group-addon">&pound;</span>
	            				<input type="text" class="form-control text-right" id="paypal_amount" name="paypal_amount">
	            			</div>
	            		</div>
	            		<div class="form-group">
	            			<label for="paypal_fees" class="control-label">PayPal Fees...</label>
	            			<div class="input-group">
	            				<span class="input-group-addon">&pound;</span>
	            				<input type="text" class="form-control text-right" id="paypal_fees" name="paypal_fees" disabled="disabled">
	            			</div>
	            		</div>
	            		<div class="form-group">
	            			<label for="paypal_total" class="control-label">TOTAL</label>
	            			<div class="input-group">
	            				<span class="input-group-addon">&pound;</span>
	            				<input type="text" class="form-control text-right" id="paypal_total" name="paypal_total" disabled="disabled">
	            			</div>
	            		</div>
	            	</form>
	            	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	            		<input type="hidden" name="cmd" value="_xclick">
	            		<input type="hidden" name="business" value="agilityaid@outlook.com">
	            		<input type="hidden" name="amount" id="paypal_amount_final" value="">
	            		<input type="hidden" name="item_name" value="Payment to AgilityAid">
		                <INPUT TYPE="hidden" NAME="currency_code" value="GBP">
		                <INPUT TYPE="hidden" NAME="return" id="paypal_return_url" value="">
		                <input type="hidden" name="first_name" value="<?php echo $current_user->user_firstname; ?>">
		                <input type="hidden" name="last_name" value="<?php echo $current_user->user_lastname; ?>">
		                <input type="hidden" name="email" value="<?php echo $current_user->user_email; ?>">
		                <input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" alt="PayPal - The safer, easier way to pay online">
		             </form>
	            </div>
            </div>
        </div>
    </div>
</div>

<?php function footer_js(){ 
	global $current_user;?>
<script>
jQuery(document).ready(function($) {
	$("#paypal_amount").keyup(function() {
		var amount = $(this).val();
		var fees = Number((amount*0.035)+0.3).toFixed(2);
		var total = Number(parseFloat(amount) + parseFloat(fees)).toFixed(2);

		$("#paypal_fees").val(fees);
		$("#paypal_total").val(total);
		$("#paypal_amount_final").val(total);
		$("#paypal_return_url").val("<?php echo get_site_url(); ?>/process-paypal/?result=done&amount="+total+"&user=<?php echo $current_user->ID; ?>");
		
	});
});
</script>

<?php }
add_action('wp_footer', 'footer_js', 100);?>
<?php get_footer(); ?>