<?php

require_once './setting.php';
require_once './utils.php';

$file_pdf	= $argv[1];
$file_xsv	= $argv[2];
$pdf_type	= $argv[3];
$mode		= $argv[4];
$year		= $argv[5];
$month		= $argv[6];
$proc_id	= $argv[7];

$time_1 = new DateTime();
echo '[' . $time_1->format('Y-m-d H:i:s') . '] スクリプトを開始します。'. "\n\n";

$dir_time = explode('_', $proc_id);
$dir_time = "{$dir_time[0]}_{$dir_time[1]}";

define('DATA_BASE', 	"/{$pdf_type}/{$year}_{$month}-{$dir_time}");
define('DATA_DIR', 		FILES_DIR . DATA_BASE . '/source/');
define('RESULT_DIR', 	FILES_DIR . DATA_BASE . '/members/');
define('LOG_BASE', 		CURRENT_DIR . "/log/");
define('LOG_DIR', 		LOG_BASE . "{$pdf_type}/");







write_status_log($proc_id, 'running', $year, $month, $pdf_type);

for ($i = 1; $i < count($argv); $i++) {
	echo $argv[$i];
	echo "\n";
}
echo "----------------------------\n";

if (empty($file_pdf) || empty($file_xsv)) return;

// CSVを読み込み
$member_data = load_csv_data($file_xsv, $mode);

$pdftk	= PDFtk_PATH;
$cmd 	= "{$pdftk} {$file_pdf} dump_data | grep NumberOfPages | sed 's/[^0-9]*//'";
exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);
$pdf_total_pages = (int)$output[0];

$proc_time 			= explode('_', $proc_id);
$proc_time 			= "{$proc_time[0]}_{$proc_time[1]}";
$month_label		= "{$year}_{$month}";

$current_start_page = 1;
$current_status		= '';
$status_check_cycle	= get_status_check_cycle(count($member_data));

for ($count = 0; $count < count($member_data); $count++) {
	if ($count % $status_check_cycle == 0) {
		$current_status = get_current_prcess_log_status($proc_time, $month_label, $pdf_type);
		echo "current_status : {$current_status}\n";
	}

	if ($current_status == 'stop') break;

	$result = split_PDF($file_pdf, $member_data[$count], $current_start_page, $pdf_type, $year, $month, $pdf_total_pages);

	if ($result['error']) {
		write_error_log($proc_id, $member_data[$count][0], $result['error_message'], $year, $month, $pdf_type);
		echo 'エラー : ' . $result['error_message'] . "\n";
		return;
	}
	else echo RESULT_DIR . $result['data'] . "\n";
	echo '----------------' . "\n";
}

removeFile($file_pdf);
removeFile($file_xsv);

echo "\n\n------------------------\n";
$time_2 = new DateTime();
$diff = $time_2->diff($time_1);

if ($current_status == 'stop') {
	echo '[' . $time_2->format('Y-m-d H:i:s')  . '] スクリプトは中断しました。'. "\n";
	write_status_log($proc_id, 'stopped', $year, $month, $pdf_type);
} else {
	echo '[' . $time_2->format('Y-m-d H:i:s')  . '] スクリプトは終了しました。'. "\n";
	echo '処理にかかった時間は' . $diff->format('%h:%i:%s') . '秒です。' . "\n";
	write_status_log($proc_id, 'fin', $year, $month, $pdf_type);
}






// 以下function

function split_PDF($pdf_path, $data, &$start, $pdf_type, $year, $month, $total_pages) {
	$memberCd	= $data[0];
	$pages		= (int)$data[count($data) - 2];
	$end 		= $start + $pages - 1;

	if ($end < $start) return array('error' => 1, 'error_message' => "分割終了ページ ({$end}) が開始ページ ({$start}) よりも小さいです。");
	
	$file_name 	= "{$pdf_type}-{$year}_{$month}-{$memberCd}.pdf";
	$save_dir 	= RESULT_DIR;
	$pdftk		= PDFtk_PATH;

	if ($total_pages < $end) return array('error' => 1, 'error_message' => "分割終了ページ ({$end}) が総ページ数 ({$total_pages}) よりも大きいです。");

	$cmd = "{$pdftk} {$pdf_path} cat {$start}-{$end} output {$save_dir}{$file_name}";
	// echo $cmd . "<br>\n";
	exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);

	echo $result . " \n";

	$start = $end + 1;

	if ($result == 0) {
		return array('error' => 0, 'data' => $file_name);
	} else {
		return array('error' => 1, 'error_message' => "PDFの分割に失敗しました。");
	}
}

function get_status_check_cycle($length) {
	return ceil($length / 50);
}

function removeFile($path) {
	exec("rm -rf {$path}");
}

function echo_error($error) {
	return json_encode(array('error' => 1, 'error_message' => $error->getMessage()));
}

function write_error_log($proc_id, $memberCd, $error_msg, $year, $month, $pdf_type) {
	$log_path = LOG_BASE . 'error.log';

	$log = fopen($log_path, 'a');
	if ($log === FALSE) echo 'エラーログの書き込みに失敗しました。';

	$time = new DateTime();
	fputcsv($log, array($time->format('Y-m-d H:i:s'), "{$year}_{$month}", $pdf_type, $proc_id, $memberCd, $error_msg, LOG_DIR . "{$year}_{$month}/{$proc_id}.log"));
	fclose($log);
}

function write_status_log($proc_id, $msg, $year, $month, $pdf_type) {
	$log_path = LOG_DIR . "{$pdf_type}-{$proc_id}_status.log";

	$log = fopen($log_path, 'w');
	if ($log === FALSE) write_error_log($proc_id, '', 'ステータスログの書き込みに失敗しました。', $year, $month, $pdf_type);

	flock($log, LOCK_EX);
	$time = new DateTime();
	fputcsv($log, array($time->format('Y-m-d\TH:i:s'), $pdf_type, $year, $month, $msg));
	fclose($log);

	check_all_done($proc_id, $year, $month, $pdf_type);
}

function check_all_done($proc_id, $year, $month, $pdf_type) {
	$proc_time = explode('_', $proc_id);
	$proc_time = "{$proc_time[0]}_{$proc_time[1]}";

	$file_names = glob(LOG_DIR . '*_status.log');
	$log_list = array();

	foreach($file_names as $file_name) {
		if (preg_match("@({$proc_time})@", $file_name, $m)) {
			$log_list[] = $file_name;
		}
	}

	$fin_count 	= 0;
	$is_finish 	= false;
	$is_stopped = false;
	$time_stamps = array();
	foreach($log_list as $path) {
		$proc_status = load_csv_data($path, 'csv', false);
		$proc_status = $proc_status[0];
		if ($proc_status[count($proc_status) - 1] == 'fin'|| $proc_status[count($proc_status) - 1] == 'stopped' ) $fin_count++;
		if ($proc_status[count($proc_status) - 1] == 'stopped') $is_stopped = true;
		$time_stamps[] = $proc_status[0];
	}
	$is_finish = $fin_count == count($log_list) ? true : false;

	if ($is_finish) {

		if ($is_stopped) {
			echo "[{$time_stamps[count($time_stamps) - 1]}] すべての処理を中断しました。\n";
			remove_working_dir($proc_id, $year, $month, $pdf_type);

			set_process_log($proc_time, $year, $month, $pdf_type, 'stopped');
		} else {
			echo "[{$time_stamps[count($time_stamps) - 1]}] すべての処理が終了しました。\n";
			rename_working_dir($proc_id, $year, $month, $pdf_type);

			set_process_log($proc_time, $year, $month, $pdf_type, 'fin');
		}
	}
}



