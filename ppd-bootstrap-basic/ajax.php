<?php

add_action('wp_ajax_entry_details', 'get_comp_entries');
add_action('wp_ajax_nopriv_entry_details', 'get_comp_entries');

add_action('wp_ajax_comp_nos', 'view_comp_nos');
add_action('wp_ajax_nopriv_comp_nos', 'view_comp_nos');

function get_comp_entries(){
	global $wpdb, $current_user;
	
	$return = array();
	
	if (!in_array( 'administrator', $current_user->roles )){
		$return['error'] = "ERROR - you don't have the correct permission to view the entry details of this show";
		echo json_encode($return);
		wp_die();
	}
	
	$show_id = intval($_POST['show_id']);
	$show = get_post( $show_id );
	$show_meta = get_post_meta($show_id);
	
	$return['show'] = $show;
	
	$args = array (
			'post_type'		=> 'entries',
			'post_status'	=> array('publish'),
			'numberposts'	=> -1,
			'order'			=> 'DESC',
			'post_parent' 	=> $show_id
	);

	$orders = array();
	$animals = array();
	$clients = array();
	$entries = array();
	
	// get entries (posts)
	$posts = get_posts($args);
	
	global $post;
	foreach( $posts as $post ) {
		setup_postdata( $post );
		$entry_data = get_field('entry_data-pm', false, false);
		$postMeta = get_post_custom($post->ID);
		$user = get_user_by( 'ID', $post->post_author );
		$userMeta = get_user_meta($user->ID);
	
		$user_details = array($post->ID, $userMeta['first_name'][0], $userMeta['last_name'][0] ,$userMeta['first_name'][0].' '.$userMeta['last_name'][0]);
		$user_details = array_pad($user_details, 4, '');
		array_push($user_details, rtrim(preg_replace('/\s+/', ' ',$userMeta['address'][0])), rtrim($userMeta['town'][0]),rtrim($userMeta['county'][0]),rtrim($userMeta['country'][0]));
		$user_details = array_pad($user_details, 9, '');
		array_push($user_details, rtrim($userMeta['postcode'][0]));
		array_push($user_details, (isset($userMeta['mobile'][0])) ? $userMeta['mobile'][0] : $userMeta['landline'][0]);
		array_push($user_details, $user->user_email);
	
		$ro_postal = get_field('show_data-pm', false, false)['ro_postal'];

		$entry_data = get_field('entry_data-pm', false, false);
		if (isset(get_field('show_data-pm', false, false)['dogs'])){
			$dogs = get_field('show_data-pm', false, false)['dogs'];
			foreach ($dogs as $dog){
				$dog_breed = get_term_by('id', $dog['breed'], 'dog-breeds');
				$dog['breedName'] = $dog_breed->name;
				$dog['level'] = $dog['meta_data'][$show_meta['affiliation'][0].'_level'];
				$dogs[$dog['id']] = $dog;
			}
		}
		else{
			$dogs = array();
		}
	
		$classes = array();
		foreach ($entry_data as $dog_id => $dogEntry){
			if(isset($dogEntry['classes'])){
				$classes[$dog_id] = array();
				foreach ($dogEntry['classes'] as $class_no => $class){
					if (!isset($classes[$dog_id][$class['handler']])){ $classes[$dog_id][$class['handler']] = array(); }
					array_push($classes[$dog_id][$class['handler']], $class_no);
					if (!isset($dogs[$dog_id])){ $dogs[$dog_id] = array(); }
					$dogs[$dog_id]['classHeight'] = $class['height'];
					$dogs[$dog_id]['classLevel'] = isset($class['level']) ? $class['level'] : $dogs[$dog_id]['level'];
					$dogs[$dog_id]['LHO'] = isset($class['lho']) ? $class['lho'] : 0;
				}
			}
			else{
				//print_r($dogEntry);
			}
		}
		
		foreach ($classes as $dog_id => $classesPerHandler){
			foreach ($classesPerHandler as $handler => $classes){
				$dog = $dogs[$dog_id];
				if (!isset($dog['id'])){
					// Get dog details when not saved with show data/entry
					$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}agility_dogs left outer join {$wpdb->prefix}terms on breed=term_id WHERE ID = %d", $dog_id );
					$row = $wpdb->get_row( $sql );
					$dog['id'] = $dog_id;
					$dog['pet_name'] = $row->pet_name;
					$dog['kc_name'] = $row->kc_name;
					$dog['kc_number'] = $row->kc_number;
					$dog['birth_date'] = $row->birth_date;
					$dog['breedName'] = $row->name;
					$dog['sex'] = $row->sex;
					if (!isset($dog['classHeight']) || !isset($dog['classLevel'])){
						$sql = $wpdb->prepare("select meta_key, meta_value from {$wpdb->prefix}agility_dogsmeta where dog_id = $dog_id and meta_key like '".$show_meta['affiliation'][0]."_%'");
						$results = $wpdb->get_results( $sql, 'OBJECT_K');
						if (!isset($dog['classHeight'])){
							$dog['classHeight'] = $results[$show_meta['affiliation'][0]."_height"]->meta_value;
						}
						if (!isset($dog['classLevel'])){
							$dog['classLevel'] = $results[$show_meta['affiliation'][0]."_level"]->meta_value;
						}
					}
				}
				
				$handler_details = explode(' ', $handler);
				$user_details[1] = array_shift($handler_details);
				$user_details[2] = implode(' ', $handler_details);
				$user_details[3] = $handler;
				
				$name = (isset($dog['kc_name'])) ? $dog['kc_name'] : $dog['pet_name'];
				$entry_row = array_merge(array_slice($user_details, 0, 4), array($name, $dog['classHeight'], $dog['classLevel'], $dog['breedName'], $dog['sex'], $dog['birth_date'], $dog['kc_number'], $dog_id), array_slice($user_details, 4), array("", "", "", rtrim($ro_postal), implode(',', $classes), $dog['LHO']));
				array_push($entries, $entry_row);
			}
		}
		
	}
	$return['entries'] = $entries;
	echo json_encode($return);
	wp_die();
}

function view_comp_nos(){
	global $wpdb, $current_user;
	
	$return = array();
	$show_id = intval($_POST['show_id']);
	
	$show = get_post( $show_id );	
	$return['show'] = $show;
	
	if (!in_array( 'administrator', $current_user->roles ) && $show->post_author !== $current_user->ID){
		$return['error'] = "ERROR - you don't have the correct permission to view the entry details of this show";
		echo json_encode($return);
		wp_die();
	}
	
	$show_meta = get_post_meta($show_id);
	
	$args = array (
			'post_type'	=> 'entries',
			'post_status'	=> array('publish'),
			'numberposts'	=> -1,
			'post_parent' 	=> $show_id
	);
	// get posts
	$posts = get_posts($args);
	$return['entry_count'] = count($posts);
	
	global $post;
	$nfc_count = 0;
	$class_counts = array();
	foreach( $posts as $post ) {
		//echo get_the_ID()."\n";
		setup_postdata( $post );
		$entry_data = get_field('entry_data-pm', false, false);
		foreach ($entry_data as $dog_id => $dogEntry){
			if($dogEntry == 'nfc'){ $nfc_count++; continue; }
			foreach ($dogEntry['classes'] as $classNo => $classDetails){
				if (!isset($class_counts[$classNo])){
					$class_counts[$classNo] = array();
					$class_counts[$classNo]['title'] = $classDetails['class_title'];
				}
				if ($classDetails['lho']){
					$class_counts[$classNo]['lho']++;
				}
				else{
					$class_counts[$classNo]['fho']++;
				}
			}
		}
	}
	ksort($class_counts);
	
	$return['class_data'] = $class_counts;
	
	echo json_encode($return);
	wp_die();
	
}


?>