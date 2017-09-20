<?php
global $wpdb;
ini_set("auto_detect_line_endings", true);

$header = array();
$payments = array();

$file = (isset($_GET['file'])) ? $_GET['file'] : str_replace('file=', '', $argv[1]);
if($file == '' && !empty($args)){ $file = $args[0]; } 

if (($handle = fopen($file, "r")) !== FALSE) {
	$row = 0;
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		if ($row == 0){
			//head row - capture column titles
			$header = $data;
			$row ++;
			continue;
		}
		//Array([0] => postID    [1] => date    [2] => method	[3] => amount)
		
		if (!isset($payments[$data[0]])){
			$sql = "select * from wpao_postmeta where post_id=".$data[0]." and meta_key='paid-pm'";
        	        $res = $wpdb->get_row($sql);
                	if (!empty($res)){
                        	$existing_payment = unserialize($res->meta_value);
				$payments[$data[0]] = $existing_payment;
	                }
			else {
				$payments[$data[0]] = array();
			}
		}
		
		$tmp_array = array();
		for ($col = 1; $col < count($header); $col++) {
			$tmp_array[$header[$col]] = $data[$col];
		}
		array_push($payments[$data[0]], $tmp_array);	
	}
	print_r($payments);
//	echo "REPLACE INTO wpao_postmeta VALUES (NULL, ".$show_id.", 'classes', '".serialize($classes)."');\n";
//	echo "REPLACE INTO wpao_postmeta VALUES (NULL, ".$show_id.", 'lho_options', '".serialize($lho_options)."');\n";
}

foreach ($payments as $post_id => $paymentArray){
	echo "REPLACE INTO wpao_postmeta VALUES (NULL, ".$post_id.", 'paid-pm', '".serialize($paymentArray)."');\n";
}
?>


