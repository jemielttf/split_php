<?php

date_default_timezone_set('Asia/Tokyo');

$pdf_type	= $_POST['pdf_type'];
$year		= $_POST['year'];
$month		= str_pad($_POST['month'], 2, 0, STR_PAD_LEFT);
$mode		= NULL;
$file_pdf	= array();
$file_xsv	= array();

define('CURRENT_DIR', 	__DIR__);
define('DATA_BASE', 	'/data/');
define('RESULT_BASE', 	'/result/');
define('DATA_DIR', 		CURRENT_DIR . DATA_BASE . "upload/{$year}/{$month}/");
define('RESULT_DIR', 	CURRENT_DIR . RESULT_BASE . "{$year}/{$month}/");


// echo DATA_DIR . "<br>\n";
// echo RESULT_DIR . "<br><br>\n";

echo "このスクリプトはバックグラウンドで処理を実行します。<br>\n";

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
	// echo "key name : {$key}<br>\n";
	// echo "file name : {$data['name']}<br>\n";
	// echo "file type : {$data['type']}<br>\n";
	// echo "file tmp_name : {$data['tmp_name']}<br>\n";

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
				$file_xsv 	= $filedata;
				$mode 		= $data['type'] == 'text/csv' || $data['type'] == 'application/vnd.ms-excel' ? 'csv' : 'tsv';
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

$file_pdf_path = $file_pdf['path'];
$file_xsv_path = $file_xsv['path'];
$time = new DateTime();
$time_str = $time -> format('Ymd_His');

$output = null;
// 開発ローカル
$cmd = "nohup /usr/local/bin/php ./split_pdf_for_commandline.php '{$file_pdf_path}' '{$file_xsv_path}' '{$pdf_type}' '{$mode}' '{$year}' '{$month}' >> ./log/bat_{$time_str}.log 2>&1 &";
// TTC
// $cmd = "nohup /usr/bin/php ./split_pdf_for_commandline.php '{$file_pdf_path}' '{$file_xsv_path}' '{$pdf_type}' '{$mode}' '{$year}' '{$month}' >> ./log/bat_{$time_str}.log 2>&1 &";
echo $cmd . "<br>\n";

exec($cmd, $output);

echo "<br><br>バックグラウンド処理プロセスを起動しました。<br><br>\n";