<?php

define('PDFtk_PATH', 	'/usr/local/bin/pdftk');
define('PHP_PATH', 		'/usr/local/bin/php');
// define('PDFtk_PATH', 	'/usr/bin/pdftk');
// define('PHP_PATH', 		'/usr/bin/php');


function load_csv_data($file_xsv, $type = 'csv', $skip_first_row = true) {
	$file = new SplFileObject($file_xsv, 'r');
	$file->setFlags(SplFileObject::READ_CSV);
	if ($type == 'tsv') $file->setCsvControl("\t");

	$array = array();

	$count = 0;
	foreach ($file as $row) {
		$count++;
		if ($skip_first_row && $count == 1) continue;
		if (!is_null($row[0])) array_push($array, $row);
	}

	return $array;
}

function set_process_log($proc_time, $year, $month, $pdf_type, $mode = 'new') {
	$log_path = LOG_BASE . "process.log";

	$json_str = '';
	if (file_exists($log_path)) $json_str = file_get_contents($log_path);
	
	$proccess_data = $json_str ? json_decode($json_str, true) : null;;

	$log = fopen($log_path, 'w+');
	flock($log, LOCK_EX);

	if 		($mode == 'new') 	$proccess_data = update_process_log_data($proccess_data, $proc_time, "{$year}_{$month}", $pdf_type);
	elseif 	($mode == 'fin') 	$proccess_data = update_process_log_data($proccess_data, $proc_time, "{$year}_{$month}", $pdf_type, 'fin');
	
	fwrite($log, json_encode($proccess_data));
	fclose($log);

	return $proccess_data;
}

function update_process_log_data($data, $proc_time, $month_label, $pdf_type, $msg_1 = 'running') {
	if (empty($data)) 								$data = array();
	if (empty($data[$pdf_type]))					$data[$pdf_type] = array();
	if (empty($data[$pdf_type][$month_label]))		$data[$pdf_type][$month_label] = array();

	if (!empty($data[$pdf_type][$month_label][$proc_time])) {
		$data[$pdf_type][$month_label][$proc_time] = $msg_1;
	} else {
		foreach($data[$pdf_type][$month_label] as $key => &$value) {
			$value = $value == 'running' ? 'stop' : $value;
		}
		$data[$pdf_type][$month_label][$proc_time] = $msg_1;
	}

	return $data;
}