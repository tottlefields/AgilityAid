<?php
get_header();
?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
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
            <div class="col-md-3" id="sidebar">
            <?php
            if(is_front_page()) {
                dynamic_sidebar('Home Sidebar');
            } else {
                dynamic_sidebar('Standard Sidebar');
            }
            ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
?>