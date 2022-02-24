<?php
require_once 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

$pdf_type	= $_POST['pdf_type'];
$year		= $_POST['year'];
$month		= str_pad($_POST['month'], 2, 0, STR_PAD_LEFT);
$mode		= NULL;
$file_pdf	= array();
$file_xsv	= array();

define('DOMAIN', 		$_SERVER['HTTP_HOST']);
define('PAGE_PATH', 	makePagePath(DOMAIN, $_SERVER['REQUEST_URI']));
define('CURRENT_DIR', 	__DIR__);
define('DATA_BASE', 	'/data/');
define('RESULT_BASE', 	'/result/');
define('DATA_DIR', 		CURRENT_DIR . DATA_BASE . "upload/{$year}/{$month}/");
define('DATA_PATH', 	PAGE_PATH . DATA_BASE . "upload/{$year}/{$month}/");
define('RESULT_DIR', 	CURRENT_DIR . RESULT_BASE . "{$year}/{$month}/");
define('RESULT_PATH', 	PAGE_PATH . RESULT_BASE . "{$year}/{$month}/");

echo PAGE_PATH  . "<br>\n";
echo DATA_DIR . "<br>\n";
echo RESULT_DIR . "<br><br>\n";
echo DATA_PATH. "<br>\n";
echo RESULT_PATH. "<br><br>\n";


if (file_exists(DATA_DIR)) {
	echo DATA_DIR . "は既に存在します。<br>\n";
} else {
    if (mkdir(DATA_DIR, 0777, true)) {
        chmod(DATA_DIR, 0777);
        echo DATA_DIR . "の作成に成功しました。<br>\n";
    } else {
		echo json_encode(array('error' => 1, 'error_message' =>  DATA_DIR . "の作成に失敗しました。"), JSON_UNESCAPED_UNICODE);
		return;
    }
}

if (file_exists(RESULT_DIR)) {
	echo RESULT_DIR . "は既に存在します。<br>\n";
} else {
    if (mkdir(RESULT_DIR, 0777, true)) {
        chmod(RESULT_DIR, 0777);
        echo RESULT_DIR . "の作成に成功しました。<br>\n";
    } else {
		echo json_encode(array('error' => 1, 'error_message' =>  RESULT_DIR . "の作成に失敗しました。"), JSON_UNESCAPED_UNICODE);
		return;
    }
}
echo "<br>------------------------------------------------<br>\n";
foreach ($_FILES as $key => $data) {
	echo "key name : {$key}<br>\n";
	echo "file name : {$data['name']}<br>\n";
	echo "file type : {$data['type']}<br>\n";
	echo "file tmp_name : {$data['tmp_name']}<br>\n";

	$tempfile = $data['tmp_name'];
	$filedata = array(
		'path' => DATA_DIR . $data['name'],
		'name' => $data['name']
	);

	if (is_uploaded_file($tempfile)) {
		if ( move_uploaded_file($tempfile, $filedata['path'])) {
			echo $filedata['path'] . "をアップロードしました。<br>\n";

			if 		($key == 'PDF') $file_pdf = $filedata;
			elseif	($key == 'xSV') {
				$file_xsv = $filedata;
				$mode = $data['type'] == 'text/csv' ? 'csv' : 'tsv';
			}
		} else {
			echo "ファイルをアップロードできません。<br>\n";
		}
	}
	echo "------------------------------------------------<br>\n";
}

if (empty($file_pdf) || empty($file_xsv)) {
	echo "アップロードファイルが不足しています。<br>\n";
	return;
}

// TSVを読み込み
$member_data = openMemberData($file_xsv, $mode);

$uri = DATA_PATH . $file_pdf['name'];
echo "<a href='{$uri}' target='_blank'>分割元PDFファイル</a>\n";

$current_start_page = 1;
for ($count = 0; $count < count($member_data); $count++) {
	echo "<br>------------------------<br>\n";
	
	$file_link = split_PDF($file_pdf['path'], $member_data[$count], $current_start_page, $pdf_type, $year, $month);

	echo $file_link;
}

function openMemberData($file, $type = 'csv') {
	$uri = DATA_PATH . $file['name'];
	echo "<br>------------------------<br>\n";
	echo "<a href='{$uri}' target='_blank'>PDFファイル分割データ用TSV/CSVファイル</a><br>\n";

	$file = new SplFileObject($file['path'], 'r');
	$file->setFlags(SplFileObject::READ_CSV);
	if ($type == 'tsv') $file->setCsvControl("\t");

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

	if ($end < $start) {
		return json_encode(array('error' => 1, 'error_message' => "終了ページ ({$end}) が開始ページ ({$start}) よりも小さいです。"), JSON_UNESCAPED_UNICODE);
	}

	$pdf 			= new Fpdi();
	
	try {
		$total_pages 	= $pdf -> setSourceFile($pdf_path);	// PDFファイルを読み込む

		if ($total_pages < $end) {
			return json_encode(array('error' => 1, 'error_message' => "終了ページ ({$end}) が総ページ数 ({$total_pages}) よりも大きいです。"), JSON_UNESCAPED_UNICODE);
		}
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

	$uri = RESULT_PATH . $file_name;
	$start = $end + 1;

	return "<a href='{$uri}' target='_blank'>" . str_replace('//', '', $uri) . "</a>\n";
}

function makePagePath($domain, $path) {
	$path_str = '';

	$path_array = explode('/', $path);
	
	for($i = 1; $i < count($path_array) - 1; $i++) {
		$path_str = $path_str . '/' . $path_array[$i];
	}

	return "//{$domain}{$path_str}";
}

function echo_error($error) {
	return json_encode(array('error' => 1, 'error_message' => $error -> getMessage()));
}
