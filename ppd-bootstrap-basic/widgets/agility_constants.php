<?php

$KC_HEIGHTS = array('Small' => 'Small (&le;35cm)','Medium' => 'Medium (35cm-43cm)','Large' => 'Large (&gt;43cm)');
$BS_HEIGHTS = array('Toy' => 'Toy (&le;32cm)', 'Small' => 'Small (26cm-39cm)', 'Medium' => 'Medium (37cm-48cm)', 'Standard' => 'Standard (&gt;44cm)', 'Large' => 'Large (&gt;44cm)');
$TA_HEIGHTS = array('Micro' => 'Micro (&le;35.5cm)', 'Small' => 'Small (&le;43cm)', 'Medium' => 'Medium (35.5cm-51cm)', 'Standard' => 'Standard (&gt;43cm)', 'Large' => 'Large (&gt;43cm)');

$KC_GRADES = array('NFC', '1', '2', '3', '4', '5', '6', '7');
$BS_LEVELS = array('NFC', 'Beginners', 'Elementary', 'Starters', 'Novice', 'Graduate', 'Senior', 'Veterans', 'Allsorts');
$TA_LEVELS = array('NFC', 'Elementary', 'Starters', 'Novice', 'Senior', 'Veterans', 'Anysize');

$JOBS = array('None', 'Any', 'Caller', 'Leads', 'Pads', 'Scrimer', 'Scorer');

function get_options_for_jobs($selected){
	global $JOBS;
	$options = '';
	foreach ($JOBS as $job){
		$options .= '<option value="'.$job.'"';
		if ($job == $selected){ $options .= ' selected="selected"';}
		$options .= '>'.$job.'</option>';
	}
	return $options;
}

function get_all_agility_heights($type){
	global $KC_HEIGHTS,$BS_HEIGHTS, $TA_HEIGHTS;
	switch ($type) {
		case "kc":
			return $KC_HEIGHTS;
			break;
		case "bs":
			return $BS_HEIGHTS;
			break;
		case "ta":
			return $TA_HEIGHTS;
			break;
//		case "cake":
//			echo "i is cake";
//			break;
	}
}

function get_agility_height($type, $height){
	$HEIGHTS = get_all_agility_heights($type);
	return $HEIGHTS[$height];
}

function get_all_agility_levels($type){
	global $KC_GRADES, $BS_LEVELS, $TA_LEVELS;
	switch ($type) {
		case "kc":
			return $KC_GRADES;
			break;
		case "bs":
			return $BS_LEVELS;
			break;
		case "ta":
			return $TA_LEVELS;
			break;
	}
}



function get_options_for_heights($type, $selected){
	$options = '';
	$HEIGHTS = get_all_agility_heights($type);
	foreach ($HEIGHTS as $val => $text){
		$options .= '<option value="'.$val.'"';
		if ($val == $selected){ $options .= ' selected="selected"';}
		$options .= '>'.$text.'</option>';
	}
	return $options;
}

function get_options_for_heights_minmax($type, $selected, $minHeight, $maxHeight){
	$options = '';
	$HEIGHTS = get_all_agility_heights($type);
	$height_avail = 0;
	foreach ($HEIGHTS as $val => $text){
		if (!$height_avail && strtolower($val) == strtolower($minHeight)){
			$height_avail = 1;
		}
		if ($height_avail){
			$options .= '<option value="'.$val.'"';
			if(strtolower($val) == strtolower($selected)){ $options .= ' selected="selected"';}
			$options .= '>'.$text.'</option>';
		}
		if (strtolower($val) == strtolower($maxHeight)){
			$height_avail = 0;
		}
	}
	return $options;
}

function get_options_for_levels($type, $selected){
	$options = '';
	$LEVELS = get_all_agility_levels($type);
	foreach ($LEVELS as $level){
		$options .= '<option value="'.$level.'"';
		if ($level == $selected){ $options .= ' selected="selected"';}
		$options .= '>'.$level.'</option>';
	}
	return $options;
}

function check_class_level($type, $dogLevel, $minLevel, $maxLevel){
	$LEVELS = get_all_agility_levels($type);
	$level_avail = 0;
	foreach ($LEVELS as $level){
		if (!$level_avail && strtolower($level) == strtolower($minLevel)){
			$level_avail = 1;
		}
		if ($level_avail && strtolower($level) == strtolower($dogLevel)){
			return 1;
		}
		if (strtolower($level) == strtolower($maxLevel)){
			$level_avail = 0;
		}
	}
	return 0;
}

function check_class_height($type, $dogHeight, $minHeight, $maxHeight){
	$HEIGHTS = get_all_agility_heights($type);
	$height_avail = 0;
	foreach ($HEIGHTS as $val => $text){
		if (!$height_avail && strtolower($val) == strtolower($minHeight)){
			$height_avail = 1;
		}
		if($height_avail && strtolower($val) == strtolower($dogHeight)){
			return 1;
		}
		if (strtolower($val) == strtolower($maxHeight)){
			$height_avail = 0;
		}
	}
	return 0;
}

function get_dogs_for_user($userId){
	global $wpdb;
	$dogs = array();
	$dogData = $wpdb->get_results("SELECT * FROM wpao_agility_dogs WHERE user_id = '".$wpdb->_real_escape($userId)."' ORDER BY `pet_name`", 'ARRAY_A');
	if(!empty($dogData)) {
		foreach($dogData as $dog) {
			$dogMeta = array();
			$dogMetaQ = $wpdb->get_results("select meta_key,meta_value from wpao_agility_dogsmeta where dog_id= '".$dog['id']."'", 'ARRAY_A');
			foreach ($dogMetaQ as $meta){
				$dogMeta[$meta['meta_key']] = $meta['meta_value'];
			}
			$dog['meta_data'] = $dogMeta;
			array_push($dogs, $dog);
		}
	}	
	return $dogs;
}