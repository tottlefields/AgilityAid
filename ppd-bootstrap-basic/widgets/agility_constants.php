<?php

$KC_HEIGHTS = array('Small' => 'Small (&le;35cm)','Medium' => 'Medium (35cm-43cm)','Large' => 'Large (&gt;43cm)');
$BS_HEIGHTS = array('Toy' => 'Toy (&le;32cm)', 'Small' => 'Small (26cm-39cm)', 'Medium' => 'Medium (37cm-48cm)', 'Standard' => 'Standard (&gt;44cm)', 'Large' => 'Large (&gt;44cm)');

$KC_GRADES = array('NFC', '1', '2', '3', '4', '5', '6', '7');
$BS_LEVELS = array('Beginners', 'Elementary', 'Starters', 'Novice', 'Graduate', 'Senior', 'Veterans', 'Allsorts');

function get_all_agility_heights($type){
	global $KC_HEIGHTS,$BS_HEIGHTS;
	switch ($type) {
		case "kc":
			return $KC_HEIGHTS;
			break;
		case "bs":
			return $BS_HEIGHTS;
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
	global $KC_GRADES, $BS_LEVELS;
	switch ($type) {
		case "kc":
			return $KC_GRADES;
			break;
		case "bs":
			return $BS_LEVELS;
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