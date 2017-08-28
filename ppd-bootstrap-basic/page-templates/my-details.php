<?php /* Template Name: My Details */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
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

$handlers = array();
if(isset($user_meta['handlers'][0])){
	$handlers = unserialize($user_meta['handlers'][0]);
}

if (isset($_POST['add_handler']) && $_POST['add_handler'] == 'submit'){
	array_push($handlers, $_POST['new_handler']);
	$uiq_handlers = array_unique($handlers);
	update_user_meta($userId, 'handlers', $uiq_handlers);	
}
asort($handlers);

?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-6" id="main-content">
                <?php
                if(have_posts()):
                    while(have_posts()):
                        the_post();

                        echo '<h1 class="title">' . get_the_title() . '</h1>';

                        the_content();
                    endwhile;
                endif;
                ?>
            </div>
            <div class="col-md-6">
				<a href="/account/" class="btn btn-info pull-right">My Account</a><h1>&nbsp;</h1>
				<h4>Extra Handlers</h4>
				<?php 
				
				if (count($handlers) > 0){
					echo '<ul>';
					foreach ($handlers as $h){
						echo '<li>'.$h.'</li>';
					}
					echo '</ul>';
				}
				?>
				<form method="post" class="form-inline">
					<div class="form-group">
						<input class="form-control" type="text" name="new_handler" id="new_handler" placeholder="Handler Name" />
					</div>
					<button type="submit" class="btn btn-primary" name="add_handler" id="add_handler" value="submit">Add Handler</button>
				</form>
			</div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
