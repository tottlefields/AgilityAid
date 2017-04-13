<?php /* Template Name: Shows Archive */ ?>
<?php get_header(); ?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1 class="title">Upcoming Shows</h1>
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

                <?php
                if(have_posts()):
                    while(have_posts()):
                        the_post();
                		$post_id = get_the_ID();
                		
                		$start_date = get_field('start_date', false, false);
                		$end_date = get_field('end_date', false, false);
                		$close_date = get_field('close_date', false, false);
                		$schedule_file = get_field('pdf_upload_schedule');
                		$entryform_file = get_field('pdf_upload_entryform');
                		$venue = get_field('venue', false, false);
                		
                		$start_date = new DateTime($start_date);
                		$end_date = new DateTime($end_date);
                		$closes = new DateTime($close_date);
                		$show_dates = $start_date->format('jS M');
                		if($start_date != $end_date){
                			$show_dates .= ' to '.$end_date->format('jS M Y');
                		}
                		else{
                			$show_dates .= $start_date->format(' Y');
                		}
                		
                		//green () for open shows; orange (warning) for closing in 7 days; red (danger) for closed
                		$panel_class = 'success';
                		$closes_text = '&nbsp;';
                		$enter_show = ' ||  <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$entryform_file['url'].'">Entry Form</a>
                				|| <i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<a href="">Enter Show</a>';
                		if ($close_date < date('Ymd')){
                			$panel_class = 'danger';
                			$closes_text = 'CLOSED';
                			$enter_show = '';
                		}
                		elseif ($close_date <= date('Ymd', strtotime("+7 day"))){
                			$panel_class = 'warning';
                			$closes_text = 'Closes: '.$closes->format('d jS M');
                		}
                		
                        echo '
                		<div class="panel panel-'.$panel_class.'">
					    	<div class="panel-heading" role="tab" id="heading'.$post_id.'">
					      		<h4 class="panel-title">
                					<span class="pull-right">'.$closes_text.'</span>
					        		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$post_id.'" aria-expanded="false" aria-controls="collapseOne">' . get_the_title() . ' ('.$show_dates.')</a>
					      		</h4>
					    	</div>
					    	<div id="collapse'.$post_id.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading'.$post_id.'">
					      		<div class="panel-body">
					        		<div class="row">
						        		<div class="col-md-6">
		                        			<strong>Dates:</strong> '.$show_dates.'<br />
							        		<strong>Closes:</strong> '.$closes->format('jS M Y').'<br />
		                        		</div>
		                        		<div class="col-md-6">
		                        			<strong>Venue:</strong><br />
		                        			'.$venue.'
		                        		</div>
	                        		</div>
					        		<div class="row">
						        		<div class="col-md-12">
		                        			<i class="fa fa-map-o" aria-hidden="true"></i>&nbsp;<a href="'.get_the_permalink().'">View Show</a> || 
							        		<i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$schedule_file['url'].'">Schedule</a>
							        		'.$enter_show.'
							        	</div>
	                        		</div>
					      		</div>
					    	</div>
					  	</div>';

                        
                    endwhile;
                endif;
                ?>
				</div>
				<div class="row">
					<h4>Colour key to shows:</h4>
					<div class="col-sm-4"><i class="fa fa-square text-success" aria-hidden="true"></i>&nbsp;Open</div>
					<div class="col-sm-4"><i class="fa fa-square text-warning" aria-hidden="true"></i>&nbsp;Closes within 7 days</div>
					<div class="col-sm-4"><i class="fa fa-square text-danger" aria-hidden="true"></i>&nbsp;CLOSED</div>
				</div>
            </div>
            <div class="col-md-3" id="sidebar">
            <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>