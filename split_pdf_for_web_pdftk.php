<style>
	html {
		font-size: 10px;
	}

	body {
		margin: 0;
		padding: 1rem;
		font-size: 1.3rem;
		font-weight: 300;
		line-height: 1.8;
		font-family: -apple-system,
			BlinkMacSystemFont,
			'Segoe UI',
			'Hiragino Kaku Gothic ProN',
			'Hiragino Sans',
			'BIZ UDPGothic',
			Meiryo,
			Roboto,
			sans-serif;
	}

	h1 {
		font-size: 1.8rem;
		font-weight: 600;
		padding: 0.5em .8em;
		margin: 0;
		background-color: #e7e7e7;
	}

	h2 {
		font-size: 1.5rem;
		font-weight: 600;
		padding: 1em 0;
		margin: 0;
		border-bottom: 1px solid #e3e3e3;
	}

	p {
		font-size: 1.4rem;
		font-weight: 300;
		line-height: 1.8;
	}

	.important {
		font-size: 1.5rem;
		line-height: 1.5;
		color: #bc140b;
		font-weight: 600;
	}

	a {
		font-size: 1.6rem;
		display: inline-block;
		font-weight: 600;
		color: rgb(27, 92, 166);
	}
</style>

<?php

// echo '<link rel="stylesheet" href="style.css?v=0.0.6">' . "\n";

require_once './setting.php';
require_once './utils.php';

$pdf_type	= $_POST['pdf_type'];
$parallel	= 'multi';
$page_count	= $_POST['page_count'];
$year		= $_POST['year'];
$month		= str_pad($_POST['month'], 2, 0, STR_PAD_LEFT);
$mode		= NULL;
$file_pdf	= array();
$file_xsv	= array();

$time_1 = new DateTime();

echo '<main>' . "\n";
echo '<h1>[' . $time_1->format('Y-m-d H:i:s') . '] スクリプトを開始します。</h1>' . "\n";
echo "<p class='important'>すべてのバックグラウンド処理が開始するまでページを閉じたりリロードをしないようお願いします。</p><br>\n";

define('DOMAIN', 		$_SERVER['HTTP_HOST']);
define('PAGE_PATH', 	makePagePath(DOMAIN, $_SERVER['REQUEST_URI']));
define('DATA_BASE', 	"/{$pdf_type}/{$year}_{$month}-{$time_1->format('Ymd_His')}");
define('DATA_DIR', 		FILES_DIR . DATA_BASE . '/source/');
define('RESULT_DIR', 	FILES_DIR . DATA_BASE . '/members/');
define('LOG_BASE', 		CURRENT_DIR . "/log/");
define('LOG_DIR', 		LOG_BASE . "{$pdf_type}/");






if (file_exists(DATA_DIR)) {
	echo DATA_DIR . "は既に存在します。<br>\n";
} else {
    if (mkdir(DATA_DIR, 0777, true)) {
        chmod(DATA_DIR, 0777);
        echo DATA_DIR . "の作成に成功しました。<br>\n";
    } else {
		echo DATA_DIR . "の作成に失敗しました。<br>\n";
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
		echo RESULT_DIR . "の作成に失敗しました。<br>\n";
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
		echo LOG_DIR . "の作成に失敗しました。<br>\n";
		return;
    }
}

@ob_flush();
@flush();

$proccess_log = set_process_log($time_1->format('Ymd_His'), $year, $month, $pdf_type);

echo "<br>------------------------------------------------<br>\n";
foreach ($_FILES as $key => $data) {
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

	@ob_flush();
	@flush();
}

if (empty($file_pdf) || empty($file_xsv)) {
	echo "アップロードファイルが不足しています。<br>\n";
	return;
}

echo "------------------------------------------------<br><br>\n";
echo "作業用分割ファイルの作成を開始します。<br>\n";
echo "(この作業には数分かかる可能性があります。)<br><br>\n";
echo "------------------------------------------------<br>\n";

@ob_flush();
@flush();

// TSVを読み込み
$member_data = load_csv_data($file_xsv['path'], $mode);

$current_start_page = 1;

$split_member_data = splitMemberData($member_data, $page_count);
$file_list = array();
$split_file_data;

for ($i = 0; $i < count($split_member_data); $i++) {
	$split_file_data = $split_member_data[$i];
	$pages = 0;
	
	foreach ($split_file_data as $key => $sub_array) {
		$pages = $pages + $sub_array[count($sub_array) - 2];
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
		echo  "作業用分割ファイル " . ((int)$i + 1) . " PDFを作成しました。<br>\n";
		echo "------------------------------------------------<br>\n";
	}

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
	echo "\n------------------------------------------------<br>\n";
	// echo $cmd . "<br>\n";
	echo "作業用分割ファイル " . ((int)$i + 1) . " PDFのページ分割処理をバックグラウンドで開始します。<br>({$file_pdf_path})<br>\n";

	@ob_flush();
	@flush();
}

echo "\n--------------------------------------------<br><br>\n";
echo "<p class='important'>すべてのバックグラウンド処理を開始しました。<br>ページを閉じたりリロードしても問題ありません。</p>\n";
echo "<a href='./info_iframe.php'>実行状況はこちらから確認できます。</a>";
echo '</main>' . "<br>\n";
// echo "<script>\n";
// echo "setTimeout(function () {\n";
// echo "	location.href = './info_iframe.php';\n";
// echo "}, 2000);\n";
// echo "</script>\n";






// 以下function

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

	$cmd = "{$pdftk} {$pdf_path} cat {$start}-{$end} output {$save_dir}{$file_name} >> " . LOG_BASE . "pdftk.log 2>&1";
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
