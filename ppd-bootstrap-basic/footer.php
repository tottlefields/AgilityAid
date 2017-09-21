 <?php
 # if (current_user_can('administrator')){
 #  global $wpdb;
 #  echo "<pre>";
 #  print_r($wpdb->queries);
 #  echo "</pre>";
 #}//Lists all the queries executed on your page
?>

	<hr />
			<footer>
			</footer>
		
		</div> <!-- /container -->
		
		<!-- start: JavaScript-->
		<script>
		var ajaxObject = {"ajax_url":"<?php echo admin_url( 'admin-ajax.php' ); ?>"};
		</script>		
		<?php wp_footer(); ?>

	</body>
</html>