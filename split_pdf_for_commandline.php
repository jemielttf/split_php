<?php

ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Tokyo');

require_once 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

$file_pdf	= $argv[1];
$file_xsv	= $argv[2];
$pdf_type	= $argv[3];
$mode		= $argv[4];
$year		= $argv[5];
$month		= $argv[6];

$time_1 = new DateTime();
echo '[' . $time_1 -> format('Y-m-d H:i:s') . '] スクリプトを開始します。'. "\n\n";

define('CURRENT_DIR', 	__DIR__);
define('DATA_BASE', 	'/data/');
define('RESULT_BASE', 	'/result/');
define('DATA_DIR', 		CURRENT_DIR . DATA_BASE . "upload/{$year}/{$month}/");
define('RESULT_DIR', 	CURRENT_DIR . RESULT_BASE . "{$year}/{$month}/");

// echo DATA_DIR . "<br>\n";
// echo RESULT_DIR . "<br><br>\n";

for ($i = 1; $i < count($argv); $i++) {
	echo $argv[$i];
	echo "\n";
}
echo "----------------------------\n";

if (empty($file_pdf) || empty($file_xsv)) return;

// TSVを読み込み
$member_data = openMemberData($file_xsv, $mode);

$current_start_page = 1;
for ($count = 0; $count < count($member_data); $count++) {
	$file_name = split_PDF($file_pdf, $member_data[$count], $current_start_page, $pdf_type, $year, $month);

	echo DATA_DIR . $file_name . "\n";
	echo '----------------' . "\n";
}

echo "\n\n------------------------\n";
$time_2 = new DateTime();
$diff = $time_2 -> diff($time_1);
echo '[' . $time_2 -> format('Y-m-d H:i:s')  . '] スクリプトは終了しました。'. "\n";
echo '処理にかかった時間は' . $diff -> format('%h:%i:%s') . '秒です。' . "\n";

// 以下function


function openMemberData($file_xsv, $type = 'csv') {
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

function split_PDF($pdf_path, $data, &$start, $pdf_type, $year, $month) {
	$memberCd	= $data[0];
	$pages		= (int)$data[count($data) - 2];
	$end 		= $start + $pages - 1;

	if ($end < $start) return;

	$pdf 				= new Fpdi();
	
	try {
		$total_pages 	= $pdf -> setSourceFile($pdf_path);	// PDFファイルを読み込む
		if ($total_pages < $end) return;
	} catch (Exception $error) {
		return echo_error($error);
	}

	for ($i =  $start; $i <= $end; $i++) {						
		try {
			$templateId = $pdf -> importPage($i);								// 該当ページをテンプレートとしてインポート
		} catch (Exception $error) {
			return echo_error($error);
		}
	
		$pdf -> AddPage();														// 出力用のページを一つ追加
		$pdf -> useTemplate($templateId, ['adjustPageSize' => true]);			// 出力用のページに、テンプレートを適用する
		// $pdf -> useTemplate($templateId, null, null, 0, 0, true);				// 旧バージョン用
	}
	
	$file_name = "{$memberCd}_{$year}{$month}_{$pdf_type}_{$start}-{$end}" . '.pdf';
	
	
	/**
	 * I: send the file inline to the browser. The PDF viewer is used if available.
	 * D: send to the browser and force a file download with the name given by name.
	 * F: save to a local file with the name given by name (may include a path).
	 * S: return the document as a string.
	 */
	$pdf -> Output(RESULT_DIR . $file_name, 'F');

	$start = $end + 1;
	
	return $file_name;
}

function echo_error($error) {
	return json_encode(array('error' => 1, 'error_message' => $error -> getMessage()));
}
