<?php /* Template Name: KC Declaration */ ?>
<?php 
if(!is_user_logged_in()) {
	wp_redirect(site_url('/login/'));
	exit;
}

$data = getCustomSessionData();
if (isset($_POST['kc_declaration_ok']) && $_POST['kc_declaration_ok'] == 'on'){
	$data['kc_declaration_ok'] = 1;
	setCustomSessionData($data);
	wp_redirect(site_url('/enter-show/individual-classes/?show='.$data['show_id']));
	exit;
}

//debug_array($data);
?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1>KC Declaration</h1>
				<form action="" method="post" class="form-horizontal" id="entryForm">
				
					<p>Entry to this show is bound by Kennel Club Rules and regulations. Therefore, before proceeding with your entry, please confirm that you agree with the following declaration. You will be unable to proceed with your entry until you have accepted.</p>
					
					<div class="well">
						<p>I/We agree to submit to and be bound by Kennel Club Limited Rules & Regulations in their present form or as they may be amended from time to time in relation to all canine matters with which the Kennel Club is concerned and that this entry is made upon the basis that all current single or joint registered owners of this dog(s) have authorised/consented to this entry. </p>
						<p>I/We also undertake to abide by the Regulations of this Show and not to bring to the Show any dog which has contracted or been knowingly exposed to any infectious or contagious disease during the 21 days prior to the Show, or which is suffering from a visible condition which adversely affects its health or welfare.</p>
						<p>I/We further declare that, I believe to the best of my knowledge that dogs are not liable to disqualification under Kennel Club Agility Show Regulations.</p>
					</div>
					
					<label class="checkbox-inline"><input type="checkbox" name="kc_declaration_ok" checked="checked" />&nbsp;I confirm that I accept the KC declaration and wish to process with my entry.</label>
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