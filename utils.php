<?php

function load_csv_data($file_xsv, $type = 'csv', $skip_first_row = true) {
	$file = new SplFileObject($file_xsv, 'r');
	$file->flock(LOCK_EX);

	$file->setFlags(SplFileObject::READ_CSV);
	if ($type == 'tsv') $file->setCsvControl("\t");
	
	$array = array();

	$count = 0;
	foreach ($file as $row) {
		$count++;
		if ($skip_first_row && $count == 1) continue;
		if (!is_null($row[0])) array_push($array, $row);
	}

	$file->flock(LOCK_UN);

	return $array;
}

function set_process_log($proc_time, $year, $month, $pdf_type, $mode = 'new') {
	$log_path = LOG_BASE . "process.log";

	$proccess_data = null;
	if (file_exists($log_path)) $proccess_data = load_csv_data($log_path, 'csv', false);

	$log = fopen($log_path, 'w+');
	flock($log, LOCK_EX);

	if 		($mode == 'new') 	$proccess_data = update_process_log_data($proccess_data, $proc_time, "{$year}_{$month}", $pdf_type, 'running');
	else						$proccess_data = update_process_log_data($proccess_data, $proc_time, "{$year}_{$month}", $pdf_type, $mode);

	foreach($proccess_data as $data) {
		fputcsv($log, $data);
	}
	fclose($log);

	return $proccess_data;
}

function update_process_log_data($proccess_data, $proc_time, $month_label, $pdf_type, $msg) {
	if (empty($proccess_data)) $proccess_data = array();
	
	$is_update_proc = false;
	$delete_indexs	= array();
	foreach($proccess_data as $index => &$data) {
		$is_match_pdf_type 	= $data[0] == $pdf_type 	? true : false;
		$is_match_month 	= $data[1] == $month_label 	? true : false;
		$is_match_time		= $data[2] == $proc_time 	? true : false;

		if($is_match_pdf_type && $is_match_month) {
			if ($is_match_time) {
				$data[3] = $msg;
				$is_update_proc = true;
			} else {
				$data[3] = $data[3] == 'running' && $msg == 'running' ? 'stop' : $data[3];
			}
		}
		if ($data[3] == 'fin' || $data[3] == 'stopped') $delete_indexs[] = $index;
	}

	if (!$is_update_proc) {
		$proccess_data[] = array($pdf_type, $month_label, $proc_time, $msg);
	}

	rsort($delete_indexs);
	for ($i = 0; $i < count($delete_indexs); $i++) {
		array_splice($proccess_data, $delete_indexs[$i], 1);
	}

	return $proccess_data;
}

function get_current_prcess_log_status($proc_time, $month_label, $pdf_type) {
	$log_path = LOG_BASE . "process.log";

	$proccess_data = null;
	if (file_exists($log_path)) $proccess_data = load_csv_data($log_path, 'csv', false);

	$current_status = '';
	foreach($proccess_data as $index => $data) {
		$is_match_pdf_type 	= $data[0] == $pdf_type 	? true : false;
		$is_match_month 	= $data[1] == $month_label 	? true : false;
		$is_match_time		= $data[2] == $proc_time 	? true : false;

		if($is_match_pdf_type && $is_match_month && $is_match_time) {
			$current_status = $data[3];
		}
	}

	return $current_status;
}

function rename_working_dir($proc_id, $year, $month, $pdf_type) {
	$working_dir	= FILES_DIR . DATA_BASE;
	$data_dir		= explode('-', $working_dir);
	$data_dir		= $data_dir[0];
	echo $data_dir . "\n";

	if (file_exists($data_dir)) {
		exec("rm -rf {$data_dir}", $outupt);

		if (count($outupt) > 1) {
			echo "旧ディレクトリの削除に失敗しました。\n";
			write_error_log($proc_id, '', '旧ディレクトリの削除に失敗しました。', $year, $month, $pdf_type);
			return;
		} else {
			echo "旧ディレクトリを削除しました。\n";
		}
	}

	if (rename($working_dir, $data_dir)) {
		echo "作業ディレクトリをリネームしました。\n";
	} else {
		echo "作業ディレクトリのリネームに失敗しました。\n";
	}
}

function remove_working_dir($proc_id, $year, $month, $pdf_type) {
	$working_dir	= FILES_DIR . DATA_BASE;

	if (file_exists($working_dir)) {
		exec("rm -rf {$working_dir}", $outupt);

		if (count($outupt) > 1) {
			echo "作業ディレクトリの削除に失敗しました。\n";
			write_error_log($proc_id, '', '作業ディレクトリの削除に失敗しました。', $year, $month, $pdf_type);
			return;
		} else {
			echo "作業ディレクトリを削除しました。\n";
		}
	}
}

function error_stop($proc_time, $year, $month, $pdf_type) {
	remove_working_dir($proc_time, $year, $month, $pdf_type);
	set_process_log($proc_time, $year, $month, $pdf_type, 'fin');
}