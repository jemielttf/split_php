<?php

ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Tokyo');

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

define('CURRENT_DIR', 	__DIR__);
define('DATA_BASE', 	"/data/{$pdf_type}/{$year}_{$month}-{$dir_time}");
define('DATA_DIR', 		CURRENT_DIR . DATA_BASE . '/source/');
define('RESULT_DIR', 	CURRENT_DIR . DATA_BASE . '/members/');
define('LOG_BASE', 		CURRENT_DIR . "/log/");
define('LOG_DIR', 		LOG_BASE . "{$pdf_type}/");

define('PDFtk_PATH', 	'/usr/local/bin/pdftk');
// define('PDFtk_PATH', 	'/usr/bin/pdftk');

write_status_log($proc_id, 'start', $year, $month, $pdf_type);

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

$current_start_page = 1;
for ($count = 0; $count < count($member_data); $count++) {
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
echo '[' . $time_2->format('Y-m-d H:i:s')  . '] スクリプトは終了しました。'. "\n";
echo '処理にかかった時間は' . $diff->format('%h:%i:%s') . '秒です。' . "\n";
write_status_log($proc_id, 'fin', $year, $month, $pdf_type);





// 以下function

function load_csv_data($file_xsv, $type = 'csv', $skip_row_1 = true) {
	$file = new SplFileObject($file_xsv, 'r');
	$file->setFlags(SplFileObject::READ_CSV);
	if ($type == 'tsv') $file->setCsvControl("\t");

	$array = array();

	$count = 0;
	foreach ($file as $row) {
		$count++;
		if ($skip_row_1 && $count == 1) continue;
		if (!is_null($row[0])) array_push($array, $row);
	}

	return $array;
}

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

	$time = new DateTime();
	fputcsv($log, array($time->format('Y-m-d\TH:i:s'), $pdf_type, $year, $month, $msg));
	fclose($log);

	check_all_done($proc_id, $year, $month, $pdf_type);
}

function check_all_done($proc_id, $year, $month, $pdf_type) {
	$proc_date = explode('_', $proc_id);
	$proc_date = "{$proc_date[0]}_{$proc_date[1]}";

	$file_names = glob(LOG_DIR . '*_status.log', );
	$log_list = array();

	foreach($file_names as $file_name) {
		if (preg_match("@({$proc_date})@", $file_name, $m)) {
			$log_list[] = $file_name;
		}
	}

	$fin_count = 0;
	$is_finish = false;
	$time_stamps = array();
	foreach($log_list as $path) {
		$proc_status = load_csv_data($path, 'csv', false);
		$proc_status = $proc_status[0];
		if ($proc_status[count($proc_status) - 1] == 'fin') $fin_count++;
		$time_stamps[] = $proc_status[0];
	}
	$is_finish = $fin_count == count($log_list) ? true : false;

	if ($is_finish) {
		echo "[{$time_stamps[count($time_stamps) - 1]}] すべての処理が終了しました。\n";
		rename_working_dir($proc_id, $year, $month, $pdf_type);
	}
}

function rename_working_dir($proc_id, $year, $month, $pdf_type) {
	$working_dir	= CURRENT_DIR . DATA_BASE;
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
			echo "旧ディレクトリの削除しました。\n";
		}
	}

	if (rename($working_dir, $data_dir)) {
		echo "作業ディレクトリをリネームしました。\n";
	} else {
		echo "作業ディレクトリのリネームに失敗しました。\n";
	}
}


