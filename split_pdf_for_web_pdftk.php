<?php

echo '<link rel="stylesheet" href="style.css?v=0.0.5">' . "\n";

ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Tokyo');

$pdf_type	= $_POST['pdf_type'];
$parallel	= 'multi';
$page_count	= $_POST['page_count'];
$year		= $_POST['year'];
$month		= str_pad($_POST['month'], 2, 0, STR_PAD_LEFT);
$mode		= NULL;
$file_pdf	= array();
$file_xsv	= array();

$time_1 = new DateTime();
echo '[' . $time_1->format('Y-m-d H:i:s') . '] スクリプトを開始します。'. "<br><br>\n";

define('DOMAIN', 		$_SERVER['HTTP_HOST']);
define('PAGE_PATH', 	makePagePath(DOMAIN, $_SERVER['REQUEST_URI']));
define('CURRENT_DIR', 	__DIR__);
define('DATA_BASE', 	"/data/{$pdf_type}/{$year}_{$month}-{$time_1->format('Ymd_His')}");
define('DATA_DIR', 		CURRENT_DIR . DATA_BASE . '/source/');
define('RESULT_DIR', 	CURRENT_DIR . DATA_BASE . '/members/');
define('LOG_BASE', 		CURRENT_DIR . "/log/");
define('LOG_DIR', 		LOG_BASE . "{$pdf_type}/");

define('PDFtk_PATH', 	'/usr/local/bin/pdftk');
define('PHP_PATH', 		'/usr/local/bin/php');
// define('PDFtk_PATH', 	'/usr/bin/pdftk');
// define('PHP_PATH', 		'/usr/bin/php');

// echo PAGE_PATH  . "<br>\n";
// echo DATA_DIR . "<br>\n";
// echo RESULT_DIR . "<br><br>\n";


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

if (file_exists(LOG_DIR)) {
	echo LOG_DIR . "は既に存在します。<br>\n";
} else {
    if (mkdir(LOG_DIR, 0777, true)) {
        chmod(LOG_DIR, 0777);
        echo LOG_DIR . "の作成に成功しました。<br>\n";
    } else {
		echo json_encode(array('error' => 1, 'error_message' =>  LOG_DIR . "の作成に失敗しました。"), JSON_UNESCAPED_UNICODE);
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

@ob_flush();
@flush();

// TSVを読み込み
$member_data = load_csv_data($file_xsv, $mode);

$current_start_page = 1;

$split_member_data = splitMemberData($member_data, $page_count);
$file_list = array();
$split_file_data;

for ($i = 0; $i < count($split_member_data); $i++) {
	$split_file_data = $split_member_data[$i];
	$pages = 0;
	echo "\n<br>--------------------------------------------<br>\n";
	// echo count($split_file_data) . "<br>\n";
	foreach ($split_file_data as $key => $sub_array) {
		$pages = $pages + $sub_array[count($sub_array) - 2];
		// foreach ($sub_array as $key2 => $value) {
		// 	echo "Key => {$key2}, value => {$value}<br>\n";
		// }
		// echo $sub_array[count($sub_array) - 2] . "<br>\n";
		// echo "\n<br>-----------------<br>\n";
	}

	// echo "Pages => {$pages}<br>\n";

	$split_data = array("tmp_{$time_1->format('Ymd_His')}_{$i}", $pages);

	$result = split_tmp_PDF($file_pdf['path'], $split_data, $current_start_page, $split_file_data);

	if ($result['error']) {
		echo 'エラー : ' . $result['error_message'] . "<br>\n";
		echo "--------------------------------------------<br>\n";
		echo "エラーにより処理が中断しました。\n";
		return;
	} else {
		array_push($file_list, $result['data']);
		echo  $result['data']['pdf'] . "<br>\n";
		echo  $result['data']['csv'] . "<br>\n";
	}

	echo "--------------------------------------------<br>\n";
	@ob_flush();
	@flush();
}

// print_r($file_list);

// $time 		= new DateTime();
for ($i = 0; $i < count($file_list); $i++) {
	$file_data		= $file_list[$i];
	$output 		= null;
	$php_path		= PHP_PATH;
	$file_pdf_path	= $file_data['pdf'];
	$file_xsv_path	= $file_data['csv'];
	$time_str 		= $time_1->format('Ymd_His') . '_' . $i;
	$log_dir		= LOG_DIR;

	$cmd = "nohup {$php_path} ./split_pdf_for_commandline.php '{$file_pdf_path}' '{$file_xsv_path}' '{$pdf_type}' 'cvs' '{$year}' '{$month}' '$time_str' >> {$log_dir}{$pdf_type}-{$time_str}.log 2>&1 &";

	exec($cmd, $output);
	echo "\n--------------------------------------------<br>\n";
	// echo $cmd . "<br>\n";
	echo "バックグラウンドで処理を実行中です。({$log_dir}{$pdf_type}-{$time_str}.log)<br>\n";

	@ob_flush();
	@flush();
}

echo "\n--------------------------------------------<br>\n";
echo "<a href='./info_iframe.php'>実行状況はこちらから確認できます。</a>";

// echo "<script>\n";
// echo "setTimeout(function () {\n";
// echo "	location.href = './info_iframe.php';\n";
// echo "}, 2000);\n";
// echo "</script>\n";






// 以下function


function load_csv_data($file, $type = 'csv') {
	// echo "<br>------------------------<br>\n";
	// echo "<a href='{$uri}' target='_blank'>PDFファイル分割データ用TSV/CSVファイル</a><br>\n";

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

function split_tmp_PDF($pdf_path, $data, &$start, $split_file_data) {
	$prefix		= $data[0];
	$pages		= (int)$data[1];
	$end 		= $start + $pages - 1;
	
	if ($end < $start) return array('error' => 1, 'error_message' => "分割終了ページ ({$end}) が開始ページ ({$start}) よりも小さいです。");
	
	$file_name 		= "{$prefix}-{$start}_{$end}" . '.pdf';
	$file_name_csv 	= "{$prefix}-{$start}_{$end}" . '.csv';
	$save_dir 		= DATA_DIR;
	$pdftk			= PDFtk_PATH;

	$cmd = "{$pdftk} {$pdf_path} dump_data | grep NumberOfPages | sed 's/[^0-9]*//'";
	exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);

	$pdf_total_pages = (int)$output[0];
	if ($pdf_total_pages < $end) return array('error' => 1, 'error_message' => "分割終了ページ ({$end}) が総ページ数 ({$pdf_total_pages}) よりも大きいです。");

	$cmd = "{$pdftk} {$pdf_path} cat {$start}-{$end} output {$save_dir}{$file_name} >> ./log/pdftk.log 2>&1";
	// echo $cmd . "<br>\n";
	exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);

	

	$csv = fopen($save_dir . $file_name_csv, 'w');
	if ($csv === FALSE) return array('error' => 1, 'error_message' => "CSVファイルの書き込みに失敗しました。");

	fputcsv($csv, array('header_1', 'header_2', 'header_3'));
	foreach($split_file_data as $data) {
		fputcsv($csv, $data);
	}
	fclose($csv);

	$start = $end + 1;

	if ($result == 0) {
		return array('error' => 0, 'data' => array('pdf' => "{$save_dir}{$file_name}", 'csv' => "{$save_dir}{$file_name_csv}"));
	} else {
		return array('error' => 1, 'error_message' => "PDFの分割に失敗しました。");;
	}
}

function splitMemberData($data, $num) {
	$split_array = array();
	$loop_step = 1;
	$loop_limit = $num * $loop_step;

	for ($i = 0; $i < count($data); $i++) {
		if ($i >= $loop_limit) {
			$loop_step = $loop_step + 1;
			$loop_limit = $num * $loop_step;
		}
		if (empty($split_array[$loop_step - 1])) $split_array[$loop_step - 1] = array();

		array_push($split_array[$loop_step - 1], $data[$i]);
	}


	return $split_array;
}

function makePagePath($domain, $path) {
	$path_str = '';

	$path_array = explode('/', $path);
	
	for($i = 1; $i < count($path_array) - 1; $i++) {
		$path_str = $path_str . '/' . $path_array[$i];
	}

	// return "//{$domain}{$path_str}";
	return "{$path_str}";
}

function echo_error($error) {
	return json_encode(array('error' => 1, 'error_message' => $error->getMessage()));
}
