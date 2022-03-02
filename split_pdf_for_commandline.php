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

define('CURRENT_DIR', 	__DIR__);
define('DATA_BASE', 	'/data/');
define('RESULT_BASE', 	'/result/');
define('DATA_DIR', 		CURRENT_DIR . DATA_BASE . "upload/{$year}/{$month}/");
define('RESULT_DIR', 	CURRENT_DIR . RESULT_BASE . "{$year}/{$month}/");
define('LOG_DIR', 		CURRENT_DIR . "/log/");

define('PDFtk_PATH', 	'/usr/local/bin/pdftk');
// define('PDFtk_PATH', 	'/usr/bin/pdftk');

$time_1 = new DateTime();
echo '[' . $time_1 -> format('Y-m-d H:i:s') . '] スクリプトを開始します。'. "\n\n";
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
$diff = $time_2 -> diff($time_1);
echo '[' . $time_2 -> format('Y-m-d H:i:s')  . '] スクリプトは終了しました。'. "\n";
echo '処理にかかった時間は' . $diff -> format('%h:%i:%s') . '秒です。' . "\n";
write_status_log($proc_id, 'fin', $year, $month, $pdf_type);





// 以下function

function load_csv_data($file_xsv, $type = 'csv') {
	$file = new SplFileObject($file_xsv, 'r');
	$file -> setFlags(SplFileObject::READ_CSV);
	if ($type == 'tsv') $file -> setCsvControl("\t");

	$array = array();

	$count = 0;
	foreach ($file as $row) {
		$count++;
		if ($count == 1) continue;
		if (!is_null($row[0])) array_push($array, $row);
	}

	return $array;
}

function split_PDF($pdf_path, $data, &$start, $pdf_type, $year, $month, $total_pages) {
	$memberCd	= $data[0];
	$pages		= (int)$data[count($data) - 2];
	$end 		= $start + $pages - 1;

	if ($end < $start) return array('error' => 1, 'error_message' => "分割終了ページ ({$end}) が開始ページ ({$start}) よりも小さいです。");
	
	$file_name 	= "{$memberCd}_{$year}{$month}_{$pdf_type}" . '.pdf';
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
	return json_encode(array('error' => 1, 'error_message' => $error -> getMessage()));
}

function write_error_log($proc_id, $memberCd, $error_msg, $year, $month, $pdf_type) {
	$log_path = LOG_DIR . 'error.log';

	$log = fopen($log_path, 'a');
	if ($log === FALSE) echo 'エラーログの書き込みに失敗しました。';

	$time = new DateTime();
	fputcsv($log, array($time -> format('Y-m-d H:i:s'), "{$year}_{$month}", $pdf_type, $proc_id, $memberCd, $error_msg, LOG_DIR . "{$year}/{$month}/{$proc_id}.log"));
	fclose($log);
}

function write_status_log($proc_id, $msg, $year, $month, $pdf_type) {
	$log_path = LOG_DIR . "{$year}/{$month}/{$pdf_type}_{$proc_id}_status.log";

	$log = fopen($log_path, 'w');
	if ($log === FALSE) write_error_log($proc_id, '', 'ステータスログの書き込みに失敗しました。', $year, $month, $pdf_type);

	$time = new DateTime();
	fputcsv($log, array($time -> format('Y-m-d\TH:i:s'), $msg));
	fclose($log);
}

