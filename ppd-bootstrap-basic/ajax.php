<?php

add_action('wp_ajax_entry_details', 'get_comp_entries');
add_action('wp_ajax_nopriv_entry_details', 'get_comp_entries');

add_action('wp_ajax_pairs_details', 'get_pairs_entries');
add_action('wp_ajax_nopriv_pairs_details', 'get_pairs_entries');

add_action('wp_ajax_entry_data', 'get_entry_data');
add_action('wp_ajax_nopriv_entry_data', 'get_entry_data');

add_action('wp_ajax_comp_nos', 'view_comp_nos');
add_action('wp_ajax_nopriv_comp_nos', 'view_comp_nos');


function _check_is_admin(){
	global $wpdb, $current_user;
	
	$return = array();
	
	if (!in_array( 'administrator', $current_user->roles )){
		$return['error'] = "ERROR - you don't have the correct permission to view the entry details of this show";
		echo json_encode($return);
		wp_die();
	}
}


function get_entry_data(){
	global $wpdb, $current_user;
	_check_is_admin();
	
	$return = array();
	$show_id = intval($_POST['show_id']);
	$show = get_post( $show_id );
	$show_meta = get_post_meta($show_id);
	$show->showMeta = $show_meta;
	
	$start_date = new DateTime(get_field('start_date', false, false));
	$end_date 	= new DateTime(get_field('end_date', false, false));
	
	$show_dates = $start_date->format('jS M');
	if($start_date != $end_date){
		$show_dates .= ' to '.$end_date->format('jS M Y');
	}
	else{
		$show_dates .= $start_date->format(' Y');
	}
	
	$show->showDates = $show_dates;
	
	$sql = $wpdb->prepare("select distinct p.id from ".$wpdb->prefix."ring_card_info INNER JOIN $wpdb->posts p on p.id=post_id WHERE post_parent = %d", $show_id);
	$results = $wpdb->get_results($sql);
	$entry_ids = array();
	foreach ($results as $row){
		array_push($entry_ids, $row->id);
	}
	
	$args = array(
			'post_type' => 'entries',
			'numberposts' => -1,
			'post__in' => $entry_ids,
			'order' => 'ASC',
			'order_by' => 'meta_value',
			'meta_key' => 'entry_ts-pm'
	);
	$posts = get_posts($args);
	$entryData = get_entries_from_posts($posts, $show_meta);	
	$entries = $entryData['class_data'];
	
//	$form_no = 999;
	$last_form_no = 0;
	$forms_array = array();
	foreach ($entries as $entry){
		if ($entry[0] !== $last_form_no) {
			$last_form_no = $entry[0];
			$form_no = $entry[0];
//			$form_no++;
			
			$address = array_filter(array_slice($entry, 14, 6));
			$forms_array[$form_no] = array();
			$forms_array[$form_no]['details'] = array(
					'name' => strtoupper($entry[4]),
					'address' => strtoupper(implode(', ', $address)),
					'tel' => $entry[20],
					'email' => $entry[21]
			);
			$forms_array[$form_no]['dogs'] = array();
			$forms_array[$form_no]['camping'] = array();
			$forms_array[$form_no]['helpers'] = array();
			$forms_array[$form_no]['fees'] = $entryData['fees'][$entry[1]];
			
			if (isset($entryData['camping'][$entry[1]])){
				$forms_array[$form_no]['camping'] = $entryData['camping'][$entry[1]];
			}
			
			if (isset($entryData['helpers'][$entry[1]])){
				$forms_array[$form_no]['helpers'] = $entryData['helpers'][$entry[1]];
			}
		}
		
		$lho = '';
		if ($entry[27] === 1) { $lho = 'Y'; }
		if ($entry[27] === 0) { $lho = 'N'; }
	
		$dog_ring_no = $wpdb->get_var("SELECT ring_no FROM ".$wpdb->prefix."ring_card_info WHERE post_id=$entry[1] AND dog_id=$entry[13]");
		array_push($forms_array[$form_no]['dogs'], array(
				'kc_name' => strtoupper($entry[5]),
				'pet_name' => strtoupper($entry[6]),
				'kc_no' => strtoupper($entry[12]),
				'sex' => strtoupper($entry[10]),
				'dob' => SQLToDate($entry[11]),
				'breed' => strtoupper($entry[9]),
				'grade' => $entry[8],
				'handler' => strtoupper($entry[4]),
				'ring_no' => $dog_ring_no,
				'dog_id' => $entry[13],
				'lho' => $lho,
				'classes' => $entry[26]
		));
		
		//if ($form_no > 1005){ break; }
	}
	
	$return['form_data'] = $forms_array;
	$return['show'] = $show;
	
	echo json_encode($return);
	wp_die();
	
}

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
	$show->showMeta = $show_meta;
	
	$start_date = new DateTime(get_field('start_date', false, false));
	$end_date 	= new DateTime(get_field('end_date', false, false));
	
	$show_dates = $start_date->format('jS M');
	if($start_date != $end_date){
		$show_dates .= ' to '.$end_date->format('jS M Y');
	}
	else{
		$show_dates .= $start_date->format(' Y');
	}
	
	$show->showDates = $show_dates;
	
	$return['show'] = $show;
	
	$args = array (
			'post_type'	=> 'entries',
			'post_status'	=> array('publish'),
			'numberposts'	=> -1,
			'order'		=> 'ASC',
			'post_parent' 	=> $show_id
	);
	
	// get entries (posts)
	$posts = get_posts($args);
	$entries = get_entries_from_posts($posts, $show_meta);
	
	$return['entries'] = $entries['class_data'];
//	$return['debug'] = $show_meta;
	echo json_encode($return);
	wp_die();
}

function get_pairs_entries(){
	global $wpdb, $current_user;
	_check_is_admin();
	
	$return = array();
	
	$show_id = intval($_POST['show_id']);
	$show = get_post( $show_id );
	$show_meta = get_post_meta($show_id);
	$show->showMeta = $show_meta;
	
	$start_date = new DateTime(get_field('start_date', false, false));
	$end_date 	= new DateTime(get_field('end_date', false, false));
	
	$show_dates = $start_date->format('jS M');
	if($start_date != $end_date){
		$show_dates .= ' to '.$end_date->format('jS M Y');
	}
	else{
		$show_dates .= $start_date->format(' Y');
	}
	
	$show->showDates = $show_dates;
	
	$return['show'] = $show;
	
	$args = array (
			'post_type'		=> 'entries',
			'post_status'	=> array('publish'),
			'numberposts'	=> -1,
			'order'			=> 'ASC',
			'post_parent' 	=> $show_id
	);
	
	// get entries (posts)
	$posts = get_posts($args);
	$entries = get_entries_from_posts($posts, $show_meta);
	
	$return['pairs_info'] = $entries['pairs_data'];
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

function get_entries_from_posts($posts, $show_meta){
	
	global $wpdb, $post;
	$entries = array();
	$entries['class_data'] = array();
	$entries['pairs_data'] = array();
	$entries['helpers'] = array();
	$entries['camping'] = array();
	$entries['fees'] = array();
	
	$show_aff = $show_meta['affiliation'][0];
	
	$guess_class_cost = 0;
	$form_no = 999;
	
	foreach( $posts as $post ) {
		setup_postdata( $post );
//		$entry_data = get_field('entry_data-pm', false, false);
		$postMeta = get_post_custom($post->ID);
		$user = get_user_by( 'ID', $post->post_author );
		$userMeta = get_user_meta($user->ID);
		$form_no++;

		$user_details = array($form_no, $post->ID, $userMeta['first_name'][0], $userMeta['last_name'][0] ,$userMeta['first_name'][0].' '.$userMeta['last_name'][0]);
		$user_details = array_pad($user_details, 5, '');
		array_push($user_details, rtrim(preg_replace('/\s+/', ' ',$userMeta['address'][0])), rtrim($userMeta['town'][0]),rtrim($userMeta['county'][0]),rtrim($userMeta['country'][0]));
		$user_details = array_pad($user_details, 9, '');
		array_push($user_details, rtrim($userMeta['postcode'][0]));
		array_push($user_details, (isset($userMeta['mobile'][0])) ? $userMeta['mobile'][0] : $userMeta['landline'][0]);
		array_push($user_details, $user->user_email);

		$ro_postal = get_field('ro_postal-pm', false, false);	
		if(!isset($ro_postal) || $ro_postal == ''){ $ro_postal = get_field('show_data-pm', false, false)['ro_postal']; }
		
		$nfc_dogs = array();
		$classes_counted = 0;
	
		$entry_data = unserialize(get_field('entry_data-pm', false, false));
		$show_data = unserialize(get_field('show_data-pm', false, false));
		if (isset($show_data['dogs'])){
			$dogs = $show_data['dogs'];
			foreach ($dogs as $dog){
				//$dog_breed = get_term_by('id', $dog['breed'], 'dog-breeds');
				$sql = $wpdb->prepare("select name from wpao_terms where term_id=%d", $dog['breed']);
				$breedTerm = $wpdb->get_row($sql);
				$dog['breedName'] = $breedTerm->name;
				$dog['level'] = $dog['meta_data'][$show_aff.'_level'];
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
					if (!isset($class['handler']) || $class['handler'] == "" || $class['handler'] == " "){ $class['handler'] = $userMeta['first_name'][0].' '.$userMeta['last_name'][0]; }
					if (!isset($classes[$dog_id][$class['handler']])){ $classes[$dog_id][$class['handler']] = array(); }
					array_push($classes[$dog_id][$class['handler']], $class_no);
					if (!isset($dogs[$dog_id])){ $dogs[$dog_id] = array(); }
					$dogs[$dog_id]['classHeight'] = $class['height'];
					$dogs[$dog_id]['classLevel'] = (isset($class['level']) && $class['level'] !='') ? $class['level'] : $dogs[$dog_id]['level'];
					$lho = 0;
					if (isset($class['lho'])){ $lho = $class['lho']; }
					elseif (isset($dogEntry['lho']) && $dogEntry['lho'] != ''){ $lho = 1; }
					//$dogs[$dog_id]['LHO'] = isset($class['lho']) ? $class['lho'] : 0;
					$dogs[$dog_id]['LHO'] = $lho;
				}
			}
			else{
				if (!isset($dogs[$dog_id])){ $dogs[$dog_id] = array(); }
				array_push($nfc_dogs, $dog_id);
			}

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
				$dogs[$dog_id] = $dog;
			}
			
			$dog['kc_name'] = str_replace("\'", "'", $dog['kc_name']);
			
			if (!isset($dog['classHeight']) || !isset($dog['classLevel'])){
				//$sql = $wpdb->prepare("select meta_key, meta_value from {$wpdb->prefix}agility_dogsmeta where dog_id = $dog_id and meta_key like '".$show_aff."_\%'");
				$sql = "select meta_key, meta_value from {$wpdb->prefix}agility_dogsmeta where dog_id = $dog_id and meta_key like '".$show_aff."_%'";
				$results = $wpdb->get_results( $sql, 'OBJECT_K');
				if (!isset($dog['classHeight'])){
					$dog['classHeight'] = $results[$show_aff."_height"]->meta_value;
				}
				if (!isset($dog['classLevel'])){
					$dog['classLevel'] = $results[$show_aff."_level"]->meta_value;
				}
			}
			
			if (empty($dog['breedName'])){
				$sql = $wpdb->prepare("select name from wpao_terms where term_id=%d", $dog['breed']);
				$breedTerm = $wpdb->get_row($sql);
				$dog['breedName'] = $breedTerm->name;
			}
			$dogs[$dog_id] = $dog;
		}
		
		if ( get_post_meta($post->ID, 'pairs_count-pm', true) > 0){
			$pairs_teams = unserialize(get_post_meta($post->ID, 'pairs_teams-pm', true));
			foreach ($pairs_teams as $class_no => $pairsDetails){
				for ($i=0; $i< count($pairsDetails)-2; $i++){
					$class_entry = $pairsDetails[$i];
	                foreach ($class_entry as $combo){
	                	if ($combo['handler'] != '' || $combo['dog'] != ''){
							$entry_row = array($user_details[0], $class_no, ($i+1), $combo['handler'], $combo['dog']);
							array_push($entries['pairs_data'], $entry_row);
						}
					}
				}
			}
		}
		
		foreach ($classes as $dog_id => $classesPerHandler){
			foreach ($classesPerHandler as $handler => $class_list){
				
				$dog = $dogs[$dog_id];
	
				$handler_details = explode(' ', $handler);
				$user_details[2] = array_shift($handler_details);
				$user_details[3] = implode(' ', $handler_details);
				$user_details[4] = $handler;
	
				$name = (isset($dog['kc_name']) && $dog['kc_name'] != '') ? $dog['kc_name'] : $dog['pet_name'];
				$entry_row = array_merge(array_slice($user_details, 0, 5), array($name, $dog['pet_name'], $dog['classHeight'], $dog['classLevel'], $dog['breedName'], $dog['sex'], $dog['birth_date'], $dog['kc_number'], $dog_id), array_slice($user_details, 4), array("", "", "", rtrim($ro_postal), implode(',', $class_list), $dog['LHO']));
				$classes_counted += count($class_list);
				array_push($entries['class_data'], $entry_row);
			}
		}
		
		foreach ($nfc_dogs as $dog_id){
			$dog = $dogs[$dog_id];
			$name = (isset($dog['kc_name']) && $dog['kc_name'] != '') ? $dog['kc_name'] : $dog['pet_name'];
			$entry_row = array_merge(array_slice($user_details, 0, 5), array($name, $dog['pet_name'], '', '', $dog['breedName'], $dog['sex'], $dog['birth_date'], $dog['kc_number'], $dog_id), array_slice($user_details, 4), array("", "", "", rtrim($ro_postal), 'NFC', ''));
			array_push($entries['class_data'], $entry_row);
		}
		
		
		$fees = array();
		$total_cost = get_field('total_cost-pm', false, false);
		$total_cost -= 0.5;
		if (rtrim($ro_postal) == 'yes'){$total_cost -= 1;}
		
		if ($show_meta['camping_avail']){
			$camping = get_field('show_data-pm', false, false)['camping'];
			if (count($camping) > 0){
				$fees['camping'] = array('count' => $camping['total_pitches'], 'amount' => $camping['total_amount']);
				$total_cost -= $camping['total_amount'];
				if (isset($camping['camp_whole'])){
					$entries['camping'][$post->ID] = array('duration' => 'whole show', 'group' => $camping['camping_group'], 'pitches' => $camping['camp_whole']['pitches']);
				}
				#TODO : add camping details for per night camping...
			}
		}
		
		$helpers = get_field('show_data-pm', false, false)['helpers'];
		if (count($helpers) > 0){
			$entries['helpers'][$post->ID] = $helpers;
		}

		$class_cost = ($total_cost/get_field('show_data-pm', false, false)['class_count']);
		if ($guess_class_cost == 0 && $class_cost > 0){ $guess_class_cost = $class_cost; }
		if (!$class_cost){ $class_cost = $guess_class_cost; }
		if (get_field('show_data-pm', false, false)['class_count'] > 0){ $class_count = get_field('show_data-pm', false, false)['class_count']; }
		else{ $class_count = $classes_counted; }
		$fees['classes'] = array('count' => $class_count, 'amount' => $class_cost);
		
		$entries['fees'][$post->ID] = $fees;	
	}
		
		//print_r($entries['camping']);
		//print_r($entries['fees']);
	//print_r($entries);
	return $entries;
}

?>
