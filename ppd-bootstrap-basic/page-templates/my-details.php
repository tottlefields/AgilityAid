<?php /* Template Name: My Details */ ?>
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
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
