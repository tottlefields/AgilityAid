<?php /* Template Name: Paypal Payment */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

global $wpdb;
$current_user = wp_get_current_user();

$userId = $current_user->ID;

if($_GET['result'] != 'done' || $_GET['user'] != $userId){
	echo "ERROR\n";
	exit;
}

$new_payment = array(
		'date' => date('Y-m-d'),
		'method' => 'paypal',
		'amount' => $_GET['amount']
);

if (isset($_GET['entry'])){
	$args = array (
			'post_type'		=> 'entries',
			'post_status'	=> array('publish'),
			'order'			=> 'ASC',
			'numberposts'	=> -1,
			'author'		=> $userId,
			'post_parent'	=> $_GET['entry']
	/*		'meta_query' 	=> array(
					array(
							'key'		=> 'show_id-pm',
							'compare'	=> '=',
							'value'		=> $_GET['entry'],
					),
			)*/
	);
		
	// get posts
	$posts = get_posts($args);
	global $post;
	
	$post = array_shift($posts);
	$post_id = get_the_ID();
	$paid_data = get_field('paid-pm');
	
	$payments = array();
	
	if(isset($paid_data) && $paid_data != ''){
		$payments = $paid_data;
	}
	array_push($payments, $new_payment);
	
	update_post_meta($post_id, 'paid-pm', $payments);
}
if (isset($_GET['fees'])){
	$fees_payment = array(
			'user_id' => $userId,
			'payment_date' => date('Y-m-d'),
			'method' => 'INVOICE',
			'amount' => $_GET['fees'],
			'description' => 'PayPal Fees'
	);
	$wpdb->insert( $wpdb->prefix.'agility_payments', $fees_payment);
}

$new_payment['user_id'] = $userId;
$new_payment['payment_date'] = $new_payment['date'];
$new_payment['description'] = 'Payment received, thank you';
unset($new_payment['date']);
$wpdb->insert( $wpdb->prefix.'agility_payments', $new_payment);
$payment_id = $wpdb->insert_id;


//wp_redirect(site_url('/account/my-entries/'));
wp_redirect(site_url('/account/my-payments/'));
exit;

?>
