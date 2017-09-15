<?php /* Template Name: My Account */ ?>
<?php 

global $current_user, $wpdb;
get_currentuserinfo();

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

                        echo '<h1 class="title">' . get_the_title() . '</h1>';

                        the_content();
                    endwhile;
                endif;
                ?>
                <?php 
                if (in_array( 'author', $current_user->roles ) || in_array( 'administrator', $current_user->roles )){ ?>
                <div class="row-fluid">
                	<div class="col-sm-6"><div class="well well-lg"><a href="/account/my-comps/"><i class="fa fa-trophy fa-4x" aria-hidden="true"></i>&nbsp;View Competition Data</a></div></div>
                <?php if ( in_array( 'administrator', $current_user->roles ) ) { ?>
					<div class="col-sm-6"><div class="well well-lg"><a href="/admin/"><i class="fa fa-clipboard fa-4x" aria-hidden="true"></i>&nbsp;Adminstration Tasks</a></div></div>
				</div>	
                <?php } } ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
