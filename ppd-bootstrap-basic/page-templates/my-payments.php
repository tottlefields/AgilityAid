<?php /* Template Name: My Payments */ ?>
<?php 

global $current_user, $wpdb;
get_currentuserinfo();

$user_ref = get_user_meta($current_user->ID, 'user_ref', true);
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
            <div class="col-md-12" id="main-content">
                <?php
                if(have_posts()):
                    while(have_posts()):
                        the_post();

                        echo '<h1 class="title">' . get_the_title() . ' <span class="pull-right"><a href="/account/" class="btn btn-info">My Account</a></span></h1>';

                        //the_content();
                    endwhile;
                endif;
                ?>
                <?php if (count($payments) > 0){ 
                $table = '<table id="payments" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
					<thead>
						<th class="text-center">Payment Date</th>
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
                <div class="alert alert-warning"><i class="fa fa-paypal" aria-hidden="true"></i>&nbsp;Payments can also be made through <a href="https://www.paypal.me/agilityaid" target="_blank">PayPal.me</a> using the AgilityAid account - <strong>agilityaid@outlook.com</strong>.<br />
                When paying by PayPal, please note that your payment is subject to a handling fee of 3.5% + 30p, which will be included in your transaction.</div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>